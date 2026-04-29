<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if (!$utilisateurConnecte) {
    header('Location: auth.php');
    exit();
}

// Profil base uniquement sur les donnees du compte connecte
$profilUtilisateur = [
    'nom' => (string) $utilisateurConnecte['nom'],
    'email' => (string) $utilisateurConnecte['email'],
    'age' => $utilisateurConnecte['age'] ?? null,
    'poids' => $utilisateurConnecte['poids'] ?? null,
    'taille' => $utilisateurConnecte['taille'] ?? null,
    'imc' => $utilisateurConnecte['imc'] ?? null,
    'objectif' => $utilisateurConnecte['objectif'] ?? null
];

$messagesErreur = [
    'invalid_data' => 'Veuillez verifier les informations saisies (email, age, poids, taille).',
    'email_exists' => 'Cette adresse email est deja utilisee par un autre compte.',
    'name_exists' => 'Ce nom est deja utilise par un autre compte.',
    'password_too_short' => 'Le nouveau mot de passe doit contenir au moins 6 caracteres.',
    'server_error' => 'Une erreur est survenue pendant la mise a jour.',
    'profile_update' => 'La mise a jour du profil a echoue.'
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

    <section class="section-wrap recipes-section">
    <div class="profile-full-wrapper">
        <main>
            <!-- Page Profil Utilisateur -->
            <div class="profile-page">
                <!-- Barre de profil simplifiée -->
                <section class="panel profile-header-bar">
                    <?php if ($success === 'profile_updated'): ?>
                        <div class="profile-feedback success">Vos informations ont ete mises a jour avec succes.</div>
                    <?php endif; ?>

                    <?php if ($error !== '' && isset($messagesErreur[$error])): ?>
                        <div class="profile-feedback error"><?php echo htmlspecialchars($messagesErreur[$error]); ?></div>
                    <?php endif; ?>

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
                                <strong>-</strong>
                                <span>Recettes</span>
                            </div>
                            <div class="stat-item">
                                <strong>-</strong>
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
                                <h3><?php echo htmlspecialchars($profilUtilisateur['objectif'] ?: 'Non renseigne'); ?></h3>
                                <p class="objective-detail">Poids actuel: <?php echo $profilUtilisateur['poids'] !== null && $profilUtilisateur['poids'] !== '' ? htmlspecialchars((string) $profilUtilisateur['poids']) . ' kg' : 'Non renseigne'; ?></p>
                            </div>
                        </section>

                        <!-- Informations Personnelles -->
                        <section class="panel profile-section">
                            <h2>📋 Informations Personnelles</h2>
                            <form class="profile-edit-form" method="POST" action="../CONTROLLER/AuthController.php?action=update_profile">
                                <div class="profile-grid">
                                    <div class="profile-field">
                                        <label for="nom">Nom</label>
                                        <input id="nom" name="nom" type="text" required value="<?php echo htmlspecialchars((string) $profilUtilisateur['nom']); ?>">
                                    </div>
                                    <div class="profile-field">
                                        <label for="email">Email</label>
                                        <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars((string) $profilUtilisateur['email']); ?>">
                                    </div>
                                </div>

                                <div class="profile-grid">
                                    <div class="profile-field">
                                        <label for="age">Age</label>
                                        <input id="age" name="age" type="number" min="1" required value="<?php echo htmlspecialchars((string) ($profilUtilisateur['age'] ?? '')); ?>">
                                    </div>
                                    <div class="profile-field">
                                        <label for="poids">Poids (kg)</label>
                                        <input id="poids" name="poids" type="number" min="1" step="0.1" value="<?php echo htmlspecialchars((string) ($profilUtilisateur['poids'] ?? '')); ?>">
                                    </div>
                                    <div class="profile-field">
                                        <label for="taille">Taille (cm)</label>
                                        <input id="taille" name="taille" type="number" min="1" step="0.1" value="<?php echo htmlspecialchars((string) ($profilUtilisateur['taille'] ?? '')); ?>">
                                    </div>
                                </div>

                                <div class="profile-grid">
                                    <div class="profile-field">
                                        <label for="nouveau_mot_de_passe">Nouveau mot de passe (optionnel)</label>
                                        <input id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" type="password" minlength="6" placeholder="Laisser vide pour conserver l'actuel">
                                    </div>
                                </div>

                                <div class="profile-form-actions">
                                    <button class="btn" type="submit">Enregistrer les modifications</button>
                                </div>
                            </form>

                            <h3 class="profile-subtitle">Resume actuel</h3>
                            <div class="profile-grid profile-readonly-grid">
                                <div class="profile-field">
                                    <label>Âge</label>
                                    <p><?php echo $profilUtilisateur['age'] !== null && $profilUtilisateur['age'] !== '' ? htmlspecialchars((string) $profilUtilisateur['age']) . ' ans' : 'Non renseigne'; ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Poids</label>
                                    <p><?php echo $profilUtilisateur['poids'] !== null && $profilUtilisateur['poids'] !== '' ? htmlspecialchars((string) $profilUtilisateur['poids']) . ' kg' : 'Non renseigne'; ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>Taille</label>
                                    <p><?php echo $profilUtilisateur['taille'] !== null && $profilUtilisateur['taille'] !== '' ? htmlspecialchars((string) $profilUtilisateur['taille']) . ' cm' : 'Non renseigne'; ?></p>
                                </div>
                                <div class="profile-field">
                                    <label>IMC</label>
                                    <p><?php echo $profilUtilisateur['imc'] !== null && $profilUtilisateur['imc'] !== '' ? htmlspecialchars((string) $profilUtilisateur['imc']) : 'Non renseigne'; ?></p>
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
                                <p>Aucune certification disponible pour ce compte.</p>
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
