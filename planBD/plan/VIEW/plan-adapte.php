<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Adapté - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #4caf50;
            --green-dark: #2f8a43;
            --teal: #4a9b8e;
            --orange: #ff9800;
            --text: #2c3e2f;
            --text-light: #666;
            --panel: #ffffff;
            --bg: #f5f5f5;
            --line: #e0e0e0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", "Segoe UI", sans-serif;
            color: var(--text);
            background: var(--bg);
        }

        /* Navigation */
        .navbar {
            background: #fff;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e8e8e8;
        }

        .logo {
            font-size: 20px;
            font-weight: 800;
            color: var(--green);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: var(--green);
        }

        .btn-subscribe {
            background: var(--teal);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-login {
            background: transparent;
            color: var(--teal);
            border: 2px solid var(--teal);
            padding: 8px 18px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
        }

        /* Plan Summary Bar */
        .plan-summary {
            background: var(--green);
            color: white;
            padding: 24px 40px;
            display: flex;
            gap: 20px;
            align-items: center;
            border-radius: 12px;
            margin: 20px 40px 0;
        }

        .plan-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            flex: 1;
        }

        .plan-badges {
            display: flex;
            gap: 12px;
        }

        .badge {
            background: rgba(255,255,255,0.25);
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 40px;
        }

        /* Day Block */
        .day-block {
            background: white;
            margin-bottom: 12px;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .day-header {
            background: var(--teal);
            color: white;
            padding: 16px;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .day-header:hover {
            background: #408a7e;
        }

        .day-header.collapsed {
            background: #f5f5f5;
            color: var(--teal);
            border-bottom: 1px solid var(--line);
        }

        .day-date {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .day-toggle {
            font-size: 18px;
        }

        .day-content {
            padding: 20px;
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .day-content.collapsed {
            max-height: 0;
            padding: 0 20px;
            overflow: hidden;
        }

        /* Meal Item */
        .meal {
            margin-bottom: 20px;
            padding-left: 12px;
            border-left: 4px solid var(--green);
        }

        .meal:last-child {
            margin-bottom: 0;
        }

        .meal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .meal-title {
            font-weight: 700;
            color: var(--green);
            font-size: 15px;
        }

        .meal-kcal {
            font-size: 14px;
            color: var(--orange);
            font-weight: 700;
        }

        .meal-description {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
            margin-bottom: 10px;
        }

        .meal-action {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .meal-action.consumed {
            background: var(--green);
            color: white;
        }

        .meal-action.pending {
            background: #d4e8d4;
            color: var(--green);
        }

        .meal-action:hover {
            opacity: 0.9;
        }

        /* Day Footer */
        .day-footer {
            padding: 12px 20px;
            background: #fafafa;
            border-top: 1px solid var(--line);
            text-align: right;
            font-size: 13px;
            font-weight: 600;
        }

        .day-footer.collapsed {
            display: none;
        }

        .total-kcal {
            color: var(--orange);
        }

        .total-kcal.success {
            color: var(--green);
        }

        /* Recommendation */
        .recommendation {
            background: #e0f2f1;
            border-left: 4px solid var(--teal);
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .recommendation-title {
            font-weight: 700;
            color: var(--teal);
            font-size: 13px;
            margin-bottom: 8px;
        }

        .recommendation-content {
            font-size: 13px;
            color: #4a4a4a;
            line-height: 1.5;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 4px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--teal);
            color: white;
        }

        .btn-secondary {
            background: var(--orange);
            color: white;
        }

        .btn-tertiary {
            background: var(--green);
            color: white;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 20px;
            }

            .plan-summary {
                flex-direction: column;
                margin-left: 20px;
                margin-right: 20px;
                padding: 16px;
            }

            .plan-badges {
                width: 100%;
                justify-content: flex-start;
            }

            .container {
                padding: 16px 20px;
            }

            .plan-summary {
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a class="logo" href="index.php">
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
        <ul class="nav-links">
            <li><a href="#accueil">Accueil</a></li>
            <li><a href="#fonctionnalites">Fonctionnalités</a></li>
            <li><a href="#plan">Plan</a></li>
            <li><a href="#recettes">Recettes</a></li>
            <li><a href="#impact">Impact</a></li>
            <li><button class="btn-subscribe">S'inscrire</button></li>
            <li><button class="btn-login">Se connecter</button></li>
        </ul>
    </nav>

<?php
// Préparer les données du plan pour l'affichage
$objectifLabels = [
    'perte-poids'  => 'Perte de poids',
    'maintien'     => 'Maintien',
    'prise-muscle' => 'Prise de muscle',
];
$caloriesMap = [
    'perte-poids'  => 1750,
    'maintien'     => 2000,
    'prise-muscle' => 2500,
];

if (!empty($currentPlan)) {
    $planNom       = htmlspecialchars($currentPlan['nom']);
    $planObjectif  = $currentPlan['objectif'];
    $planDuree     = (int)$currentPlan['duree'];
    $planPref      = htmlspecialchars($currentPlan['preference'] ?? 'Standard');
    $objectifText  = $objectifLabels[$planObjectif] ?? 'Maintien';
    $baseCalories  = $caloriesMap[$planObjectif] ?? 2000;
}
?>

    <?php if (!empty($currentPlan)): ?>
    <!-- Plan Summary -->
    <div class="plan-summary">
        <h1 class="plan-title"><?= strtoupper($planNom) ?> – <?= strtoupper($objectifText) ?> (<?= $planDuree ?> JOURS)</h1>
        <div class="plan-badges">
            <div class="badge"><span>🎯</span><span>Objectif : <?= $objectifText ?></span></div>
            <div class="badge"><span>⚡</span><span><?= $baseCalories ?> kcal / jour</span></div>
            <div class="badge"><span>✓</span><span><?= $planPref ?></span></div>
<?php
// Calcul de la progression du plan
$totalRepasPlan = count($repasForFront);
$repasConsommes = 0;
$repasAnnules = 0;
$caloriesTotal = 0;
$caloriesObjectifTotal = $baseCalories * $planDuree;

foreach ($repasForFront as $r) {
    if ($r['statut'] === 'consomme') {
        $repasConsommes++;
        $caloriesTotal += (int)($r['calories_consommees'] ?? 0);
    } elseif ($r['statut'] === 'annule') {
        $repasAnnules++;
    }
}
$pourcentageRepas = $totalRepasPlan > 0 ? round(($repasConsommes / $totalRepasPlan) * 100) : 0;
$pourcentageCalories = $caloriesObjectifTotal > 0 ? round(($caloriesTotal / $caloriesObjectifTotal) * 100) : 0;
$reste = $totalRepasPlan - $repasConsommes - $repasAnnules;
?>
            <div class="badge"><span>📊</span><span><?= $pourcentageRepas ?>% complété</span></div>
            <div class="badge"><span>🔥</span><span><?= $caloriesTotal ?> / <?= $caloriesObjectifTotal ?> kcal</span></div>
            <div class="badge"><span>⏳</span><span><?= $reste ?> restants</span></div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <div id="plansContainer"></div>

        <!-- Graphique des calories par jour -->
        <div style="background: white; border-radius: 12px; padding: 20px; margin: 20px 0; border: 1px solid #e0e0e0;">
            <h4 style="color: #2f8a43; margin: 0 0 15px; font-size: 16px;">📊 Évolution des calories consommées</h4>
            <div id="caloriesChart" style="display: flex; gap: 10px; align-items: flex-end; min-height: 200px; justify-content: space-around;">
                <!-- Les barres seront générées par JavaScript -->
            </div>
        </div>
        <!-- Recommendation -->
        <div class="recommendation">
            <div class="recommendation-title">💡 RECOMMANDATION IA</div>
            <div class="recommendation-content">
                "Pensez à boire 1,5L d'eau par jour pour optimiser votre équilibre. Ajoutez une collation protéinée (amandes ou houmous) si vous ressentez une baisse d'énergie."
            </div>
        </div>

        <!-- 👥 DIGITAL TWIN -->
        <div style="background:white;border-radius:12px;padding:20px;margin:20px 0;border:1px solid #e0e0e0;">
            <h4 style="color:#2e7d32;margin:0 0 4px;font-size:16px;display:flex;align-items:center;gap:8px;">👥 Jumeau Nutritionnel <span style="font-size:11px;background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:12px;font-weight:600;">DIGITAL TWIN</span></h4>
            <p style="color:#888;font-size:12px;margin:0 0 14px;">Simulation métabolique en temps réel basée sur vos données</p>
            <div id="twin-container"></div>
        </div>
        <style>
            .twin-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px}
            .twin-grid-2{grid-template-columns:1fr 1fr}
            .twin-card{background:#f8faf8;border:1px solid #e8efe8;border-radius:12px;padding:14px;text-align:center}
            .twin-card-trend{background:linear-gradient(135deg,#e8f5e9,#c8e6c9)}
            .twin-card-icon{font-size:24px;margin-bottom:4px}
            .twin-card-title{font-size:11px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:0.5px}
            .twin-card-value{font-size:18px;font-weight:800;color:#2e7d32;margin:4px 0}
            .twin-card-sub{font-size:11px;color:#999}
            .twin-preds{display:flex;gap:8px;justify-content:center;margin-top:8px}
            .twin-pred{background:#fff;border:1px solid #e0e0e0;border-radius:10px;padding:8px 12px;text-align:center;flex:1}
            .twin-pred-label{font-size:10px;font-weight:700;color:#888;text-transform:uppercase}
            .twin-pred-value{font-size:15px;font-weight:800;color:#333}
            .twin-conseil{background:#e0f2f1;border-left:4px solid #4a9b8e;padding:14px;border-radius:4px;margin-top:12px;font-size:13px;color:#333;line-height:1.5}
            .twin-btn{padding:10px 20px;border-radius:20px;border:none;font-weight:700;font-size:13px;cursor:pointer;background:linear-gradient(135deg,#4caf50,#2e7d32);color:#fff;transition:transform .2s}
            .twin-btn:hover{transform:scale(1.04)}
            .twin-btn-secondary{background:#e8e8e8;color:#555}
            .twin-btn-secondary:hover{background:#d0d0d0}
            .twin-modal{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center}
            .twin-modal-content{background:#fff;border-radius:18px;padding:24px;width:100%;max-width:420px;box-shadow:0 8px 40px rgba(0,0,0,0.2)}
            @media(max-width:768px){.twin-grid{grid-template-columns:1fr 1fr}.twin-grid-2{grid-template-columns:1fr}}
        </style>

        <!-- ⚖️ ORCHESTRATEUR MULTI-OBJECTIFS -->
        <div class="mo-container">
            <div class="mo-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="mo-header-icon">⚖️</div>
                    <div>
                        <h4 class="mo-header-title">Orchestrateur Multi-Objectifs</h4>
                        <div class="mo-header-sub">Le moteur intelligent qui équilibre vos priorités</div>
                    </div>
                </div>
                <button class="btn btn-tertiary" style="padding:8px 16px; font-size:12px;" onclick="generateRestaurantQRCode()">📱 Générer QR Code Restaurants</button>
            </div>
            
            <div class="mo-grid">
                <!-- Sliders -->
                <div class="mo-sliders-container">
                    <div style="font-weight:700; color:#333; margin-bottom:16px;">🎚️ Mes Priorités</div>
                    
                    <div class="mo-slider-group">
                        <div class="mo-slider-header">
                            <span class="mo-slider-label">Perte de poids</span>
                            <span class="mo-slider-value" id="mo-weight-perte-val">80%</span>
                        </div>
                        <input type="range" id="mo-weight-perte" class="mo-slider" min="0" max="100" value="80">
                    </div>
                    
                    <div class="mo-slider-group">
                        <div class="mo-slider-header">
                            <span class="mo-slider-label">Plaisir gustatif</span>
                            <span class="mo-slider-value" id="mo-weight-plaisir-val">30%</span>
                        </div>
                        <input type="range" id="mo-weight-plaisir" class="mo-slider" min="0" max="100" value="30">
                    </div>
                    
                    <div class="mo-slider-group">
                        <div class="mo-slider-header">
                            <span class="mo-slider-label">Budget (€)</span>
                            <span class="mo-slider-value" id="mo-weight-budget-val">60%</span>
                        </div>
                        <input type="range" id="mo-weight-budget" class="mo-slider" min="0" max="100" value="60">
                    </div>
                    
                    <div class="mo-slider-group">
                        <div class="mo-slider-header">
                            <span class="mo-slider-label">Rapidité</span>
                            <span class="mo-slider-value" id="mo-weight-rapidite-val">60%</span>
                        </div>
                        <input type="range" id="mo-weight-rapidite" class="mo-slider" min="0" max="100" value="60">
                    </div>
                    
                    <div class="mo-slider-group">
                        <div class="mo-slider-header">
                            <span class="mo-slider-label">Écologie (🌍)</span>
                            <span class="mo-slider-value" id="mo-weight-eco-val">30%</span>
                        </div>
                        <input type="range" id="mo-weight-eco" class="mo-slider" min="0" max="100" value="30">
                    </div>
                </div>

                <!-- Résultats -->
                <div id="mo-results">
                    <!-- Généré par JS -->
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-tertiary" onclick="window.location.href='index.php?page=plan-nutritionnel'">➕ AJOUTER</button>
            <form method="post" action="index.php?page=plan-adapte" style="display:flex; margin:0; padding:0;" onsubmit="return confirm('Voulez-vous vraiment supprimer ce plan ? Cette action est irréversible.');">
                <input type="hidden" name="action_type" value="plan">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$currentPlan['id'] ?>">
                <button type="submit" class="btn" style="background:#e53935; color:white; height:100%;">🗑️ SUPPRIMER</button>
            </form>
            <button class="btn btn-primary" onclick="openEditPlanModal()">🔧 MODIFIER</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='../CONTROLLER/generate_pdf.php?id=<?= (int)$currentPlan['id'] ?>'">📥 PDF</button>
        </div>

        <!-- Modal Modification du Plan -->
        <div id="editPlanModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999;">
            <div style="background:#fff; border-radius:18px; padding:28px; width:100%; max-width:520px; box-shadow:0 8px 40px rgba(0,0,0,0.2); margin:auto; position:relative; top:50%; transform:translateY(-50%);">
                <h3 style="color:#2f8a43; margin:0 0 20px; font-size:1.2rem;">🔧 Modifier le plan</h3>
                <form method="post" action="index.php?page=plan-adapte" id="editPlanForm">
                    <input type="hidden" name="action_type" value="plan">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$currentPlan['id'] ?>">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div style="grid-column:span 2;">
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Nom du plan</label>
                            <input type="text" name="nom" value="<?= htmlspecialchars($currentPlan['nom']) ?>" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Objectif</label>
                            <select name="objectif" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;background:white;">
                                <option value="perte-poids" <?= $currentPlan['objectif']==='perte-poids'?'selected':'' ?>>Perte de poids</option>
                                <option value="maintien" <?= $currentPlan['objectif']==='maintien'?'selected':'' ?>>Maintien</option>
                                <option value="prise-muscle" <?= $currentPlan['objectif']==='prise-muscle'?'selected':'' ?>>Prise de muscle</option>
                            </select>
                        </div>
                        <input type="hidden" name="utilisateur_id" value="<?= htmlspecialchars($currentPlan['utilisateur_id']) ?>">
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Durée (jours)</label>
                            <input type="text" name="duree" value="<?= htmlspecialchars($currentPlan['duree']) ?>" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Préférence</label>
                            <input type="text" name="preference" value="<?= htmlspecialchars($currentPlan['preference'] ?? '') ?>" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div style="grid-column:span 2;">
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Allergies</label>
                            <textarea name="allergies" rows="2" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;resize:vertical;"><?= htmlspecialchars($currentPlan['allergies'] ?? '') ?></textarea>
                        </div>
                        <div style="grid-column:span 2;border-top:1px solid #e8e8e8;padding-top:12px;margin-top:4px;">
                            <div style="font-size:0.82rem;font-weight:700;color:#2e7d32;margin-bottom:8px;">👥 Données biométriques (Jumeau)</div>
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Âge</label>
                            <input type="text" name="age" value="<?= htmlspecialchars($currentPlan['age'] ?? 30) ?>" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Poids (kg)</label>
                            <input type="text" name="poids" value="<?= htmlspecialchars($currentPlan['poids'] ?? 70) ?>" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Taille (cm)</label>
                            <input type="text" name="taille" value="<?= htmlspecialchars($currentPlan['taille'] ?? 170) ?>" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Sexe</label>
                            <select name="sexe" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;background:white;">
                                <option value="homme" <?= ($currentPlan['sexe'] ?? 'homme')==='homme'?'selected':'' ?>>Homme</option>
                                <option value="femme" <?= ($currentPlan['sexe'] ?? 'homme')==='femme'?'selected':'' ?>>Femme</option>
                            </select>
                        </div>
                        <div style="grid-column:span 2;">
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Niveau d'activité</label>
                            <select name="niveau_activite" style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;background:white;">
                                <option value="sedentaire" <?= ($currentPlan['niveau_activite'] ?? 'modere')==='sedentaire'?'selected':'' ?>>Sédentaire</option>
                                <option value="leger" <?= ($currentPlan['niveau_activite'] ?? 'modere')==='leger'?'selected':'' ?>>Activité légère</option>
                                <option value="modere" <?= ($currentPlan['niveau_activite'] ?? 'modere')==='modere'?'selected':'' ?>>Activité modérée</option>
                                <option value="actif" <?= ($currentPlan['niveau_activite'] ?? 'modere')==='actif'?'selected':'' ?>>Très actif</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:18px;">
                        <button type="submit" class="btn btn-tertiary" style="flex:1;">✓ Enregistrer</button>
                        <button type="button" class="btn" style="flex:1;background:#e0e0e0;color:#333;" onclick="closeEditPlanModal()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Modal Modification des Critères (Multi-Objectifs) -->
        <div id="editCriteresModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999;">
            <div style="background:#fff; border-radius:18px; padding:28px; width:100%; max-width:400px; box-shadow:0 8px 40px rgba(0,0,0,0.2); margin:auto; position:relative; top:50%; transform:translateY(-50%);">
                <h3 style="color:#2f8a43; margin:0 0 20px; font-size:1.2rem;">⚙️ Modifier les critères</h3>
                <form id="editCriteresForm" onsubmit="event.preventDefault(); saveCriteres();">
                    <input type="hidden" id="critere_repas_id" value="">
                    <div style="display:grid; gap:12px;">
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Prix (€)</label>
                            <input type="number" id="critere_prix" step="0.1" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Temps de préparation (min)</label>
                            <input type="number" id="critere_temps" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Éco-score</label>
                            <select id="critere_eco" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;background:white;">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;display:block;margin-bottom:4px;">Plaisir (/10)</label>
                            <input type="number" id="critere_plaisir" min="1" max="10" required style="width:100%;border:1px solid #e0e0e0;border-radius:10px;padding:10px;font:inherit;">
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-top:18px;">
                        <button type="submit" class="btn btn-tertiary" style="flex:1;">✓ Enregistrer</button>
                        <button type="button" class="btn" style="flex:1;background:#e0e0e0;color:#333;" onclick="document.getElementById('editCriteresModal').style.display='none'">Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal QR Code -->
        <div id="qrCodeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999;">
            <div style="background:#fff; border-radius:18px; padding:30px; width:100%; max-width:350px; text-align:center; box-shadow:0 8px 40px rgba(0,0,0,0.2); margin:auto; position:relative; top:50%; transform:translateY(-50%);">
                <h3 style="color:#2f8a43; margin:0 0 10px; font-size:1.3rem;">📱 Scannez-moi !</h3>
                <p style="font-size:0.9rem; color:#666; margin-bottom:20px;">Découvrez les vrais restaurants en Tunisie qui correspondent à vos critères.</p>
                
                <div style="background:#f9f9f9; padding:15px; border-radius:12px; display:inline-block; margin-bottom:20px; border:1px solid #eee;">
                    <img id="qrCodeImage" src="" alt="QR Code" style="width:200px; height:200px; display:block;">
                </div>
                
                <div style="margin-bottom:20px; font-size:0.8rem; word-break:break-all;">
                    <a id="qrCodeLink" href="#" target="_blank" style="color:#1976d2; text-decoration:none; font-weight:600;">Lien</a>
                </div>

                <button class="btn" style="background:#e0e0e0; color:#333; width:100%;" onclick="document.getElementById('qrCodeModal').style.display='none'">Fermer</button>
            </div>
        </div>

    </main>

    <script>
        <?php
        // On Windows, gethostbynamel() can return multiple IPs including virtual adapters
        // (VMware: 192.168.172.x, VirtualBox: 192.168.56.x, etc.).
        // We MUST prefer the real Wi-Fi IP (192.168.1.x) so phones on the same network
        // can reach the server. Hardcoded fallback = 192.168.1.16 (current Wi-Fi IP).
        // Get all network IPs
        $allIPs = gethostbynamel(gethostname()) ?: [];

        $wifiIP    = null;   // preferred: 192.168.1.x  (your Wi-Fi subnet)
        $otherIP   = null;   // fallback : any other 192.168.x.x (not virtual)
        $WIFI_SUBNET   = '192.168.1.';
        $SKIP_SUBNETS  = ['192.168.172.', '192.168.56.', '192.168.99.'];

        // First priority: find Wi-Fi IP (192.168.1.x)
        foreach ($allIPs as $ip) {
            if (strpos($ip, $WIFI_SUBNET) === 0) {
                $wifiIP = $ip;
                break;
            }
        }
        
        // Second priority: find other non-virtual IP
        if (!$wifiIP) {
            foreach ($allIPs as $ip) {
                if (!$otherIP && strpos($ip, '192.168.') === 0) {
                    $skip = false;
                    foreach ($SKIP_SUBNETS as $bad) {
                        if (strpos($ip, $bad) === 0) { $skip = true; break; }
                    }
                    if (!$skip) $otherIP = $ip;
                }
            }
        }

        // Third priority: use 192.168.1.16 (your Wi-Fi IP)
        $detectedIP = $wifiIP ?? $otherIP ?? '192.168.1.16';
        ?>
        window.APP_SERVER_IP = "<?= $detectedIP ?>";
    </script>
    <script src="assets/qrcode-generator.js?v=<?= time() ?>"></script>
    <script>
        (function () {
            const baseCalories = <?= $baseCalories ?>;

            // ── Real meal data injected from PHP ──
            const mealData = <?= json_encode($repasForFront ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

            const typeRepasLabels = {
                'petit_dejeuner': 'Petit-déjeuner',
                'dejeuner': 'Déjeuner',
                'diner': 'Dîner',
                'collation': 'Collation'
            };

            const plansContainer = document.getElementById("plansContainer");
            plansContainer.innerHTML = "";

            if (mealData.length === 0) {
                plansContainer.innerHTML = `
                    <div style="text-align:center; padding:40px; color:#5b6f5f; background:#fff; border-radius:8px; border:1px solid #e0e0e0;">
                        <div style="font-size:2.5rem; margin-bottom:12px;">🍽️</div>
                        <p style="font-size:1rem; font-weight:600;">Aucun repas enregistré pour ce plan.</p>
                        <p style="font-size:0.88rem; margin-top:6px;">Ajoutez des repas depuis le <a href="index.php?page=backoffice" style="color:#4a9b8e;font-weight:700;">backoffice</a>.</p>
                    </div>`;
            } else {
                const byDate = {};
                mealData.forEach(repas => {
                    const d = repas.date || 'Sans date';
                    if (!byDate[d]) byDate[d] = [];
                    byDate[d].push(repas);
                });

                const daysOfWeek = ["DIMANCHE", "LUNDI", "MARDI", "MERCREDI", "JEUDI", "VENDREDI", "SAMEDI"];
                const monthNames = ["JANVIER", "FÉVRIER", "MARS", "AVRIL", "MAI", "JUIN",
                                    "JUILLET", "AOÛT", "SEPTEMBRE", "OCTOBRE", "NOVEMBRE", "DÉCEMBRE"];

                Object.keys(byDate).sort().forEach(dateStr => {
                    const meals = byDate[dateStr];
                    let dayLabel = dateStr;
                    let totalKcal = 0;

                    if (dateStr !== 'Sans date') {
                        const d = new Date(dateStr);
                        if (!isNaN(d)) {
                            dayLabel = `${daysOfWeek[d.getDay()]} ${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`;
                        }
                    }

                    meals.forEach(m => {
                        totalKcal += parseInt(m.calories_consommees) || 0;
                    });

                    const dayBlock = document.createElement("div");
                    dayBlock.className = "day-block";
                    dayBlock.innerHTML = `
                        <div class="day-header" onclick="toggleDay(this)">
                            <div class="day-date">📅 ${dayLabel}</div>
                            <div class="day-toggle">▼</div>
                        </div>
                        <div class="day-content">
                            ${meals.map(repas => {
                                const typeLabel = typeRepasLabels[repas.type_repas] || repas.type_repas || '—';
                                const heure = repas.heure_prevue || '—';
                                const kcal = repas.calories_consommees ? parseInt(repas.calories_consommees) : null;
                                const isConsumed = repas.statut === 'consomme';
                                const isAnnule  = repas.statut === 'annule';
                                const btnClass  = isConsumed ? 'consumed' : 'pending';
                                const btnLabel  = isConsumed ? '✓ Consommé' : (isAnnule ? '✗ Annulé' : '→ À venir');
                                const notes     = repas.notes ? `<div class="meal-description" style="font-style:italic;">${repas.notes}</div>` : '';
                                return `
                                    <div class="meal">
                                        <div class="meal-header">
                                            <div>
                                                <div class="meal-title">🍽️ ${repas.nom_recette || '—'} <span style="font-size:0.82rem;color:#888;font-weight:500;">(${typeLabel} – ${heure})</span></div>
                                                ${notes}
                                            </div>
                                            <div class="meal-kcal">${kcal !== null ? kcal + ' kcal' : '—'}</div>
                                        </div>
                                        <button class="meal-action ${btnClass}">${btnLabel}</button>
                                    </div>`;
                            }).join('')}
                        </div>
                        <div class="day-footer">
                            <span>Total jour : <span class="total-kcal ${totalKcal === baseCalories ? 'success' : ''}">${totalKcal} / ${baseCalories} kcal</span></span>
                        </div>
                    `;
                    plansContainer.appendChild(dayBlock);
                });
            }

            // Générer le graphique des calories par jour
            function renderCaloriesChart() {
                const chartContainer = document.getElementById('caloriesChart');
                if (!chartContainer) return;
                
                // Grouper les calories par date
                const caloriesParJour = {};
                mealData.forEach(repas => {
                    const date = repas.date;
                    const calories = parseInt(repas.calories_consommees) || 0;
                    if (!caloriesParJour[date]) caloriesParJour[date] = 0;
                    caloriesParJour[date] += calories;
                });
                
                const chartData = Object.keys(caloriesParJour).sort().map(date => ({
                    date: date,
                    calories: caloriesParJour[date],
                    objectif: baseCalories
                }));
                
                if (chartData.length === 0) {
                    chartContainer.innerHTML = '<div style="text-align:center; color:#999; padding:40px;">📭 Aucune donnée calorique disponible</div>';
                    return;
                }
                
                const maxCalories = Math.max(...chartData.map(d => d.calories), baseCalories);
                const maxHeight = 150;
                
                chartContainer.innerHTML = chartData.map(day => {
                    const height = Math.max(30, (day.calories / maxCalories) * maxHeight);
                    const objectifHeight = (day.objectif / maxCalories) * maxHeight;
                    const dateObj = new Date(day.date);
                    const dateFormatee = dateObj.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
                    
                    return `
                        <div style="flex:1; text-align: center;">
                            <div style="position: relative; height: ${maxHeight}px; margin-bottom: 8px;">
                                <div style="position: absolute; bottom: 0; width: 100%; background: #ff9800; height: ${objectifHeight}px; border-radius: 8px 8px 0 0; opacity: 0.3;"></div>
                                <div style="position: absolute; bottom: 0; width: 100%; background: linear-gradient(180deg, #4caf50, #2e7d32); height: ${height}px; border-radius: 8px 8px 0 0; transition: height 0.3s;"></div>
                            </div>
                            <div style="font-size: 11px; color: #555;">${dateFormatee}</div>
                            <div style="font-size: 13px; font-weight: bold; color: #2e7d32;">${day.calories}</div>
                            <div style="font-size: 10px; color: #999;">/ ${day.objectif}</div>
                        </div>
                    `;
                }).join('');
            }

            // Appeler la fonction après avoir construit les repas
            renderCaloriesChart();

        })();

        function toggleDay(header) {
            const toggle = header.querySelector(".day-toggle");
            const content = header.nextElementSibling;
            const footer = header.parentElement.querySelector(".day-footer");
            const isCollapsed = header.classList.contains("collapsed");

            if (isCollapsed && content && content.classList.contains("day-content")) {
                header.classList.remove("collapsed");
                content.classList.remove("collapsed");
                if (footer) footer.classList.remove("collapsed");
                toggle.textContent = "▼";
            } else if (!isCollapsed && content && content.classList.contains("day-content")) {
                header.classList.add("collapsed");
                content.classList.add("collapsed");
                if (footer) footer.classList.add("collapsed");
                toggle.textContent = "▶";
            }
        }

        function openEditPlanModal() {
            document.getElementById('editPlanModal').style.display = 'flex';
        }
        function closeEditPlanModal() {
            document.getElementById('editPlanModal').style.display = 'none';
        }
        document.getElementById('editPlanModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditPlanModal();
        });
    </script>

    <?php else: ?>
    <!-- Aucun plan sélectionné -->
    <main class="container">
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:12px; border:1px solid #e0e0e0; margin-top:30px;">
            <div style="font-size:3rem; margin-bottom:16px;">📋</div>
            <h2 style="color:#2f8a43; margin:0 0 10px;">Aucun plan sélectionné</h2>
            <p style="color:#666; font-size:1rem; margin:0 0 20px;">Créez un plan nutritionnel personnalisé pour commencer.</p>
            <a href="index.php?page=plan-nutritionnel" style="display:inline-block; padding:12px 28px; background:#4a9b8e; color:white; border-radius:25px; text-decoration:none; font-weight:700; font-size:0.95rem;">✨ Créer mon plan</a>
        </div>
    </main>
    <?php endif; ?>

    <?php if (!empty($currentPlan)): ?>
    <!-- Coach Nutritionnel Chatbot -->
    <link rel="stylesheet" href="assets/chatbot.css">
    <link rel="stylesheet" href="assets/multi-objective.css?v=<?= time() ?>">
    <script src="assets/chatbot.js"></script>
    <script src="assets/jumeau.js?v=<?= time() ?>"></script>
    <script src="assets/multi-objective.js?v=<?= time() ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ChatbotInit(<?= (int)$currentPlan['id'] ?>);
            if (typeof JumeauInit === 'function') {
                JumeauInit(<?= (int)$currentPlan['id'] ?>);
            }
            if (typeof MultiObjectiveInit === 'function') {
                MultiObjectiveInit(<?= (int)$currentPlan['id'] ?>);
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
