<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Admin Back-Office</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

  <aside class="sidebar">
    <div class="sidebar-header">
      <i class="fas fa-seedling"></i> Kool Healthy Admin
    </div>
    <div class="sidebar-nav">
      <div class="nav-item active"><i class="fa-solid fa-users"></i> Gestion Utilisateurs</div>
      <div class="nav-item"><i class="fa-solid fa-box"></i> Global Stock</div>
      <div class="nav-item"><i class="fa-solid fa-chart-line"></i> Statistiques</div>
    </div>
  </aside>

  <main class="main-content">
    <header class="header">
      <h2>Module : Anti-Gaspillage</h2>
      <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <a class="btn btn-primary btn-sm" href="../client.php" style="text-decoration:none;">
          <i class="fa-solid fa-cart-shopping"></i> Espace client
        </a>
        <a class="btn btn-outline btn-sm" href="../vendeur.php" style="text-decoration:none;">
          <i class="fa-solid fa-store"></i> Espace vendeur
        </a>
        <a class="btn btn-outline btn-sm" href="/integweb/VIEW/home.php" style="text-decoration:none;">
          <i class="fa-solid fa-house"></i> Accueil
        </a>
        <span>Admin principal</span>
        <div style="background:#ddd; width:35px; height:35px; border-radius:50%; display:flex; justify-content:center; align-items:center;">
          <i class="fa-solid fa-user"></i>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="panel chart-panel-admin">
        <div class="panel-header">
          <div class="panel-title"><i class="fa-solid fa-chart-pie"></i> Statuts des ventes (global)</div>
        </div>
        <div class="panel-body">
          <div class="chart-wrap-admin">
            <canvas id="adminSalesChart" aria-label="Graphique des ventes"></canvas>
          </div>
        </div>
      </div>

      <div class="panel users-panel">
        <div class="panel-header">
          <div class="panel-title">Liste des Utilisateurs</div>
        </div>
        <div class="panel-body">
          <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="usersSearch" class="search-input" placeholder="Rechercher un utilisateur par nom">
          </div>
          <table>
            <thead>
              <tr><th>ID</th><th>Nom</th><th>Email</th><th>Actions</th></tr>
            </thead>
            <tbody id="usersTableBody"></tbody>
          </table>
        </div>
      </div>

      <div class="panel details-panel" id="userDetailsPanel">
        <div class="panel-header">
          <div class="panel-title">Gestion : <span id="currentUserName"></span></div>
          <button class="btn btn-sm btn-primary" id="closeUserDetailsButton">Fermer</button>
        </div>
        <div class="panel-body">
          <div id="backofficeAlert" class="admin-alert" style="display:none;"></div>

          <div class="section-toolbar" style="margin-top:0;">
            <h3><i class="fa-solid fa-warehouse"></i> Inventaire (Edition / Suppression)</h3>
          </div>
          <div class="filter-toolbar-admin">
            <div class="search-bar flex-grow">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="productsSearch" class="search-input" placeholder="Rechercher un produit par nom dans l inventaire">
            </div>
            <div class="form-group filter-select-wrap-admin">
              <label for="productsStockFilter">Stock</label>
              <select id="productsStockFilter" class="form-control">
                <option value="">Tous</option>
                <option value="low">1-5</option>
                <option value="mid">6-20</option>
                <option value="high">&gt;20</option>
              </select>
            </div>
          </div>
          <table style="margin-bottom: 2rem;">
            <thead>
              <tr><th>ID Prod</th><th>Nom</th><th>Expiration</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody id="userProductsBody"></tbody>
          </table>

          <div class="section-toolbar">
            <h3><i class="fa-solid fa-tags"></i> Ventes en cours / Historique</h3>
          </div>
          <div class="filter-toolbar-admin">
            <div class="search-bar flex-grow">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="salesSearch" class="search-input" placeholder="Rechercher un produit par nom dans les ventes">
            </div>
            <div class="form-group filter-select-wrap-admin">
              <label for="salesStatusFilter">Statut vente</label>
              <select id="salesStatusFilter" class="form-control">
                <option value="">Tous</option>
                <option value="disponible">Disponible</option>
                <option value="reservee">Reservee</option>
                <option value="vendue">Vendue</option>
              </select>
            </div>
          </div>
          <table>
            <thead>
              <tr><th>ID Vente</th><th>Prod ID</th><th>Produit</th><th>Qte</th><th>Prix (TND)</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody id="userSalesBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <div class="modal" id="productModal">
    <div class="modal-content">
      <i class="fa-solid fa-xmark close-btn" id="closeProductModalButton"></i>
      <h3 id="productModalTitle" style="margin-bottom: 1.5rem; color: var(--admin-primary);">Modifier Produit</h3>
      <div id="productFormAlert" class="admin-alert" style="display:none;"></div>
      <form id="productForm" novalidate>
        <input type="hidden" id="formOriginalId" value="">

        <div class="form-group">
          <label for="mProdId">ID Produit</label>
          <input type="text" id="mProdId">
          <small class="field-error" id="mProdIdError"></small>
        </div>
        <div class="form-group">
          <label for="mProdName">Nom</label>
          <input type="text" id="mProdName">
          <small class="field-error" id="mProdNameError"></small>
        </div>
        <div class="form-group">
          <label for="mProdExp">Expiration</label>
          <input type="date" id="mProdExp">
          <small class="field-error" id="mProdExpError"></small>
        </div>
        <div class="form-group">
          <label for="mProdStock">Stock</label>
          <input type="number" id="mProdStock">
          <small class="field-error" id="mProdStockError"></small>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">Mettre a jour</button>
      </form>
    </div>
  </div>

  <div class="modal" id="saleModal">
    <div class="modal-content">
      <i class="fa-solid fa-xmark close-btn" id="closeSaleModalButton"></i>
      <h3 id="saleModalTitle" style="margin-bottom: 1.5rem; color: var(--admin-primary);">Modifier Vente</h3>
      <div id="saleFormAlert" class="admin-alert" style="display:none;"></div>
      <form id="saleForm" novalidate>
        <input type="hidden" id="saleId" value="">
        <input type="hidden" id="saleProductId" value="">

        <div class="form-group">
          <label for="sProdLabel">Produit</label>
          <input type="text" id="sProdLabel" disabled>
        </div>
        <div class="form-group">
          <label for="sSaleQty">Quantite</label>
          <input type="number" id="sSaleQty">
          <small class="field-error" id="sSaleQtyError"></small>
        </div>
        <div class="form-group">
          <label for="sSalePrice">Prix</label>
          <input type="number" id="sSalePrice">
          <small class="field-error" id="sSalePriceError"></small>
        </div>
        <div class="form-group">
          <label for="sSaleStatus">Statut</label>
          <select id="sSaleStatus">
            <option value="disponible">Disponible</option>
            <option value="reservee">Reservee</option>
            <option value="vendue">Vendue</option>
          </select>
          <small class="field-error" id="sSaleStatusError"></small>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">Mettre a jour</button>
      </form>
    </div>
  </div>

  <script src="assets/script.js"></script>
</body>
</html>
