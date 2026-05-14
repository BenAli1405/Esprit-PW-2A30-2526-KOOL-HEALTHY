<?php
declare(strict_types=1);

// Integweb root (VIEW/marketplace -> VIEW -> integweb)
$assetsBase      = '/integweb/VIEW/marketplace/assets/';
$frontOfficeBase = '/integweb/VIEW/marketplace/api/';
$appWebRoot      = '/integweb';

$linkHome    = '/integweb/VIEW/home.php';
$linkClient  = '/integweb/VIEW/marketplace/client.php';
$linkVendeur = '/integweb/VIEW/marketplace/vendeur.php';
$navActive   = 'hub';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Accueil</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetsBase . 'style.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="page-hub">

<?php require __DIR__ . '/partials/nav_front.php'; ?>
<?php require __DIR__ . '/partials/weather_strip.php'; ?>

  <main class="hub-main container">
    <section class="card hub-hero">
      <h1 class="hub-title"><i class="fas fa-seedling"></i> Kool Healthy</h1>
      <p class="hub-lead">Anti-gaspillage alimentaire : choisissez votre espace.</p>
      <div class="hub-actions">
        <a class="btn btn-primary hub-tile" href="<?php echo htmlspecialchars($linkClient, ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="hub-tile-title">Espace client</span>
          <span class="hub-tile-desc">Marche, panier, paiement Stripe ou sur place, aide Gemini</span>
        </a>
        <a class="btn btn-outline hub-tile" href="<?php echo htmlspecialchars($linkVendeur, ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fa-solid fa-store"></i>
          <span class="hub-tile-title">Espace vendeur</span>
          <span class="hub-tile-desc">Produits, inventaire, annonces et statistiques</span>
        </a>
      </div>
      <p class="panel-note hub-note">Le <a href="<?php echo htmlspecialchars(kool_public_href($appWebRoot, 'View/BackOffice/index.php'), ENT_QUOTES, 'UTF-8'); ?>">BackOffice</a> reste dedie a la moderation avancee.</p>
    </section>
  </main>
  <script>
    (function () {
      var el = document.getElementById('weatherStripText');
      if (!el) return;
      var url = <?php echo json_encode(rtrim($frontOfficeBase, '/') . '/api_weather.php', JSON_UNESCAPED_SLASHES); ?>;
      fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.success && d.summary) {
            var loc = d.location_label ? d.location_label + ' — ' : '';
            el.textContent = loc + d.summary;
          } else {
            el.textContent = d.error || 'Meteo indisponible';
          }
        })
        .catch(function () { el.textContent = 'Meteo indisponible'; });
    })();
  </script>
</body>
</html>
