/**
 * Orchestrateur Multi-Objectifs – JavaScript
 */
(function () {
    'use strict';

    var PLAN_ID = 0;
    var API_URL = 'CONTROLLER/MultiObjectiveController.php';
    var debounceTimer;
    var chosenMealId = null;

    function init(planId) {
        PLAN_ID = parseInt(planId, 10);
        if (!PLAN_ID || PLAN_ID <= 0) return;
        
        bindEvents();
        loadRecommendations();
    }

    function bindEvents() {
        var sliders = document.querySelectorAll('.mo-slider');
        sliders.forEach(function(slider) {
            // Update value display immediately
            slider.addEventListener('input', function(e) {
                var target = document.getElementById(e.target.id + '-val');
                if (target) {
                    target.textContent = e.target.value + '%';
                }
            });
            
            // Trigger AJAX update after user stops moving slider
            slider.addEventListener('change', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    chosenMealId = null; // Reset choice on slider move
                    updateWeights();
                }, 300);
            });
        });

        var resultsContainer = document.getElementById('mo-results');
        if (resultsContainer) {
            resultsContainer.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('mo-choose-btn')) {
                    var id = e.target.getAttribute('data-id');
                    if (id) {
                        chosenMealId = parseInt(id, 10);
                        // Re-render with the current data in memory? 
                        // Instead of full ajax, just reload from memory? 
                        // It's easier to just call loadRecommendations or keep the last response.
                        // Let's just trigger a re-render from the last fetch.
                        if (window.lastRepasList) {
                            renderResults(window.lastRepasList);
                        }
                    }
                }
            });
        }
    }

    function updateWeights() {
        var weights = {
            perte_poids: document.getElementById('mo-weight-perte').value,
            plaisir: document.getElementById('mo-weight-plaisir').value,
            budget: document.getElementById('mo-weight-budget').value,
            rapidite: document.getElementById('mo-weight-rapidite').value,
            ecologie: document.getElementById('mo-weight-eco').value
        };

        ajaxPost({ action: 'update_weights', weights: weights })
            .then(function(resp) {
                if (resp.success) {
                    loadRecommendations(); // Reload list with new weights
                }
            })
            .catch(function(err) {
                console.error("Erreur mise à jour des poids:", err);
            });
    }

    function loadRecommendations() {
        var container = document.getElementById('mo-results');
        if (!container) return;
        container.innerHTML = '<div style="text-align:center;padding:20px;color:#888;">⏳ Calcul des recommandations...</div>';

        ajaxPost({ action: 'get_recommendation', plan_id: PLAN_ID })
            .then(function (resp) {
                if (resp.success) {
                    // Update slider positions just in case they differ from session
                    if (resp.weights) {
                        document.getElementById('mo-weight-perte').value = resp.weights.perte_poids || 0;
                        document.getElementById('mo-weight-perte-val').textContent = (resp.weights.perte_poids || 0) + '%';
                        
                        document.getElementById('mo-weight-plaisir').value = resp.weights.plaisir || 0;
                        document.getElementById('mo-weight-plaisir-val').textContent = (resp.weights.plaisir || 0) + '%';
                        
                        document.getElementById('mo-weight-budget').value = resp.weights.budget || 0;
                        document.getElementById('mo-weight-budget-val').textContent = (resp.weights.budget || 0) + '%';
                        
                        document.getElementById('mo-weight-rapidite').value = resp.weights.rapidite || 0;
                        document.getElementById('mo-weight-rapidite-val').textContent = (resp.weights.rapidite || 0) + '%';
                        
                        document.getElementById('mo-weight-eco').value = resp.weights.ecologie || 0;
                        document.getElementById('mo-weight-eco-val').textContent = (resp.weights.ecologie || 0) + '%';
                    }
                    window.lastRepasList = resp.recommendations;
                    renderResults(resp.recommendations);
                } else {
                    container.innerHTML = '<div style="color:#e53935;">❌ ' + (resp.message || 'Erreur') + '</div>';
                }
            })
            .catch(function (err) {
                container.innerHTML = '<div style="color:#e53935;">❌ Erreur de connexion</div>';
            });
    }

    function renderResults(repasList) {
        var container = document.getElementById('mo-results');
        if (!repasList || repasList.length === 0) {
            container.innerHTML = '<div style="text-align:center;padding:20px;color:#888;">Aucun repas enregistré pour ce plan.</div>';
            return;
        }

        // Clone list to avoid modifying original on multiple renders
        var list = repasList.slice();

        // If a meal was chosen, move it to the top
        if (chosenMealId !== null) {
            var idx = list.findIndex(function(r) { return parseInt(r.id, 10) === chosenMealId; });
            if (idx > -1) {
                var chosen = list.splice(idx, 1)[0];
                list.unshift(chosen);
            }
        }

        var html = '';
        var best = list[0];

        // ── RECOMMANDATION PRINCIPALE ──
        var isChosen = (chosenMealId !== null && parseInt(best.id, 10) === chosenMealId);
        var titleText = isChosen ? '✅ Repas Choisi' : '🏆 Repas Recommandé';
        var bgGrad = isChosen ? 'linear-gradient(135deg, #e3f2fd, #bbdefb)' : 'linear-gradient(135deg, #e8f5e9, #c8e6c9)';
        var borderColor = isChosen ? '#90caf9' : '#a5d6a7';
        var titleColor = isChosen ? '#1565c0' : '#2e7d32';
        var badgeColor = isChosen ? '#1976d2' : '#4caf50';

        var gearIconParamsBest = best.id + ", " + best._simulations.prix + ", " + best._simulations.temps + ", '" + best._simulations.eco + "', " + best._simulations.plaisir;

        html += '<div style="background: ' + bgGrad + '; border-radius:12px; padding:20px; margin-bottom:16px; border: 1px solid ' + borderColor + ';">';
        html += '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">';
        html += '<div>';
        html += '<div style="font-size:12px; font-weight:700; color:' + titleColor + '; margin-bottom:4px; text-transform:uppercase;">' + titleText + '</div>';
        html += '<h3 style="margin:0; color:#1b5e20; font-size:18px; display:flex; align-items:center; gap:8px;">' + best.nom_recette + ' <span style="cursor:pointer;font-size:16px;" onclick="openCriteresModal(' + gearIconParamsBest + ')" title="Modifier les critères">⚙️</span></h3>';
        html += '</div>';
        html += '<div style="background:' + badgeColor + '; color:#fff; font-weight:800; padding:6px 12px; border-radius:20px; font-size:18px;">' + best.score_composite + '/100</div>';
        html += '</div>';

        // Pourquoi ce repas ? (Détails des scores normaux)
        html += '<div style="background:rgba(255,255,255,0.6); padding:10px; border-radius:8px; margin-bottom:12px; font-size:11px;">';
        html += '<strong style="color:#555; display:block; margin-bottom:6px;">ℹ️ Pourquoi ce repas ? (Scores normalisés /100) :</strong>';
        html += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; color:#444;">';
        html += '<div>⚖️ Perte poids: <strong>' + best._scores.perte_poids + '</strong></div>';
        html += '<div>😋 Plaisir: <strong>' + best._scores.plaisir + '</strong></div>';
        html += '<div>💶 Budget: <strong>' + best._scores.budget + '</strong></div>';
        html += '<div>⏱️ Rapidité: <strong>' + best._scores.rapidite + '</strong></div>';
        html += '<div>🌍 Écologie: <strong>' + best._scores.ecologie + '</strong></div>';
        html += '</div></div>';

        // Badges d'informations
        html += '<div style="display:flex; flex-wrap:wrap; gap:8px;">';
        html += '<span class="mo-badge">🔥 ' + best._simulations.calories + ' kcal</span>';
        html += '<span class="mo-badge">⏱️ ' + best._simulations.temps + ' min</span>';
        html += '<span class="mo-badge">💶 ' + best._simulations.prix + ' €</span>';
        html += '<span class="mo-badge">🌍 Éco ' + best._simulations.eco + '</span>';
        html += '<span class="mo-badge">😋 Plaisir ' + best._simulations.plaisir + '/10</span>';
        html += '</div>';
        html += '</div>';

        // ── ALTERNATIVES ──
        if (list.length > 1) {
            html += '<div style="font-weight:700; color:#555; margin-bottom:10px; font-size:13px;">🔄 Alternatives :</div>';
            html += '<div style="display:flex; flex-direction:column; gap:8px;">';
            for (var i = 1; i < list.length; i++) {
                var alt = list[i];
                var gearIconParamsAlt = alt.id + ", " + alt._simulations.prix + ", " + alt._simulations.temps + ", '" + alt._simulations.eco + "', " + alt._simulations.plaisir;

                html += '<div style="background:#fff; border:1px solid #e0e0e0; border-radius:8px; padding:12px; display:flex; justify-content:space-between; align-items:center;">';
                html += '<div>';
                html += '<div style="font-weight:600; color:#333; margin-bottom:4px; display:flex; align-items:center; gap:6px;">' + alt.nom_recette + ' <span style="cursor:pointer;font-size:14px;" onclick="openCriteresModal(' + gearIconParamsAlt + ')" title="Modifier les critères">⚙️</span></div>';
                html += '<div style="font-size:11px; color:#888;">' + alt._simulations.calories + ' kcal • ' + alt._simulations.temps + ' min • ' + alt._simulations.prix + '€</div>';
                html += '</div>';
                html += '<div style="display:flex; align-items:center; gap:10px;">';
                html += '<div style="background:#f5f5f5; color:#555; font-weight:700; padding:4px 8px; border-radius:12px; font-size:13px;">' + alt.score_composite + '/100</div>';
                html += '<button class="mo-choose-btn" data-id="' + alt.id + '" style="background:#1976d2; color:white; border:none; border-radius:6px; padding:4px 10px; font-size:11px; cursor:pointer; font-weight:600;">Choisir</button>';
                html += '</div>';
                html += '</div>';
            }
            html += '</div>';
        }

        container.innerHTML = html;
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

    window.openCriteresModal = function(repasId, prix, temps, eco, plaisir) {
        document.getElementById('critere_repas_id').value = repasId;
        document.getElementById('critere_prix').value = prix;
        document.getElementById('critere_temps').value = temps;
        document.getElementById('critere_eco').value = eco;
        document.getElementById('critere_plaisir').value = plaisir;
        document.getElementById('editCriteresModal').style.display = 'flex';
    };

    window.saveCriteres = function() {
        var id = document.getElementById('critere_repas_id').value;
        var data = {
            action: 'save_criteres',
            repas_id: id,
            prix: document.getElementById('critere_prix').value,
            temps_preparation: document.getElementById('critere_temps').value,
            eco_score: document.getElementById('critere_eco').value,
            note_plaisir: document.getElementById('critere_plaisir').value
        };

        var submitBtn = document.querySelector('#editCriteresForm button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enregistrement...';

        ajaxPost(data).then(function(resp) {
            submitBtn.disabled = false;
            submitBtn.textContent = '✓ Enregistrer';
            if (resp.success) {
                document.getElementById('editCriteresModal').style.display = 'none';
                loadRecommendations(); // Recharger les calculs avec les nouveaux critères
            } else {
                alert('Erreur : ' + (resp.message || 'Impossible de sauvegarder'));
            }
        }).catch(function(err) {
            submitBtn.disabled = false;
            submitBtn.textContent = '✓ Enregistrer';
            alert('Erreur de connexion');
        });
    };

    window.MultiObjectiveInit = init;
})();
