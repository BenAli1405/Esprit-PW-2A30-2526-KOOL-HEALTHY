const adminUsers = [
  { id: 'USR-01', name: 'Amine Tounsi', email: 'amine@mail.com' }
];
const backofficeSearchState = {
  users: '',
  products: '',
  productsStock: '',
  sales: '',
  salesStatus: ''
};

let adminSalesChartInstance = null;

let selectedUserId = null;
let products = [];
let sales = [];

const productForm = document.getElementById('productForm');
const saleForm = document.getElementById('saleForm');

async function apiRequest(url, options = {}) {
  const response = await fetch(`../api/${url}`, {
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

function showAlert(id, message, type) {
  const box = document.getElementById(id);
  box.textContent = message;
  box.className = `admin-alert ${type}`;
  box.style.display = 'block';
}

function hideAlert(id) {
  const box = document.getElementById(id);
  box.textContent = '';
  box.className = 'admin-alert';
  box.style.display = 'none';
}

function clearFieldErrors(formId) {
  document.querySelectorAll(`#${formId} .field-error`).forEach((element) => {
    element.textContent = '';
  });
}

function showFieldError(id, message) {
  const field = document.getElementById(id);
  if (field) {
    field.textContent = message;
  }
}

function sanitizeText(value) {
  return value.trim().replace(/\s+/g, ' ');
}

function normalizeSearchValue(value) {
  return sanitizeText(value).toLowerCase();
}

function getReservedQuantity(productId, excludedSaleId = null) {
  return sales
    .filter((sale) => sale.id_produit === productId)
    .filter((sale) => sale.statut !== 'vendue')
    .filter((sale) => excludedSaleId === null || Number(sale.id_vente) !== Number(excludedSaleId))
    .reduce((total, sale) => total + Number(sale.qte_a_vendre), 0);
}

function validateProduct(payload, originalId) {
  clearFieldErrors('productForm');
  hideAlert('productFormAlert');

  const idPattern = /^[A-Za-z0-9-]+$/;
  const namePattern = /^[A-Za-z0-9' -]+$/;
  const qtyPattern = /^[0-9]+$/;
  const datePattern = /^\d{4}-\d{2}-\d{2}$/;
  const reservedQuantity = getReservedQuantity(originalId || payload.id);
  let valid = true;

  if (!payload.id) {
    showFieldError('mProdIdError', 'L ID produit est obligatoire.');
    valid = false;
  } else if (!idPattern.test(payload.id)) {
    showFieldError('mProdIdError', 'Utilisez uniquement lettres, chiffres et tirets.');
    valid = false;
  } else if (payload.id.length > 30) {
    showFieldError('mProdIdError', 'Maximum 30 caracteres.');
    valid = false;
  } else if (products.some((product) => product.id === payload.id && product.id !== originalId)) {
    showFieldError('mProdIdError', 'Cet ID produit existe deja.');
    valid = false;
  }

  if (!payload.nom) {
    showFieldError('mProdNameError', 'Le nom est obligatoire.');
    valid = false;
  } else if (!namePattern.test(payload.nom)) {
    showFieldError('mProdNameError', 'Caracteres autorises: lettres, chiffres, espace, apostrophe et tiret.');
    valid = false;
  } else if (payload.nom.length < 2 || payload.nom.length > 120) {
    showFieldError('mProdNameError', 'Entre 2 et 120 caracteres.');
    valid = false;
  }

  if (!payload.date_expiration) {
    showFieldError('mProdExpError', 'La date est obligatoire.');
    valid = false;
  } else if (!datePattern.test(payload.date_expiration) || Number.isNaN(Date.parse(payload.date_expiration))) {
    showFieldError('mProdExpError', 'Format attendu: YYYY-MM-DD.');
    valid = false;
  }

  if (!payload.qteRaw) {
    showFieldError('mProdStockError', 'La quantite est obligatoire.');
    valid = false;
  } else if (!qtyPattern.test(payload.qteRaw)) {
    showFieldError('mProdStockError', 'Entrez un entier positif.');
    valid = false;
  } else if (!Number.isInteger(payload.qte) || payload.qte <= 0 || payload.qte > 99999) {
    showFieldError('mProdStockError', 'Valeur autorisee: 1 a 99999.');
    valid = false;
  } else if (payload.qte < reservedQuantity) {
    showFieldError('mProdStockError', `Minimum requis: ${reservedQuantity}.`);
    valid = false;
  }

  if (!valid) {
    showAlert('productFormAlert', 'Veuillez corriger les erreurs du produit.', 'error');
  }

  return valid;
}

function validateSale(payload, maxStock) {
  clearFieldErrors('saleForm');
  hideAlert('saleFormAlert');

  const quantityPattern = /^[0-9]+$/;
  const pricePattern = /^\d+(\.\d{1,2})?$/;
  const allowedStatuses = ['disponible', 'reservee', 'vendue'];
  let valid = true;

  if (!payload.qteRaw) {
    showFieldError('sSaleQtyError', 'La quantite est obligatoire.');
    valid = false;
  } else if (!quantityPattern.test(payload.qteRaw)) {
    showFieldError('sSaleQtyError', 'Entrez un entier positif.');
    valid = false;
  } else if (!Number.isInteger(payload.qte_a_vendre) || payload.qte_a_vendre <= 0 || payload.qte_a_vendre > maxStock) {
    showFieldError('sSaleQtyError', `La valeur doit etre comprise entre 1 et ${maxStock}.`);
    valid = false;
  }

  if (!payload.prixRaw) {
    showFieldError('sSalePriceError', 'Le prix est obligatoire.');
    valid = false;
  } else if (!pricePattern.test(payload.prixRaw)) {
    showFieldError('sSalePriceError', 'Format accepte: 12 ou 12.50.');
    valid = false;
  } else if (Number.isNaN(payload.prix) || payload.prix <= 0 || payload.prix > 99999.99) {
    showFieldError('sSalePriceError', 'Valeur autorisee: 0.01 a 99999.99.');
    valid = false;
  }

  if (!allowedStatuses.includes(payload.statut)) {
    showFieldError('sSaleStatusError', 'Selection invalide.');
    valid = false;
  }

  if (!valid) {
    showAlert('saleFormAlert', 'Veuillez corriger les erreurs de la vente.', 'error');
  }

  return valid;
}

function initUsers() {
  const tbody = document.getElementById('usersTableBody');
  const filteredUsers = adminUsers.filter((user) =>
    user.name.toLowerCase().includes(backofficeSearchState.users)
  );

  tbody.innerHTML = filteredUsers.length === 0
    ? `<tr><td colspan="4" style="text-align:center;">Aucun utilisateur ne correspond a votre recherche.</td></tr>`
    : filteredUsers.map((user) => `
    <tr>
      <td><strong>${user.id}</strong></td>
      <td>${user.name}</td>
      <td>${user.email}</td>
      <td>
        <button class="btn btn-primary btn-sm" onclick="selectUser('${user.id}')">
          <i class="fa-solid fa-eye"></i> Gerer
        </button>
      </td>
    </tr>
  `).join('');
}

function matchesProductsStockFilter(qte) {
  const band = backofficeSearchState.productsStock;
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

function updateAdminSalesChart() {
  const canvas = document.getElementById('adminSalesChart');
  if (!canvas || typeof Chart === 'undefined') {
    return;
  }

  const counts = { disponible: 0, reservee: 0, vendue: 0 };
  sales.forEach((sale) => {
    if (counts[sale.statut] !== undefined) {
      counts[sale.statut] += 1;
    }
  });

  const values = [counts.disponible, counts.reservee, counts.vendue];
  const hasData = values.some((v) => v > 0);

  if (adminSalesChartInstance) {
    adminSalesChartInstance.destroy();
    adminSalesChartInstance = null;
  }

  if (!hasData) {
    const ctx = canvas.getContext('2d');
    if (ctx) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
    return;
  }

  adminSalesChartInstance = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['Disponible', 'Reservee', 'Vendue'],
      datasets: [{
        data: values,
        backgroundColor: ['#4CAF50', '#FFB74D', '#90A4AE'],
        borderWidth: 0
      }]
    },
    options: {
      plugins: { legend: { position: 'bottom' } },
      maintainAspectRatio: false
    }
  });
}

async function loadData() {
  const [productsResult, salesResult] = await Promise.all([
    apiRequest('api_get_products.php'),
    apiRequest('api_get_sales.php')
  ]);

  if (productsResult.error) {
    throw new Error(productsResult.error);
  }

  if (salesResult.error) {
    throw new Error(salesResult.error);
  }

  products = Array.isArray(productsResult) ? productsResult : [];
  sales = Array.isArray(salesResult) ? salesResult : [];
  updateAdminSalesChart();
}

async function selectUser(userId) {
  selectedUserId = userId;
  const user = adminUsers.find((item) => item.id === userId);
  document.getElementById('currentUserName').textContent = `${user.name} (${user.id})`;
  document.getElementById('userDetailsPanel').style.display = 'flex';

  try {
    await loadData();
    refreshUserDetails();
    hideAlert('backofficeAlert');
  } catch (error) {
    showAlert('backofficeAlert', 'Impossible de charger les donnees: ' + error.message, 'error');
  }
}

function closeUserDetails() {
  document.getElementById('userDetailsPanel').style.display = 'none';
  selectedUserId = null;
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

function refreshUserDetails() {
  if (!selectedUserId) {
    return;
  }

  document.getElementById('userProductsBody').innerHTML = products.length === 0
    ? `<tr><td colspan="5" style="text-align:center;">Aucun produit</td></tr>`
    : products
      .filter((product) => product.nom.toLowerCase().includes(backofficeSearchState.products))
      .filter((product) => matchesProductsStockFilter(product.qte))
      .map((product) => `
      <tr>
        <td>${product.id}</td>
        <td>${product.nom}</td>
        <td>${product.date_expiration}</td>
        <td><strong>${product.qte}</strong></td>
        <td class="actions-cell">
          <button class="btn btn-edit btn-sm" onclick="openEditProductModal('${product.id}')"><i class="fa-solid fa-pen"></i></button>
          <button class="btn btn-delete btn-sm" onclick="deleteProduct('${product.id}')"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `).join('') || `<tr><td colspan="5" style="text-align:center;">Aucun produit ne correspond a votre recherche.</td></tr>`;

  const salesStatus = backofficeSearchState.salesStatus;
  document.getElementById('userSalesBody').innerHTML = sales.length === 0
    ? `<tr><td colspan="7" style="text-align:center;">Aucune vente</td></tr>`
    : sales
      .filter((sale) => sale.nom_produit.toLowerCase().includes(backofficeSearchState.sales))
      .filter((sale) => !salesStatus || sale.statut === salesStatus)
      .map((sale) => `
      <tr>
        <td>${sale.id_vente}</td>
        <td>${sale.id_produit}</td>
        <td>${sale.nom_produit}</td>
        <td>${sale.qte_a_vendre}</td>
        <td>${Number(sale.prix).toFixed(2)}</td>
        <td>${formatSaleStatus(sale.statut)}</td>
        <td class="actions-cell">
          <button class="btn btn-edit btn-sm" onclick="openEditSaleModal(${sale.id_vente})"><i class="fa-solid fa-pen"></i></button>
          <button class="btn btn-delete btn-sm" onclick="deleteSale(${sale.id_vente})"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>
    `).join('') || `<tr><td colspan="7" style="text-align:center;">Aucune vente ne correspond a votre recherche.</td></tr>`;
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function openEditProductModal(productId) {
  const product = products.find((item) => item.id === productId);
  if (!product) {
    return;
  }

  document.getElementById('formOriginalId').value = product.id;
  document.getElementById('mProdId').value = product.id;
  document.getElementById('mProdName').value = product.nom;
  document.getElementById('mProdExp').value = product.date_expiration;
  document.getElementById('mProdStock').value = product.qte;
  clearFieldErrors('productForm');
  hideAlert('productFormAlert');
  document.getElementById('productModal').style.display = 'flex';
}

function openEditSaleModal(idVente) {
  const sale = sales.find((item) => Number(item.id_vente) === Number(idVente));
  if (!sale) {
    return;
  }

  document.getElementById('saleId').value = sale.id_vente;
  document.getElementById('saleProductId').value = sale.id_produit;
  document.getElementById('sProdLabel').value = `${sale.nom_produit} (${sale.id_produit})`;
  document.getElementById('sSaleQty').value = sale.qte_a_vendre;
  document.getElementById('sSalePrice').value = Number(sale.prix).toFixed(2);
  document.getElementById('sSaleStatus').value = sale.statut;
  clearFieldErrors('saleForm');
  hideAlert('saleFormAlert');
  document.getElementById('saleModal').style.display = 'flex';
}

productForm.addEventListener('submit', async (event) => {
  event.preventDefault();

  const originalId = document.getElementById('formOriginalId').value;
  const payload = {
    id: sanitizeText(document.getElementById('mProdId').value),
    nom: sanitizeText(document.getElementById('mProdName').value),
    date_expiration: document.getElementById('mProdExp').value.trim(),
    qteRaw: document.getElementById('mProdStock').value.trim(),
    qte: Number.parseInt(document.getElementById('mProdStock').value, 10)
  };

  if (!validateProduct(payload, originalId)) {
    return;
  }

  try {
    const result = await apiRequest('api_update_product.php', {
      method: 'POST',
      body: JSON.stringify({
        original_id: originalId,
        id: payload.id,
        nom: payload.nom,
        date_expiration: payload.date_expiration,
        qte: payload.qte
      })
    });

    if (!result.success) {
      throw new Error(result.error || 'Mise a jour impossible');
    }

    await loadData();
    refreshUserDetails();
    closeModal('productModal');
    showAlert('backofficeAlert', 'Produit mis a jour avec succes.', 'success');
  } catch (error) {
    showAlert('productFormAlert', 'Erreur lors de la mise a jour: ' + error.message, 'error');
  }
});

saleForm.addEventListener('submit', async (event) => {
  event.preventDefault();

  const saleId = Number.parseInt(document.getElementById('saleId').value, 10);
  const productId = document.getElementById('saleProductId').value;
  const product = products.find((item) => item.id === productId);
  const maxStock = product ? product.qte - getReservedQuantity(productId, saleId) : 0;
  const payload = {
    id_vente: saleId,
    id_produit: productId,
    qteRaw: document.getElementById('sSaleQty').value.trim(),
    qte_a_vendre: Number.parseInt(document.getElementById('sSaleQty').value, 10),
    prixRaw: document.getElementById('sSalePrice').value.trim(),
    prix: Number.parseFloat(document.getElementById('sSalePrice').value),
    statut: document.getElementById('sSaleStatus').value
  };

  if (!validateSale(payload, maxStock)) {
    return;
  }

  try {
    const result = await apiRequest('api_update_sale.php', {
      method: 'POST',
      body: JSON.stringify(payload)
    });

    if (!result.success) {
      throw new Error(result.error || 'Mise a jour impossible');
    }

    await loadData();
    refreshUserDetails();
    closeModal('saleModal');
    showAlert('backofficeAlert', 'Vente mise a jour avec succes.', 'success');
  } catch (error) {
    showAlert('saleFormAlert', 'Erreur lors de la mise a jour: ' + error.message, 'error');
  }
});

async function deleteProduct(productId) {
  if (!confirm('Confirmer la suppression administrateur de ce produit ?')) {
    return;
  }

  try {
    const result = await apiRequest('api_delete_product.php', {
      method: 'POST',
      body: JSON.stringify({ id: productId })
    });

    if (!result.success) {
      throw new Error(result.error || 'Suppression impossible');
    }

    await loadData();
    refreshUserDetails();
    showAlert('backofficeAlert', 'Produit supprime avec succes.', 'success');
  } catch (error) {
    showAlert('backofficeAlert', 'Erreur lors de la suppression du produit: ' + error.message, 'error');
  }
}

async function deleteSale(idVente) {
  if (!confirm('Confirmer la suppression de cette vente ?')) {
    return;
  }

  try {
    const result = await apiRequest('api_delete_sale.php', {
      method: 'POST',
      body: JSON.stringify({ id_vente: idVente })
    });

    if (!result.success) {
      throw new Error(result.error || 'Suppression impossible');
    }

    await loadData();
    refreshUserDetails();
    showAlert('backofficeAlert', 'Vente supprimee avec succes.', 'success');
  } catch (error) {
    showAlert('backofficeAlert', 'Erreur lors de la suppression de la vente: ' + error.message, 'error');
  }
}

document.getElementById('closeUserDetailsButton').addEventListener('click', closeUserDetails);
document.getElementById('closeProductModalButton').addEventListener('click', () => closeModal('productModal'));
document.getElementById('closeSaleModalButton').addEventListener('click', () => closeModal('saleModal'));
document.getElementById('usersSearch').addEventListener('input', (event) => {
  backofficeSearchState.users = normalizeSearchValue(event.target.value);
  initUsers();
});
document.getElementById('productsSearch').addEventListener('input', (event) => {
  backofficeSearchState.products = normalizeSearchValue(event.target.value);
  refreshUserDetails();
});
document.getElementById('salesSearch').addEventListener('input', (event) => {
  backofficeSearchState.sales = normalizeSearchValue(event.target.value);
  refreshUserDetails();
});

document.getElementById('productsStockFilter')?.addEventListener('change', (event) => {
  backofficeSearchState.productsStock = event.target.value;
  refreshUserDetails();
});

document.getElementById('salesStatusFilter')?.addEventListener('change', (event) => {
  backofficeSearchState.salesStatus = event.target.value;
  refreshUserDetails();
});

window.addEventListener('click', (event) => {
  if (event.target.id === 'productModal' || event.target.id === 'saleModal') {
    closeModal(event.target.id);
  }
});

(async () => {
  try {
    await loadData();
  } catch (error) {
    console.error(error);
  }
})();

initUsers();
