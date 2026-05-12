/**
 * Jumeau Nutritionnel (Digital Twin) – JavaScript
 */
(function () {
    'use strict';

    var PLAN_ID = 0;
    var API_URL = 'CONTROLLER/JumeauController.php';
    var twinData = null;

    function init(planId) {
        PLAN_ID = parseInt(planId, 10);
        if (!PLAN_ID || PLAN_ID <= 0) return;
        loadStats();
        bindEvents();
    }

    function bindEvents() {
        // Bouton Simuler
        document.addEventListener('click', function (e) {
            if (e.target && e.target.id === 'twin-simulate-btn') {
                openSimModal();
            }
            if (e.target && e.target.id === 'twin-sim-close') {
                closeSimModal();
            }
            if (e.target && e.target.id === 'twin-sim-run') {
                runSimulation();
            }
            if (e.target && e.target.id === 'twin-refresh') {
                loadStats();
            }
        });
    }

    function loadStats() {
        var container = document.getElementById('twin-container');
        if (!container) return;
        container.innerHTML = '<div style="text-align:center;padding:30px;color:#888;">⏳ Chargement du Jumeau Nutritionnel...</div>';

        ajaxPost({ action: 'get_twin_stats', plan_id: PLAN_ID })
            .then(function (resp) {
                if (resp.success && resp.stats) {
                    twinData = resp.stats;
                    renderTwin(resp.stats);
                } else {
                    container.innerHTML = '<div style="text-align:center;padding:30px;color:#e53935;">❌ ' + (resp.message || 'Erreur') + '</div>';
                }
            })
            .catch(function () {
                container.innerHTML = '<div style="text-align:center;padding:30px;color:#e53935;">❌ Erreur de connexion</div>';
            });
    }

    function renderTwin(s) {
        var container = document.getElementById('twin-container');
        var html = '';

        // ── Profil métabolique & Confiance ──
        html += '<div class="twin-grid">';
        html += renderCard('🧬', 'Métabolisme', s.bmr + ' kcal/j', 'Formule de Harris-Benedict');
        html += renderCard('🔥', 'Besoins', s.besoins + ' kcal/j', 'Activité : ' + s.profil.activite);
        html += renderCard(s.energie.emoji, 'Énergie', s.energie.score + '/100', buildEnergyBar(s.energie.score));
        html += renderCard('🛡️', 'Confiance', s.confiance.score + '%', buildEnergyBar(s.confiance.score) + '<div style="font-size:9px;margin-top:2px;">(Bio:'+s.confiance.details.biometrie+' Freq:'+s.confiance.details.frequence+' Reg:'+s.confiance.details.regularite+')</div>');
        html += '</div>';

        // ── Alertes & Émotions (Plateau, Hhumeur) ──
        if (s.plateau && s.plateau.plateau) {
            html += '<div style="background:#fff3e0;border-left:4px solid #ff9800;padding:12px;border-radius:4px;margin-bottom:12px;font-size:13px;">';
            html += '<strong style="color:#e65100;">' + s.plateau.message + '</strong><br>';
            html += '<span style="color:#555;">' + s.plateau.conseil + '</span>';
            html += '</div>';
        }

        if (s.emotions) {
            html += '<div style="background:#f3e5f5;border-left:4px solid #9c27b0;padding:12px;border-radius:4px;margin-bottom:12px;font-size:13px;display:flex;align-items:center;gap:12px;">';
            html += '<div style="font-size:24px;">' + s.emotions.emoji + '</div>';
            html += '<div><strong style="color:#6a1b9a;">Analyse Émotionnelle : ' + s.emotions.emotion_dominante + '</strong><br>';
            html += '<span style="color:#555;">' + s.emotions.conseil + '</span></div>';
            html += '</div>';
        }

        // ── Tendance & prédictions ──
        html += '<div class="twin-grid twin-grid-2">';
        // Tendance
        html += '<div class="twin-card twin-card-trend">';
        html += '<div class="twin-card-icon">' + s.tendance.emoji + '</div>';
        html += '<div class="twin-card-title">Tendance actuelle</div>';
        html += '<div class="twin-card-value">' + s.tendance.label + '</div>';
        html += '<div class="twin-card-sub">Écart moyen : ' + (s.tendance.ecart_moyen > 0 ? '+' : '') + s.tendance.ecart_moyen + ' kcal/j</div>';
        html += '</div>';
        // Prédictions poids
        html += '<div class="twin-card">';
        html += '<div class="twin-card-icon">⚖️</div>';
        html += '<div class="twin-card-title">Prédiction de poids</div>';
        html += '<div class="twin-preds">';
        html += predBadge('7j', s.predictions[7], s.profil.poids);
        html += predBadge('14j', s.predictions[14], s.profil.poids);
        html += predBadge('30j', s.predictions[30], s.profil.poids);
        html += '</div>';
        html += '<div class="twin-card-sub" style="margin-top:8px;">Poids actuel : <strong>' + s.profil.poids + ' kg</strong></div>';
        html += '</div>';
        html += '</div>';

        // ── Graphique forecast ──
        html += '<div class="twin-card" style="margin-top:12px;">';
        html += '<div class="twin-card-title" style="margin-bottom:12px;">📈 Prévision sur 7 jours</div>';
        html += renderForecastChart(s.forecast, s.profil.poids);
        html += '</div>';

        // ── Plan de Correction ──
        if (s.correction && s.correction.actif) {
            html += '<div style="background:#e3f2fd;border:1px solid #90caf9;padding:14px;border-radius:8px;margin-top:12px;">';
            html += '<div style="font-weight:700;color:#1565c0;margin-bottom:8px;">🛠️ Plan de Correction Automatique (3 jours)</div>';
            html += '<div style="font-size:12px;color:#333;margin-bottom:10px;">' + s.correction.message + ' (' + (s.correction.ajustement > 0 ? '+' : '') + s.correction.ajustement + ' kcal/j)</div>';
            html += '<div style="display:flex;gap:8px;">';
            for (var c = 0; c < s.correction.cibles.length; c++) {
                var cible = s.correction.cibles[c];
                html += '<div style="background:#fff;border-radius:6px;padding:8px;text-align:center;flex:1;font-size:11px;">';
                html += '<div style="font-weight:700;color:#1976d2;">' + cible.jour + '</div>';
                html += '<div style="font-weight:800;font-size:14px;">' + cible.calories + '</div>';
                html += '</div>';
            }
            html += '</div></div>';
        }

        // ── Conseil ──
        html += '<div class="twin-conseil">';
        html += '<div style="font-weight:700;margin-bottom:6px;">🧠 Synthèse du Jumeau</div>';
        html += '<div>' + s.conseil + '</div>';
        html += '</div>';

        // ── Boutons ──
        html += '<div style="display:flex;gap:10px;margin-top:12px;">';
        html += '<button id="twin-simulate-btn" class="twin-btn">🔬 Simuler un écart</button>';
        html += '<button id="twin-refresh" class="twin-btn twin-btn-secondary">🔄 Actualiser</button>';
        html += '</div>';

        // ── Modal simulation (caché) ──
        html += '<div id="twin-sim-modal" class="twin-modal" style="display:none;">';
        html += '<div class="twin-modal-content">';
        html += '<h3 style="color:#2e7d32;margin:0 0 16px;">🔬 Simuler un écart calorique</h3>';
        html += '<label style="font-size:13px;font-weight:600;color:#555;">Écart quotidien (kcal) :</label>';
        html += '<input type="number" id="twin-ecart-input" value="500" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;font-size:14px;margin:8px 0 12px;">';
        html += '<div style="font-size:12px;color:#888;margin-bottom:12px;">Positif = surplus, Négatif = déficit (ex: -300 pour déficit)</div>';
        html += '<div id="twin-sim-result" style="min-height:40px;"></div>';
        html += '<div style="display:flex;gap:10px;margin-top:14px;">';
        html += '<button id="twin-sim-run" class="twin-btn" style="flex:1;">▶ Simuler</button>';
        html += '<button id="twin-sim-close" class="twin-btn twin-btn-secondary" style="flex:1;">Fermer</button>';
        html += '</div></div></div>';

        container.innerHTML = html;
    }

    function renderCard(icon, title, value, sub) {
        var s = '<div class="twin-card">';
        s += '<div class="twin-card-icon">' + icon + '</div>';
        s += '<div class="twin-card-title">' + title + '</div>';
        s += '<div class="twin-card-value">' + value + '</div>';
        if (sub) s += '<div class="twin-card-sub">' + sub + '</div>';
        s += '</div>';
        return s;
    }

    function buildEnergyBar(score) {
        var color = score >= 75 ? '#4caf50' : score >= 50 ? '#ff9800' : '#e53935';
        return '<div style="background:#e8e8e8;border-radius:8px;height:8px;margin-top:6px;overflow:hidden;">' +
               '<div style="width:' + score + '%;height:100%;background:' + color + ';border-radius:8px;transition:width 0.6s;"></div></div>';
    }

    function predBadge(label, val, current) {
        var diff = (val - current).toFixed(2);
        var sign = diff >= 0 ? '+' : '';
        var color = diff > 0 ? '#e53935' : diff < 0 ? '#4caf50' : '#888';
        return '<div class="twin-pred">' +
               '<div class="twin-pred-label">' + label + '</div>' +
               '<div class="twin-pred-value">' + val + ' kg</div>' +
               '<div style="font-size:11px;color:' + color + ';font-weight:700;">' + sign + diff + ' kg</div></div>';
    }

    function renderForecastChart(forecast, currentWeight) {
        if (!forecast || forecast.length === 0) return '<div style="color:#999;text-align:center;">Pas de données</div>';

        var values = forecast.map(function (f) { return f.poids_predit; });
        values.unshift(currentWeight);
        var min = Math.min.apply(null, values) - 0.5;
        var max = Math.max.apply(null, values) + 0.5;
        var range = max - min || 1;
        var h = 120;

        var html = '<div style="display:flex;align-items:flex-end;gap:6px;height:' + h + 'px;padding:0 4px;">';
        // Current weight bar
        var bh0 = Math.max(20, ((currentWeight - min) / range) * h);
        html += '<div style="flex:1;text-align:center;">';
        html += '<div style="font-size:10px;font-weight:700;color:#4a9b8e;margin-bottom:3px;">' + currentWeight + '</div>';
        html += '<div style="height:' + bh0 + 'px;background:linear-gradient(180deg,#4a9b8e,#357a6e);border-radius:6px 6px 0 0;transition:height 0.4s;"></div>';
        html += '<div style="font-size:10px;color:#555;margin-top:4px;">Actuel</div></div>';

        for (var i = 0; i < forecast.length; i++) {
            var f = forecast[i];
            var bh = Math.max(20, ((f.poids_predit - min) / range) * h);
            var diff = (f.poids_predit - currentWeight).toFixed(2);
            var color = diff > 0 ? '#ff9800' : diff < 0 ? '#4caf50' : '#4a9b8e';
            html += '<div style="flex:1;text-align:center;">';
            html += '<div style="font-size:10px;font-weight:700;color:' + color + ';margin-bottom:3px;">' + f.poids_predit + '</div>';
            html += '<div style="height:' + bh + 'px;background:linear-gradient(180deg,' + color + ',' + color + '99);border-radius:6px 6px 0 0;transition:height 0.4s;"></div>';
            html += '<div style="font-size:10px;color:#555;margin-top:4px;">J+' + f.jour + '</div></div>';
        }
        html += '</div>';
        return html;
    }

    function openSimModal() {
        var m = document.getElementById('twin-sim-modal');
        if (m) { m.style.display = 'flex'; document.getElementById('twin-sim-result').innerHTML = ''; }
    }
    function closeSimModal() {
        var m = document.getElementById('twin-sim-modal');
        if (m) m.style.display = 'none';
    }

    function runSimulation() {
        var input = document.getElementById('twin-ecart-input');
        var val = parseInt(input.value, 10);
        if (isNaN(val)) { alert('Veuillez saisir un nombre.'); return; }
        var result = document.getElementById('twin-sim-result');
        result.innerHTML = '<div style="color:#888;">⏳ Simulation...</div>';

        ajaxPost({ action: 'simulate_ecart', plan_id: PLAN_ID, ecart: val })
            .then(function (resp) {
                if (resp.success && resp.simulation) {
                    var sim = resp.simulation;
                    var html = '<div style="background:#e8f5e9;border-radius:10px;padding:12px;margin-top:8px;">';
                    html += '<div style="font-weight:700;margin-bottom:8px;">' + formatBold(sim.message) + '</div>';
                    html += '<div style="display:flex;gap:8px;flex-wrap:wrap;">';
                    for (var j in sim.impact) {
                        html += '<div style="background:#fff;padding:6px 12px;border-radius:8px;font-size:12px;text-align:center;">';
                        html += '<div style="font-weight:700;color:#2e7d32;">' + j + 'j</div>';
                        html += '<div>' + sim.impact[j] + ' kg</div></div>';
                    }
                    html += '</div></div>';
                    result.innerHTML = html;
                } else {
                    result.innerHTML = '<div style="color:#e53935;">❌ ' + (resp.message || 'Erreur') + '</div>';
                }
            })
            .catch(function () {
                result.innerHTML = '<div style="color:#e53935;">❌ Erreur de connexion</div>';
            });
    }

    function formatBold(str) {
        return str.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    }

    function ajaxPost(data) {
        return new Promise(function (resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', API_URL, true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try { resolve(JSON.parse(xhr.responseText)); }
                        catch (e) { reject(e); }
                    } else { reject(new Error('HTTP ' + xhr.status)); }
                }
            };
            xhr.onerror = function () { reject(new Error('Network error')); };
            xhr.send(JSON.stringify(data));
        });
    }

    window.JumeauInit = init;
})();
