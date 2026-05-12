/**
 * Système de suivi (Follow/Unfollow)
 * Refait de zéro avec gestion correcte du scope et de la classe CSS
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Follow system initialized');
    attachFollowListeners();
});

function attachFollowListeners() {
    const buttons = document.querySelectorAll('.follow-btn[data-user-id]');
    console.log('📍 Boutons trouvés:', buttons.length);
    
    buttons.forEach((button, index) => {
        console.log(`  Bouton ${index}: "${button.textContent.trim()}" (User ${button.dataset.userId}), Classe: "${button.className}"`);
        
        // Click handler
        button.addEventListener('click', handleFollowClick);
        
        // Hover handlers
        button.addEventListener('mouseenter', handleHoverEnter);
        button.addEventListener('mouseleave', handleHoverLeave);
    });
}

function handleFollowClick(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const button = event.currentTarget;
    const userId = button.dataset.userId;
    const isFollowing = button.classList.contains('following');
    const action = isFollowing ? 'unfollow' : 'follow';
    
    console.log(`\n=== CLICK on User ${userId} ===`);
    console.log('Current state:', isFollowing ? 'FOLLOWING' : 'NOT FOLLOWING');
    console.log('Action to send:', action);
    
    // Disable button and show loading
    button.disabled = true;
    button.textContent = '⏳';
    
    const url = '/Recettes/CONTROLLER/UserController.php?action=' + action + '&format=json';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'user_id=' + encodeURIComponent(userId)
    })
    .then(response => {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(data => {
        console.log('📡 Server response:', data);
        
        if (data && typeof data.success === 'boolean') {
            const newFollowingState = data.is_following;
            console.log('✅ Updating UI to:', newFollowingState ? 'FOLLOWING' : 'NOT FOLLOWING');

            if (action === 'unfollow' && !newFollowingState) {
                const card = button.closest('.account-card');
                if (card) {
                    card.remove();
                    return;
                }

                const row = button.closest('.follower-item');
                if (row) {
                    row.remove();
                    return;
                }

                const suggestion = button.closest('.suggest-item');
                if (suggestion) {
                    suggestion.remove();
                    return;
                }
            }

            updateButtonDisplay(button, newFollowingState);
        } else {
            throw new Error('Invalid response format');
        }
    })
    .catch(error => {
        console.error('❌ Error:', error.message);
        button.textContent = 'Suivre';
        button.classList.remove('following');
        alert('Erreur: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
    });
}

function updateButtonDisplay(button, isFollowing) {
    if (isFollowing) {
        button.textContent = '✓ Suivi';
        button.classList.add('following');
        console.log('✅ Button: "✓ Suivi" + class "following"');
    } else {
        button.textContent = 'Suivre';
        button.classList.remove('following');
        console.log('✅ Button: "Suivre" - class "following" removed');
    }
}

function handleHoverEnter(event) {
    const button = event.currentTarget;
    if (button.classList.contains('following')) {
        button.textContent = 'Ne plus suivre';
        button.classList.add('hover-unfollow');
    }
}

function handleHoverLeave(event) {
    const button = event.currentTarget;
    if (button.classList.contains('hover-unfollow')) {
        button.textContent = '✓ Suivi';
        button.classList.remove('hover-unfollow');
    }
}
