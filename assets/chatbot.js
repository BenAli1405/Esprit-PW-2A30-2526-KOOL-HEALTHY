/**
 * Coach Nutritionnel – Chatbot JavaScript
 * Gestion de l'interface et appels AJAX vers ChatbotController.php
 */
(function () {
    'use strict';

    // ── Configuration ──
    let PLAN_ID = 0;
    const API_URL = 'CONTROLLER/ChatbotController.php';

    const sentimentEmojis = {
        fatigue: '😴', stress: '😰', coupable: '😔',
        manque_temps: '⏰', motivation: '💪', faim: '🍽️', neutre: '😊'
    };
    const sentimentLabels = {
        fatigue: 'Fatigue', stress: 'Stress', coupable: 'Culpabilité',
        manque_temps: 'Manque de temps', motivation: 'Motivation',
        faim: 'Faim', neutre: 'Neutre'
    };

    // ── Initialisation ──
    function init(planId) {
        PLAN_ID = parseInt(planId, 10);
        if (!PLAN_ID || PLAN_ID <= 0) return;
        injectHTML();
        bindEvents();
        loadHistorique();
    }

    // ── Injection du DOM ──
    function injectHTML() {
        // Bulle flottante
        const bubble = document.createElement('button');
        bubble.id = 'chatbot-bubble';
        bubble.className = 'chatbot-bubble pulse';
        bubble.title = 'Coach Nutritionnel';
        bubble.innerHTML = '🥗';
        document.body.appendChild(bubble);

        // Panneau de chat
        const panel = document.createElement('div');
        panel.id = 'chatbot-panel';
        panel.className = 'chatbot-panel';
        panel.innerHTML =
            '<div class="chatbot-header">' +
                '<div class="chatbot-header-avatar">🧑‍⚕️</div>' +
                '<div class="chatbot-header-info">' +
                    '<p class="chatbot-header-title">Coach Nutritionnel</p>' +
                    '<p class="chatbot-header-subtitle">En ligne • Kool Healthy</p>' +
                '</div>' +
                '<button class="chatbot-header-close" id="chatbot-close" title="Fermer">✕</button>' +
            '</div>' +
            '<div class="chatbot-quick-actions">' +
                '<button class="chatbot-quick-btn" data-msg="Comment je me sens aujourd\'hui">🤔 Mon état</button>' +
                '<button class="chatbot-quick-btn" data-msg="Analyse mes notes de repas">📊 Analyser notes</button>' +
                '<button class="chatbot-quick-btn" data-msg="Je suis fatigué ces derniers jours">😴 Fatigue</button>' +
                '<button class="chatbot-quick-btn" data-msg="Je manque de temps pour cuisiner">⏰ Temps</button>' +
            '</div>' +
            '<div class="chatbot-messages" id="chatbot-messages">' +
                '<div class="chatbot-typing" id="chatbot-typing">' +
                    '<span></span><span></span><span></span>' +
                '</div>' +
            '</div>' +
            '<div class="chatbot-input-area">' +
                '<textarea class="chatbot-input" id="chatbot-input" ' +
                    'placeholder="Parlez de vos repas, ressentis..." rows="1"></textarea>' +
                '<button class="chatbot-send-btn" id="chatbot-send" title="Envoyer">➤</button>' +
            '</div>';
        document.body.appendChild(panel);
    }

    // ── Liaison des événements ──
    function bindEvents() {
        var bubble = document.getElementById('chatbot-bubble');
        var panel  = document.getElementById('chatbot-panel');
        var closeBtn = document.getElementById('chatbot-close');
        var sendBtn  = document.getElementById('chatbot-send');
        var input    = document.getElementById('chatbot-input');

        bubble.addEventListener('click', function () {
            panel.classList.toggle('open');
            bubble.classList.remove('pulse');
            if (panel.classList.contains('open')) {
                input.focus();
            }
        });

        closeBtn.addEventListener('click', function () {
            panel.classList.remove('open');
        });

        sendBtn.addEventListener('click', function () {
            sendMessage();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize textarea
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 80) + 'px';
        });

        // Quick action buttons
        var quickBtns = document.querySelectorAll('.chatbot-quick-btn');
        for (var i = 0; i < quickBtns.length; i++) {
            quickBtns[i].addEventListener('click', function () {
                var msg = this.getAttribute('data-msg');
                if (msg === 'Analyse mes notes de repas') {
                    analyzeNotes();
                } else {
                    input.value = msg;
                    sendMessage();
                }
            });
        }
    }

    // ── Envoi de message ──
    function sendMessage() {
        var input = document.getElementById('chatbot-input');
        var message = input.value.trim();
        if (!message) return;

        appendMessage('user', message);
        input.value = '';
        input.style.height = 'auto';
        showTyping(true);

        var sendBtn = document.getElementById('chatbot-send');
        sendBtn.disabled = true;

        ajaxPost({ action: 'send_message', plan_id: PLAN_ID, message: message })
            .then(function (resp) {
                showTyping(false);
                sendBtn.disabled = false;
                if (resp.success) {
                    appendMessage('bot', resp.reponse, resp.sentiment);
                } else {
                    appendMessage('bot', '❌ ' + resp.message);
                }
            })
            .catch(function (err) {
                showTyping(false);
                sendBtn.disabled = false;
                appendMessage('bot', '❌ Erreur de connexion. Réessayez.');
            });
    }

    // ── Analyse des notes ──
    function analyzeNotes() {
        appendMessage('user', '📊 Analyse de mes notes de repas');
        showTyping(true);

        ajaxPost({ action: 'analyze_notes', plan_id: PLAN_ID })
            .then(function (resp) {
                showTyping(false);
                if (resp.success && resp.analyse) {
                    appendMessage('bot', resp.analyse.resume);
                } else {
                    appendMessage('bot', '❌ ' + (resp.message || 'Erreur lors de l\'analyse.'));
                }
            })
            .catch(function () {
                showTyping(false);
                appendMessage('bot', '❌ Erreur de connexion.');
            });
    }

    // ── Chargement de l'historique ──
    function loadHistorique() {
        ajaxPost({ action: 'get_historique', plan_id: PLAN_ID, limit: 20 })
            .then(function (resp) {
                if (resp.success && resp.historique && resp.historique.length > 0) {
                    for (var i = 0; i < resp.historique.length; i++) {
                        var conv = resp.historique[i];
                        appendMessage('user', conv.message_utilisateur, null, conv.date_creation);
                        appendMessage('bot', conv.reponse_chatbot, conv.sentiment_detecte, conv.date_creation);
                    }
                } else {
                    // Message de bienvenue
                    appendMessage('bot',
                        '👋 Bonjour ! Je suis votre Coach Nutritionnel.\n\n' +
                        'Je peux vous aider à :\n' +
                        '• Analyser vos habitudes alimentaires\n' +
                        '• Détecter vos états émotionnels\n' +
                        '• Donner des conseils personnalisés\n\n' +
                        'Parlez-moi de vos repas ou de comment vous vous sentez !'
                    );
                }
            })
            .catch(function () {
                appendMessage('bot', '👋 Bienvenue ! Je suis votre Coach Nutritionnel. Comment puis-je vous aider ?');
            });
    }

    // ── Ajout d'un message à la zone de chat ──
    function appendMessage(type, text, sentiment, dateStr) {
        var container = document.getElementById('chatbot-messages');
        var typing    = document.getElementById('chatbot-typing');
        var div = document.createElement('div');
        div.className = 'chatbot-msg ' + type;

        var content = '';

        // Badge sentiment
        if (sentiment && sentiment !== 'neutre' && type === 'bot') {
            var emoji = sentimentEmojis[sentiment] || '';
            var label = sentimentLabels[sentiment] || sentiment;
            content += '<span class="chatbot-sentiment">' + emoji + ' ' + label + '</span><br>';
        }

        // Formater le texte (supporte **gras** et les sauts de ligne)
        var formatted = escapeHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
        content += formatted;

        // Horodatage
        var timeStr = '';
        if (dateStr) {
            var d = new Date(dateStr);
            timeStr = d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        } else {
            var now = new Date();
            timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
        content += '<span class="msg-time">' + timeStr + '</span>';

        div.innerHTML = content;
        container.insertBefore(div, typing);
        container.scrollTop = container.scrollHeight;
    }

    // ── Indicateur de frappe ──
    function showTyping(show) {
        var typing = document.getElementById('chatbot-typing');
        if (show) {
            typing.classList.add('visible');
        } else {
            typing.classList.remove('visible');
        }
        var container = document.getElementById('chatbot-messages');
        container.scrollTop = container.scrollHeight;
    }

    // ── Appel AJAX POST (JSON) ──
    function ajaxPost(data) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', API_URL, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            resolve(JSON.parse(xhr.responseText));
                        } catch (e) {
                            reject(e);
                        }
                    } else {
                        try {
                            reject(JSON.parse(xhr.responseText));
                        } catch (e) {
                            reject(new Error('HTTP ' + xhr.status));
                        }
                    }
                }
            };
            xhr.onerror = function () { reject(new Error('Network error')); };
            xhr.send(JSON.stringify(data));
        });
    }

    // ── Utilitaire d'échappement HTML ──
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // ── Exposer la fonction d'initialisation ──
    window.ChatbotInit = init;

})();
