<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateurConnecte = $authController->utilisateurConnecte();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="home.php" aria-label="Kool Healthy">
            <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
        </a>

        <nav class="top-nav" aria-label="Navigation principale">
            <a href="home.php">Accueil</a>
            <a class="disabled-control" href="home.php#features">Fonctionnalites</a>
            <a class="disabled-control" href="fil-recettes.php">Recettes</a>
            <a class="disabled-control" href="home.php#impact">Impact</a>
            <a href="fil-recettes.php">Partage</a>
        </nav>

        <div class="topbar-tools">
            <?php if ($utilisateurConnecte): ?>
                <details class="profile-menu">
                    <summary class="profile-menu-trigger" aria-label="Menu profil">
                        <span class="profile-avatar"><?php echo strtoupper(substr($utilisateurConnecte['nom'] ?? 'U', 0, 1)); ?></span>
                    </summary>
                    <div class="profile-menu-dropdown">
                        <div class="profile-menu-user">
                            <strong><?php echo htmlspecialchars($utilisateurConnecte['nom'] ?? 'Utilisateur'); ?></strong>
                            <small><?php echo htmlspecialchars($utilisateurConnecte['email'] ?? ''); ?></small>
                        </div>
                        <a href="profil.php">Mon profil</a>
                        <?php if (($utilisateurConnecte['role'] ?? '') === 'admin'): ?>
                            <a href="backoffice.php">Backoffice</a>
                        <?php endif; ?>
                        <a class="danger" href="../CONTROLLER/AuthController.php?action=logout">Se deconnecter</a>
                    </div>
                </details>
            <?php else: ?>
                <a class="auth-link" href="auth.php">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <section id="accueil" class="hero">
        <div class="hero-content">
            <h1>Mangez mieux, <span>preservez la planete</span></h1>
            <p>Kool Healthy combine nutrition intelligente, partage communautaire et recettes durables dans un frontoffice unique.</p>
            <div class="hero-buttons">
                <a class="btn" href="recettes.php">Commencer gratuitement</a>
                <a class="auth-link" href="#features">En savoir plus</a>
            </div>
        </div>
        <div class="hero-card panel">
            <h3>Mode RS integre</h3>
            <p>Publication de recettes, favoris, fil communautaire et profils sur la meme interface.</p>
            <div class="hero-chip">+ Tableau de bord backoffice disponible</div>
        </div>
    </section>

    <section id="features" class="section-wrap">
        <h2 class="section-title">Nutrition intelligente · IA & durabilite</h2>
        <p class="section-subtitle">La technologie au service de votre sante et de la planete.</p>
        <div class="features-grid">
            <article class="feature-card panel">
                <h3>Analyse nutrition</h3>
                <p>Suivez les besoins calorifiques et les objectifs personnels.</p>
            </article>
            <article class="feature-card panel">
                <h3>Partage recettes</h3>
                <p>Publiez des recettes avec ingredients, etapes et visuels.</p>
            </article>
            <article class="feature-card panel">
                <h3>Module favoris</h3>
                <p>Sauvegardez les recettes preferees et retrouvez-les rapidement.</p>
            </article>
            <article class="feature-card panel">
                <h3>Backoffice admin</h3>
                <p>Consultez les statistiques, utilisateurs et tendances d'usage.</p>
            </article>
        </div>
    </section>

    <section id="impact" class="section-wrap">
        <div class="impact-section panel">
            <h2>Notre impact collectif</h2>
            <div class="impact-stats">
                <div class="impact-stat">
                    <strong>1,284 kg</strong>
                    <span>CO2 economises</span>
                </div>
                <div class="impact-stat">
                    <strong>3,452</strong>
                    <span>Repas durables partages</span>
                </div>
                <div class="impact-stat">
                    <strong>2,189</strong>
                    <span>Utilisateurs actifs</span>
                </div>
                <div class="impact-stat">
                    <strong>87.6</strong>
                    <span>Score nutrition moyen</span>
                </div>
            </div>
        </div>
    </section>

    <section class="section-wrap">
        <div class="profiles-block panel">
            <h2>Profils</h2>
            <p>Section profils prete: gestion du compte, preferences et historique seront integres ici ensuite.</p>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 Kool Healthy. Mangez mieux, preservez la planete.</p>
        </div>
    </footer>

    <script>
        // Smooth scroll behavior (optionnel)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
