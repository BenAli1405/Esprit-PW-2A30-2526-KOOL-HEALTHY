(function () {
    'use strict';

    /**
     * Récupère l'URL ngrok si elle est disponible
     * ngrok expose une API locale sur http://127.0.0.1:4040/api/tunnels
     */
    function getNgrokUrl() {
        return fetch('get_ngrok_url.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.ngrok_url) {
                console.log('[QR Code] ngrok actif :', data.ngrok_url);
                return data.ngrok_url;
            }
            return null;
        })
        .catch(error => {
            console.log('[QR Code] ngrok non disponible:', error);
            return null;
        });
    }

    window.generateRestaurantQRCode = function () {

        // ── 1. Lecture des curseurs ──────────────────────────────────────────
        var prixVal    = document.getElementById('mo-weight-budget')
                         ? parseInt(document.getElementById('mo-weight-budget').value,   10) : 50;
        var ecoVal     = document.getElementById('mo-weight-eco')
                         ? parseInt(document.getElementById('mo-weight-eco').value,      10) : 50;
        var plaisirVal = document.getElementById('mo-weight-plaisir')
                         ? parseInt(document.getElementById('mo-weight-plaisir').value,  10) : 50;
        var tempsVal   = document.getElementById('mo-weight-rapidite')
                         ? parseInt(document.getElementById('mo-weight-rapidite').value, 10) : 50;

        // ── 2. Objectif & végétarien ─────────────────────────────────────────
        var objectif = 'perte-poids';
        var objEl = document.querySelector('select[name="objectif"]');
        if (objEl) objectif = objEl.value;

        var isVege = false;
        var prefEl = document.querySelector('input[name="preference"]');
        if (prefEl) {
            var p = prefEl.value.toLowerCase();
            if (p.indexOf('veg') !== -1 || p.indexOf('vég') !== -1) isVege = true;
        }

        // ── 3. Récupérer l'URL (ngrok ou IP locale) ──────────────────────────
        var qrImg  = document.getElementById('qrCodeImage');
        var qrLink = document.getElementById('qrCodeLink');
        var modal  = document.getElementById('qrCodeModal');
        var urlStatus = document.getElementById('urlStatus');

        if (!qrImg || !qrLink || !modal) {
            alert('Erreur : éléments de la modale QR Code introuvables.');
            return;
        }

        // Afficher un indicateur de chargement
        qrImg.src = '';
        qrImg.alt = 'Chargement...';
        if (qrLink) qrLink.innerHTML = '⏳ Chargement...';

        // Récupérer ngrok et générer l'URL cible
        getNgrokUrl().then(ngrokUrl => {
            var SERVER_HOST;
            var urlType = 'local';

            if (ngrokUrl) {
                // Utiliser ngrok si disponible
                SERVER_HOST = ngrokUrl.replace(/^https?:\/\//, ''); // Enlever https://
                urlType = 'public';
            } else {
                // Utiliser l'IP locale (APP_SERVER_IP injecté par PHP)
                var SERVER_IP  = (window.APP_SERVER_IP && window.APP_SERVER_IP !== '127.0.0.1' && window.APP_SERVER_IP !== '::1')
                                 ? window.APP_SERVER_IP
                                 : '192.168.1.16';
                SERVER_HOST = SERVER_IP;
                urlType = 'local';
            }

            var RESTO_PATH = '/planBD/planBD/planBD/plan/restaurants.php';

            var targetUrl = 'http://' + SERVER_HOST + RESTO_PATH
                + '?p_prix='     + prixVal
                + '&p_eco='      + ecoVal
                + '&p_plaisir='  + plaisirVal
                + '&p_temps='    + tempsVal
                + '&objectif='   + encodeURIComponent(objectif)
                + '&vegetarien=' + (isVege ? '1' : '0');

            // ── 4. Génération du QR Code (avec fallback) ─────────────────────
            var qrApiUrlPrimary = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='
                                 + encodeURIComponent(targetUrl);
            var qrApiUrlFallback = 'https://quickchart.io/qr?size=250&text='
                                  + encodeURIComponent(targetUrl);

            // ── 5. Mise à jour de la modale ──────────────────────────────────
            qrImg.onerror = function () {
                // If primary provider fails, try fallback provider once.
                if (qrImg.getAttribute('data-fallback-tried') !== '1') {
                    qrImg.setAttribute('data-fallback-tried', '1');
                    qrImg.src = qrApiUrlFallback;
                    return;
                }
                // Last resort: keep the direct clickable link even if image fails.
                qrImg.style.display = 'none';
                if (qrLink) {
                    qrLink.innerHTML = '❌ QR indisponible. Ouvrir directement le lien restaurants';
                }
            };
            qrImg.setAttribute('data-fallback-tried', '0');
            qrImg.style.display = 'block';
            qrImg.src  = qrApiUrlPrimary;
            qrImg.alt  = 'QR Code restaurants';
            qrLink.href = targetUrl;
            
            // Afficher un badge d'état
            var badge = urlType === 'public' 
                ? '🌐 <span style="background:#4caf50; color:#fff; padding:2px 6px; border-radius:3px; font-size:0.75rem; margin-right:4px;">ACCESSIBLE PARTOUT</span>'
                : '📱 <span style="background:#ff9800; color:#fff; padding:2px 6px; border-radius:3px; font-size:0.75rem; margin-right:4px;">RÉSEAU LOCAL</span>';
            
            qrLink.innerHTML = badge + ' <span style="text-decoration:underline;">🔗 Cliquer ici pour voir les restaurants</span>';

            // Afficher l'URL dans la console
            console.log('[QR Code] Type d\'URL :', urlType);
            console.log('[QR Code] URL générée :', targetUrl);

            modal.style.display = 'flex';
        });
    };

})();
