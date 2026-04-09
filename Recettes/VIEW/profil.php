<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateurConnecte = $authController->utilisateurConnecte();

// Utilisateur fictif pour la démonstration
$profilUtilisateur = [
    'nom' => 'Mohsen',
    'email' => 'mohsen@koolhealthy.com',
    'age' => 28,
    'poids' => 62,
    'taille' => 170,
    'imc' => 21.4,
    'objectif' => 'Perdre 5 kg',
    'progression' => 60,
    'objectif_poids' => 57,
    'score_nutrition' => 78,
    'recettes_partagees' => 12,
    'followers' => 234,
    'certifications' => [
        ['titre' => 'Nutrition Expert', 'niveau' => 'Avancé'],
        ['titre' => 'Chef Santé', 'niveau' => 'Confirmé'],
        ['titre' => 'Authenticité Vérifiée', 'niveau' => 'Actif'],
        ['titre' => 'Recettes Qualité', 'niveau' => 'Premium'],
        ['titre' => 'Nutritionniste Pro', 'niveau' => 'Expert'],
    ]
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Kool Healthy</title>
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
            <a href="profil.php">Profils</a>
            <a href="fil-recettes.php">Partage</a>
        </nav>

        <div class="topbar-tools">
            <a class="auth-link" href="auth.php">Connexion</a>
        </div>
    </header>

    <section class="section-wrap recipes-section">
    <div class="profile-full-wrapper">
        <main>
            <!-- Page Profil Utilisateur -->
            <div class="profile-page">
                <!-- Barre de profil simplifiée -->
                <section class="panel profile-header-bar">
                    <div class="profile-header-content">
                        <div class="profile-avatar-small">
                            <?php echo strtoupper(substr($profilUtilisateur['nom'], 0, 2)); ?>
                        </div>
                        <div class="profile-header-info">
                            <h1><?php echo htmlspecialchars($profilUtilisateur['nom']); ?></h1>
                            <p class="profile-header-email">📧 <?php echo htmlspecialchars($profilUtilisateur['email']); ?></p>
                        </div>
                        <div class="profile-header-stats">
                            <div class="stat-item">
                                <strong><?php echo $profilUtilisateur['recettes_partagees']; ?></strong>
                                <span>Recettes</span>
                            </div>
                            <div class="stat-item">
                                <strong><?php echo $profilUtilisateur['followers']; ?></strong>
                                <span>Followers</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Conteneur en grille pour les sections -->
                <div class="profile-grid-container">
                    <!-- Colonne gauche: Infos personnelles & nutrition -->
                    <div class="profile-column-left">
                        <!-- Objectif et Progression (Priorité haute) -->
                        <section class="panel profile-section objective-highlight">
                            <h2>🎯 Objectif Nutritionnel</h2>
                            <div class="objective-box">
                                <h3><?php echo htmlspecialchars($profilUtilisateur['objectif']); ?></h3>
                                <p class="objective-detail">Poids cible: <?php echo $profilUtilisateur['objectif_poids']; ?> kg | Poids actuel: <?php echo $profilUtilisateur['poids']; ?> kg</p>
                                <div class="progress-bar-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $profilUtilisateur['progression']; ?>%"></div>
                                    </div>
                                    <p class="progress-text"><?php echo $profilUtilisateur['progression']; ?>% d'avancement</p>
                                </div>
                            </div>
                        </section>

                        <!-- Informations Personnelles -->
                        <section class="panel profile-section">
                            <h2>📋 Informations Personnelles</h2>
                            <div class="profile-grid">
                                <div class="profile-field">
                                    <label>Âge</label>
                                    <p><?php echo $profilUtilisateur['age']; ?> ans</p>
                                </div>
                                <div class="profile-field">
                                    <label>Poids</label>
                                    <p><?php echo $profilUtilisateur['poids']; ?> kg</p>
                                </div>
                                <div class="profile-field">
                                    <label>Taille</label>
                                    <p><?php echo $profilUtilisateur['taille']; ?> cm</p>
                                </div>
                                <div class="profile-field">
                                    <label>IMC</label>
                                    <p><?php echo $profilUtilisateur['imc']; ?></p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Colonne droite: Certifications -->
                    <div class="profile-column-right">
                        <!-- Certifications et Réalisations -->
                        <section class="panel profile-section">
                            <h2>⭐ Certifications & Réalisations</h2>
                            <div class="certifications-grid">
                                <?php foreach ($profilUtilisateur['certifications'] as $cert): ?>
                                <div class="certification-card">
                                    <div class="cert-icon">✓</div>
                                    <p class="cert-title"><?php echo htmlspecialchars($cert['titre']); ?></p>
                                    <small class="cert-level"><?php echo htmlspecialchars($cert['niveau']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </div>
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
