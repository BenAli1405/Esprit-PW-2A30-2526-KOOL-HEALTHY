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

    <!-- Plan Summary -->
    <div class="plan-summary">
        <h1 class="plan-title" id="planTitle">MON PLAN NUTRITIONNEL</h1>
        <div class="plan-badges">
            <div class="badge">
                <span>🎯</span>
                <span id="badgeObjectif">Objectif</span>
            </div>
            <div class="badge">
                <span>⚡</span>
                <span id="badgeCalories">kcal / jour</span>
            </div>
            <div class="badge">
                <span>✓</span>
                <span id="badgeDiet">Régime</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container">
        <div id="plansContainer"></div>

        <!-- Recommendation -->
        <div class="recommendation">
            <div class="recommendation-title">💡 RECOMMANDATION IA</div>
            <div class="recommendation-content">
                "Pensez à boire 1,5L d'eau par jour pour optimiser votre équilibre. Ajoutez une collation protéinée (amandes ou houmous) si vous ressentez une baisse d'énergie."
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-tertiary" onclick="window.location.href='plan-nutritionnel.php'">➕ AJOUTER</button>
            <button class="btn" style="background:#e53935; color:white;" onclick="if(confirm('Voulez-vous supprimer ce plan ?')) window.location.href='plan-nutritionnel.php';">🗑️ SUPPRIMER</button>
            <button class="btn btn-primary" onclick="window.history.back()">🔧 MODIFIER</button>
            <button class="btn btn-secondary">📥 PDF</button>
        </div>
    </main>

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);

            const objectifMap = {
                "perte-poids": "Perte de poids",
                "maintien": "Maintien",
                "prise-muscle": "Prise de muscle"
            };

            const caloriesMap = {
                "perte-poids": 1750,
                "maintien": 2000,
                "prise-muscle": 2500
            };

            const objectif = params.get("objectif") || "maintien";
            const duree = params.get("duree") || "7";
            const preferences = params.getAll("preferences").length ? params.getAll("preferences") : ["Standard"];

            const totalDays = Math.max(1, Math.min(parseInt(duree) || 7, 30));
            const baseCalories = caloriesMap[objectif] || 2000;
            const objectifText = objectifMap[objectif] || "Maintien";
            const dietText = preferences.map(p => p.charAt(0).toUpperCase() + p.slice(1).replace('-', ' ')).join(' & ');

            // Mise à jour de la barre d'entête avec les infos dynamiques
            document.getElementById('planTitle').textContent = `MON PLAN NUTRITIONNEL – ${objectifText.toUpperCase()} (${totalDays} JOURS)`;
            document.getElementById('badgeObjectif').textContent = `Objectif : ${objectifText}`;
            document.getElementById('badgeCalories').textContent = `${baseCalories} kcal / jour`;
            document.getElementById('badgeDiet').textContent = dietText;

            // Days of the week
            const daysOfWeek = ["LUNDI", "MARDI", "MERCREDI", "JEUDI", "VENDREDI", "SAMEDI", "DIMANCHE"];
            const startDate = new Date(2026, 3, 14);

            // Meal templates
            const mealData = [
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Porridge à la banane – œufs brouillés bio", kcal: 450, status: "consumed" },
                        { name: "Déjeuner", time: "12:30", desc: "Buddha bowl quinoa – légumes rôtis – sauce tahini", kcal: 580, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Curry pois chiches – lait de coco – riz basmati", kcal: 520, status: "pending" }
                    ]
                },
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Smoothie bowl fruits – granola maison", kcal: 420, status: "pending" },
                        { name: "Déjeuner", time: "12:30", desc: "Salade falafel – houmous – crudités", kcal: 610, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Risotto champignons – asperges – parmesan", kcal: 540, status: "pending" }
                    ]
                },
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Toast fromage frais – tomate – avocado", kcal: 380, status: "pending" },
                        { name: "Déjeuner", time: "12:30", desc: "Pâtes complètes sauce tomate – épinards", kcal: 600, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Ratatouille – tofu fumé – pain complet", kcal: 510, status: "pending" }
                    ]
                },
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Yaourt grec – muesli – miel", kcal: 410, status: "pending" },
                        { name: "Déjeuner", time: "12:30", desc: "Wrap végétal – guacamole – crudités", kcal: 610, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Lentilles corail – légumes estivaux", kcal: 480, status: "pending" }
                    ]
                },
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Pancakes complets – fruits rouges", kcal: 470, status: "pending" },
                        { name: "Déjeuner", time: "12:30", desc: "Buddha bowl riz – légumes – sauce soja", kcal: 610, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Pizza maison – base tomate – légumes", kcal: 520, status: "pending" }
                    ]
                },
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Œufs à la coque – pain grillé – confiture", kcal: 430, status: "pending" },
                        { name: "Déjeuner", time: "12:30", desc: "Taboulé quinoa – pois chiche – menthe", kcal: 550, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Gratin de légumes – sauce béchamel légère", kcal: 490, status: "pending" }
                    ]
                },
                {
                    meals: [
                        { name: "Petit-déjeuner", time: "08:00", desc: "Acai bowl – granola – noix de coco", kcal: 460, status: "pending" },
                        { name: "Déjeuner", time: "12:30", desc: "Falafel maison – salade croquante", kcal: 630, status: "pending" },
                        { name: "Dîner", time: "19:00", desc: "Couscous légumes – sauce tomate épicée", kcal: 530, status: "pending" }
                    ]
                }
            ];

            const plansContainer = document.getElementById("plansContainer");
            plansContainer.innerHTML = "";

            for (let i = 0; i < totalDays && i < mealData.length; i++) {
                const day = mealData[i];
                const dayDate = new Date(startDate);
                dayDate.setDate(dayDate.getDate() + i);
                const dayName = daysOfWeek[dayDate.getDay()];
                const dayNum = dayDate.getDate();
                const monthName = ["JANVIER", "FÉVRIER", "MARS", "AVRIL", "MAI", "JUIN", "JUILLET", "AOÛT", "SEPTEMBRE", "OCTOBRE", "NOVEMBRE", "DÉCEMBRE"][dayDate.getMonth()];

                const totalMealKcal = day.meals.reduce((sum, meal) => sum + meal.kcal, 0);
                const dayBlock = document.createElement("div");
                dayBlock.className = "day-block";
                dayBlock.innerHTML = `
                    <div class="day-header" onclick="toggleDay(this)">
                        <div class="day-date">📅 ${dayName} ${dayNum} ${monthName}</div>
                        <div class="day-toggle">▼</div>
                    </div>
                    <div class="day-content">
                        ${day.meals.map(meal => `
                            <div class="meal">
                                <div class="meal-header">
                                    <div>
                                        <div class="meal-title">🍽️ ${meal.name} (${meal.time})</div>
                                        <div class="meal-description">${meal.desc}</div>
                                    </div>
                                    <div class="meal-kcal">${meal.kcal} kcal</div>
                                </div>
                                <button class="meal-action ${meal.status === 'consumed' ? 'consumed' : 'pending'}">
                                    ${meal.status === 'consumed' ? '✓ Consommé' : '→ À venir'}
                                </button>
                            </div>
                        `).join('')}
                    </div>
                    <div class="day-footer">
                        <span>Total jour : <span class="total-kcal ${totalMealKcal === baseCalories ? 'success' : ''}">${totalMealKcal} / ${baseCalories} kcal</span></span>
                    </div>
                `;
                plansContainer.appendChild(dayBlock);
            }

            // Add collapsed day headers for remaining days
            for (let i = totalDays; i < 7; i++) {
                const dayDate = new Date(startDate);
                dayDate.setDate(dayDate.getDate() + i);
                const dayName = daysOfWeek[dayDate.getDay()];
                const dayNum = dayDate.getDate();
                const monthName = ["JANVIER", "FÉVRIER", "MARS", "AVRIL", "MAI", "JUIN", "JUILLET", "AOÛT", "SEPTEMBRE", "OCTOBRE", "NOVEMBRE", "DÉCEMBRE"][dayDate.getMonth()];

                const dayBlock = document.createElement("div");
                dayBlock.className = "day-block";
                dayBlock.innerHTML = `
                    <div class="day-header collapsed" onclick="toggleDay(this)">
                        <div class="day-date">📅 ${dayName} ${dayNum} ${monthName}</div>
                        <div class="day-toggle">▶</div>
                    </div>
                `;
                plansContainer.appendChild(dayBlock);
            }

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
    </script>
</body>
</html>
