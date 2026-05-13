(function () {
    'use strict';

    var RESTO_PATH = '/integweb/VIEW/restaurants.php';

    window.generateRestaurantQRCode = function () {

        // ── 1. Lecture des curseurs ──────────────────────────────────
        var prixVal    = document.getElementById('mo-weight-budget')
                         ? parseInt(document.getElementById('mo-weight-budget').value,   10) : 50;
        var ecoVal     = document.getElementById('mo-weight-eco')
                         ? parseInt(document.getElementById('mo-weight-eco').value,      10) : 50;
        var plaisirVal = document.getElementById('mo-weight-plaisir')
                         ? parseInt(document.getElementById('mo-weight-plaisir').value,  10) : 50;
        var tempsVal   = document.getElementById('mo-weight-rapidite')
                         ? parseInt(document.getElementById('mo-weight-rapidite').value, 10) : 50;

        var objectif = 'perte-poids';
        var objEl = document.querySelector('select[name="objectif"]');
        if (objEl) objectif = objEl.value;

        var isVege = false;
        var prefEl = document.querySelector('input[name="preference"]');
        if (prefEl && prefEl.value) {
            var p = prefEl.value.toLowerCase();
            if (p.indexOf('veg') !== -1 || p.indexOf('vég') !== -1) isVege = true;
        }

        // ── 2. Éléments de la modale ─────────────────────────────────
        var qrImg  = document.getElementById('qrCodeImage');
        var qrLink = document.getElementById('qrCodeLink');
        var modal  = document.getElementById('qrCodeModal');

        if (!qrImg || !qrLink || !modal) {
            alert('Erreur : éléments de la modale QR Code introuvables.');
            return;
        }

        // ── 3. Construire l'URL de base ───────────────────────────────
        var params = '?p_prix='     + prixVal
                   + '&p_eco='      + ecoVal
                   + '&p_plaisir='  + plaisirVal
                   + '&p_temps='    + tempsVal
                   + '&objectif='   + encodeURIComponent(objectif)
                   + '&vegetarien=' + (isVege ? '1' : '0');

        var targetUrl;
        var isTunnel = false;

        if (window.APP_TUNNEL_URL) {
            // Tunnel public disponible → accessible depuis n'importe quel réseau
            targetUrl = window.APP_TUNNEL_URL + RESTO_PATH + params;
            isTunnel = true;
        } else {
            // Fallback réseau local
            var serverIp   = (window.APP_SERVER_IP
                              && window.APP_SERVER_IP !== '127.0.0.1'
                              && window.APP_SERVER_IP !== '::1')
                             ? window.APP_SERVER_IP
                             : location.hostname;
            var serverPort = window.APP_SERVER_PORT || '8080';
            targetUrl = 'http://' + serverIp + ':' + serverPort + RESTO_PATH + params;
        }

        console.log('[QR] Mode :', isTunnel ? 'TUNNEL PUBLIC' : 'RÉSEAU LOCAL');
        console.log('[QR] URL  :', targetUrl);

        // ── 4. Génération du QR via API externe ──────────────────────
        var qrPrimary  = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data='
                         + encodeURIComponent(targetUrl);
        var qrFallback = 'https://quickchart.io/qr?size=250&text='
                         + encodeURIComponent(targetUrl);

        qrImg.setAttribute('data-fallback-tried', '0');
        qrImg.style.display = 'block';
        qrImg.alt = 'QR Code restaurants';
        qrImg.onerror = function () {
            if (qrImg.getAttribute('data-fallback-tried') !== '1') {
                qrImg.setAttribute('data-fallback-tried', '1');
                qrImg.src = qrFallback;
                return;
            }
            qrImg.style.display = 'none';
            qrLink.innerHTML = '❌ QR indisponible — <a href="' + targetUrl + '" target="_blank">Ouvrir le lien</a>';
        };
        qrImg.src = qrPrimary;

        // ── 5. Lien cliquable ─────────────────────────────────────────
        qrLink.href = targetUrl;
        if (isTunnel) {
            qrLink.innerHTML = '🌐 <span style="background:#4caf50;color:#fff;padding:2px 8px;border-radius:4px;font-size:0.75rem;">ACCÈS PUBLIC</span>'
                             + ' &nbsp;<span style="text-decoration:underline;">🔗 Voir les restaurants</span>';
        } else {
            qrLink.innerHTML = '📱 <span style="background:#ff9800;color:#fff;padding:2px 8px;border-radius:4px;font-size:0.75rem;">RÉSEAU LOCAL</span>'
                             + ' &nbsp;<span style="text-decoration:underline;">🔗 Voir les restaurants</span>';
        }

        modal.style.display = 'flex';
    };

})();
