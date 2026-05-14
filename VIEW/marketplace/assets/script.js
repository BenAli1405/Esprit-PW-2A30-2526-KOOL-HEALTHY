const API_BASE = typeof window !== 'undefined' && window.APP_API_BASE ? window.APP_API_BASE : '';
const PAGE_ROLE =
  typeof window !== 'undefined' && window.KOOL_PAGE_ROLE ? String(window.KOOL_PAGE_ROLE) : 'legacy';

function apiUrl(file) {
  const name = String(file).replace(/^\//, '');
  const base = API_BASE;
  if (!base) {
    return name;
  }
  if (base.startsWith('/')) {
    return base.replace(/\/?$/, '/') + name;
  }
  return base + name;
}

let myProducts = [];
let mySales = [];
let statsChartInstance = null;
const geminiHistory = [];

const frontSearchState = {
  inventory: '',
  inventoryStock: '',
  sales: '',
  mySalesStatus: '',
  marketplace: '',
  marketplaceStatus: 'active'
};

const productForm = document.getElementById('productForm');
const saleForm = document.getElementById('saleForm');
const saleModal = document.getElementById('saleModal');

const productFields = {
  id: document.getElementById('prodId'),
  nom: document.getElementById('prodName'),
  date_expiration: document.getElementById('prodDate'),
  qte: document.getElementById('prodStock')
};

const saleFields = {
  qte_a_vendre: document.getElementById('saleStock'),
  prix: document.getElementById('salePrice'),
  statut: document.getElementById('saleStatus')
};

async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    headers: { 'Content-Type': 'application/json' },
    ...options
  });

  const text = await response.text();

  try {
    return JSON.parse(text);
  } catch (error) {
    throw new Error(text || 'Invalid server response');
  }
}

function sanitizeText(value) {
  return value.trim().replace(/\s+/g, ' ');
}

function normalizeSearchValue(value) {
  return sanitizeText(value).toLowerCase();
}

function clearErrors(prefix) {
  document.querySelectorAll(`#${prefix}Form .field-error`).forEach((element) => {
    element.textContent = '';
  });
}

function showFieldError(fieldId, message) {
  const errorElement = document.getElementById(`${fieldId}Error`);
  if (errorElement) {
    errorElement.textContent = message;
  }
}

function showFormAlert(alertId, message, type) {
  const alertBox = document.getElementById(alertId);
  if (!alertBox) {
    return;
  }
  alertBox.textContent = message;
  alertBox.className = `form-alert ${type}`;
  alertBox.style.display = 'block';
}

function hideFormAlert(alertId) {
  const alertBox = document.getElementById(alertId);
  if (!alertBox) {
    return;
  }
  alertBox.textContent = '';
  alertBox.className = 'form-alert';
  alertBox.style.display = 'none';
}

