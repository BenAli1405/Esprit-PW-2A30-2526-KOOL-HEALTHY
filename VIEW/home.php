<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();
$prenom = explode(' ', trim($utilisateurConnecte['nom'] ?? 'Utilisateur'))[0];
$heure = (int)date('H');
$salut = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/styles.css">
    <style>
        :root {
            --vert-kool: #4CAF50;
            --vert-kool-dark: #388E3C;
            --vert-kool-light: #E8F5E9;
            --bleu-tech: #29B6F6;
            --bleu-tech-dark: #0288D1;
            --orange: #FF7043;
            --purple: #7E57C2;
            --gold: #FFC107;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Inter", sans-serif;
            background: #f0f4f0;
            color: #2C3E2F;
            overflow-x: hidden;
        }

        /* ── HERO ─────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1B5E20 0%, #2E7D32 40%, #0288D1 100%);
            padding: 70px 5% 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -100px; left: -60px;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-greeting {
            font-size: 1rem;
            color: rgba(255,255,255,0.75);
            font-weight: 500;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .hero h1 {
            font-size: clamp(2rem, 4vw, 3.2rem);
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 18px;
        }

        .hero h1 span {
            background: linear-gradient(90deg, #A5D6A7, #80DEEA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-desc {
            color: rgba(255,255,255,0.82);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 32px;
            max-width: 480px;
        }

        .hero-cta {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background: #fff;
            color: var(--vert-kool-dark);
            font-weight: 700;
            font-size: 0.95rem;
            padding: 13px 28px;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .btn-hero-outline {
            border: 2px solid rgba(255,255,255,0.6);
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 11px 26px;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-hero-outline:hover {
            background: rgba(255,255,255,0.12);
            border-color: #fff;
        }

        /* ── Hero card ── */
        .hero-card {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 32px;
            color: #fff;
        }

        .hero-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .hero-stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .hero-stat-item {
            background: rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        .hero-stat-item strong {
            display: block;
            font-size: 1.6rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 4px;
        }

        .hero-stat-item span {
            font-size: 0.78rem;
            opacity: 0.75;
        }

        /* ── QUICK ACCESS ─────────────────────────────────────── */
        .section-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 5%;
        }

        .section-label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--vert-kool-dark);
            margin-bottom: 8px;
        }

        .section-title {
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            font-weight: 800;
            color: #1B2E1D;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .section-subtitle {
            color: #6B7C6E;
            font-size: 1rem;
            margin-bottom: 40px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .quick-card {
            background: #fff;
            border-radius: 20px;
            padding: 28px 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.25s;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1.5px solid #e8f0e9;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .quick-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--card-color, var(--vert-kool));
            border-radius: 20px 20px 0 0;
        }

        .quick-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.1);
            border-color: var(--card-color, var(--vert-kool));
        }

        .quick-card-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            background: var(--card-bg, #E8F5E9);
            color: var(--card-color, var(--vert-kool-dark));
        }

        .quick-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #1B2E1D;
        }

        .quick-card p {
            font-size: 0.85rem;
            color: #6B7C6E;
            line-height: 1.5;
        }

        .quick-card-arrow {
            margin-top: auto;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--card-color, var(--vert-kool-dark));
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ── FEATURES ─────────────────────────────────────────── */
        .features-bg {
            background: linear-gradient(180deg, #f0f4f0 0%, #e8f5e9 100%);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .feature-card {
            background: #fff;
            border-radius: 20px;
            padding: 32px 26px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            border: 1px solid #e8f0e9;
            transition: transform 0.2s;
        }

        .feature-card:hover { transform: translateY(-3px); }

        .feature-icon {
            width: 56px; height: 56px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 18px;
        }

        .feature-card h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1B2E1D;
        }

        .feature-card p {
            font-size: 0.88rem;
            color: #6B7C6E;
            line-height: 1.6;
        }

        /* ── IMPACT ───────────────────────────────────────────── */
        .impact-section {
            background: linear-gradient(135deg, #1B5E20, #2E7D32);
            border-radius: 28px;
            padding: 56px 48px;
            color: #fff;
            text-align: center;
        }

        .impact-section h2 {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 800;
            margin-bottom: 10px;
        }

        .impact-section > p {
            opacity: 0.8;
            margin-bottom: 44px;
            font-size: 1rem;
        }

        .impact-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 24px;
        }

        .impact-stat {
            background: rgba(255,255,255,0.12);
            border-radius: 18px;
            padding: 28px 20px;
            border: 1px solid rgba(255,255,255,0.15);
            transition: background 0.2s;
        }

        .impact-stat:hover { background: rgba(255,255,255,0.18); }

        .impact-stat strong {
            display: block;
            font-size: 2.2rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 8px;
            background: linear-gradient(90deg, #A5D6A7, #80DEEA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .impact-stat span {
            font-size: 0.85rem;
            opacity: 0.78;
        }

        /* ── CTA BANNER ───────────────────────────────────────── */
        .cta-banner {
            background: #fff;
            border-radius: 24px;
            padding: 48px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            flex-wrap: wrap;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1.5px solid #e8f0e9;
        }

        .cta-banner h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1B2E1D;
            margin-bottom: 8px;
        }

        .cta-banner p { color: #6B7C6E; font-size: 0.95rem; }

        .cta-buttons { display: flex; gap: 12px; flex-wrap: wrap; }

        .btn-green {
            background: var(--vert-kool-dark);
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 13px 28px;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-green:hover {
            background: #1B5E20;
            transform: translateY(-1px);
        }

        .btn-outline-green {
            border: 2px solid var(--vert-kool-dark);
            color: var(--vert-kool-dark);
            font-weight: 600;
            font-size: 0.95rem;
            padding: 11px 26px;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-outline-green:hover { background: var(--vert-kool-light); }

        /* ── FOOTER ───────────────────────────────────────────── */
        .footer {
            background: #1B2E1D;
            color: rgba(255,255,255,0.7);
            text-align: center;
            padding: 32px 20px;
            font-size: 0.88rem;
        }

        .footer a { color: #A5D6A7; text-decoration: none; }

        /* ── RESPONSIVE ───────────────────────────────────────── */
        @media (max-width: 768px) {
            .hero-inner { grid-template-columns: 1fr; }
            .hero-card { display: none; }
            .impact-section { padding: 36px 24px; }
            .cta-banner { text-align: center; justify-content: center; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <!-- ═══════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════ -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-text">
                <p class="hero-greeting">🌿 <?= $salut ?>, <?= htmlspecialchars($prenom) ?> !</p>
                <h1>Mangez mieux,<br><span>préservez la planète</span></h1>
                <p class="hero-desc">
                    Kool Healthy combine nutrition intelligente, sport, partage communautaire
                    et recettes durables dans une plateforme unique.
                </p>
                <div class="hero-cta">
                    <a class="btn-hero-primary" href="/integweb/VIEW/frontoffice.php">
                        <i class="fas fa-utensils"></i> Explorer les recettes
                    </a>
                    <a class="btn-hero-outline" href="#features">En savoir plus</a>
                </div>
            </div>

            <div class="hero-card">
                <p class="hero-card-title">📊 Impact de la communauté</p>
                <div class="hero-stat-grid">
                    <div class="hero-stat-item">
                        <strong>1 284</strong>
                        <span>kg CO₂ économisés</span>
                    </div>
                    <div class="hero-stat-item">
                        <strong>3 452</strong>
                        <span>Repas durables</span>
                    </div>
                    <div class="hero-stat-item">
                        <strong>2 189</strong>
                        <span>Utilisateurs actifs</span>
                    </div>
                    <div class="hero-stat-item">
                        <strong>87.6</strong>
                        <span>Score nutrition moy.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════
         ACCÈS RAPIDE
    ═══════════════════════════════════════════ -->
    <div class="section-wrap">
        <p class="section-label">Accès rapide</p>
        <h2 class="section-title">Que voulez-vous faire aujourd'hui ?</h2>
        <p class="section-subtitle">Naviguez vers vos fonctionnalités préférées en un clic.</p>

        <div class="quick-grid">
            <a class="quick-card" href="/integweb/VIEW/frontoffice.php" style="--card-color:#4CAF50;--card-bg:#E8F5E9;">
                <div class="quick-card-icon"><i class="fas fa-utensils"></i></div>
                <h3>Recettes</h3>
                <p>Découvrez et partagez des recettes saines et durables.</p>
                <span class="quick-card-arrow">Voir les recettes <i class="fas fa-arrow-right"></i></span>
            </a>

            <a class="quick-card" href="/integweb/VIEW/gamification.php" style="--card-color:#FFC107;--card-bg:#FFF8E1;">
                <div class="quick-card-icon"><i class="fas fa-trophy"></i></div>
                <h3>Défis & Récompenses</h3>
                <p>Participez aux défis, gagnez des points et montez dans le classement.</p>
                <span class="quick-card-arrow">Voir les défis <i class="fas fa-arrow-right"></i></span>
            </a>

            <a class="quick-card" href="/integweb/sport/index.php?action=mes_entrainements" style="--card-color:#7E57C2;--card-bg:#EDE7F6;">
                <div class="quick-card-icon"><i class="fas fa-dumbbell"></i></div>
                <h3>Entraînements</h3>
                <p>Suivez vos séances, analysez votre progression et atteignez vos objectifs.</p>
                <span class="quick-card-arrow">Mes séances <i class="fas fa-arrow-right"></i></span>
            </a>

            <a class="quick-card" href="/integweb/plan.php?page=plan-adapte" style="--card-color:#FF7043;--card-bg:#FBE9E7;">
                <div class="quick-card-icon"><i class="fas fa-apple-alt"></i></div>
                <h3>Plan nutritionnel</h3>
                <p>Obtenez des recommandations de repas personnalisées selon vos objectifs.</p>
                <span class="quick-card-arrow">Mon plan <i class="fas fa-arrow-right"></i></span>
            </a>

            <a class="quick-card" href="/integweb/VIEW/fil-recettes.php" style="--card-color:#29B6F6;--card-bg:#E1F5FE;">
                <div class="quick-card-icon"><i class="fas fa-stream"></i></div>
                <h3>Fil communautaire</h3>
                <p>Suivez les publications de la communauté et partagez vos créations.</p>
                <span class="quick-card-arrow">Voir le fil <i class="fas fa-arrow-right"></i></span>
            </a>

            <a class="quick-card" href="/integweb/VIEW/profil.php" style="--card-color:#26A69A;--card-bg:#E0F2F1;">
                <div class="quick-card-icon"><i class="fas fa-user-circle"></i></div>
                <h3>Mon profil</h3>
                <p>Gérez vos informations, préférences et historique d'activité.</p>
                <span class="quick-card-arrow">Mon compte <i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         FEATURES
    ═══════════════════════════════════════════ -->
    <div class="features-bg" id="features">
        <div class="section-wrap">
            <p class="section-label">Fonctionnalités</p>
            <h2 class="section-title">Nutrition intelligente · IA & durabilité</h2>
            <p class="section-subtitle">La technologie au service de votre santé et de la planète.</p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" style="background:#E8F5E9;color:#2E7D32;">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>Analyse nutritionnelle IA</h3>
                    <p>Suivez vos besoins caloriques, macronutriments et objectifs personnels grâce à l'intelligence artificielle.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background:#E3F2FD;color:#0288D1;">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3>Partage communautaire</h3>
                    <p>Publiez vos recettes avec ingrédients, étapes et photos. Likez et commentez celles de la communauté.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background:#FFF8E1;color:#F9A825;">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>Gamification & Défis</h3>
                    <p>Proposez des défis santé, participez, gagnez des points et grimpez dans le classement.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background:#EDE7F6;color:#6A1B9A;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Suivi sportif avancé</h3>
                    <p>Analysez votre progression avec régression linéaire, détection de plateau et prédictions à 30 jours.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background:#E0F2F1;color:#00695C;">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Score écologique</h3>
                    <p>Chaque recette est notée selon son impact environnemental pour vous aider à manger plus durablement.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="background:#FCE4EC;color:#C62828;">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3>Recommandations KNN</h3>
                    <p>L'algorithme KNN analyse vos préférences pour vous proposer des exercices et repas sur mesure.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         IMPACT
    ═══════════════════════════════════════════ -->
    <div class="section-wrap" id="impact">
        <div class="impact-section">
            <h2>Notre impact collectif</h2>
            <p>Ensemble, nous construisons une alimentation plus saine et plus responsable.</p>
            <div class="impact-stats">
                <div class="impact-stat">
                    <strong class="counter" data-target="1284">0</strong>
                    <span>kg CO₂ économisés</span>
                </div>
                <div class="impact-stat">
                    <strong class="counter" data-target="3452">0</strong>
                    <span>Repas durables partagés</span>
                </div>
                <div class="impact-stat">
                    <strong class="counter" data-target="2189">0</strong>
                    <span>Utilisateurs actifs</span>
                </div>
                <div class="impact-stat">
                    <strong class="counter" data-target="876" data-decimal="true">0</strong>
                    <span>Score nutrition moyen</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         CTA FINAL
    ═══════════════════════════════════════════ -->
    <div class="section-wrap" style="padding-top:0;">
        <div class="cta-banner">
            <div>
                <h2>Prêt à commencer ?</h2>
                <p>Explorez toutes les fonctionnalités de Kool Healthy dès maintenant.</p>
            </div>
            <div class="cta-buttons">
                <a class="btn-green" href="/integweb/VIEW/frontoffice.php">
                    <i class="fas fa-utensils"></i> Voir les recettes
                </a>
                <a class="btn-outline-green" href="/integweb/sport/index.php?action=mes_entrainements">
                    <i class="fas fa-dumbbell"></i> Mes entraînements
                </a>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════ -->
    <footer class="footer">
        <p>&copy; 2026 Kool Healthy — Mangez mieux, préservez la planète.</p>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const t = document.querySelector(a.getAttribute('href'));
                if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
            });
        });

        // Animated counters
        const counters = document.querySelectorAll('.counter');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const target = +el.dataset.target;
                const isDecimal = el.dataset.decimal === 'true';
                let current = 0;
                const step = target / 60;
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    el.textContent = isDecimal
                        ? (current / 10).toFixed(1)
                        : Math.floor(current).toLocaleString('fr-FR');
                }, 25);
                observer.unobserve(el);
            });
        }, { threshold: 0.3 });

        counters.forEach(c => observer.observe(c));
    </script>
</body>
</html>
