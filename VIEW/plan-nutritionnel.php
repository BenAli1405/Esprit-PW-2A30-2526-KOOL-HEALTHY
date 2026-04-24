<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Plan Nutritionnel - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/styles.css">
    <style>
        :root {
            --vert-clair: #8BC34A;
            --vert-fonce: #4E8E2A;
            --bleu-turquoise: #4BA3A6;
            --orange: #E67E22;
            --blanc: #FFFFFF;
            --gris-clair: #F5F5F5;
            --gris-moyen: #E0E0E0;
            --gris-texte: #616161;
            --ombre-legere: 0 2px 8px rgba(0, 0, 0, 0.05);
            --text: #2C3E2F;
        }

        .nutrition-page {
            display: grid;
            gap: 24px;
        }

        .nutrition-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            padding: 28px 30px;
            background: linear-gradient(135deg, rgba(139, 195, 74, 0.15), rgba(75, 163, 166, 0.2));
        }

        .nutrition-header h1 {
            margin: 0;
            color: var(--vert-fonce);
            font-size: clamp(1.9rem, 3vw, 2.5rem);
        }

        .nutrition-header p {
            margin: 8px 0 0;
            color: #4A5B4E;
            font-size: 1.06rem;
        }

        .nutrition-chip {
            display: inline-block;
            background: var(--blanc);
            color: var(--bleu-turquoise);
            border: 1px solid var(--bleu-turquoise);
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            font-size: 0.96rem;
        }

        .navbar {
            background: var(--white);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: var(--ombre-legere);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--gris-moyen);
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: auto;
            max-width: 100%;
            text-decoration: none;
            color: var(--vert-fonce);
            font-weight: 800;
            font-size: 20px;
            gap: 8px;
        }

        .logo img {
            display: block;
            width: 100%;
            height: auto;
            max-width: 180px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links button {
            border: none;
            background: none;
            color: #4A5B4E;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
        }

        .nav-links button:hover {
            color: var(--bleu-turquoise);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--bleu-turquoise);
            color: var(--bleu-turquoise);
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-link,
        .btn-connect {
            background: var(--vert-fonce);
            color: var(--blanc);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
        }

        .auth-link:hover,
        .btn-connect:hover,
        .btn-outline:hover {
            filter: brightness(1.05);
        }

        .plan-builder {
            border: 2px solid rgba(78, 142, 42, 0.24);
            border-radius: 18px;
            background: var(--blanc);
            box-shadow: 0 20px 50px rgba(78, 142, 42, 0.08);
            padding: 28px;
        }

        .builder-block {
            border: 1px solid rgba(78, 142, 42, 0.15);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
            background: #f5fbef;
        }

        .choice-row label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 1.05rem;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(75, 163, 166, 0.14);
            color: var(--text);
        }

        .choice-row input {
            transform: scale(1.1);
            accent-color: var(--vert-clair);
        }

        .generate-btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--bleu-turquoise), var(--vert-clair));
            color: var(--blanc);
            font-weight: 800;
            font-size: 1.12rem;
            padding: 14px;
            cursor: pointer;
        }

        .generate-btn:hover {
            filter: brightness(1.05);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(140px, 1fr));
            gap: 12px;
        }

        .summary-card {
            background: var(--white);
            border: 1px solid var(--gris-moyen);
            border-radius: 14px;
            padding: 14px;
        }

        .summary-card .label {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .summary-card .value {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--vert-fonce);
        }

        .entity-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .entity-card {
            border: 2px solid var(--vert-fonce);
            border-radius: 18px;
            overflow: hidden;
            background: var(--white);
        }

        .entity-card h2 {
            margin: 0;
            padding: 16px 20px;
            background: linear-gradient(135deg, #d8ecb2, #c8e19e);
            color: #184d2f;
            font-size: 1.6rem;
            text-align: center;
        }

        .entity-fields {
            margin: 0;
            padding: 18px 26px 22px;
            list-style: none;
            display: grid;
            gap: 8px;
            color: #244f3a;
            font-size: 1.1rem;
        }

        .entity-fields li::before {
            content: "- ";
            color: #244f3a;
            font-weight: 600;
        }

        .relation-block {
            display: grid;
            justify-items: center;
            gap: 6px;
            color: #1c5339;
            font-weight: 800;
            margin: 2px 0;
        }

        .relation-line {
            width: 2px;
            height: 34px;
            background: #2a6b4d;
        }

        .relation-arrow {
            width: 0;
            height: 0;
            border-left: 9px solid transparent;
            border-right: 9px solid transparent;
            border-top: 14px solid #2a6b4d;
        }

        .module-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .plan-builder {
            border: 2px solid #24523b;
            border-radius: 18px;
            background: var(--white);
            padding: 28px;
        }

        .builder-title {
            margin: 0 0 18px;
            color: #173f2d;
            font-size: 1.6rem;
            font-weight: 800;
        }

        .builder-block {
            border: 1px solid #24523b;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 16px;
        }

        .builder-label {
            margin: 0 0 12px;
            color: #173f2d;
            font-weight: 800;
            font-size: 1.14rem;
            letter-spacing: 0.02em;
        }

        .choice-row {
            display: flex;
            gap: 26px;
            flex-wrap: wrap;
            color: #1f5139;
        }

        .choice-row label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .choice-row input {
            transform: scale(1.1);
        }

        .allergy-input {
            width: 100%;
            border: 1px solid var(--gris-moyen);
            border-radius: 12px;
            padding: 12px 14px;
            font: inherit;
            font-size: 1.02rem;
            color: var(--text);
            background: var(--white);
        }

        .generate-wrap {
            margin-top: 16px;
            border: 2px solid rgba(78, 142, 42, 0.35);
            border-radius: 14px;
            padding: 14px;
        }

        .generate-btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--bleu-turquoise), var(--vert-clair));
            color: var(--blanc);
            font-weight: 800;
            font-size: 1.12rem;
            padding: 14px;
            cursor: pointer;
        }

        .generate-btn:hover {
            filter: brightness(0.95);
        }

        .panel-title {
            margin: 0 0 14px;
            color: var(--vert-fonce);
            font-size: 1.2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .form-grid input,
        .form-grid select,
        .toolbar select,
        .toolbar input {
            width: 100%;
            border: 1px solid var(--gris-moyen);
            border-radius: 10px;
            padding: 10px;
            font: inherit;
            background: var(--white);
        }

        .form-grid .full {
            grid-column: span 2;
        }

        .toolbar {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 10px;
            margin-bottom: 12px;
        }

        .btn {
            border: 0;
            border-radius: 999px;
            background: var(--vert-clair);
            color: var(--blanc);
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn.secondary {
            background: var(--bleu-turquoise);
        }

        .action-row {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .planner-wrap {
            overflow-x: auto;
            border: 1px solid var(--gris-moyen);
            border-radius: 12px;
        }

        .planner-table,
        .meals-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
            background: var(--white);
        }

        .planner-table th,
        .planner-table td,
        .meals-table th,
        .meals-table td {
            border-bottom: 1px solid var(--gris-moyen);
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .planner-table th,
        .meals-table th {
            background: #f6fbf7;
            color: var(--vert-fonce);
            font-size: 0.88rem;
        }

        .planner-cell {
            font-size: 0.85rem;
            line-height: 1.35;
        }

        .status {
            display: inline-block;
            font-size: 0.76rem;
            border-radius: 999px;
            padding: 3px 8px;
            margin-top: 6px;
            font-weight: 700;
        }

        .status.valid {
            background: #e8f5e9;
            color: #2f7a34;
        }

        .status.pending {
            background: #fff4e5;
            color: #9c6800;
        }

        .status.missed {
            background: #fde8e8;
            color: #a72828;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .stat-box {
            background: var(--white);
            border: 1px solid var(--gris-moyen);
            border-radius: 12px;
            padding: 14px;
        }

        .stat-box h4 {
            margin: 0 0 10px;
            color: var(--vert-fonce);
        }

        .meter {
            height: 12px;
            border-radius: 999px;
            background: var(--gris-clair);
            overflow: hidden;
            border: 1px solid var(--gris-moyen);
        }

        .meter > span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, var(--vert-clair), var(--bleu-turquoise));
        }

        .hint {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 8px;
        }

        .meals-panel {
            padding: 20px;
        }

        .meals-panel h3 {
            margin: 0 0 14px;
            color: var(--vert-fonce);
        }

        .meal-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .meal-card {
            border: 1px solid var(--gris-moyen);
            border-left: 4px solid var(--bleu-turquoise);
            border-radius: 12px;
            padding: 12px;
            background: var(--white);
        }

        .meal-card strong {
            color: var(--bleu-turquoise);
            display: block;
            margin-bottom: 4px;
        }

        .meal-card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* Styles for buttons to look like links */
        .brand {
            border: none;
            background: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--vert-fonce);
            font-weight: 800;
            font-size: 2rem;
        }

        .top-nav button {
            border: none;
            background: none;
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .top-nav button:hover {
            text-decoration: underline;
        }

        .auth-link {
            border: none;
            background: var(--vert-fonce);
            color: var(--blanc);
            border-radius: 999px;
            padding: 10px 18px;
            font-weight: 700;
            cursor: pointer;
        }

        .top-bar {
            background: var(--white);
            border-bottom: 1px solid var(--gris-moyen);
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin: 0;
        }

        .top-bar .page-title h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--vert-fonce);
        }

        .top-bar .page-title p {
            margin: 8px 0 0;
            color: var(--gris-texte);
        }

        .horizontal-bar {
            background: linear-gradient(90deg, rgba(139, 195, 74, 0.08), rgba(75, 163, 166, 0.06));
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
            border-top: 1px solid rgba(200, 200, 200, 0.15);
            border-bottom: 1px solid rgba(200, 200, 200, 0.15);
        }

        .hbar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 2px solid #999;
            border-radius: 12px;
            padding: 8px 16px;
            background: var(--blanc);
        }

        .hbar-logo i {
            font-size: 1.6rem;
            color: var(--vert-clair);
        }

        .hbar-logo h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--vert-fonce);
        }

        .hbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(139, 195, 74, 0.15);
            border-radius: 40px;
            padding: 8px 16px;
        }

        .hbar-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--vert-clair);
            color: var(--blanc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .hbar-text {
            font-weight: 600;
            color: var(--vert-fonce);
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--bleu-turquoise), var(--vert-clair));
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: 600;
            color: var(--blanc);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-primary:hover {
            filter: brightness(1.05);
        }

        @media (max-width: 760px) {
            .summary-grid,
            .module-grid,
            .stats-grid,
            .form-grid,
            .toolbar {
                grid-template-columns: 1fr;
            }

            .choice-row {
                gap: 12px;
                flex-direction: column;
                align-items: flex-start;
            }

            .form-grid .full {
                grid-column: auto;
            }

            .entity-fields {
                font-size: 1rem;
                padding: 16px 18px 18px;
            }

            .nutrition-header {
                padding: 20px;
            }

            .plan-builder {
                padding: 18px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="logo" href="plan-nutritionnel.php" aria-label="Kool Healthy">
            <svg width="50" height="50" viewBox="0 0 200 200" style="margin-right: 10px; vertical-align: middle;">
                <!-- Leaves -->
                <path d="M 50 80 Q 40 60 50 40 Q 60 50 60 80" fill="#7CB342" stroke="#7CB342" stroke-width="2"/>
                <path d="M 70 90 Q 65 70 80 50 Q 85 70 85 90" fill="#9CCC65" stroke="#9CCC65" stroke-width="2"/>
                <!-- Orange curve -->
                <path d="M 40 100 Q 80 80 120 110" stroke="#FF9800" stroke-width="8" fill="none" stroke-linecap="round"/>
                <!-- Dots -->
                <circle cx="120" cy="70" r="6" fill="#FFC107"/>
                <circle cx="110" cy="100" r="5" fill="#9C27B0"/>
                <circle cx="125" cy="95" r="4" fill="#7C4DFF"/>
                <!-- Text: Kool -->
                <text x="130" y="110" font-family="Arial, sans-serif" font-size="28" font-weight="700" fill="#FF9800">Kool</text>
                <!-- Text: HEALTHY -->
                <text x="60" y="160" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#4A9B8E">HEALTHY</text>
            </svg>
            KOOL HEALTHY
        </a>
        <div class="nav-links">
            <button onclick="window.location.href='../backoffice'">Accueil</button>
            <button onclick="window.location.href='../backoffice'">Fonctionnalités</button>
            <button onclick="window.location.href='../../plan/plan-adapte'">Plan</button>
            <button onclick="window.location.href='../backoffice'">Recettes</button>
            <button onclick="window.location.href='../backoffice'">Impact</button>
        </div>
        <div class="nav-actions">
            <button class="btn-outline" onclick="window.location.href='../backoffice'">S'inscrire</button>
            <button class="btn-connect" onclick="window.location.href='../backoffice'">Se connecter</button>
        </div>
    </nav>

    <div class="horizontal-bar">
        <div class="hbar-logo">
            <i class="fas fa-seedling"></i>
            <h2>Kool Healthy</h2>
        </div>
        <div class="hbar-info">
            <div class="hbar-avatar">KH</div>
            <div class="hbar-text">Maintien - 7j</div>
        </div>
    </div>

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

                        <form action="index.php?page=plan-nutritionnel" method="POST" onsubmit="return validateAndPreparePlanForm(this);">
                            <input type="hidden" name="action_type" value="plan">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="nom" id="autoNom" value="">
                            <input type="hidden" name="utilisateur_id" id="autoUserId" value="">
                            <input type="hidden" id="plansCount" value="<?= $nextUserId ?? 1 ?>">
                            <input type="hidden" name="preference" id="autoPreference" value="">
                            <div id="formErrors" style="display:none;"></div>

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
                                <input type="text" name="duree" id="dureeInput" placeholder="ex: 7, 14, 21, 30" style="width:100%; border:1px solid #e0e0e0; border-radius:10px; padding:12px 14px; font:inherit;">
                                <small style="color:#666; display:block; margin-top:5px;">La durée minimum est de 7 jours.</small>
                            </div>

                            <div class="builder-block">
                                <p class="builder-label">🥗 PREFERENCES :</p>
                                <input type="text" name="preference_input" id="preferenceInput" placeholder="ex: Végétarien, Sans gluten, Sans lactose" style="width:100%; border:1px solid #e0e0e0; border-radius:10px; padding:12px 14px; font:inherit;">
                                <small style="color:#666; display:block; margin-top:5px;">Séparez vos préférences par des virgules.</small>
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
                        function validateAndPreparePlanForm(form) {
                            var errors = [];

                            // 1. Vérifier que l'objectif est sélectionné
                            var objectifRadio = form.querySelector('input[name="objectif"]:checked');
                            if (!objectifRadio) {
                                errors.push("Veuillez choisir un objectif.");
                            }

                            // 2. Vérifier la durée (champ texte)
                            var duree = document.getElementById('dureeInput').value.trim();
                            if (duree === "") {
                                errors.push("La durée est obligatoire.");
                            } else if (!/^[0-9]+$/.test(duree)) {
                                errors.push("La durée doit être un nombre valide.");
                            } else if (parseInt(duree) < 7) {
                                errors.push("La durée doit être au minimum de 7 jours.");
                            } else if (parseInt(duree) > 365) {
                                errors.push("La durée ne peut pas dépasser 365 jours.");
                            }

                            // 3. Vérifier la préférence
                            var preference = document.getElementById('preferenceInput').value.trim();
                            if (preference === "") {
                                errors.push("La préférence alimentaire est obligatoire.");
                            } else if (preference.length > 255) {
                                errors.push("La préférence ne peut pas dépasser 255 caractères.");
                            }

                            // 4. Vérifier les allergies
                            var allergies = form.querySelector('input[name="allergies"]').value.trim();
                            if (allergies !== "") {
                                if (allergies.length > 1000) {
                                    errors.push("Les allergies ne peuvent pas dépasser 1000 caractères.");
                                }
                                if (/[<>]/.test(allergies)) {
                                    errors.push("Le champ allergies ne doit pas contenir les caractères < ou >.");
                                }
                            }

                            // 5. Afficher les erreurs
                            var errorDiv = document.getElementById('formErrors');
                            if (errors.length > 0) {
                                errorDiv.innerHTML = '<ul style="margin:0;padding-left:18px;">' +
                                    errors.map(function(e) { return '<li>' + e + '</li>'; }).join('') + '</ul>';
                                errorDiv.style.display = 'block';
                                errorDiv.style.padding = '14px 18px';
                                errorDiv.style.borderRadius = '14px';
                                errorDiv.style.marginBottom = '18px';
                                errorDiv.style.background = '#fbe9e7';
                                errorDiv.style.color = '#b71c1c';
                                errorDiv.style.border = '1px solid #f5c6cb';
                                errorDiv.style.fontWeight = '600';
                                return false;
                            }
                            errorDiv.style.display = 'none';

                            // 6. Préparer les champs cachés pour l'envoi
                            var objectifLabels = {
                                'perte-poids': 'Perte de poids',
                                'maintien': 'Maintien',
                                'prise-muscle': 'Prise de muscle'
                            };
                            var label = objectifLabels[objectifRadio.value] || 'Nutritionnel';
                            document.getElementById('autoNom').value = 'Plan ' + label;

                            var nextUserId = document.getElementById('plansCount').value;
                            document.getElementById('autoUserId').value = parseInt(nextUserId);

                            document.getElementById('autoPreference').value = preference;

                            if (allergies === "") {
                                form.querySelector('input[name="allergies"]').value = 'Aucune';
                            }

                            return true;
                        }
                        </script>
                    </section>

                </div>
            </main>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 Kool Healthy. Mangez mieux, preservez la planete.</p>
        </div>
    </footer>
</body>
</html>
