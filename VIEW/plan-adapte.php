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
    <link rel="stylesheet" href="/Gamification/CSS/styles.css">
    <link rel="stylesheet" href="/Gamification/Assets/multi-objective.css">
    <link rel="stylesheet" href="/Gamification/Assets/jumeau.css">
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

                    <div class="mo-container">
                        <div class="mo-header">
                            <div class="mo-header-icon">⚖️</div>
                            <div style="flex: 1;">
                                <h3 class="mo-header-title">Orchestrateur Multi-Objectifs</h3>
                                <div class="mo-header-sub">Le moteur intelligent qui équilibre vos priorités</div>
                            </div>
                            <button class="btn btn-primary" type="button" onclick="generateRestaurantQRCode()">📱 Générer QR Code Restaurants</button>
                        </div>

                        <div class="mo-grid">
                            <div class="mo-sliders-container">
                                <div class="mo-slider-group">
                                    <div class="mo-slider-header">
                                        <span class="mo-slider-label">Perte de poids</span>
                                        <span class="mo-slider-value" id="mo-weight-perte-val">20%</span>
                                    </div>
                                    <input class="mo-slider" type="range" id="mo-weight-perte" min="0" max="100" value="20">
                                </div>
                                <div class="mo-slider-group">
                                    <div class="mo-slider-header">
                                        <span class="mo-slider-label">Plaisir gustatif</span>
                                        <span class="mo-slider-value" id="mo-weight-plaisir-val">20%</span>
                                    </div>
                                    <input class="mo-slider" type="range" id="mo-weight-plaisir" min="0" max="100" value="20">
                                </div>
                                <div class="mo-slider-group">
                                    <div class="mo-slider-header">
                                        <span class="mo-slider-label">Budget</span>
                                        <span class="mo-slider-value" id="mo-weight-budget-val">20%</span>
                                    </div>
                                    <input class="mo-slider" type="range" id="mo-weight-budget" min="0" max="100" value="20">
                                </div>
                                <div class="mo-slider-group">
                                    <div class="mo-slider-header">
                                        <span class="mo-slider-label">Rapidité</span>
                                        <span class="mo-slider-value" id="mo-weight-rapidite-val">20%</span>
                                    </div>
                                    <input class="mo-slider" type="range" id="mo-weight-rapidite" min="0" max="100" value="20">
                                </div>
                                <div class="mo-slider-group">
                                    <div class="mo-slider-header">
                                        <span class="mo-slider-label">Écologie</span>
                                        <span class="mo-slider-value" id="mo-weight-eco-val">20%</span>
                                    </div>
                                    <input class="mo-slider" type="range" id="mo-weight-eco" min="0" max="100" value="20">
                                </div>
                            </div>

                            <div id="mo-results"></div>
                        </div>
                    </div>

                    <div class="mo-container" style="margin-top: 20px;">
                        <div class="mo-header">
                            <div class="mo-header-icon">🧬</div>
                            <div>
                                <h3 class="mo-header-title">Jumeau Nutritionnel</h3>
                                <div class="mo-header-sub">Prédictions et simulations personnalisées</div>
                            </div>
                        </div>
                        <div id="twin-container"></div>
                    </div>

                    <div id="editCriteresModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9997;align-items:center;justify-content:center;padding:12px;">
                        <div style="background:#fff;border-radius:12px;padding:16px;max-width:420px;width:100%;">
                            <h3 style="margin-top:0;color:var(--vert-fonce);">Modifier les critères du repas</h3>
                            <form id="editCriteresForm" onsubmit="event.preventDefault(); saveCriteres();">
                                <input type="hidden" id="critere_repas_id">
                                <label style="display:block;margin-bottom:8px;">Prix (€)</label>
                                <input id="critere_prix" type="number" step="0.1" min="0" max="50" style="width:100%;padding:8px;margin-bottom:10px;">
                                <label style="display:block;margin-bottom:8px;">Temps préparation (min)</label>
                                <input id="critere_temps" type="number" min="1" max="180" style="width:100%;padding:8px;margin-bottom:10px;">
                                <label style="display:block;margin-bottom:8px;">Eco-score</label>
                                <select id="critere_eco" style="width:100%;padding:8px;margin-bottom:10px;">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                </select>
                                <label style="display:block;margin-bottom:8px;">Plaisir (1-10)</label>
                                <input id="critere_plaisir" type="number" min="1" max="10" style="width:100%;padding:8px;margin-bottom:14px;">
                                <div style="display:flex;gap:8px;justify-content:flex-end;">
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('editCriteresModal').style.display='none'">Annuler</button>
                                    <button type="submit" class="btn btn-primary">✓ Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="qrCodeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9996;align-items:center;justify-content:center;padding:12px;">
                        <div style="background:#fff;border-radius:12px;padding:16px;max-width:430px;width:100%;text-align:center;">
                            <h3 style="margin-top:0;color:var(--vert-fonce);">Restaurants Recommandés</h3>
                            <img id="qrCodeImage" alt="QR Code" style="max-width:250px;width:100%;height:auto;border-radius:8px;">
                            <p style="margin:12px 0;"><a id="qrCodeLink" href="#" target="_blank" rel="noopener noreferrer">🔗 Ouvrir</a></p>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('qrCodeModal').style.display='none'">Fermer</button>
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
                        if (content) content.classList.toggle('collapsed');
                    }

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
        <script>
            window.APP_SERVER_IP = <?= json_encode($_SERVER['SERVER_ADDR'] ?? '127.0.0.1') ?>;
        </script>
        <script src="/Gamification/Assets/multi-objective.js"></script>
        <script src="/Gamification/Assets/jumeau.js"></script>
        <script src="/Gamification/Assets/qrcode-generator.js"></script>
        <script src="/Gamification/Assets/chatbot.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function(){
                try {
                    if (window.MultiObjectiveInit && <?= (int)($currentPlan['id'] ?? 0) ?>) {
                        window.MultiObjectiveInit(<?= (int)($currentPlan['id'] ?? 0) ?>);
                    }
                    if (window.JumeauInit && <?= (int)($currentPlan['id'] ?? 0) ?>) {
                        window.JumeauInit(<?= (int)($currentPlan['id'] ?? 0) ?>);
                    }
                    if (window.ChatbotCoach && <?= (int)($currentPlan['id'] ?? 0) ?>) {
                        window.ChatbotCoach.init(<?= (int)($currentPlan['id'] ?? 0) ?>);
                    }
                } catch(e) { console.error('Chatbot init error', e); }
            });
        </script>
    <?php endif; ?>
</body>
</html>
