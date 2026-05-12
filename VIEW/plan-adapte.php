<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos Repas - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/integweb/CSS/styles.css">
    <style>
        :root {
            --vert-clair: #8BC34A;
            --vert-fonce: #4E8E2A;
            --bleu-turquoise: #4BA3A6;
            --orange: #E67E22;
            --blanc: #FFFFFF;
            --gris-clair: #F5F5F5;
            --gris-moyen: #E0E0E0;
            --text: #2C3E2F;
        }

        body {
            background-color: var(--gris-clair);
        }

        .section-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .profile-full-wrapper {
            background: var(--blanc);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .plan-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .badge {
            background: linear-gradient(135deg, var(--vert-clair), var(--bleu-turquoise));
            color: var(--blanc);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .badge-title {
            font-size: 0.9rem;
            font-weight: 600;
            opacity: 0.9;
        }

        .badge-value {
            font-size: 1.5rem;
            font-weight: 800;
            margin-top: 8px;
        }

        .day-block {
            background: var(--blanc);
            border: 1px solid var(--gris-moyen);
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .day-header {
            background: linear-gradient(90deg, rgba(139, 195, 74, 0.1), rgba(75, 163, 166, 0.1));
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
            border-bottom: 1px solid var(--gris-moyen);
        }

        .day-header:hover {
            background: linear-gradient(90deg, rgba(139, 195, 74, 0.15), rgba(75, 163, 166, 0.15));
        }

        .day-content {
            padding: 15px;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .day-content.collapsed {
            max-height: 0;
            padding: 0 15px;
        }

        .meal {
            margin-bottom: 15px;
            padding-left: 15px;
            border-left: 4px solid var(--vert-clair);
        }

        .meal-title {
            font-weight: 700;
            color: var(--vert-fonce);
        }

        .meal-kcal {
            color: var(--orange);
            font-weight: 700;
        }

        .meal-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 10px;
        }

        .meal-status.consumed {
            background: var(--vert-clair);
            color: var(--blanc);
        }

        .meal-status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .day-footer {
            padding: 12px 15px;
            background: var(--gris-clair);
            border-top: 1px solid var(--gris-moyen);
            text-align: right;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .recommendation {
            background: #e0f2f1;
            border-left: 4px solid var(--bleu-turquoise);
            padding: 16px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .recommendation-title {
            font-weight: 700;
            color: var(--bleu-turquoise);
            margin-bottom: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--bleu-turquoise);
            color: var(--blanc);
        }

        .btn-secondary {
            background: var(--vert-clair);
            color: var(--blanc);
        }

        .btn:hover {
            filter: brightness(1.05);
        }

        @media (max-width: 768px) {
            .section-wrap { padding: 20px; }
            .profile-full-wrapper { padding: 20px; }
            .plan-summary { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .action-buttons .btn { width: 100%; }
        }

        /* Range Input Styling */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 5px;
            outline: none;
            cursor: pointer;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            border: 3px solid currentColor;
            box-shadow: 0 3px 10px rgba(0,0,0,0.25);
            transition: all 0.2s ease;
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 14px rgba(0,0,0,0.3);
        }

        input[type="range"]::-moz-range-thumb {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            border: 3px solid currentColor;
            box-shadow: 0 3px 10px rgba(0,0,0,0.25);
            transition: all 0.2s ease;
        }

        input[type="range"]::-moz-range-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 4px 14px rgba(0,0,0,0.3);
        }

        input[type="range"]::-moz-range-track {
            background: transparent;
            border: none;
        }
    </style>
    
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <section class="section-wrap recipes-section">
        <div class="profile-full-wrapper">
            <main>
                <h2 style="color: var(--vert-fonce); margin: 0 0 30px;">📅 Vos Repas</h2>

                <?php if ($currentPlan): ?>
                    <div class="plan-summary">
                        <div class="badge">
                            <div class="badge-title">Objectif</div>
                            <div class="badge-value"><?= htmlspecialchars($currentPlan['objectif']) ?></div>
                        </div>
                        <div class="badge">
                            <div class="badge-title">Calories/Jour</div>
                            <div class="badge-value">2000</div>
                        </div>
                        <div class="badge">
                            <div class="badge-title">Progression</div>
                            <div class="badge-value">45%</div>
                        </div>
                    </div>

                    <div id="repasContainer">
                        <!-- Meals will be rendered here -->
                    </div>

                    <div class="recommendation">
                        <div class="recommendation-title">💡 Recommandations</div>
                        <p style="margin: 0; color: #00695c;">Continuez à suivre votre plan nutritionnel pour atteindre vos objectifs de santé.</p>
                    </div>

                    <div class="action-buttons">
                        <button class="btn btn-primary">Modifier le plan</button>
                        <button class="btn btn-secondary">Exporter PDF</button>
                    </div>

                    <!-- MULTI-OBJECTIVE Panel -->
                    <div style="margin-top: 40px;">
                        <h3 style="color: var(--vert-fonce); margin: 20px 0 10px; display: flex; align-items: center; cursor: pointer; font-size: 1.4rem;" onclick="togglePanel('multiObjectivePanel')">
                            ⚙️ Orchestrateur Multi-Objectifs <span id="multiObjectiveToggle" style="margin-left: auto; font-size: 1.2rem; transition: transform 0.3s;">▼</span>
                        </h3>
                        <p style="color: #666; font-size: 0.95rem; margin-bottom: 20px;">Le moteur intelligent qui équilibre vos priorités</p>
                        
                        <div id="multiObjectivePanel" style="display: none; background: linear-gradient(135deg, #f9f9f9, #ffffff); border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;">
                            <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px;">
                                <!-- Left: Sliders -->
                                <div>
                                    <h4 style="color: var(--vert-fonce); margin: 0 0 20px; font-size: 1.1rem;">📌 Mes Priorités</h4>
                                    <div style="display: flex; flex-direction: column; gap: 20px;">
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <label style="font-weight: 600; color: var(--vert-fonce);">Perte de poids</label>
                                                <span id="w-perte" style="font-weight: 700; color: var(--bleu-turquoise);">20%</span>
                                            </div>
                                            <input type="range" id="weight-perte_poids" min="0" max="100" value="20" 
                                                style="width: 100%; height: 8px; border-radius: 5px; background: linear-gradient(to right, var(--vert-clair), var(--vert-fonce)); outline: none; -webkit-appearance: none;">
                                        </div>
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <label style="font-weight: 600; color: var(--vert-fonce);">Plaisir gustatif</label>
                                                <span id="w-plaisir" style="font-weight: 700; color: var(--orange);">20%</span>
                                            </div>
                                            <input type="range" id="weight-plaisir" min="0" max="100" value="20" 
                                                style="width: 100%; height: 8px; border-radius: 5px; background: linear-gradient(to right, var(--orange), #ff6b35); outline: none; -webkit-appearance: none;">
                                        </div>
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <label style="font-weight: 600; color: var(--vert-fonce);">Budget (€)</label>
                                                <span id="w-budget" style="font-weight: 700; color: #27ae60;">20%</span>
                                            </div>
                                            <input type="range" id="weight-budget" min="0" max="100" value="20" 
                                                style="width: 100%; height: 8px; border-radius: 5px; background: linear-gradient(to right, #27ae60, #229954); outline: none; -webkit-appearance: none;">
                                        </div>
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <label style="font-weight: 600; color: var(--vert-fonce);">Rapidité</label>
                                                <span id="w-rapidite" style="font-weight: 700; color: #e74c3c;">20%</span>
                                            </div>
                                            <input type="range" id="weight-rapidite" min="0" max="100" value="20" 
                                                style="width: 100%; height: 8px; border-radius: 5px; background: linear-gradient(to right, #e74c3c, #c0392b); outline: none; -webkit-appearance: none;">
                                        </div>
                                        <div>
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                <label style="font-weight: 600; color: var(--vert-fonce);">Écologie 🌍</label>
                                                <span id="w-ecologie" style="font-weight: 700; color: #16a085;">20%</span>
                                            </div>
                                            <input type="range" id="weight-ecologie" min="0" max="100" value="20" 
                                                style="width: 100%; height: 8px; border-radius: 5px; background: linear-gradient(to right, #16a085, #138d75); outline: none; -webkit-appearance: none;">
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" onclick="saveMultiObjectiveWeights()" style="width: 100%; margin-top: 20px; padding: 12px;">💾 Enregistrer mes priorités</button>
                                </div>

                                <!-- Right: Recommendation Card -->
                                <div id="multiObjectiveRecommendationContainer" style="display: flex; flex-direction: column; gap: 15px;">
                                    <div id="multiObjectiveResult" style="background: linear-gradient(135deg, var(--vert-clair), var(--vert-fonce)); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 8px 16px rgba(139,195,74,0.3); display: none;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                            <h4 style="margin: 0; font-size: 1.1rem;">🍽️ REPAS RECOMMANDÉ</h4>
                                            <span id="moRecScore" style="font-size: 2.2rem; font-weight: 800;">-</span>
                                        </div>
                                        <div id="moRecName" style="font-size: 1.4rem; font-weight: 700; margin-bottom: 15px;">-</div>
                                        <div id="moRecScores" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 0.9rem; margin-bottom: 15px;"></div>
                                        <button class="btn btn-secondary" onclick="getMultiObjectiveRecommendation()" style="width: 100%; background: rgba(255,255,255,0.2); border: 2px solid white; color: white; padding: 10px;">🔄 Actualiser recommandation</button>
                                    </div>
                                    
                                    <div id="multiObjectiveAlternatives" style="display: none;">
                                        <h4 style="color: var(--vert-fonce); margin: 0 0 12px; font-size: 1rem;">💡 Alternatives :</h4>
                                        <div id="moAltList" style="display: flex; flex-direction: column; gap: 10px;"></div>
                                    </div>

                                    <button class="btn btn-primary" onclick="getMultiObjectiveRecommendation()" style="width: 100%; padding: 12px; margin-top: 10px;" id="moInitButton">🎯 Obtenir recommandations</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- JUMEAU Panel -->
                    <div style="margin-top: 40px;">
                        <h3 style="color: var(--bleu-turquoise); margin: 20px 0 10px; display: flex; align-items: center; cursor: pointer; font-size: 1.4rem;" onclick="togglePanel('jumeauPanel')">
                            👯 Jumeau - Analyse & Prédiction <span id="jumeauToggle" style="margin-left: auto; font-size: 1.2rem; transition: transform 0.3s;">▼</span>
                        </h3>
                        <p style="color: #666; font-size: 0.95rem; margin-bottom: 20px;">Analysez votre progression et simulez des scénarios nutritionnels</p>
                        
                        <div id="jumeauPanel" style="display: none; background: linear-gradient(135deg, #f0f8f9, #ffffff); border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #e0f2f3;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                                <button class="btn btn-primary" onclick="getJumeauStats()" style="padding: 14px; font-weight: 700; background: linear-gradient(135deg, var(--bleu-turquoise), #2f8b89); border: none; border-radius: 10px;">📈 Voir statistiques</button>
                                <button class="btn btn-secondary" onclick="showJumeauPredictionInput()" style="padding: 14px; font-weight: 700; background: linear-gradient(135deg, #3498db, #2980b9); border: none; border-radius: 10px; color: white;">🔮 Prédiction 7j</button>
                                <button class="btn btn-secondary" onclick="showJumeauSimulationInput()" style="padding: 14px; font-weight: 700; background: linear-gradient(135deg, #9b59b6, #8e44ad); border: none; border-radius: 10px; color: white;">🎯 Simuler écart</button>
                            </div>

                            <div id="jumeauPredictionInput" style="display: none; background: white; padding: 20px; border-radius: 12px; border-left: 4px solid #3498db; margin-bottom: 20px;">
                                <label style="font-weight: 700; color: var(--vert-fonce); display: block; margin-bottom: 10px;">Prédire sur combien de jours ?</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="number" id="jumeauNbJours" min="1" max="90" value="7" style="width: 80px; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-weight: 600; font-size: 16px;">
                                    <span style="color: #999; font-size: 0.9rem;"> jours</span>
                                    <button class="btn btn-primary" onclick="getJumeauPrediction()" style="margin-left: auto;">Prédire</button>
                                    <button class="btn btn-secondary" onclick="this.parentElement.parentElement.style.display='none'">Annuler</button>
                                </div>
                            </div>

                            <div id="jumeauSimulationInput" style="display: none; background: white; padding: 20px; border-radius: 12px; border-left: 4px solid #9b59b6; margin-bottom: 20px;">
                                <label style="font-weight: 700; color: var(--vert-fonce); display: block; margin-bottom: 10px;">Écart calorique quotidien</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="number" id="jumeauEcart" value="200" style="width: 100px; padding: 10px; border: 2px solid #ddd; border-radius: 8px; font-weight: 600; font-size: 16px;">
                                    <span style="color: #999; font-size: 0.9rem;"> kcal/jour</span>
                                    <button class="btn btn-primary" onclick="simulateJumeauEcart()" style="margin-left: auto;">Simuler</button>
                                    <button class="btn btn-secondary" onclick="this.parentElement.parentElement.style.display='none'">Annuler</button>
                                </div>
                            </div>

                            <div id="jumeauResult" style="background: linear-gradient(135deg, #d4f1f4, #e8fafc); padding: 20px; border-radius: 12px; border-left: 4px solid var(--bleu-turquoise); display: none;"></div>
                        </div>
                    </div>

                    <script>
                    const mealData = <?= json_encode($repasForFront ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
                    
                    function renderMeals() {
                        const container = document.getElementById('repasContainer');
                        if (!mealData || mealData.length === 0) {
                            container.innerHTML = '<p style="text-align: center; color: #999;">Aucun repas disponible</p>';
                            return;
                        }

                        let html = '';
                        mealData.forEach((day, idx) => {
                            html += `
                                <div class="day-block">
                                    <div class="day-header" onclick="toggleDay(${idx})">
                                        <span>📆 ${day.date || 'Jour ' + (idx + 1)}</span>
                                        <span>▼</span>
                                    </div>
                                    <div class="day-content" id="day${idx}">
                                        ${(day.meals || []).map(meal => `
                                            <div class="meal">
                                                <div class="meal-title">${meal.nom || 'Repas'} <span class="meal-kcal">${meal.calories || 0} kcal</span></div>
                                                <p style="margin: 5px 0; font-size: 0.9rem;">${meal.description || ''}</p>
                                            </div>
                                        `).join('')}
                                    </div>
                                    <div class="day-footer">Total: ${day.totalCalories || 2000} kcal</div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    }

                    function toggleDay(idx) {
                        const content = document.getElementById('day' + idx);
                        content.classList.toggle('collapsed');
                    }

                    function togglePanel(panelId) {
                        const panel = document.getElementById(panelId);
                        const toggle = panelId === 'multiObjectivePanel' ? 'multiObjectiveToggle' : 'jumeauToggle';
                        if (panel.style.display === 'none') {
                            panel.style.display = 'block';
                            document.getElementById(toggle).textContent = '▲';
                        } else {
                            panel.style.display = 'none';
                            document.getElementById(toggle).textContent = '▼';
                        }
                    }

                    // ── Multi-Objective Functions ──
                    function saveMultiObjectiveWeights() {
                        const weights = {
                            'perte_poids': parseInt(document.getElementById('weight-perte_poids').value),
                            'plaisir': parseInt(document.getElementById('weight-plaisir').value),
                            'budget': parseInt(document.getElementById('weight-budget').value),
                            'rapidite': parseInt(document.getElementById('weight-rapidite').value),
                            'ecologie': parseInt(document.getElementById('weight-ecologie').value)
                        };
                        
                        ajaxPost('CONTROLLER/MultiObjectiveController.php', {
                            action: 'update_weights',
                            weights: weights
                        }).then(resp => {
                            if (resp.success) {
                                document.getElementById('moInitButton').textContent = '✓ Priorités enregistrées ! Obtenir recommandations';
                                setTimeout(() => { document.getElementById('moInitButton').textContent = '🎯 Obtenir recommandations'; }, 2000);
                            }
                        }).catch(err => console.error(err));
                    }

                    function getMultiObjectiveRecommendation() {
                        const planId = <?= (int)($currentPlan['id'] ?? 0) ?>;
                        if (!planId) return;
                        
                        ajaxPost('CONTROLLER/MultiObjectiveController.php', {
                            action: 'get_recommendation',
                            plan_id: planId
                        }).then(resp => {
                            const resultDiv = document.getElementById('multiObjectiveResult');
                            const altDiv = document.getElementById('multiObjectiveAlternatives');
                            
                            if (resp.success && resp.recommendations && resp.recommendations.length > 0) {
                                const main = resp.recommendations[0];
                                const score = (main.score || 0).toFixed(0);
                                
                                // Main recommendation
                                document.getElementById('moRecName').textContent = main.nom_recette || main.nom || 'Repas';
                                document.getElementById('moRecScore').textContent = score + '/100';
                                
                                // Scores breakdown
                                const scoresHtml = `
                                    <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px;">
                                        <span style="display: inline-block; margin: 4px; background: rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                            💪 Perte poids: ${(main.score_perte_poids || 0).toFixed(0)}
                                        </span>
                                        <span style="display: inline-block; margin: 4px; background: rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                            😋 Plaisir: ${(main.score_plaisir || 0).toFixed(0)}
                                        </span>
                                        <span style="display: inline-block; margin: 4px; background: rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                            💰 Budget: ${(main.score_budget || 0).toFixed(0)}
                                        </span>
                                        <span style="display: inline-block; margin: 4px; background: rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                            ⏱️ Rapidité: ${(main.score_rapidite || 0).toFixed(0)}
                                        </span>
                                        <span style="display: inline-block; margin: 4px; background: rgba(255,255,255,0.3); padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                            🌍 Écologie: ${(main.score_ecologie || 0).toFixed(0)}
                                        </span>
                                    </div>
                                `;
                                document.getElementById('moRecScores').innerHTML = scoresHtml;
                                resultDiv.style.display = 'block';
                                
                                // Alternatives
                                if (resp.recommendations.length > 1) {
                                    let altHtml = '';
                                    resp.recommendations.slice(1, 4).forEach(alt => {
                                        const altScore = (alt.score || 0).toFixed(0);
                                        altHtml += `
                                            <div style="background: white; padding: 15px; border-radius: 10px; border: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                                <div>
                                                    <div style="font-weight: 700; color: var(--vert-fonce);">${alt.nom_recette || alt.nom || 'Repas'}</div>
                                                    <div style="font-size: 0.85rem; color: #999;">Score: ${altScore}/100</div>
                                                </div>
                                                <button class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">Choisir</button>
                                            </div>
                                        `;
                                    });
                                    document.getElementById('moAltList').innerHTML = altHtml;
                                    altDiv.style.display = 'block';
                                }
                            }
                        }).catch(err => console.error(err));
                    }

                    // ── Jumeau Functions ──
                    function getJumeauStats() {
                        const planId = <?= (int)($currentPlan['id'] ?? 0) ?>;
                        if (!planId) return;
                        
                        ajaxPost('CONTROLLER/JumeauController.php', {
                            action: 'get_twin_stats',
                            plan_id: planId
                        }).then(resp => {
                            const resultDiv = document.getElementById('jumeauResult');
                            if (resp.success && resp.stats) {
                                const s = resp.stats;
                                const avgKcal = s.days ? (s.totalCalories / s.days | 0) : 0;
                                let html = `
                                    <h4 style="color: var(--bleu-turquoise); margin: 0 0 15px;">📊 Statistiques de votre plan</h4>
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                                        <div style="background: white; padding: 15px; border-radius: 10px; border-left: 4px solid var(--bleu-turquoise); box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                            <div style="font-size: 0.85rem; color: #666; font-weight: 600;">Total Calories</div>
                                            <div style="font-size: 1.8rem; font-weight: 800; color: var(--bleu-turquoise);">${s.totalCalories || 0}</div>
                                            <div style="font-size: 0.8rem; color: #999;">kcal</div>
                                        </div>
                                        <div style="background: white; padding: 15px; border-radius: 10px; border-left: 4px solid var(--orange); box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                            <div style="font-size: 0.85rem; color: #666; font-weight: 600;">Nombre de jours</div>
                                            <div style="font-size: 1.8rem; font-weight: 800; color: var(--orange);">${s.days || 0}</div>
                                            <div style="font-size: 0.8rem; color: #999;">j</div>
                                        </div>
                                        <div style="background: white; padding: 15px; border-radius: 10px; border-left: 4px solid var(--vert-clair); box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                            <div style="font-size: 0.85rem; color: #666; font-weight: 600;">Moyenne/jour</div>
                                            <div style="font-size: 1.8rem; font-weight: 800; color: var(--vert-clair);">${avgKcal}</div>
                                            <div style="font-size: 0.8rem; color: #999;">kcal</div>
                                        </div>
                                        ${s.forecast ? `
                                        <div style="background: white; padding: 15px; border-radius: 10px; border-left: 4px solid #9b59b6; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                                            <div style="font-size: 0.85rem; color: #666; font-weight: 600;">Prévision</div>
                                            <div style="font-size: 1.8rem; font-weight: 800; color: #9b59b6;">${s.forecast.toFixed(0)}</div>
                                            <div style="font-size: 0.8rem; color: #999;">kcal</div>
                                        </div>
                                        ` : ''}
                                    </div>
                                `;
                                resultDiv.innerHTML = html;
                                resultDiv.style.display = 'block';
                            } else {
                                resultDiv.innerHTML = '<div style="color: #e74c3c; font-weight: 600;">❌ ' + (resp.message || 'Impossible de charger') + '</div>';
                                resultDiv.style.display = 'block';
                            }
                        }).catch(err => {
                            document.getElementById('jumeauResult').innerHTML = '<div style="color: #e74c3c; font-weight: 600;">❌ Erreur réseau</div>';
                            document.getElementById('jumeauResult').style.display = 'block';
                        });
                    }

                    function showJumeauPredictionInput() {
                        document.getElementById('jumeauPredictionInput').style.display = 'block';
                        document.getElementById('jumeauSimulationInput').style.display = 'none';
                    }

                    function getJumeauPrediction() {
                        const planId = <?= (int)($currentPlan['id'] ?? 0) ?>;
                        const nbJours = parseInt(document.getElementById('jumeauNbJours').value) || 7;
                        if (!planId) return;
                        
                        ajaxPost('CONTROLLER/JumeauController.php', {
                            action: 'get_prediction',
                            plan_id: planId,
                            nb_jours: nbJours
                        }).then(resp => {
                            const resultDiv = document.getElementById('jumeauResult');
                            if (resp.success) {
                                let html = `
                                    <h4 style="color: var(--bleu-turquoise); margin: 0 0 15px;">🔮 Prédiction sur ${nbJours} jours</h4>
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                                        <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid #3498db;">
                                            <div style="font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 8px;">Calories prévues</div>
                                            <div style="font-size: 2rem; font-weight: 800; color: #3498db;">${resp.forecast ? resp.forecast.toFixed(0) : 'N/A'}</div>
                                            <div style="font-size: 0.85rem; color: #999;">kcal total</div>
                                        </div>
                                        <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid #27ae60;">
                                            <div style="font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 8px;">Moyenne par jour</div>
                                            <div style="font-size: 2rem; font-weight: 800; color: #27ae60;">${resp.forecast ? (resp.forecast / nbJours | 0) : 'N/A'}</div>
                                            <div style="font-size: 0.85rem; color: #999;">kcal/jour</div>
                                        </div>
                                    </div>
                                `;
                                resultDiv.innerHTML = html;
                                resultDiv.style.display = 'block';
                                document.getElementById('jumeauPredictionInput').style.display = 'none';
                            } else {
                                resultDiv.innerHTML = '<div style="color: #e74c3c; font-weight: 600;">❌ ' + (resp.message || 'Erreur') + '</div>';
                                resultDiv.style.display = 'block';
                            }
                        }).catch(err => {
                            document.getElementById('jumeauResult').innerHTML = '<div style="color: #e74c3c; font-weight: 600;">❌ Erreur réseau</div>';
                            document.getElementById('jumeauResult').style.display = 'block';
                        });
                    }

                    function showJumeauSimulationInput() {
                        document.getElementById('jumeauSimulationInput').style.display = 'block';
                        document.getElementById('jumeauPredictionInput').style.display = 'none';
                    }

                    function simulateJumeauEcart() {
                        const planId = <?= (int)($currentPlan['id'] ?? 0) ?>;
                        const ecart = parseInt(document.getElementById('jumeauEcart').value) || 0;
                        if (!planId) return;
                        
                        ajaxPost('CONTROLLER/JumeauController.php', {
                            action: 'simulate_ecart',
                            plan_id: planId,
                            ecart: ecart
                        }).then(resp => {
                            const resultDiv = document.getElementById('jumeauResult');
                            if (resp.success && resp.simulation) {
                                const sim = resp.simulation;
                                const diff = (sim.simulatedTotal || 0) - (sim.originalTotal || 0);
                                let html = `
                                    <h4 style="color: var(--bleu-turquoise); margin: 0 0 15px;">🎯 Simulation (écart ${ecart > 0 ? '+' : ''}${ecart} kcal/jour)</h4>
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                                        <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid #3498db;">
                                            <div style="font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 8px;">Total original</div>
                                            <div style="font-size: 1.8rem; font-weight: 800; color: #3498db;">${sim.originalTotal || 0}</div>
                                            <div style="font-size: 0.85rem; color: #999;">kcal</div>
                                        </div>
                                        <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid ${diff > 0 ? '#e74c3c' : '#27ae60'};">
                                            <div style="font-size: 0.9rem; color: #666; font-weight: 600; margin-bottom: 8px;">Total simulé</div>
                                            <div style="font-size: 1.8rem; font-weight: 800; color: ${diff > 0 ? '#e74c3c' : '#27ae60'};">${sim.simulatedTotal || 0}</div>
                                            <div style="font-size: 0.85rem; color: #999;">kcal</div>
                                        </div>
                                    </div>
                                    <div style="background: white; padding: 15px; border-radius: 10px; margin-top: 12px; border-left: 4px solid ${diff > 0 ? '#e74c3c' : '#27ae60'};">
                                        <div style="font-weight: 700; color: ${diff > 0 ? '#e74c3c' : '#27ae60'};">
                                            Différence: ${diff > 0 ? '+' : ''}${diff} kcal
                                        </div>
                                    </div>
                                `;
                                resultDiv.innerHTML = html;
                                resultDiv.style.display = 'block';
                                document.getElementById('jumeauSimulationInput').style.display = 'none';
                            } else {
                                resultDiv.innerHTML = '<div style="color: #e74c3c; font-weight: 600;">❌ ' + (resp.message || 'Erreur') + '</div>';
                                resultDiv.style.display = 'block';
                            }
                        }).catch(err => {
                            document.getElementById('jumeauResult').innerHTML = '<div style="color: #e74c3c; font-weight: 600;">❌ Erreur réseau</div>';
                            document.getElementById('jumeauResult').style.display = 'block';
                        });
                    }

                    // ── Utility ──
                    function ajaxPost(endpoint, data) {
                        return new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', endpoint, true);
                            xhr.setRequestHeader('Content-Type', 'application/json');
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === 4) {
                                    if (xhr.status >= 200 && xhr.status < 300) {
                                        try { resolve(JSON.parse(xhr.responseText)); }
                                        catch (e) { reject(e); }
                                    } else {
                                        try { reject(JSON.parse(xhr.responseText)); }
                                        catch (e) { reject(new Error('HTTP ' + xhr.status)); }
                                    }
                                }
                            };
                            xhr.onerror = () => reject(new Error('Network error'));
                            xhr.send(JSON.stringify(data));
                        });
                    }

                    // Update slider labels in real-time
                    ['perte_poids', 'plaisir', 'budget', 'rapidite', 'ecologie'].forEach(key => {
                        const input = document.getElementById('weight-' + key);
                        if (input) {
                            input.addEventListener('input', function() {
                                document.getElementById('w-' + key).textContent = this.value;
                            });
                        }
                    });

                    renderMeals();
                    
                    </script>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #999;">
                        <p>Aucun plan nutritionnel créé.</p>
                        <a href="plan.php?page=plan-nutritionnel" style="color: var(--bleu-turquoise); text-decoration: none; font-weight: 700;">
                            Créer un plan →
                        </a>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </section>
    <?php if ($currentPlan): ?>
        <script src="/integweb/Assets/chatbot.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                try {
                    if (window.ChatbotCoach && <?= (int)($currentPlan['id'] ?? 0) ?>) {
                        window.ChatbotCoach.init(<?= (int)($currentPlan['id'] ?? 0) ?>);
                    }
                } catch(e) { console.error('Chatbot init error', e); }
            });
        </script>
    <?php endif; ?>
</body>
</html>
