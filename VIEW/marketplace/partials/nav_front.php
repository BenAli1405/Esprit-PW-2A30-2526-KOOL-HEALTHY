<?php
declare(strict_types=1);
/**
 * Barre de navigation Front Office (hub / client / vendeur).
 *
 * @var string $navActive 'hub'|'client'|'vendeur'
 * @var string $linkHome URL vers index.php (racine kool)
 * @var string $linkClient URL vers View/FrontOffice/client.php
 * @var string $linkVendeur URL vers View/FrontOffice/vendeur.php
 */
$navActive   = $navActive   ?? 'hub';
$linkHome    = $linkHome    ?? '/integweb/VIEW/home.php';
$linkClient  = $linkClient  ?? '/integweb/VIEW/marketplace/client.php';
$linkVendeur = $linkVendeur ?? '/integweb/VIEW/marketplace/vendeur.php';
?>
<nav class="navbar">
  <a href="<?php echo htmlspecialchars($linkHome, ENT_QUOTES, 'UTF-8'); ?>" class="logo logo-link">
    <i class="fas fa-seedling"></i>
    Kool Healthy | Anti-Waste
  </a>
  <div class="nav-actions">
    <a href="<?php echo htmlspecialchars($linkVendeur, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline btn-small<?php echo $navActive === 'vendeur' ? ' nav-link--active' : ''; ?>">
      <i class="fa-solid fa-store"></i> Espace vendeur
    </a>
    <a href="<?php echo htmlspecialchars($linkClient, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary btn-small<?php echo $navActive === 'client' ? ' nav-link--active' : ''; ?>">
      <i class="fa-solid fa-cart-shopping"></i> Espace client
    </a>
  </div>
  <div class="user-profile">
    <span>Bienvenue, Amine</span>
    <div class="avatar">A</div>
  </div>
</nav>
