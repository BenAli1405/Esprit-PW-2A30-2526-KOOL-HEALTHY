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
$navActive   = 'client';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Espace client</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="/integweb/CSS/styles.css">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetsBase . 'style.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="page-client">

<?php require $integwebRoot . '/VIEW/includes/topbar.php'; ?>
<?php require __DIR__ . '/partials/weather_strip.php'; ?>

  <div id="globalPaymentBanner" class="global-banner" style="display:none;" role="status"></div>

  <div class="container page-single-column">
    <main class="main-stack" id="main-client">

      <aside class="card client-seller-hint" role="note">
        <p class="panel-note" style="margin:0;">
          <strong>Vous etes vendeur ?</strong> Produits, stock et prix des annonces se gerent dans
          <a href="<?php echo htmlspecialchars($linkVendeur, ENT_QUOTES, 'UTF-8'); ?>">l'espace vendeur</a>.
          Sur cette page : uniquement <strong>parcourir les offres</strong>, <strong>panier</strong> et <strong>commander</strong>.
        </p>
      </aside>

      <section class="card" id="marche" aria-label="Produits disponibles">
        <h2 class="section-title"><i class="fa-solid fa-store"></i> Produits disponibles</h2>
        <p class="panel-note">Indiquez la <strong>quantite</strong> puis <strong>Ajouter au panier</strong> ; reglez la quantite aussi dans <strong>Mon panier</strong> avant de payer. Ou <strong>Commander</strong> pour une offre (quantite dans la fenetre).</p>
        <div class="filter-toolbar">
          <div class="search-bar flex-grow">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="marketplaceSearch" class="search-input" placeholder="Rechercher par nom">
          </div>
          <div class="form-group filter-select-wrap">
            <label for="marketplaceStatusFilter" class="visually-hidden">Statut</label>
            <select id="marketplaceStatusFilter" class="form-control">
              <option value="active">Disponible + Reservee</option>
              <option value="disponible">Disponible seulement</option>
              <option value="reservee">Reservee seulement</option>
              <option value="all">Toutes (y compris vendues)</option>
            </select>
          </div>
        </div>
        <div id="marketplaceList"></div>
      </section>

      <div class="card panier-card" id="panier">
        <h2 class="section-title"><i class="fa-solid fa-basket-shopping"></i> Mon panier</h2>
        <p class="panel-note">Ajustez les quantites ici si besoin, puis payez en ligne (Stripe) ou reservez pour paiement sur place.</p>
        <div id="cartAlert" class="form-alert" style="display:none;"></div>
        <div id="cartLines" class="cart-lines"></div>
        <div class="cart-summary" id="cartSummary" style="display:none;">
          <div class="cart-total-row">
            <span>Total affiche (TND)</span>
            <strong id="cartTotalTnd">0.00</strong>
          </div>
          <div class="form-group" style="margin-top: 1rem;">
            <label for="cartBuyerEmail">Email (confirmation)</label>
            <input type="email" id="cartBuyerEmail" class="form-control" placeholder="vous@exemple.com" autocomplete="email">
          </div>
          <div class="cart-actions-row">
            <button type="button" class="btn btn-primary" id="cartPayOnlineButton">
              <i class="fa-brands fa-stripe-s"></i> Payer le panier en ligne
            </button>
            <button type="button" class="btn btn-outline" id="cartPayOnsiteButton">
              <i class="fa-solid fa-shop"></i> Reserver / payer sur place
            </button>
          </div>
          <button type="button" class="btn btn-link-like" id="cartClearButton">Vider le panier</button>
        </div>
      </div>
    </main>
  </div>

  <div class="modal" id="quickBuyModal">
    <div class="modal-content">
      <i class="fa-solid fa-xmark close-modal" id="closeQuickBuyModalButton"></i>
      <h2 class="section-title"><i class="fa-solid fa-bag-shopping"></i> Commander ce produit</h2>
      <p class="modal-note" id="quickBuyProductLabel"></p>
      <div id="quickBuyAlert" class="form-alert" style="display:none;"></div>
      <input type="hidden" id="quickBuySaleId" value="">
      <div class="form-group">
        <label for="quickBuyQty">Quantite</label>
        <input type="number" id="quickBuyQty" class="form-control" value="1" min="1" step="1">
      </div>
      <div class="form-group">
        <label for="quickBuyEmail">Email (confirmation)</label>
        <input type="email" id="quickBuyEmail" class="form-control" placeholder="vous@exemple.com" autocomplete="email">
      </div>
      <div class="quick-buy-actions">
        <button type="button" class="btn btn-primary" id="quickBuyStripeButton" style="flex:1;">
          <i class="fa-brands fa-stripe-s"></i> Payer en ligne
        </button>
        <button type="button" class="btn btn-outline" id="quickBuyOnsiteButton" style="flex:1;">
          <i class="fa-solid fa-shop"></i> Sur place
        </button>
      </div>
    </div>
  </div>

  <button type="button" class="cart-fab" id="cartFabButton" title="Voir mon panier" aria-label="Mon panier">
    <i class="fa-solid fa-cart-shopping"></i>
    <span class="cart-fab-badge" id="cartFabBadge" data-count="0">0</span>
  </button>

  <button type="button" class="chat-fab" id="chatFab" title="Chatbot Gemini — Kool Healthy" aria-label="Ouvrir le chatbot Gemini">
    <i class="fa-solid fa-robot"></i>
  </button>
  <div class="chat-panel" id="chatPanel" aria-hidden="true">
    <div class="chat-panel-header">
      <span><i class="fa-solid fa-robot"></i> Chatbot Gemini</span>
      <button type="button" class="chat-close" id="chatCloseButton" aria-label="Fermer">&times;</button>
    </div>
    <div class="chat-panel-sub">Anti-gaspillage alimentaire — reponses en francais</div>
    <div class="chat-messages" id="chatMessages"></div>
    <div class="chat-typing" id="chatTyping" style="display:none;" aria-live="polite">
      <span></span><span></span><span></span> Gemini ecrit…
    </div>
    <div class="chat-quick-prompts" id="chatQuickPrompts">
      <button type="button" class="chat-chip" data-prompt="Qu est-ce que Kool Healthy ?">C est quoi Kool ?</button>
      <button type="button" class="chat-chip" data-prompt="Comment remplir mon panier et payer ou reserver sur place ?">Panier et commande</button>
      <button type="button" class="chat-chip" data-prompt="Donne-moi des idees anti-gaspillage pour la maison.">Idees anti-gaspi</button>
    </div>
    <form class="chat-form" id="chatForm">
      <input type="text" id="chatInput" class="form-control" placeholder="Ecrivez votre message au chatbot..." maxlength="2000" autocomplete="off">
      <button type="submit" class="btn btn-primary btn-small" id="chatSendButton">Envoyer</button>
    </form>
  </div>

  <script>
    window.APP_API_BASE = <?php echo json_encode($frontOfficeBase, JSON_UNESCAPED_SLASHES); ?>;
    window.STRIPE_PUBLISHABLE_KEY = <?php echo json_encode($stripePublishable); ?>;
    window.GEMINI_READY = <?php echo $geminiReady ? 'true' : 'false'; ?>;
    window.KOOL_PAGE_ROLE = 'client';
  </script>
  <script src="<?php echo htmlspecialchars($assetsBase . 'script.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
