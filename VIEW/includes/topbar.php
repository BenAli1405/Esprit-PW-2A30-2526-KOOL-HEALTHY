<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($utilisateurConnecte)) {
    if (!class_exists('AuthController')) {
        require_once __DIR__ . '/../../CONTROLLER/AuthController.php';
    }
    $authController = new AuthController();
    $utilisateurConnecte = $authController->utilisateurConnecte();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header class="topbar">
    <a class="brand" href="/integweb/VIEW/home.php" aria-label="Kool Healthy">
        <img class="brand-logo" src="/integweb/Assets/logo-kool-healthy.png" alt="Kool Healthy"
             onerror="this.onerror=null;this.src='/integweb/public/images/logo.png';">
    </a>

    <nav class="top-nav" aria-label="Navigation principale">
        <?php if ($currentPage !== 'gamification.php'): ?>
            <a href="/integweb/VIEW/home.php"         <?= $currentPage === 'home.php'          ? 'class="active"' : '' ?>>Accueil</a>
        <?php endif; ?>
        <a href="/integweb/VIEW/home.php#features" class="disabled-control">Fonctionnalites</a>
        <a href="/integweb/VIEW/frontoffice.php"  <?= $currentPage === 'frontoffice.php'   ? 'class="active"' : '' ?>>Recettes</a>
        <a href="/integweb/VIEW/home.php#impact"  class="disabled-control">Impact</a>
        <a href="/integweb/VIEW/fil-recettes.php" <?= $currentPage === 'fil-recettes.php'  ? 'class="active"' : '' ?>>Partage</a>
        <a href="/integweb/plan.php?page=plan-adapte" <?= (strpos($currentPage, 'plan-adapte') !== false ? 'class="active"' : '') ?>>Vos repas</a>
        <a href="/integweb/VIEW/gamification.php" <?= $currentPage === 'gamification.php' ? 'class="active"' : '' ?>>🏆 Défis & Récompenses</a>
        <a href="/integweb/sport/index.php?action=mes_entrainements" <?= (strpos($currentPage, 'sport') !== false ? 'class="active"' : '') ?>>💪 Entraînement & Exercice</a>
        <a href="/integweb/VIEW/marketplace/client.php" class="btn-topbar-marketplace<?= (strpos($_SERVER['REQUEST_URI'], '/marketplace/client') !== false ? ' active' : '') ?>">🛒 Espace client</a>
        <a href="/integweb/VIEW/marketplace/vendeur.php" class="btn-topbar-marketplace btn-topbar-vendeur<?= (strpos($_SERVER['REQUEST_URI'], '/marketplace/vendeur') !== false ? ' active' : '') ?>">🏪 Espace vendeur</a>
    </nav>

    <div class="topbar-tools">
        <?php if ($utilisateurConnecte): ?>
            <details class="profile-menu">
                <summary class="profile-menu-trigger" aria-label="Menu profil">
                    <span class="profile-avatar"><?= strtoupper(substr($utilisateurConnecte['nom'] ?? 'U', 0, 1)) ?></span>
                </summary>
                <div class="profile-menu-dropdown">
                    <div class="profile-menu-user">
                        <strong><?= htmlspecialchars($utilisateurConnecte['nom'] ?? 'Utilisateur') ?></strong>
                        <small><?= htmlspecialchars($utilisateurConnecte['email'] ?? '') ?></small>
                    </div>
                    <a href="/integweb/VIEW/profil.php">Mon profil</a>
                    <?php if (($utilisateurConnecte['role'] ?? '') === 'admin'): ?>
                        <a href="/integweb/VIEW/backoffice.php">Backoffice</a>
                    <?php endif; ?>
                    <a class="danger" href="/integweb/CONTROLLER/AuthController.php?action=logout">Se deconnecter</a>
                </div>
            </details>
        <?php else: ?>
            <a class="auth-link" href="/integweb/VIEW/auth.php">Connexion</a>
        <?php endif; ?>
    </div>
</header>