function validateProductPayload(payload, originalId) {
  clearErrors('product');
  hideFormAlert('productFormAlert');

  const errors = [];
  const idPattern = /^[A-Za-z0-9-]+$/;
  const namePattern = /^[A-Za-z0-9' -]+$/;
  const qtyPattern = /^[0-9]+$/;
  const datePattern = /^\d{4}-\d{2}-\d{2}$/;
  const reservedQuantity = getReservedQuantity(originalId || payload.id);

  if (!payload.id) {
    errors.push('L ID produit est obligatoire.');
    showFieldError('prodId', 'L ID produit est obligatoire.');
  } else if (!idPattern.test(payload.id)) {
    errors.push('L ID produit accepte uniquement lettres, chiffres et tirets.');
    showFieldError('prodId', 'Utilisez uniquement lettres, chiffres et tirets.');
  } else if (payload.id.length > 30) {
    errors.push('L ID produit ne doit pas depasser 30 caracteres.');
    showFieldError('prodId', 'Maximum 30 caracteres.');
  } else {
    const duplicate = myProducts.some((product) => product.id === payload.id && product.id !== originalId);
    if (duplicate) {
      errors.push('Cet ID produit existe deja.');
      showFieldError('prodId', 'Cet ID produit existe deja.');
    }
  }

  if (!payload.nom) {
    errors.push('Le nom du produit est obligatoire.');
    showFieldError('prodName', 'Le nom du produit est obligatoire.');
  } else if (!namePattern.test(payload.nom)) {
    errors.push('Le nom contient des caracteres invalides.');
    showFieldError('prodName', 'Caracteres autorises: lettres, chiffres, espace, apostrophe et tiret.');
  } else if (payload.nom.length < 2 || payload.nom.length > 120) {
    errors.push('Le nom doit contenir entre 2 et 120 caracteres.');
    showFieldError('prodName', 'Entre 2 et 120 caracteres.');
  }

  if (!payload.date_expiration) {
    errors.push('La date d expiration est obligatoire.');
    showFieldError('prodDate', 'La date d expiration est obligatoire.');
  } else if (!datePattern.test(payload.date_expiration) || Number.isNaN(Date.parse(payload.date_expiration))) {
    errors.push('Le format de date est invalide.');
    showFieldError('prodDate', 'Format attendu: YYYY-MM-DD.');
  }

  const quantityAsText = String(payload.qteRaw);
  if (!quantityAsText) {
    errors.push('La quantite est obligatoire.');
    showFieldError('prodStock', 'La quantite est obligatoire.');
  } else if (!qtyPattern.test(quantityAsText)) {
    errors.push('La quantite doit etre un entier positif.');
    showFieldError('prodStock', 'Entrez un entier positif.');
  } else if (!Number.isInteger(payload.qte) || payload.qte <= 0 || payload.qte > 99999) {
    errors.push('La quantite doit etre comprise entre 1 et 99999.');
    showFieldError('prodStock', 'Valeur autorisee: 1 a 99999.');
  } else if (payload.qte < reservedQuantity) {
    errors.push('La quantite en stock ne peut pas etre inferieure aux ventes deja actives.');
    showFieldError('prodStock', `Minimum requis: ${reservedQuantity}.`);
  }

  if (errors.length > 0) {
    showFormAlert('productFormAlert', errors[0], 'error');
  }

  return errors.length === 0;
}

function validateSalePayload(payload, maxStock) {
  clearErrors('sale');
  hideFormAlert('saleFormAlert');

  const errors = [];
  const quantityPattern = /^[0-9]+$/;
  const pricePattern = /^\d+(\.\d{1,2})?$/;
  const allowedStatuses = ['disponible', 'reservee', 'vendue'];

  if (!payload.id_produit) {
    errors.push('Aucun produit selectionne.');
  }

  const qtyAsText = String(payload.qteRaw);
  if (!qtyAsText) {
    errors.push('La quantite a vendre est obligatoire.');
    showFieldError('saleStock', 'La quantite a vendre est obligatoire.');
  } else if (!quantityPattern.test(qtyAsText)) {
    errors.push('La quantite a vendre doit etre un entier positif.');
    showFieldError('saleStock', 'Entrez un entier positif.');
  } else if (!Number.isInteger(payload.qte_a_vendre) || payload.qte_a_vendre <= 0) {
    errors.push('La quantite a vendre doit etre superieure a zero.');
    showFieldError('saleStock', 'La valeur doit etre superieure a zero.');
  } else if (payload.qte_a_vendre > maxStock) {
    errors.push('La quantite a vendre ne peut pas depasser le stock disponible.');
    showFieldError('saleStock', 'La valeur depasse le stock disponible.');
  }

  if (!payload.prixRaw) {
    errors.push('Le prix unitaire est obligatoire.');
    showFieldError('salePrice', 'Le prix unitaire est obligatoire.');
  } else if (!pricePattern.test(payload.prixRaw)) {
    errors.push('Le prix unitaire doit etre numerique avec deux decimales maximum.');
    showFieldError('salePrice', 'Format accepte: 12 ou 12.50.');
  } else if (Number.isNaN(payload.prix) || payload.prix <= 0 || payload.prix > 99999.99) {
    errors.push('Le prix unitaire doit etre compris entre 0.01 et 99999.99 TND.');
    showFieldError('salePrice', 'Valeur autorisee: 0.01 a 99999.99.');
  }

  if (!allowedStatuses.includes(payload.statut)) {
    errors.push('Le statut selectionne est invalide.');
    showFieldError('saleStatus', 'Selection invalide.');
  }

  if (errors.length > 0) {
    showFormAlert('saleFormAlert', errors[0], 'error');
  }

  return errors.length === 0;
}

function setProductFormMode(mode, product = null) {
  if (!productForm || !document.getElementById('productMode')) {
    return;
  }
  document.getElementById('productMode').value = mode;
  document.getElementById('productOriginalId').value = product ? product.id : '';
  document.getElementById('productSubmitButton').innerHTML = mode === 'create'
    ? '<i class="fa-solid fa-plus"></i> Sauvegarder Produit'
    : '<i class="fa-solid fa-pen"></i> Mettre a jour Produit';

  if (!product) {
    productForm.reset();
    hideFormAlert('productFormAlert');
    clearErrors('product');
    return;
  }

  productFields.id.value = product.id;
  productFields.nom.value = product.nom;
  productFields.date_expiration.value = product.date_expiration;
  productFields.qte.value = product.qte;
  hideFormAlert('productFormAlert');
  clearErrors('product');
}

async function fetchProducts() {
  const result = await apiRequest(apiUrl('api_get_products.php'));
  if (result.error) {
    throw new Error(result.error);
  }
  myProducts = Array.isArray(result) ? result : [];
}

async function fetchSales() {
  const result = await apiRequest(apiUrl('api_get_sales.php'));
  if (result.error) {
    throw new Error(result.error);
  }
  mySales = Array.isArray(result) ? result : [];
}

function matchesInventoryStockFilter(qte) {
  const band = frontSearchState.inventoryStock;
  if (!band) {
    return true;
  }
  const n = Number(qte);
  if (band === 'low') {
    return n >= 1 && n <= 5;
  }
  if (band === 'mid') {
    return n >= 6 && n <= 20;
  }
  if (band === 'high') {
    return n > 20;
  }
  return true;
}

function updateStatsChart() {
  const canvas = document.getElementById('statsChart');
  if (!canvas || typeof Chart === 'undefined') {
    return;
  }

  const counts = { disponible: 0, reservee: 0, vendue: 0 };
  mySales.forEach((sale) => {
    const key = sale.statut;
    if (counts[key] !== undefined) {
      counts[key] += 1;
    }
  });

  const dataValues = [counts.disponible, counts.reservee, counts.vendue];
  const hasData = dataValues.some((v) => v > 0);

  if (statsChartInstance) {
    statsChartInstance.destroy();
    statsChartInstance = null;
  }

  if (!hasData) {
    const ctx = canvas.getContext('2d');
    if (ctx) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    return;
  }

  statsChartInstance = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['Disponible', 'Reservee', 'Vendue'],
      datasets: [{
        data: dataValues,
        backgroundColor: ['#4CAF50', '#FFB74D', '#90A4AE'],
        borderWidth: 0
      }]
    },
    options: {
      plugins: {
        legend: { position: 'bottom' }
      },
      maintainAspectRatio: false
    }
  });
}

async function refreshData() {
  try {
    if (PAGE_ROLE === 'vendeur') {
      await Promise.all([fetchProducts(), fetchSales()]);
      renderInventory();
      renderMySales();
      updateStatsChart();
      return;
    }
    if (PAGE_ROLE === 'client') {
      await fetchSales();
      renderMarketplace();
      renderCart();
      return;
    }
    await Promise.all([fetchProducts(), fetchSales()]);
    renderInventory();
    renderMySales();
    renderMarketplace();
    renderCart();
    updateStatsChart();
  } catch (error) {
    const msg = 'Impossible de charger les donnees: ' + error.message;
    if (PAGE_ROLE === 'vendeur' && document.getElementById('productFormAlert')) {
      showFormAlert('productFormAlert', msg, 'error');
    } else if (PAGE_ROLE === 'client') {
      if (document.getElementById('cartAlert')) {
        showCartAlert(msg, 'error');
      } else {
        showGlobalPaymentBanner(msg, 'error');
      }
    } else if (document.getElementById('productFormAlert')) {
      showFormAlert('productFormAlert', msg, 'error');
    }
  }
}

