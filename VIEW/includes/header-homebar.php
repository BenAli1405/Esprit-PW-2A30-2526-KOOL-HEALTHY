<?php
// Exact header copied from VIEW/home.php, with safe user initialization
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($utilisateurConnecte)) {
    if (!class_exists('AuthController')) {
        require_once __DIR__ . '/../../CONTROLLER/AuthController.php';
    }
    if (class_exists('AuthController')) {
        $authController = new AuthController();
        // Do not force redirects here; just get connected user if available
        $utilisateurConnecte = $authController->utilisateurConnecte();
    } else {
        $utilisateurConnecte = null;
    }
}
?>
<header class="topbar">
    <a class="brand" href="home.php" aria-label="Kool Healthy">
        <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
    </a>

    <nav class="top-nav" aria-label="Navigation principale">
        <a href="home.php">Accueil</a>
        <a class="disabled-control" href="home.php#features">Fonctionnalites</a>
        <a href="frontoffice.html">Recettes</a>
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
