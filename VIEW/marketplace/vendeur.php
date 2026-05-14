<?php
declare(strict_types=1);

// Integweb root (VIEW/marketplace -> VIEW -> integweb)
$integwebRoot = dirname(__DIR__, 2);

$config = require $integwebRoot . '/config/kool_config.php';

$stripePublishable = (string) ($config['stripe_publishable_key'] ?? '');
$geminiReady = trim((string) ($config['gemini_api_key'] ?? '')) !== '';

// Static paths for integweb marketplace
$assetsBase      = '/integweb/VIEW/marketplace/assets/';
$frontOfficeBase = '/integweb/VIEW/marketplace/api/';
$appWebRoot      = '/integweb';

$linkHome    = '/integweb/VIEW/home.php';
$linkClient  = '/integweb/VIEW/marketplace/client.php';
$linkVendeur = '/integweb/VIEW/marketplace/vendeur.php';
$navActive   = 'vendeur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Espace vendeur</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetsBase . 'style.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="page-vendeur">

<?php require __DIR__ . '/partials/nav_front.php'; ?>
<?php require __DIR__ . '/partials/weather_strip.php'; ?>

  <div class="container page-single-column">
    <main class="main-stack" id="main-vendeur">

      <aside class="card vendor-buyer-hint" role="note">
        <p class="panel-note" style="margin:0;">
          <strong>Espace vendeur</strong> : produits, inventaire, prix et annonces. Les acheteurs passent par
          <a href="<?php echo htmlspecialchars($linkClient, ENT_QUOTES, 'UTF-8'); ?>">l'espace client</a> (panier et commande uniquement).
        </p>
      </aside>

      <div class="card chart-card">
        <h2 class="section-title"><i class="fa-solid fa-chart-pie"></i> Statistiques (mes annonces)</h2>
        <p class="panel-note">Repartition des statuts de vos ventes</p>
        <div class="chart-wrap">
          <canvas id="statsChart" aria-label="Graphique statuts"></canvas>
        </div>
      </div>

      <div class="card">
        <div class="section-header">
          <h2 class="section-title"><i class="fa-solid fa-box-open"></i> Gestion des produits</h2>
          <button type="button" class="btn btn-primary btn-small" id="resetProductButton">
            <i class="fa-solid fa-rotate-left"></i> Nouveau
          </button>
        </div>
        <div id="productFormAlert" class="form-alert" style="display:none;"></div>
        <form id="productForm" novalidate>
          <input type="hidden" id="productMode" value="create">
          <input type="hidden" id="productOriginalId" value="">
          <div class="form-row">
            <div class="form-group">
              <label for="prodId">ID Produit</label>
              <input type="text" id="prodId" class="form-control" placeholder="Ex: PRD-101">
              <small class="field-error" id="prodIdError"></small>
            </div>
            <div class="form-group">
              <label for="prodName">Nom du Produit</label>
              <input type="text" id="prodName" class="form-control" placeholder="Lait, Riz, etc.">
              <small class="field-error" id="prodNameError"></small>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="prodDate">Date d'Expiration</label>
              <input type="date" id="prodDate" class="form-control">
              <small class="field-error" id="prodDateError"></small>
            </div>
            <div class="form-group">
              <label for="prodStock">Quantite en Stock</label>
              <input type="number" id="prodStock" class="form-control" placeholder="Ex: 5">
              <small class="field-error" id="prodStockError"></small>
            </div>
          </div>
          <p class="panel-note" style="margin: 0 0 0.75rem;">Pour vendre : creez le produit ici, puis utilisez <strong>Mettre en vente</strong> dans l inventaire (prix unitaire et quantite de l annonce).</p>
          <button type="submit" class="btn btn-primary" id="productSubmitButton" style="width: 100%; margin-top: 10px;">
            <i class="fa-solid fa-plus"></i> Sauvegarder Produit
          </button>
        </form>
      </div>

      <div class="card">
        <h2 class="section-title"><i class="fa-solid fa-boxes-stacked"></i> Mon Inventaire</h2>
        <div class="filter-toolbar">
          <div class="search-bar flex-grow">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="inventorySearch" class="search-input" placeholder="Rechercher par nom">
          </div>
          <div class="form-group filter-select-wrap">
            <label for="inventoryStockFilter" class="visually-hidden">Stock</label>
            <select id="inventoryStockFilter" class="form-control">
              <option value="">Tous les stocks</option>
              <option value="low">Stock faible (1-5)</option>
              <option value="mid">Stock moyen (6-20)</option>
              <option value="high">Stock eleve (&gt;20)</option>
            </select>
          </div>
        </div>
        <div class="list-container" id="inventoryList"></div>
      </div>

      <div class="card">
        <h2 class="section-title"><i class="fa-solid fa-store"></i> Mes ventes</h2>
        <div class="filter-toolbar">
          <div class="search-bar flex-grow">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="salesSearch" class="search-input" placeholder="Rechercher par nom de produit">
          </div>
          <div class="form-group filter-select-wrap">
            <label for="mySalesStatusFilter" class="visually-hidden">Statut</label>
            <select id="mySalesStatusFilter" class="form-control">
              <option value="">Tous les statuts</option>
              <option value="disponible">Disponible</option>
              <option value="reservee">Reservee</option>
              <option value="vendue">Vendue</option>
            </select>
          </div>
        </div>
        <div class="list-container" id="mySalesList"></div>
      </div>

    </main>
  </div>

  <div class="modal" id="saleModal">
    <div class="modal-content">
      <i class="fa-solid fa-xmark close-modal" id="closeSaleModalButton"></i>
      <div class="section-header">
        <h2 class="section-title" id="saleModalTitle">Mettre en vente</h2>
      </div>
      <p class="modal-note">Produit: <strong id="saleProdNameDisplay"></strong> (ID: <span id="saleProdIdDisplay"></span>)</p>
      <div id="saleFormAlert" class="form-alert" style="display:none;"></div>
      <form id="saleForm" novalidate>
        <input type="hidden" id="saleMode" value="create">
        <input type="hidden" id="saleId" value="">
        <input type="hidden" id="saleProdId" value="">
        <div class="form-row">
          <div class="form-group">
            <label for="saleStock">Stock a vendre</label>
            <input type="number" id="saleStock" class="form-control" placeholder="Ex: 2">
            <small class="field-error" id="saleStockError"></small>
            <small class="hint-text" id="maxStockHint"></small>
          </div>
          <div class="form-group">
            <label for="salePrice">Prix unitaire (TND)</label>
            <input type="number" id="salePrice" class="form-control" placeholder="Ex: 2.50 par unite">
            <small class="field-error" id="salePriceError"></small>
          </div>
        </div>
        <div class="form-group">
          <label for="saleStatus">Statut</label>
          <select id="saleStatus" class="form-control">
            <option value="disponible">Disponible</option>
            <option value="reservee">Reservee</option>
            <option value="vendue">Vendue</option>
          </select>
          <small class="field-error" id="saleStatusError"></small>
        </div>
        <button type="submit" class="btn btn-success" id="saleSubmitButton" style="width: 100%; margin-top: 15px;">
          <i class="fa-solid fa-bullhorn"></i> Publier l'annonce
        </button>
      </form>
    </div>
  </div>

  <script>
    window.APP_API_BASE = <?php echo json_encode($frontOfficeBase, JSON_UNESCAPED_SLASHES); ?>;
    window.STRIPE_PUBLISHABLE_KEY = <?php echo json_encode($stripePublishable); ?>;
    window.GEMINI_READY = <?php echo $geminiReady ? 'true' : 'false'; ?>;
    window.KOOL_PAGE_ROLE = 'vendeur';
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="<?php echo htmlspecialchars($assetsBase . 'script.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