function renderInventory() {
  const list = document.getElementById('inventoryList');
  if (!list) {
    return;
  }
  const filteredProducts = myProducts.filter((product) =>
    product.nom.toLowerCase().includes(frontSearchState.inventory) && matchesInventoryStockFilter(product.qte)
  );

  if (myProducts.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-ghost"></i><p>Votre inventaire est vide.</p></div>`;
    return;
  }

  if (filteredProducts.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-magnifying-glass"></i><p>Aucun produit ne correspond a votre recherche.</p></div>`;
    return;
  }

  list.innerHTML = filteredProducts.map((product) => {
    const reserved = getReservedQuantity(product.id);
    const libre = Math.max(0, Number(product.qte) - reserved);
    const reserveHint = reserved > 0
      ? `<span class="stock-libre-hint"><i class="fa-solid fa-store"></i> Deja en annonce : <strong>${reserved}</strong> — Libre nouvelle annonce : <strong>${libre}</strong></span>`
      : '';

    return `
    <div class="list-item">
      <div class="item-info">
        <h4>${product.nom}</h4>
        <div class="item-meta">
          <span><i class="fa-solid fa-barcode"></i> ${product.id}</span>
          <span><i class="fa-regular fa-calendar"></i> Exp: ${product.date_expiration}</span>
          <span><i class="fa-solid fa-cubes"></i> Stock physique : <strong>${product.qte}</strong></span>
          ${reserveHint}
        </div>
      </div>
      <div class="item-actions">
        <button type="button" class="btn btn-outline btn-small js-edit-product" data-product-id="${encodeURIComponent(String(product.id))}" title="Modifier le produit">
          <i class="fa-solid fa-pen"></i>
        </button>
        <button type="button" class="btn btn-outline btn-small js-delete-product" data-product-id="${encodeURIComponent(String(product.id))}" title="Supprimer le produit">
          <i class="fa-solid fa-trash"></i>
        </button>
        <button type="button" class="btn btn-outline btn-small js-open-sale-modal" data-product-id="${encodeURIComponent(String(product.id))}" title="Mettre en vente">
          <i class="fa-solid fa-tag"></i> Vendre
        </button>
      </div>
    </div>
  `;
  }).join('');
}

function renderMySales() {
  const list = document.getElementById('mySalesList');
  if (!list) {
    return;
  }
  const statusFilter = frontSearchState.mySalesStatus;
  const filteredSales = mySales.filter((sale) =>
    sale.nom_produit.toLowerCase().includes(frontSearchState.sales)
    && (!statusFilter || sale.statut === statusFilter)
  );

  if (mySales.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-store-slash"></i><p>Aucune vente en cours.</p></div>`;
    return;
  }

  if (filteredSales.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-magnifying-glass"></i><p>Aucune vente ne correspond a votre recherche.</p></div>`;
    return;
  }

  list.innerHTML = filteredSales.map((sale) => `
    <div class="list-item">
      <div class="item-info">
        <h4>${sale.nom_produit} <span class="badge badge-dispo">${formatSaleStatus(sale.statut)}</span></h4>
        <div class="item-meta">
          <span>Vente: ${sale.id_vente}</span>
          <span>Produit: ${sale.id_produit}</span>
          <span>Qt a vendre: ${sale.qte_a_vendre}</span>
          <span style="color: var(--success); font-weight: 600;">${Number(sale.prix).toFixed(2)} TND</span>
        </div>
      </div>
      <div class="item-actions">
        <span class="badge badge-dispo">Gestion en BackOffice</span>
      </div>
    </div>
  `).join('');
}

function marketplaceStatusPredicate(sale) {
  const mode = frontSearchState.marketplaceStatus || 'active';
  if (mode === 'all') {
    return true;
  }
  if (mode === 'active') {
    return sale.statut === 'disponible' || sale.statut === 'reservee';
  }
  return sale.statut === mode;
}

function renderMarketplace() {
  const list = document.getElementById('marketplaceList');
  if (!list) {
    return;
  }
  const baseList = mySales.filter((sale) => marketplaceStatusPredicate(sale));
  const availableSales = baseList.filter((sale) =>
    sale.nom_produit.toLowerCase().includes(frontSearchState.marketplace)
  );

  if (baseList.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-basket-shopping"></i><p>Aucun produit disponible pour ce filtre.</p></div>`;
    return;
  }

  if (availableSales.length === 0) {
    list.innerHTML = `<div class="empty-state"><i class="fa-solid fa-magnifying-glass"></i><p>Aucun produit ne correspond a votre recherche.</p></div>`;
    return;
  }

  list.innerHTML = availableSales.map((sale) => {
    const canShop = sale.statut === 'disponible' && Number(sale.prix) >= 0.5;
    const maxShop = Number(sale.qte_a_vendre);
    const actions = canShop
      ? `<div class="market-actions-row">
          <div class="market-add-cart-block">
            <label for="marketQty-${sale.id_vente}" class="market-qty-label">Quantite</label>
            <input type="number" class="form-control market-add-qty" id="marketQty-${sale.id_vente}" min="1" max="${maxShop}" value="1" step="1" inputmode="numeric" aria-label="Quantite a ajouter au panier">
            <button type="button" class="btn btn-outline btn-small market-add-cart-btn" data-sale-id="${sale.id_vente}" title="Ajouter au panier avec la quantite choisie, puis commander depuis Mon panier">
              <i class="fa-solid fa-cart-plus"></i> Ajouter au panier
            </button>
          </div>
          <button type="button" class="btn btn-primary btn-small market-buy-btn" data-sale-id="${sale.id_vente}" title="Commander cette offre (paiement en ligne ou sur place)">
            <i class="fa-solid fa-bag-shopping"></i> Commander
          </button>
        </div>`
      : '';

    return `
    <div class="market-card">
      <div class="market-header">
        <div class="seller-info"><i class="fa-solid fa-leaf"></i> Offre anti-gaspi</div>
        <div class="price-tag">${Number(sale.prix).toFixed(2)} TND / u.</div>
      </div>
      <h4 style="margin-bottom: 5px;">${sale.nom_produit}</h4>
      <div style="font-size: 0.85rem; color: var(--text-light); display: flex; justify-content: space-between; gap: 10px;">
        <span>Qt dispo: <strong>${sale.qte_a_vendre}</strong> — Total: <strong>${(Number(sale.prix) * Number(sale.qte_a_vendre)).toFixed(2)}</strong> TND</span>
        <span>Exp: ${sale.date_expiration}</span>
      </div>
      <div class="market-footer market-footer-row">
        <span>${formatSaleStatus(sale.statut)}</span>
      </div>
      ${actions}
    </div>
  `;
  }).join('');
}

function formatSaleStatus(status) {
  if (status === 'reservee') {
    return 'Reservee';
  }
  if (status === 'vendue') {
    return 'Vendue';
  }
  return 'Disponible';
}

const PANIER_STORAGE_KEY = 'koolPanierIds';

/**
 * @returns {{ id_vente: number, qty: number }[]}
 */
