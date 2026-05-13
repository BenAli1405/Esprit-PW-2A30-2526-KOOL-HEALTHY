document.addEventListener('DOMContentLoaded', function() {
    // Gerer les boutons J'aime
    const likeButtons = document.querySelectorAll('.action-btn.like');
    
    likeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
            const count = this.querySelector('span');
            count.textContent = this.classList.contains('active') ? 
                parseInt(count.textContent) + 1 : 
                parseInt(count.textContent) - 1;
        });
    });

    // Message de succes/erreur
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        alert('Recette publiee avec succes!');
    }
    if (urlParams.get('error')) {
        alert('Une erreur est survenue lors de la publication.');
    }
});
