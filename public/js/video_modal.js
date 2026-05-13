/**
 * video_modal.js — Gestion de la modale vidéo YouTube pour la page KNN
 *
 * Comportement :
 *   - Clic sur .btn-video → ouvre la modale avec l'URL dans l'iframe
 *   - Clic sur .modal-close ou sur l'overlay → ferme la modale + vide l'iframe
 *     (essentiel pour couper la vidéo YouTube)
 *   - Touche Escape → ferme aussi la modale
 */

(function () {
    'use strict';

    const modal   = document.getElementById('video-modal');
    const iframe  = document.getElementById('video-iframe');
    const overlay = document.getElementById('video-modal-overlay');
    const closeBtn = document.getElementById('video-modal-close');

    if (!modal || !iframe) return; // Page sans modale, on sort

    // ── Ouverture ──────────────────────────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-video');
        if (!btn) return;

        const url = btn.dataset.videoUrl;
        if (!url || url === '') return;

        // Ajouter ?autoplay=1&rel=0 pour lancer automatiquement et éviter les vidéos suggérées
        iframe.src = url + (url.includes('?') ? '&' : '?') + 'autoplay=1&rel=0';
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden'; // Bloquer le scroll fond de page
    });

    // ── Fermeture (mutualisée) ─────────────────────────────────────────────────
    function closeModal() {
        modal.classList.remove('is-open');
        iframe.src = '';                        // ← coupe la vidéo YouTube
        document.body.style.overflow = '';
    }

    if (closeBtn)  closeBtn.addEventListener('click',  closeModal);
    if (overlay)   overlay.addEventListener('click',   closeModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

})();
