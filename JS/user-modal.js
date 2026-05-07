/**
 * Gestion de la modal utilisateur (Follow + Block)
 */

// Attacher les listeners aux noms d'utilisateurs cliquables
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔔 User modal system initialized');
    
    // Attacher le listener aux noms d'utilisateurs
    document.addEventListener('click', function(e) {
        const clickedUser = e.target.closest('.username-clickable');
        if (clickedUser) {
            e.preventDefault();
            const userId = clickedUser.dataset.userId;
            const userName = clickedUser.dataset.userName || clickedUser.textContent.trim();
            const userAvatar = clickedUser.dataset.userAvatar || '';
            openUserActionModal(userId, userName, userAvatar);
        }
    });

    document.addEventListener('click', function(e) {
        const unfollowBtn = e.target.closest('.btn-unfollow');
        if (unfollowBtn) {
            e.preventDefault();
            handleDirectUnfollow(unfollowBtn);
        }
    });

    document.addEventListener('click', function(e) {
        const unblockBtn = e.target.closest('.btn-unblock');
        if (unblockBtn) {
            e.preventDefault();
            handleDirectUnblock(unblockBtn);
        }
    });
});

async function openUserActionModal(userId, userName, userAvatar = '') {
    if (!userId) return;
    
    console.log('Opening modal for user:', userId);
    
    const modal = document.getElementById('userActionModal');
    if (!modal) {
        console.error('Modal element not found');
        return;
    }
    
    // Mettre à jour les infos utilisateur dans la modal
    const nameElem = document.getElementById('modalUserName');
    if (nameElem) nameElem.textContent = userName;
    const emailElem = document.getElementById('modalUserEmail');
    if (emailElem) emailElem.textContent = '';
    const avatarElem = document.getElementById('modalUserAvatar');
    if (avatarElem) {
        avatarElem.textContent = (userName || 'U').trim().charAt(0).toUpperCase();
        avatarElem.classList.remove('has-image');
        avatarElem.style.backgroundImage = '';
        avatarElem.innerHTML = '';
        if (userAvatar) {
            avatarElem.classList.add('has-image');
            avatarElem.style.backgroundImage = `url(${userAvatar})`;
            avatarElem.textContent = '';
        } else {
            avatarElem.textContent = (userName || 'U').trim().charAt(0).toUpperCase();
        }
    }
    
    // Récupérer les infos utilisateur
    try {
        const response = await fetch(`/Recettes/CONTROLLER/UserController.php?action=get_user&user_id=${userId}&format=json`);
        if (response.ok) {
            const data = await response.json();
            configureModalState(data, userId);
            if (data.nom && nameElem) {
                nameElem.textContent = data.nom;
            }
            if (avatarElem && data.avatar) {
                avatarElem.classList.add('has-image');
                avatarElem.style.backgroundImage = `url(${data.avatar})`;
                avatarElem.textContent = '';
            }
            if (data.email && emailElem) {
                emailElem.textContent = data.email;
            }
        }
    } catch (e) {
        console.error('Error fetching user data:', e);
    }
    
    // Afficher la modal
    modal.style.display = 'flex';
    
    // Configurer les boutons d'action
    configureModalButtons(userId);
}

function closeUserModal() {
    const modal = document.getElementById('userActionModal');
    if (modal) modal.style.display = 'none';
}

function configureModalButtons(userId) {
    const followBtn = document.getElementById('modalFollowBtn');
    const blockBtn = document.getElementById('modalBlockBtn');
    
    if (followBtn) {
        followBtn.onclick = function(e) {
            e.preventDefault();
            handleFollowAction(userId);
        };
    }
    
    if (blockBtn) {
        blockBtn.onclick = function(e) {
            e.preventDefault();
            handleBlockAction(userId);
        };
    }
}

function configureModalState(data, userId) {
    const followBtn = document.getElementById('modalFollowBtn');
    const blockBtn = document.getElementById('modalBlockBtn');

    if (!followBtn || !blockBtn) {
        return;
    }

    if (data.is_self) {
        followBtn.textContent = 'C\'est vous';
        followBtn.disabled = true;
        followBtn.classList.remove('following');

        blockBtn.textContent = 'Non disponible';
        blockBtn.disabled = true;
        blockBtn.classList.remove('btn-danger');
        return;
    }

    followBtn.disabled = !data.can_follow;
    followBtn.textContent = data.is_following ? '✓ Suivi' : 'Suivre';
    followBtn.classList.toggle('following', !!data.is_following);

    if (!data.can_follow && data.is_blocked_by) {
        followBtn.textContent = 'Bloqué par cet utilisateur';
    }

    blockBtn.disabled = false;
    blockBtn.textContent = data.is_blocked ? 'Débloquer' : 'Bloquer';
    blockBtn.classList.add('btn-danger');
}

