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
    <a class="brand" href="home.php" aria-label="Kool Healthy">
        <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy"
             onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
    </a>

    <nav class="top-nav" aria-label="Navigation principale">
        <a href="home.php"         <?= $currentPage === 'home.php'          ? 'class="active"' : '' ?>>Accueil</a>
        <a href="home.php#features" class="disabled-control">Fonctionnalites</a>
        <a href="frontoffice.php"  <?= $currentPage === 'frontoffice.php'   ? 'class="active"' : '' ?>>Recettes</a>
        <a href="home.php#impact"  class="disabled-control">Impact</a>
        <a href="fil-recettes.php" <?= $currentPage === 'fil-recettes.php'  ? 'class="active"' : '' ?>>Partage</a>
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
