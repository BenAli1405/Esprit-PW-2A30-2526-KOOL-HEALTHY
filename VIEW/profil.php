<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';
require_once __DIR__ . '/../CONTROLLER/RecetteController.php';
require_once __DIR__ . '/../CONTROLLER/UserController.php';
require_once __DIR__ . '/../config.php';

$authController = new AuthController();
$recetteController = new RecetteController();
$userController = new UserController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

if (!$utilisateurConnecte) {
    header('Location: auth.php');
    exit();
}

// Get viewed profile (either current user or another user)
$profileUsername = $_GET['user'] ?? '';
$viewedProfile = null;
$isOwnProfile = true;
$isFollowing = false;
$followersCount = 0;
$followingCount = 0;
$recipesCount = 0;

if (!empty($profileUsername)) {
    $viewedProfile = $userController->getUserByNom($profileUsername);
    if (!$viewedProfile) {
        header('Location: fil-recettes.php?error=user_not_found');
        exit();
    }
    $isOwnProfile = ((int) $viewedProfile['id'] === (int) $utilisateurConnecte['id']);
} else {
    $viewedProfile = $userController->getUserById((int) $utilisateurConnecte['id']) ?: $utilisateurConnecte;
}

$avatarProfil = $viewedProfile['avatar'] ?? ($utilisateurConnecte['avatar'] ?? null);

// Get follower/following stats
$followersCount = $recetteController->getFollowersCount($viewedProfile['id']);
$followingCount = $recetteController->getFollowingCount($viewedProfile['id']);
$blockedUsers = [];

// Check if current user is following this profile
if (!$isOwnProfile) {
    $isFollowing = $recetteController->isFollowing($utilisateurConnecte['id'], $viewedProfile['id']);
}

if ($isOwnProfile) {
    $blockedUsers = $recetteController->getBlockedUsers((int) $utilisateurConnecte['id']);
}

// Get recipes count (publications table)
$db = config::getConnexion();
$sql = "SELECT COUNT(*) FROM publication WHERE auteur = :auteur";
$req = $db->prepare($sql);
$req->execute(['auteur' => $viewedProfile['nom']]);
$recipesCount = (int) $req->fetchColumn();

// Profil base uniquement sur les donnees du compte connecte
$profilUtilisateur = [
    'id' => $viewedProfile['id'] ?? null,
    'nom' => (string) $viewedProfile['nom'],
    'email' => (string) $viewedProfile['email'],
    'avatar' => $avatarProfil,
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
    <?php include __DIR__ . '/includes/header-homebar.php'; ?>

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
                        <div class="profile-avatar-main">
                            <?php if (!empty($profilUtilisateur['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($profilUtilisateur['avatar']); ?>" alt="Avatar de <?php echo htmlspecialchars($profilUtilisateur['nom']); ?>">
                            <?php else: ?>
                                <div class="avatar-main-placeholder"><?php echo strtoupper(substr($profilUtilisateur['nom'], 0, 1)); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-header-info">
                            <h1><?php echo htmlspecialchars($profilUtilisateur['nom']); ?></h1>
                            <p class="profile-header-email">📧 <?php echo htmlspecialchars($profilUtilisateur['email']); ?></p>
                        </div>
                        <div class="profile-header-stats">
                            <div class="stat-item">
                                <strong><?php echo $recipesCount; ?></strong>
                                <span>Recettes</span>
                            </div>
                            <div class="stat-item">
                                <strong><?php echo $followingCount; ?></strong>
                                <span>Following</span>
                            </div>
                        </div>
                        <?php if (!$isOwnProfile): ?>
                            <div style="margin-left: auto;">
                                <button class="follow-btn<?php echo $isFollowing ? ' following' : ''; ?>" data-user-id="<?php echo $viewedProfile['id']; ?>" type="button">
                                    <?php echo $isFollowing ? '✓ Suivi' : 'Suivre'; ?>
                                </button>
                            </div>
                        <?php endif; ?>
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
                            <form class="profile-edit-form" method="POST" action="../CONTROLLER/AuthController.php?action=update_profile" enctype="multipart/form-data">
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
                                        <label for="avatar">Photo de profil</label>
                                        <input id="avatar" name="avatar" type="file" accept="image/*">
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

                        <?php if ($isOwnProfile): ?>
                        <!-- Comptes que je suis (Following) -->
                        <section class="panel profile-section">
                            <h2>👤 Comptes que je suis (<?php echo $followingCount; ?>)</h2>
                            <div class="followers-list">
                                <?php
                                    $followedAccounts = $recetteController->getFollowedAccounts($utilisateurConnecte['id']);
                                    if (!empty($followedAccounts)):
                                        foreach (array_slice($followedAccounts, 0, 5) as $followed):
                                ?>
                                        <div class="follower-item">
                                            <div class="follower-info">
                                                <strong class="username-clickable" data-user-id="<?php echo (int) $followed['id']; ?>" style="cursor: pointer;">
                                                    <?php echo htmlspecialchars($followed['nom']); ?>
                                                </strong>
                                                <small><?php echo htmlspecialchars($followed['email']); ?></small>
                                            </div>
                                            <div class="follower-actions">
                                                <button class="btn-secondary btn-unfollow" data-user-id="<?php echo (int) $followed['id']; ?>" type="button">Ne plus suivre</button>
                                            </div>
                                        </div>
                                <?php
                                        endforeach;
                                        if ($followingCount > 5):
                                ?>
                                    <p style="text-align: center; margin-top: 10px; color: #999; font-size: 0.9rem;">
                                        +<?php echo $followingCount - 5; ?> other accounts
                                    </p>
                                <?php
                                        endif;
                                    else:
                                ?>
                                    <p>Vous ne suivez aucun compte pour le moment.</p>
                                <?php
                                    endif;
                                ?>
                            </div>
                        </section>

                        <!-- Comptes bloqués -->
                        <section class="panel profile-section">
                            <h2>🚫 Comptes bloqués</h2>
                            <?php if (!empty($blockedUsers)): ?>
                                <div class="followers-list">
                                    <?php foreach ($blockedUsers as $blockedUser): ?>
                                        <div class="follower-item">
                                            <div class="follower-info">
                                                <strong><?php echo htmlspecialchars($blockedUser['nom']); ?></strong>
                                                <small><?php echo htmlspecialchars($blockedUser['email']); ?></small>
                                            </div>
                                            <button class="btn-secondary btn-unblock" data-user-id="<?php echo (int) $blockedUser['id']; ?>" type="button">
                                                Débloquer
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="profile-subtitle">Vous n'avez bloqué aucun compte.</p>
                            <?php endif; ?>
                        </section>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    </section>

    <?php include __DIR__ . '/includes/user-action-modal.php'; ?>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 Kool Healthy. Mangez mieux, preservez la planete.</p>
        </div>
    </footer>
    <script src="../JS/follow-system.js?v=20260506"></script>
    <script src="../JS/user-modal.js?v=20260506"></script>
</body>
</html>
