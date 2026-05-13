<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$nomAffichage = $utilisateurConnecte['nom'] ?? 'Utilisateur';
$emailAffichage = $utilisateurConnecte['email'] ?? '';
?>
<aside class="panel left-nav" aria-label="Navigation recettes">
    <div class="profile-mini">
        <div class="profile-avatar-nav">
            <?php if (!empty($utilisateurConnecte['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($utilisateurConnecte['avatar']); ?>" alt="Avatar" class="avatar-nav-img">
            <?php else: ?>
                <div class="avatar-nav-placeholder"><?php echo strtoupper(substr($nomAffichage, 0, 1)); ?></div>
            <?php endif; ?>
        </div>
        <div class="profile-mini-info">
            <strong><?php echo htmlspecialchars($nomAffichage); ?></strong>
            <small><?php echo htmlspecialchars($emailAffichage); ?></small>
        </div>
    </div>
    <nav class="menu">
        <a class="<?php echo $currentPage === 'fil-recettes.php' ? 'active' : ''; ?>" href="fil-recettes.php">Fil recettes</a>
        <a class="<?php echo $currentPage === 'mes-recettes.php' ? 'active' : ''; ?>" href="mes-recettes.php">Mes recettes</a>
        <a class="<?php echo $currentPage === 'favoris.php' ? 'active' : ''; ?>" href="favoris.php">Favoris</a>
        <a class="<?php echo $currentPage === 'comptes-suivis.php' ? 'active' : ''; ?>" href="comptes-suivis.php">Comptes suivis</a>
    </nav>
</aside>