function loadPanier() {
  try {
    const raw = localStorage.getItem(PANIER_STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    if (!Array.isArray(parsed)) {
      return [];
    }
    if (parsed.length > 0 && typeof parsed[0] === 'number') {
      const migrated = parsed
        .map((v) => Number.parseInt(String(v), 10))
        .filter((n) => n > 0)
        .map((id) => ({ id_vente: id, qty: 1 }));
      savePanier(migrated);
      return migrated;
    }
    return parsed
      .map((row) => ({
        id_vente: Number.parseInt(String(row.id_vente ?? row), 10),
        qty: Math.max(1, Number.parseInt(String(row.qty ?? 1), 10) || 1)
      }))
      .filter((r) => r.id_vente > 0);
  } catch (error) {
    return [];
  }
}

function savePanier(rows) {
  localStorage.setItem(PANIER_STORAGE_KEY, JSON.stringify(rows));
}

function updateCartFabBadge() {
  const badge = document.getElementById('cartFabBadge');
  if (!badge) {
    return;
  }
  const n = syncPanierWithSales().reduce((sum, row) => sum + row.qty, 0);
  badge.textContent = String(n);
  badge.dataset.count = String(n);
  badge.classList.toggle('cart-fab-badge--empty', n === 0);
}

function highlightPanierCard() {
  const card = document.getElementById('panier');
  if (!card) {
    return;
  }
  card.classList.add('panier-card--highlight');
  window.setTimeout(() => card.classList.remove('panier-card--highlight'), 2400);
}

/**
 * @returns {{ id_vente: number, qty: number }[]}
 */
function syncPanierWithSales() {
  const rows = loadPanier();
  const adjusted = [];
  let changed = false;
  rows.forEach((row) => {
    const sale = mySales.find((s) => Number(s.id_vente) === row.id_vente);
    if (!sale || sale.statut !== 'disponible') {
      changed = true;
      return;
    }
    const maxQ = Number(sale.qte_a_vendre);
    let qty = Math.min(row.qty, maxQ);
    if (qty < 1) {
      changed = true;
      return;
    }
    if (qty !== row.qty) {
      changed = true;
    }
    adjusted.push({ id_vente: row.id_vente, qty });
  });
  if (changed || adjusted.length !== rows.length) {
    savePanier(adjusted);
  }
  return adjusted;
}

function addToPanier(idVente, addQty = 1) {
  const n = Number.parseInt(String(idVente), 10);
  const add = Math.max(1, Number.parseInt(String(addQty), 10) || 1);
  if (!n) {
    return;
  }
  const sale = mySales.find((s) => Number(s.id_vente) === n);
  if (!sale || sale.statut !== 'disponible') {
    showCartAlert('Cette annonce n est plus disponible.', 'error');
    return;
  }
  const maxQ = Number(sale.qte_a_vendre);
  const rows = loadPanier();
  const idx = rows.findIndex((r) => r.id_vente === n);
  if (idx === -1) {
    rows.push({ id_vente: n, qty: Math.min(add, maxQ) });
  } else {
    rows[idx].qty = Math.min(maxQ, rows[idx].qty + add);
  }
  savePanier(rows);
  renderCart();
  updateCartFabBadge();
  showCartAlert('Panier mis a jour. Descendez vers « Mon panier » pour commander.', 'success');
  document.getElementById('panier')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  highlightPanierCard();
}

function setPanierLineQty(idVente, qty) {
  const n = Number.parseInt(String(idVente), 10);
  const sale = mySales.find((s) => Number(s.id_vente) === n);
  if (!sale || sale.statut !== 'disponible') {
    removeFromPanier(n);
    return;
  }
  const maxQ = Number(sale.qte_a_vendre);
  const q = Math.min(maxQ, Math.max(1, Number.parseInt(String(qty), 10) || 1));
  const rows = loadPanier().map((r) => (r.id_vente === n ? { id_vente: n, qty: q } : r));
  savePanier(rows);
  renderCart();
  updateCartFabBadge();
}

function removeFromPanier(idVente) {
  const n = Number.parseInt(String(idVente), 10);
  const rows = loadPanier().filter((r) => r.id_vente !== n);
  savePanier(rows);
  renderCart();
  updateCartFabBadge();
}

function clearPanier() {
  savePanier([]);
  renderCart();
  updateCartFabBadge();
}

function showCartAlert(message, type) {
  const el = document.getElementById('cartAlert');
  if (!el) {
    return;
  }
  el.textContent = message;
  el.className = `form-alert ${type}`;
  el.style.display = 'block';
  if (type === 'success' || type === 'info') {
    window.setTimeout(() => {
      el.style.display = 'none';
    }, 2500);
  }
}

function hideCartAlert() {
  const el = document.getElementById('cartAlert');
  if (el) {
    el.style.display = 'none';
    el.textContent = '';
    el.className = 'form-alert';
  }
}

function renderCart() {
  const linesEl = document.getElementById('cartLines');
  const summaryEl = document.getElementById('cartSummary');
  const totalEl = document.getElementById('cartTotalTnd');
  if (!linesEl || !summaryEl) {
    return;
  }

  hideCartAlert();
  const rows = syncPanierWithSales();

  if (rows.length === 0) {
    linesEl.innerHTML = `<div class="empty-state" style="padding:1rem;"><i class="fa-solid fa-basket-shopping"></i><p>Votre panier est vide.</p><p class="panel-note" style="margin-top:0.5rem;">Depuis le marche : choisissez la <strong>quantite</strong>, puis <strong>Ajouter au panier</strong>.</p></div>`;
    summaryEl.style.display = 'none';
    if (totalEl) {
      totalEl.textContent = '0.00';
    }
    updateCartFabBadge();
    return;
  }

  let total = 0;
  linesEl.innerHTML = rows.map((row) => {
    const sale = mySales.find((s) => Number(s.id_vente) === row.id_vente);
    if (!sale) {
      return '';
    }
    const lineTotal = Number(sale.prix) * row.qty;
    total += lineTotal;
    const maxQ = Number(sale.qte_a_vendre);
    return `
      <div class="cart-line cart-line--qty">
        <div class="cart-line-main">
          <strong>${sale.nom_produit}</strong>
          <div class="cart-line-meta">#${sale.id_vente} — ${Number(sale.prix).toFixed(2)} TND / u. x ${row.qty} = <strong>${lineTotal.toFixed(2)}</strong> TND</div>
        </div>
        <div class="cart-qty-controls">
          <button type="button" class="btn btn-outline btn-small cart-qty-minus" data-sale-id="${sale.id_vente}" aria-label="Diminuer">−</button>
          <span class="cart-qty-value">${row.qty}</span>
          <button type="button" class="btn btn-outline btn-small cart-qty-plus" data-sale-id="${sale.id_vente}" data-max="${maxQ}" aria-label="Augmenter">+</button>
          <button type="button" class="btn btn-outline btn-small cart-remove-btn" data-sale-id="${sale.id_vente}" title="Retirer">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>
    `;
  }).join('');

  summaryEl.style.display = 'block';
  if (totalEl) {
    totalEl.textContent = total.toFixed(2);
  }
  updateCartFabBadge();
}

const quickBuyModal = document.getElementById('quickBuyModal');

function openQuickBuyModal(idVente) {
  if (!quickBuyModal) {
    return;
  }
  const sale = mySales.find((s) => Number(s.id_vente) === Number(idVente));
  if (!sale || sale.statut !== 'disponible') {
    return;
  }
  document.getElementById('quickBuySaleId').value = String(sale.id_vente);
  const qEl = document.getElementById('quickBuyQty');
  if (qEl) {
    qEl.value = '1';
    qEl.max = String(sale.qte_a_vendre);
    qEl.min = '1';
  }
  const label = document.getElementById('quickBuyProductLabel');
  if (label) {
    label.textContent = `${sale.nom_produit} — ${Number(sale.prix).toFixed(2)} TND / u., max ${sale.qte_a_vendre} u. (annonce #${sale.id_vente})`;
  }
  const alertBox = document.getElementById('quickBuyAlert');
  if (alertBox) {
    alertBox.style.display = 'none';
    alertBox.textContent = '';
    alertBox.className = 'form-alert';
  }
  quickBuyModal.style.display = 'flex';
}

function closeQuickBuyModal() {
  if (quickBuyModal) {
    quickBuyModal.style.display = 'none';
  }
}

function showQuickBuyAlert(message, type) {
  const alertBox = document.getElementById('quickBuyAlert');
  if (!alertBox) {
    return;
  }
  alertBox.textContent = message;
  alertBox.className = `form-alert ${type}`;
  alertBox.style.display = 'block';
}

async function startStripeCheckout(cartLines, email) {
  if (!cartLines || cartLines.length === 0) {
    throw new Error('Panier vide');
  }
  const body = { cart: cartLines, buyer_email: email };

  const result = await apiRequest(apiUrl('api_stripe_checkout.php'), {
    method: 'POST',
    body: JSON.stringify(body)
  });

  if (!result.success || !result.url) {
    throw new Error(result.error || 'Impossible de creer la session Stripe');
  }

  window.location.href = result.url;
}

async function reserveSurPlace(ids, email) {
  const result = await apiRequest(apiUrl('api_reserve_sur_place.php'), {
    method: 'POST',
    body: JSON.stringify({
      id_ventes: ids,
      buyer_email: email || undefined
    })
  });

  if (!result.success) {
    throw new Error(result.error || 'Reservation impossible');
  }

  return result;
}

function normalizeProductId(value) {
  return String(value ?? '').trim();
}

function getProductById(productId) {
  const key = normalizeProductId(productId);
  return myProducts.find((product) => normalizeProductId(product.id) === key);
}

function getReservedQuantity(productId, excludedSaleId = null) {
  const pid = normalizeProductId(productId);
  return mySales
    .filter((sale) => normalizeProductId(sale.id_produit) === pid)
    .filter((sale) => String(sale.statut || '').toLowerCase() !== 'vendue')
    .filter((sale) => excludedSaleId === null || Number(sale.id_vente) !== Number(excludedSaleId))
    .reduce((total, sale) => total + Number(sale.qte_a_vendre), 0);
}

function getActiveSalesLinesForProduct(productId) {
  const pid = normalizeProductId(productId);
  return mySales.filter(
    (sale) => normalizeProductId(sale.id_produit) === pid && String(sale.statut || '').toLowerCase() !== 'vendue'
  );
}

window.openSaleModal = function openSaleModal(productId) {
  if (!saleModal) {
    return;
  }
  const product = getProductById(productId);
  if (!product) {
    return;
  }

  const reserved = getReservedQuantity(product.id);
  const availableStock = Math.max(0, Number(product.qte) - reserved);
  const lines = getActiveSalesLinesForProduct(product.id);
  const annoncesDetail = lines.length > 0
    ? lines.map((s) => `#${s.id_vente} (${formatSaleStatus(s.statut)}) : ${s.qte_a_vendre} u.`).join(' ; ')
    : '';

  document.getElementById('saleMode').value = 'create';
  document.getElementById('saleId').value = '';
  document.getElementById('saleModalTitle').textContent = 'Mettre en vente';
  document.getElementById('saleSubmitButton').innerHTML = '<i class="fa-solid fa-bullhorn"></i> Publier l annonce';
  document.getElementById('saleProdId').value = product.id;
  document.getElementById('saleProdNameDisplay').textContent = product.nom;
  document.getElementById('saleProdIdDisplay').textContent = product.id;
  document.getElementById('saleStock').value = availableStock > 0 ? availableStock : '';
  document.getElementById('salePrice').value = '';
  document.getElementById('saleStatus').value = 'disponible';

  const hintEl = document.getElementById('maxStockHint');
  if (hintEl) {
    hintEl.textContent = reserved > 0
      ? `Stock physique : ${product.qte} — Deja en annonce active : ${reserved} — Quantite max pour cette nouvelle annonce : ${availableStock}. Prix = TND par unite.`
      : `Stock disponible pour une nouvelle annonce : ${availableStock}. Prix = TND par unite.`;
  }

  clearErrors('sale');
  hideFormAlert('saleFormAlert');
  saleModal.style.display = 'flex';

  if (availableStock <= 0) {
    const msg = reserved > 0
      ? `Les ${reserved} unite(s) sont deja liees a une ou plusieurs annonces actives (${annoncesDetail || 'voir Mes ventes'}). Modifiez ou supprimez une annonce dans le BackOffice, ou attendez qu une vente soit finalisee, pour liberer du stock.`
      : 'Stock insuffisant pour une nouvelle annonce.';
    showFormAlert('saleFormAlert', msg, 'error');
  }
};

function closeSaleModal() {
  if (saleForm) {
    saleForm.reset();
  }
  clearErrors('sale');
  hideFormAlert('saleFormAlert');
  if (saleModal) {
    saleModal.style.display = 'none';
  }
}

function showGlobalPaymentBanner(message, type) {
  const banner = document.getElementById('globalPaymentBanner');
  if (!banner) {
    return;
  }
  banner.textContent = message;
  banner.className = `global-banner global-banner--${type}`;
  banner.style.display = 'block';
}

function readPaymentQueryBanner() {
  const params = new URLSearchParams(window.location.search);
  const payment = params.get('payment');
  const sessionId = params.get('session_id');

  if (payment === 'success') {
    clearPanier();
    showGlobalPaymentBanner(
      'Paiement Stripe termine. Synchronisation avec la boutique et envoi du recu e-mail…',
      'success'
    );

    if (sessionId) {
      fetch(`${apiUrl('api_stripe_sync_receipt.php')}?session_id=${encodeURIComponent(sessionId)}`)
        .then((response) => response.json())
        .then((data) => {
          if (!data.success) {
            showGlobalPaymentBanner(
              'Paiement enregistre chez Stripe, mais la boutique signale : ' + (data.error || 'erreur inconnue'),
              'error'
            );
            return;
          }
          if (data.email === 'already_sent') {
            showGlobalPaymentBanner(
              'Paiement confirme. Le recu e-mail avait deja ete envoye (pas de doublon).',
              'info'
            );
            return;
          }
          if (data.email_sent) {
            showGlobalPaymentBanner(
              'Paiement confirme. Un e-mail de recapitulatif vous a ete envoye (verifiez les courriers indesirables).',
              'success'
            );
          } else {
            showGlobalPaymentBanner(
              'Paiement confirme, mais l e-mail n a pas pu partir. Configurez SMTP dans config/config.local.php (smtp_host, smtp_user, smtp_pass) — voir aussi config/storage/logs/mail.log',
              'info'
            );
          }
        })
        .catch(() => {
          showGlobalPaymentBanner(
            'Paiement Stripe termine, mais l appel de synchronisation a echoue (reseau). Rechargez la page ou verifiez les logs serveur.',
            'info'
          );
        });
    } else {
      showGlobalPaymentBanner(
        'Paiement Stripe termine. Pour recevoir le recu ici, l URL de succes doit contenir session_id (deja le cas avec {CHECKOUT_SESSION_ID} dans Stripe).',
        'info'
      );
    }
  } else if (payment === 'cancel') {
    showGlobalPaymentBanner('Paiement annule. Aucun debit n a ete effectue.', 'info');
  }
}

productForm?.addEventListener('submit', async (event) => {
  event.preventDefault();

  const mode = document.getElementById('productMode').value;
  const originalId = document.getElementById('productOriginalId').value;
  const payload = {
    id: sanitizeText(productFields.id.value),
    nom: sanitizeText(productFields.nom.value),
    date_expiration: productFields.date_expiration.value.trim(),
    qteRaw: productFields.qte.value.trim(),
    qte: Number.parseInt(productFields.qte.value, 10)
  };

  if (!validateProductPayload(payload, originalId)) {
    return;
  }

  const body = {
    id: payload.id,
    nom: payload.nom,
    date_expiration: payload.date_expiration,
    qte: payload.qte,
    original_id: originalId
  };

  try {
    const result = await apiRequest(
      apiUrl(mode === 'create' ? 'api_add_product.php' : 'api_update_product.php'),
      {
        method: 'POST',
        body: JSON.stringify(body)
      }
    );

    if (!result.success) {
      throw new Error(result.error || 'Operation impossible');
    }

    await refreshData();
    setProductFormMode('create');
    showFormAlert(
      'productFormAlert',
      mode === 'create' ? 'Produit ajoute avec succes.' : 'Produit mis a jour avec succes.',
      'success'
    );
  } catch (error) {
    showFormAlert('productFormAlert', 'Erreur lors de la sauvegarde: ' + error.message, 'error');
  }
});

saleForm?.addEventListener('submit', async (event) => {
  event.preventDefault();

  const productId = document.getElementById('saleProdId').value;
  const product = getProductById(productId);
  const availableStock = product ? product.qte - getReservedQuantity(productId) : 0;
  const payload = {
    id_produit: productId,
    qteRaw: saleFields.qte_a_vendre.value.trim(),
    qte_a_vendre: Number.parseInt(saleFields.qte_a_vendre.value, 10),
    prixRaw: saleFields.prix.value.trim(),
    prix: Number.parseFloat(saleFields.prix.value),
    statut: saleFields.statut.value
  };

  if (!product) {
    showFormAlert('saleFormAlert', 'Le produit selectionne est introuvable.', 'error');
    return;
  }

  if (!validateSalePayload(payload, availableStock)) {
    return;
  }

  try {
    const result = await apiRequest(apiUrl('api_add_sale.php'), {
      method: 'POST',
      body: JSON.stringify({
        id_produit: payload.id_produit,
        qte_a_vendre: payload.qte_a_vendre,
        prix: payload.prix,
        statut: payload.statut
      })
    });

    if (!result.success) {
      throw new Error(result.error || 'Operation impossible');
    }

    await refreshData();
    closeSaleModal();
  } catch (error) {
    showFormAlert('saleFormAlert', 'Erreur lors de la sauvegarde: ' + error.message, 'error');
  }
});

document.getElementById('resetProductButton')?.addEventListener('click', () => {
  setProductFormMode('create');
});

document.getElementById('closeSaleModalButton')?.addEventListener('click', closeSaleModal);
document.getElementById('inventorySearch')?.addEventListener('input', (event) => {
  frontSearchState.inventory = normalizeSearchValue(event.target.value);
  renderInventory();
});

document.getElementById('inventoryList')?.addEventListener('click', async (event) => {
  const delBtn = event.target.closest('.js-delete-product');
  const rawDel = delBtn?.getAttribute('data-product-id');
  if (rawDel) {
    let pid;
    try {
      pid = decodeURIComponent(rawDel);
    } catch (e2) {
      pid = rawDel;
    }
    const product = getProductById(pid);
    if (!product || !window.confirm(`Supprimer le produit « ${product.nom} » (${pid}) et ses ventes liees ?`)) {
      return;
    }
    try {
      const result = await apiRequest(apiUrl('api_delete_product.php'), {
        method: 'POST',
        body: JSON.stringify({ id: pid })
      });
      if (!result.success) {
        throw new Error(result.error || 'Suppression impossible');
      }
      await refreshData();
      setProductFormMode('create');
      showFormAlert('productFormAlert', 'Produit supprime.', 'success');
    } catch (err) {
      showFormAlert('productFormAlert', err.message || 'Erreur suppression', 'error');
    }
    return;
  }

  const editBtn = event.target.closest('.js-edit-product');
  const rawEdit = editBtn?.getAttribute('data-product-id');
  if (rawEdit) {
    let pid;
    try {
      pid = decodeURIComponent(rawEdit);
    } catch (e2) {
      pid = rawEdit;
    }
    const product = getProductById(pid);
    if (product) {
      setProductFormMode('edit', product);
      document.getElementById('productForm')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    return;
  }

  const button = event.target.closest('.js-open-sale-modal');
  const raw = button?.getAttribute('data-product-id');
  if (!raw) {
    return;
  }
  try {
    openSaleModal(decodeURIComponent(raw));
  } catch (err) {
    openSaleModal(raw);
  }
});
document.getElementById('salesSearch')?.addEventListener('input', (event) => {
  frontSearchState.sales = normalizeSearchValue(event.target.value);
  renderMySales();
});
document.getElementById('marketplaceSearch')?.addEventListener('input', (event) => {
  frontSearchState.marketplace = normalizeSearchValue(event.target.value);
  renderMarketplace();
});

document.getElementById('inventoryStockFilter')?.addEventListener('change', (event) => {
  frontSearchState.inventoryStock = event.target.value;
  renderInventory();
});

document.getElementById('mySalesStatusFilter')?.addEventListener('change', (event) => {
  frontSearchState.mySalesStatus = event.target.value;
  renderMySales();
});

document.getElementById('marketplaceStatusFilter')?.addEventListener('change', (event) => {
  frontSearchState.marketplaceStatus = event.target.value;
  renderMarketplace();
});

document.getElementById('marketplaceList')?.addEventListener('click', (event) => {
  const addBtn = event.target.closest('.market-add-cart-btn');
  if (addBtn?.dataset.saleId) {
    const sid = addBtn.dataset.saleId;
    const qtyInput = document.getElementById(`marketQty-${sid}`);
    let qty = qtyInput ? Number.parseInt(String(qtyInput.value), 10) : 1;
    if (!Number.isFinite(qty) || qty < 1) {
      qty = 1;
    }
    addToPanier(sid, qty);
    return;
  }
  const buyBtn = event.target.closest('.market-buy-btn');
  if (buyBtn?.dataset.saleId) {
    openQuickBuyModal(buyBtn.dataset.saleId);
  }
});

document.getElementById('cartLines')?.addEventListener('click', (event) => {
  const removeBtn = event.target.closest('.cart-remove-btn');
  if (removeBtn?.dataset.saleId) {
    removeFromPanier(removeBtn.dataset.saleId);
    return;
  }
  const minus = event.target.closest('.cart-qty-minus');
  if (minus?.dataset.saleId) {
    const id = Number.parseInt(minus.dataset.saleId, 10);
    const row = loadPanier().find((r) => r.id_vente === id);
    if (row) {
      if (row.qty <= 1) {
        removeFromPanier(id);
      } else {
        setPanierLineQty(id, row.qty - 1);
      }
    }
    return;
  }
  const plus = event.target.closest('.cart-qty-plus');
  if (plus?.dataset.saleId) {
    const id = Number.parseInt(plus.dataset.saleId, 10);
    const maxQ = Number.parseInt(plus.getAttribute('data-max') || '9999', 10);
    const row = loadPanier().find((r) => r.id_vente === id);
    if (row) {
      setPanierLineQty(id, Math.min(maxQ, row.qty + 1));
    }
  }
});

document.getElementById('cartClearButton')?.addEventListener('click', () => {
  clearPanier();
  showCartAlert('Panier vide.', 'info');
});

document.getElementById('cartPayOnlineButton')?.addEventListener('click', async () => {
  const lines = syncPanierWithSales();
  const email = document.getElementById('cartBuyerEmail')?.value.trim() || '';
  if (lines.length === 0) {
    showCartAlert('Panier vide.', 'error');
    return;
  }
  if (!email) {
    showCartAlert('Indiquez votre email pour la confirmation.', 'error');
    return;
  }
  try {
    await startStripeCheckout(lines, email);
  } catch (error) {
    showCartAlert(error.message, 'error');
  }
});

document.getElementById('cartPayOnsiteButton')?.addEventListener('click', async () => {
  const lines = syncPanierWithSales();
  const email = document.getElementById('cartBuyerEmail')?.value.trim() || '';
  if (lines.length === 0) {
    showCartAlert('Panier vide.', 'error');
    return;
  }
  if (lines.some((r) => r.qty > 1)) {
    showCartAlert(
      'Reservation sur place : une seule unite par annonce. Diminuez les quantites a 1 ou payez en ligne pour commander plusieurs unites.',
      'error'
    );
    return;
  }
  const ids = lines.map((r) => r.id_vente);
  try {
    await reserveSurPlace(ids, email);
    clearPanier();
    await refreshData();
    showGlobalPaymentBanner('Reservation sur place enregistree. Un email de confirmation vous a ete envoye si vous avez indique une adresse.', 'success');
    document.getElementById('panier')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (error) {
    showCartAlert(error.message, 'error');
  }
});

document.getElementById('closeQuickBuyModalButton')?.addEventListener('click', closeQuickBuyModal);

document.getElementById('quickBuyStripeButton')?.addEventListener('click', async () => {
  const idVente = Number.parseInt(document.getElementById('quickBuySaleId')?.value || '0', 10);
  const email = document.getElementById('quickBuyEmail')?.value.trim() || '';
  const qtyRaw = Number.parseInt(document.getElementById('quickBuyQty')?.value || '1', 10);
  const qty = Number.isFinite(qtyRaw) && qtyRaw > 0 ? qtyRaw : 1;
  if (!idVente || !email) {
    showQuickBuyAlert('Renseignez un email valide.', 'error');
    return;
  }
  const sale = mySales.find((s) => Number(s.id_vente) === idVente);
  if (!sale || qty > Number(sale.qte_a_vendre)) {
    showQuickBuyAlert('Quantite trop elevee pour cette annonce.', 'error');
    return;
  }
  try {
    await startStripeCheckout([{ id_vente: idVente, qty }], email);
  } catch (error) {
    showQuickBuyAlert(error.message, 'error');
  }
});

document.getElementById('quickBuyOnsiteButton')?.addEventListener('click', async () => {
  const idVente = Number.parseInt(document.getElementById('quickBuySaleId')?.value || '0', 10);
  const email = document.getElementById('quickBuyEmail')?.value.trim() || '';
  const qtyRaw = Number.parseInt(document.getElementById('quickBuyQty')?.value || '1', 10);
  const qty = Number.isFinite(qtyRaw) && qtyRaw > 0 ? qtyRaw : 1;
  if (!idVente) {
    showQuickBuyAlert('Annonce invalide.', 'error');
    return;
  }
  if (qty > 1) {
    showQuickBuyAlert('Reservation sur place : une seule unite. Utilisez le paiement en ligne pour plusieurs.', 'error');
    return;
  }
  try {
    await reserveSurPlace([idVente], email);
    removeFromPanier(idVente);
    closeQuickBuyModal();
    await refreshData();
    showGlobalPaymentBanner('Reservation sur place enregistree pour cette annonce.', 'success');
  } catch (error) {
    showQuickBuyAlert(error.message, 'error');
  }
});

document.querySelectorAll('.nav-scroll-btn').forEach((btn) => {
  btn.addEventListener('click', () => {
    const target = btn.getAttribute('data-scroll-target');
    if (!target) {
      return;
    }
    document.getElementById(target)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});

const chatPanel = document.getElementById('chatPanel');
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const chatTyping = document.getElementById('chatTyping');

let chatbotWelcomeShown = false;
let geminiMissingNotified = false;

function appendChatBubble(text, who) {
  if (!chatMessages) {
    return;
  }
  const row = document.createElement('div');
  row.className = `chat-bubble chat-bubble--${who}`;
  row.textContent = text;
  chatMessages.appendChild(row);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

function setChatTyping(visible) {
  if (chatTyping) {
    chatTyping.style.display = visible ? 'flex' : 'none';
  }
}

function isGeminiConfigured() {
  return typeof window !== 'undefined' && window.GEMINI_READY === true;
}

function formatGeminiChatError(message) {
  const text = String(message || '');
  if (/quota|exceeded your current quota|RESOURCE_EXHAUSTED|rate.limit|429/i.test(text)) {
    return 'Quota Gemini atteint (palier gratuit ou limite du modele). Reessayez dans une minute, ou dans config.local.php definissez gemini_model (ex. gemini-2.5-flash-lite, gemini-1.5-flash), ou activez la facturation : https://ai.google.dev/gemini-api/docs/rate-limits';
  }
  return text;
}

function initWeatherStrip() {
  const el = document.getElementById('weatherStripText');
  if (!el) {
    return;
  }
  fetch(apiUrl('api_weather.php'))
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.summary) {
        const loc = data.location_label ? `${data.location_label} — ` : '';
        el.textContent = loc + data.summary;
      } else {
        el.textContent = data.error || 'Meteo indisponible';
      }
    })
    .catch(() => {
      el.textContent = 'Meteo indisponible';
    });
}

async function sendGeminiMessage(rawText, options = {}) {
  const text = String(rawText || '').trim();
  if (!text || !chatMessages) {
    return;
  }

  if (!isGeminiConfigured()) {
    if (!geminiMissingNotified) {
      geminiMissingNotified = true;
      appendChatBubble(
        'Le chatbot Gemini n est pas encore configure. Ajoutez gemini_api_key dans config/config.local.php (cle API : https://aistudio.google.com/apikey ), puis rechargez la page.',
        'bot'
      );
    }
    return;
  }

  let moderation;
  try {
    moderation = await apiRequest(apiUrl('api_badwords.php'), {
      method: 'POST',
      body: JSON.stringify({ text })
    });
  } catch (error) {
    appendChatBubble('Moderation indisponible : ' + error.message, 'bot');
    return;
  }

  if (!moderation.success || moderation.allowed !== true) {
    appendChatBubble(
      moderation.error || 'Message refuse par la moderation (API badwords / PurgoMalum).',
      'bot'
    );
    return;
  }

  if (!options.skipUserBubble) {
    appendChatBubble(text, 'user');
  }

  setChatTyping(true);
  try {
    const payload = {
      message: text,
      history: geminiHistory.map((turn) => ({ role: turn.role, text: turn.text }))
    };

    const result = await apiRequest(apiUrl('api_gemini_chat.php'), {
      method: 'POST',
      body: JSON.stringify(payload)
    });

    if (!result.success) {
      throw new Error(result.error || 'Erreur Gemini');
    }

    geminiHistory.push({ role: 'user', text });
    geminiHistory.push({ role: 'model', text: result.reply });
    if (geminiHistory.length > 20) {
      geminiHistory.splice(0, geminiHistory.length - 20);
    }

    appendChatBubble(result.reply, 'bot');
  } catch (error) {
    appendChatBubble('Erreur chatbot : ' + formatGeminiChatError(error.message), 'bot');
  } finally {
    setChatTyping(false);
  }
}

function showChatbotWelcomeOnce() {
  if (chatbotWelcomeShown || !chatMessages) {
    return;
  }
  chatbotWelcomeShown = true;
  const msg =
    PAGE_ROLE === 'client'
      ? 'Bonjour. Je peux vous aider sur le panier, le paiement en ligne (Stripe) ou la reservation sur place. Posez votre question ou utilisez une suggestion ci-dessous.'
      : 'Bonjour, je suis le chatbot Gemini de Kool Healthy. Je peux vous expliquer l anti-gaspillage, le panier, le paiement Stripe ou le retrait sur place. Utilisez les suggestions ci-dessous ou posez votre question.';
  appendChatBubble(msg, 'bot');
}

document.getElementById('cartFabButton')?.addEventListener('click', () => {
  document.getElementById('panier')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  highlightPanierCard();
});

document.getElementById('chatFab')?.addEventListener('click', () => {
  if (!chatPanel) {
    return;
  }
  const open = chatPanel.classList.toggle('chat-panel--open');
  chatPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
  if (open) {
    showChatbotWelcomeOnce();
  }
});

document.getElementById('chatCloseButton')?.addEventListener('click', () => {
  chatPanel?.classList.remove('chat-panel--open');
  chatPanel?.setAttribute('aria-hidden', 'true');
});

document.getElementById('chatQuickPrompts')?.addEventListener('click', (event) => {
  const chip = event.target.closest('.chat-chip');
  const prompt = chip?.getAttribute('data-prompt');
  if (!prompt) {
    return;
  }
  sendGeminiMessage(prompt, { skipUserBubble: false });
});

chatForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const text = chatInput?.value.trim() || '';
  if (!text) {
    return;
  }
  chatInput.value = '';
  await sendGeminiMessage(text, { skipUserBubble: false });
});

window.addEventListener('click', (event) => {
  if (saleModal && event.target === saleModal) {
    closeSaleModal();
  }
  if (quickBuyModal && event.target === quickBuyModal) {
    closeQuickBuyModal();
  }
});

function scrollToHashSection() {
  const hash = window.location.hash.replace('#', '');
  if (!hash) {
    return;
  }
  const idMap = {
    'espace-client': 'marche',
    'espace-vendeur': 'main-vendeur',
    panier: 'panier',
    marche: 'marche',
    'main-client': 'main-client',
    'main-vendeur': 'main-vendeur'
  };
  const targetId = idMap[hash] || hash;
  window.setTimeout(() => {
    document.getElementById(targetId)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }, 200);
}

if (PAGE_ROLE === 'client' || PAGE_ROLE === 'legacy') {
  readPaymentQueryBanner();
  scrollToHashSection();
}
initWeatherStrip();
refreshData().then(() => updateCartFabBadge());