async function handleFollowAction(userId) {
    console.log('Follow action for user:', userId);
    
    try {
        const response = await fetch(`/Recettes/CONTROLLER/UserController.php?action=toggle_follow&format=json`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user_id=${userId}`
        });
        
        const data = await response.json();
        if (data.success) {
            const btn = document.getElementById('modalFollowBtn');
            btn.textContent = data.is_following ? '✓ Suivi' : 'Suivre';
            btn.classList.toggle('following', data.is_following);
            console.log('✅ Follow action completed');

            // Update all follow buttons across the page so UI is consistent
            try {
                const selector = `.follow-btn[data-user-id="${userId}"]`;
                document.querySelectorAll(selector).forEach(b => {
                    b.textContent = data.is_following ? '✓ Suivi' : 'Suivre';
                    b.classList.toggle('following', data.is_following);
                });
            } catch (e) {
                console.warn('Could not sync follow buttons', e);
            }
        }
    } catch (error) {
        console.error('Error in follow action:', error);
        alert('Erreur: ' + error.message);
    }
}

async function handleBlockAction(userId) {
    console.log('Block action for user:', userId);
    
    try {
        const response = await fetch(`/Recettes/CONTROLLER/UserController.php?action=toggle_block&format=json`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `user_id=${userId}`
        });
        
        const data = await response.json();
        if (data.success) {
            const btn = document.getElementById('modalBlockBtn');
            btn.textContent = data.is_blocked ? 'Débloquer' : 'Bloquer';
            btn.classList.add('btn-danger');
            console.log('✅ Block action completed');

            const followBtn = document.getElementById('modalFollowBtn');
            if (data.is_blocked) {
                followBtn.textContent = 'Bloqué';
                followBtn.disabled = true;
                followBtn.classList.remove('following');
            } else {
                followBtn.disabled = false;
                followBtn.textContent = data.is_following ? '✓ Suivi' : 'Suivre';
                followBtn.classList.toggle('following', !!data.is_following);
            }

            // Remove posts and account-cards of the blocked user from the DOM so change is immediate
            try {
                const selector = `.username-clickable[data-user-id="${userId}"]`;
                document.querySelectorAll(selector).forEach(el => {
                    const post = el.closest('.post');
                    if (post) post.remove();
                    const card = el.closest('.account-card');
                    if (card) card.remove();
                });
            } catch (e) {
                console.warn('Could not remove posts/cards for blocked user', e);
            }

            // Auto-close after 1 second
            setTimeout(closeUserModal, 1000);
        }
    } catch (error) {
        console.error('Error in block action:', error);
        alert('Erreur: ' + error.message);
    }
}

async function handleDirectUnfollow(button) {
    const userId = button.dataset.userId;
    if (!userId) return;

    try {
        const response = await fetch('/Recettes/CONTROLLER/UserController.php?action=unfollow&format=json', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + encodeURIComponent(userId)
        });

        const data = await response.json();
        if (data.success) {
            const row = button.closest('.follower-item');
            if (row) {
                row.remove();
                return;
            }

            const card = button.closest('.account-card');
            if (card) {
                card.remove();
                return;
            }

            const suggestion = button.closest('.suggest-item');
            if (suggestion) {
                suggestion.remove();
            }
        } else {
            alert('Erreur: ' + (data.error || 'Impossible de ne plus suivre ce compte'));
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

async function handleDirectUnblock(button) {
    const userId = button.dataset.userId;
    if (!userId) return;

    try {
        const response = await fetch('/Recettes/CONTROLLER/UserController.php?action=unblock&format=json', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + encodeURIComponent(userId)
        });

        const data = await response.json();
        if (data.success) {
            const row = button.closest('.follower-item');
            if (row) {
                row.remove();
            }
        } else {
            alert('Erreur: ' + (data.error || 'Impossible de débloquer ce compte'));
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

// Fermer la modal en cliquant dehors
document.addEventListener('click', function(e) {
    const modal = document.getElementById('userActionModal');
    if (modal && e.target === modal) {
        closeUserModal();
    }
});

// Fermer avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserModal();
    }
});
