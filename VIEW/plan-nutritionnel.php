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
    <title>Plan Nutritionnel - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Gamification/CSS/styles.css">
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

        .nutrition-page {
            display: grid;
            gap: 20px;
        }

        .plan-builder {
            border: 2px solid var(--vert-fonce);
            border-radius: 12px;
            padding: 25px;
        }

        .builder-title {
            margin: 0 0 20px;
            color: var(--vert-fonce);
            font-size: 1.4rem;
            font-weight: 700;
        }

        .builder-block {
            border: 1px solid var(--gris-moyen);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9fbf8;
        }

        .builder-label {
            margin: 0 0 10px;
            color: var(--vert-fonce);
            font-weight: 700;
        }

        .choice-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin: 10px 0;
        }

        .choice-row label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            cursor: pointer;
        }

        .choice-row input {
            accent-color: var(--vert-clair);
            cursor: pointer;
        }

        input, select, textarea {
            font-family: inherit;
        }

        .allergy-input,
        .builder-block input,
        .builder-block select,
        .builder-block textarea {
            width: 100%;
            border: 1px solid var(--gris-moyen);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .generate-wrap {
            margin-top: 20px;
            padding: 15px;
            border: 2px solid var(--vert-clair);
            border-radius: 10px;
        }

        .generate-btn {
            width: 100%;
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, var(--vert-clair), var(--bleu-turquoise));
            color: var(--blanc);
            font-weight: 700;
            font-size: 1rem;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .generate-btn:hover {
            filter: brightness(1.05);
        }

        .day-block {
            background: var(--blanc);
            border: 1px solid var(--gris-moyen);
            border-radius: 8px;
            margin-bottom: 12px;
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

        .day-footer {
            padding: 12px 15px;
            background: var(--gris-clair);
            border-top: 1px solid var(--gris-moyen);
            text-align: right;
            font-size: 0.9rem;
            font-weight: 600;
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

        .meal-action {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            margin-top: 8px;
        }

        .meal-action.consumed {
            background: var(--vert-clair);
            color: var(--blanc);
        }

        .meal-action.pending {
            background: #d4e8d4;
            color: var(--vert-fonce);
        }

        .recommendation {
            background: #e0f2f1;
            border-left: 4px solid var(--bleu-turquoise);
            padding: 16px;
            margin: 20px 0;
            border-radius: 8px;
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

        .btn-tertiary {
            background: var(--vert-clair);
            color: var(--blanc);
        }

        .btn:hover {
            filter: brightness(1.05);
        }

        .error-box {
            background: #fbe9e7;
            color: #b71c1c;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .section-wrap { padding: 20px; }
            .profile-full-wrapper { padding: 20px; }
            .plan-builder { padding: 20px; }
            .choice-row { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <section class="section-wrap recipes-section">
        <div class="profile-full-wrapper">
            <main>
                <div class="nutrition-page">
                    <section class="plan-builder">
                        <h3 class="builder-title">✨ CREER UN PLAN NUTRITIONNEL</h3>

                        <?php if (!empty($message)): ?>
                        <div style="padding:14px 18px; border-radius:14px; margin-bottom:18px; font-weight:600; <?= $messageType === 'success' ? 'background:#e8f5e9;color:#2f7a34;border:1px solid #c8e6c9;' : 'background:#fbe9e7;color:#b71c1c;border:1px solid #f5c6cb;' ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                        <?php endif; ?>

                        <form action="plan.php?page=plan-nutritionnel" method="POST" onsubmit="return validateForm(this);">
                            <input type="hidden" name="action_type" value="plan">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="nom" id="autoNom" value="">
                            <input type="hidden" name="preference" id="autoPreference" value="">
                            <div id="formErrors"></div>

                            <div class="builder-block">
                                <p class="builder-label">🎯 OBJECTIF :</p>
                                <div class="choice-row">
                                    <label><input type="radio" name="objectif" value="perte-poids"> Perte de poids</label>
                                    <label><input type="radio" name="objectif" value="maintien" checked> Maintien</label>
                                    <label><input type="radio" name="objectif" value="prise-muscle"> Prise de muscle</label>
                                </div>
                            </div>

                            <div class="builder-block">
                                <p class="builder-label">🗓️ DUREE (en jours) :</p>
                                <input type="text" name="duree" id="dureeInput" placeholder="ex: 7, 14, 21, 30">
                                <small style="color:#666; display:block; margin-top:5px;">Minimum 7 jours</small>
                            </div>

                            <div class="builder-block">
                                <p class="builder-label">🥗 PREFERENCES :</p>
                                <input type="text" name="preference_input" id="preferenceInput" placeholder="ex: Végétarien, Sans gluten">
                            </div>

                            <div class="builder-block">
                                <p class="builder-label">🧠 ALLERGIES :</p>
                                <input class="allergy-input" type="text" name="allergies" placeholder="ex: arachides, lactose">
                            </div>

                            <div class="generate-wrap">
                                <button class="generate-btn" type="submit">[ GENERER MON PLAN -> ]</button>
                            </div>
                        </form>

                        <script>
                        function validateForm(form) {
                            var errors = [];
                            var objectifRadio = form.querySelector('input[name="objectif"]:checked');
                            if (!objectifRadio) errors.push("Choisissez un objectif");
                            
                            var duree = document.getElementById('dureeInput').value.trim();
                            if (!duree) errors.push("Entrez la durée");
                            else if (!/^[0-9]+$/.test(duree) || parseInt(duree) < 7) errors.push("Durée invalide (minimum 7 jours)");
                            
                            var preference = document.getElementById('preferenceInput').value.trim();
                            if (!preference) errors.push("Entrez une préférence");
                            
                            var errorDiv = document.getElementById('formErrors');
                            if (errors.length > 0) {
                                errorDiv.innerHTML = '<div class="error-box"><ul style="margin:0;"><li>' + errors.join('</li><li>') + '</li></ul></div>';
                                return false;
                            }
                            
                            var labels = {'perte-poids': 'Perte de poids', 'maintien': 'Maintien', 'prise-muscle': 'Prise de muscle'};
                            document.getElementById('autoNom').value = 'Plan ' + labels[objectifRadio.value];
                            document.getElementById('autoPreference').value = preference;
                            return true;
                        }
                        </script>
                    </section>
                </div>
            </main>
        </div>
    </section>
</body>
</html>
