// ========== BACKOFFICE DATA & INITIALIZATION ==========
let ingredientsDB = [];
let recettesDB = [];

// ========== FETCH DATA ==========
async function loadData() {
  try {
    const recipesResponse = await fetch('INDEX.php?action=getAllRecipes');
    const recipesText = await recipesResponse.text();
    console.log('Recipes response:', recipesText);
    try {
      recettesDB = JSON.parse(recipesText);
    } catch (e) {
      console.error('Failed to parse recipes JSON:', e);
      showToast('Erreur: Impossible de charger les recettes', true);
      return;
    }
    
    const ingredientsResponse = await fetch('INDEX.php?action=getAllIngredients');
    const ingredientsText = await ingredientsResponse.text();
    console.log('Ingredients response:', ingredientsText);
    try {
      ingredientsDB = JSON.parse(ingredientsText);
    } catch (e) {
      console.error('Failed to parse ingredients JSON:', e);
      showToast('Erreur: Impossible de charger les ingrédients', true);
      return;
    }
    
    updateDashboard();
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors du chargement des données', true);
  }
}

// ========== UTILITAIRES ==========
function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toastMessage');
  toastMessage.textContent = message;
  toast.style.background = isError ? '#d32f2f' : 'var(--vert-kool-dark)';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/[&<>]/g, function(m) {
    if (m === '&') return '&amp;';
    if (m === '<') return '&lt;';
    if (m === '>') return '&gt;';
    return m;
  });
}

function renderStars(note) {
  let stars = '';
  for (let i = 1; i <= 5; i++) {
    stars += `<i class="fas fa-star" style="color: ${i <= note ? '#ffc107' : '#ddd'};"></i>`;
  }
  return stars;
}

// ========== DASHBOARD ==========
function updateDashboard() {
  document.getElementById('statRecettes').textContent = recettesDB.length;
  document.getElementById('statIngredients').textContent = ingredientsDB.length;
  
  let totalReviews = 0;
  recettesDB.forEach(rec => {
    if (rec.avis) {
      totalReviews += Array.isArray(rec.avis) ? rec.avis.length : (rec.nombre_avis || 0);
    }
  });
  document.getElementById('statReviews').textContent = totalReviews;
  
  // Top recettes par note moyenne
  let recettesWithAvg = recettesDB.map(rec => ({
    titre: rec.titre,
    avgNote: rec.note_moyenne || 0,
    avis: rec.nombre_avis || 0
  }));
  recettesWithAvg.sort((a, b) => b.avgNote - a.avgNote);
  const topRecipes = recettesWithAvg.slice(0, 5);
  
  const topRecipesHtml = topRecipes.map(rec => `
    <div class="top-item">
      <span class="top-item-name">${escapeHtml(rec.titre)}</span>
      <span class="top-item-value">⭐ ${rec.avgNote || 0}/5 (${rec.avis} avis)</span>
    </div>
  `).join('');
  document.getElementById('topRecipesList').innerHTML = topRecipesHtml || '<div>Aucune recette</div>';
  
  // Top ingrédients - count from recipes
  let ingredientCount = {};
  recettesDB.forEach(rec => {
    if (rec.ingredients) {
      rec.ingredients.forEach(ing => {
        let ingId = ing.ingredient_id || ing.idIng;
        ingredientCount[ingId] = (ingredientCount[ingId] || 0) + 1;
      });
    }
  });
  
  let topIngredients = Object.entries(ingredientCount)
    .map(([id, count]) => {
      let ing = ingredientsDB.find(i => i.id == id);
      return { nama: ing ? ing.nom : '?', count };
    })
    .sort((a, b) => b.count - a.count)
    .slice(0, 5);
  
  const topIngredientsHtml = topIngredients.map(ing => `
    <div class="top-item">
      <span class="top-item-name">${escapeHtml(ing.nama)}</span>
      <span class="top-item-value">${ing.count} recettes</span>
    </div>
  `).join('');
  document.getElementById('topIngredientsList').innerHTML = topIngredientsHtml || '<div>Aucun ingrédient</div>';
  
  // Activité récente - from avis data
  let allReviews = [];
  recettesDB.forEach(rec => {
    if (rec.avis && Array.isArray(rec.avis)) {
      rec.avis.forEach(avis => {
        allReviews.push({
          utilisateur: avis.utilisateur_nom || avis.utilisateur || 'Anonyme',
          recette: rec.titre,
          note: avis.note
        });
      });
    }
  });
  
  const recentReviews = allReviews.slice(0, 5);
  const recentHtml = recentReviews.map(rev => `
    <div class="activity-item">
      <div class="activity-icon"><i class="fas fa-star"></i></div>
      <div class="activity-detail">
        <p><strong>${escapeHtml(rev.utilisateur)}</strong> a noté "${escapeHtml(rev.recette)}" ${renderStars(rev.note)}</p>
      </div>
    </div>
  `).join('');
  document.getElementById('recentActivityList').innerHTML = recentHtml || '<div>Aucun avis</div>';
  
  renderRecipesTable();
  renderIngredientsTable();
  renderReviewsTable();
}

// ========== GESTION RECETTES ==========
function renderRecipesTable() {
  const tbody = document.getElementById('recipesTableBody');
  tbody.innerHTML = recettesDB.map(rec => {
    return `
      <tr>
        <td>${escapeHtml(rec.titre)}</td>
        <td>${rec.difficulte}</td>
        <td>${rec.temps_preparation} min</td>
        <td>${rec.eco_score}</td>
        <td>${rec.nombre_avis || 0}</td>
        <td class="action-icons">
          <i class="fas fa-edit edit-icon" onclick="openRecipeModal(${rec.id})"></i>
          <i class="fas fa-trash delete-icon" onclick="deleteRecipe(${rec.id})"></i>
        </td>
      </tr>
    `;
  }).join('');
}

function openRecipeModal(recipeId = null) {
  const modal = document.getElementById('recipeModal');
  const title = document.getElementById('recipeModalTitle');
  const ingredientContainer = document.getElementById('ingredientsListContainer');
  
  if (recipeId) {
    const recipe = recettesDB.find(r => r.id === recipeId);
    if (!recipe) return;
    
    title.textContent = 'Modifier une recette';
    document.getElementById('recipeId').value = recipe.id;
    document.getElementById('recipeTitle').value = recipe.titre;
    document.getElementById('recipeInstructions').value = recipe.instruction;
    document.getElementById('recipeTime').value = recipe.temps_preparation;
    document.getElementById('recipeDifficulty').value = recipe.difficulte;
    document.getElementById('recipeEcoScore').value = recipe.eco_score;
    
    ingredientContainer.innerHTML = (recipe.ingredients || []).map(ing => {
      const ingId = ing.ingredient_id || ing.idIng;
      const qty = ing.quantite || ing.qty || 0;
      return `
        <div class="ingredient-row">
          <select class="ingredient-select">
            ${ingredientsDB.map(i => `<option value="${i.id}" ${i.id === ingId ? 'selected' : ''}>${escapeHtml(i.nom)}</option>`).join('')}
          </select>
          <input type="number" class="ingredient-qty" value="${qty}">
          <input type="text" class="ingredient-unite" value="${ing.unite}" placeholder="g, ml...">
          <button type="button" class="ingredient-remove">×</button>
        </div>
      `;
    }).join('');
  } else {
    title.textContent = 'Ajouter une recette';
    document.getElementById('recipeId').value = '';
    document.getElementById('recipeForm').reset();
    ingredientContainer.innerHTML = `
      <div class="ingredient-row">
        <select class="ingredient-select">
          ${ingredientsDB.map(i => `<option value="${i.id}">${escapeHtml(i.nom)}</option>`).join('')}
        </select>
        <input type="number" class="ingredient-qty" value="100">
        <input type="text" class="ingredient-unite" value="g">
        <button type="button" class="ingredient-remove">×</button>
      </div>
    `;
  }
  
  modal.style.display = 'flex';
}

async function deleteRecipe(id) {
  if (!confirm('Êtes-vous sûr ?')) return;
  
  const formData = new FormData();
  formData.append('action', 'deleteRecipe');
  formData.append('id', id);
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    console.log('Delete recipe response:', responseText);
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      console.error('Failed to parse delete response:', e);
      console.error('Response was:', responseText);
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message);
      loadData();
    } else {
      showToast(result.message || 'Échec de la suppression', true);
    }
  } catch (error) {
    console.error('Delete error:', error);
    showToast('Erreur lors de la suppression: ' + error.message, true);
  }
}

// ========== GESTION INGRÉDIENTS ==========
function renderIngredientsTable() {
  const tbody = document.getElementById('ingredientsTableBody');
  tbody.innerHTML = ingredientsDB.map(ing => {
    let usage = 0;
    recettesDB.forEach(rec => {
      if (rec.ingredients && Array.isArray(rec.ingredients) && rec.ingredients.some(i => i.idIng === ing.id)) usage++;
    });
    return `
      <tr>
        <td>${escapeHtml(ing.nom)}</td>
        <td>${ing.calories || '-'}</td>
        <td>${ing.eco_score}</td>
        <td>${usage}</td>
        <td class="action-icons">
          <i class="fas fa-edit edit-icon" onclick="openIngredientModal(${ing.id})"></i>
          <i class="fas fa-trash delete-icon" onclick="deleteIngredient(${ing.id})"></i>
        </td>
      </tr>
    `;
  }).join('');
}

function openIngredientModal(ingredientId = null) {
  const modal = document.getElementById('ingredientModal');
  const title = document.getElementById('ingredientModalTitle');
  
  if (ingredientId) {
    const ing = ingredientsDB.find(i => i.id === ingredientId);
    if (!ing) return;
    
    title.textContent = 'Modifier l\'ingrédient';
    document.getElementById('ingredientId').value = ing.id;
    document.getElementById('ingredientName').value = ing.nom;
    document.getElementById('ingredientCalories').value = ing.calories || '';
    document.getElementById('ingredientEcoScore').value = ing.eco_score;
  } else {
    title.textContent = 'Ajouter un ingrédient';
    document.getElementById('ingredientForm').reset();
  }
  
  modal.style.display = 'flex';
}

async function deleteIngredient(id) {
  if (!confirm('Êtes-vous sûr ?')) return;
  
  const formData = new FormData();
  formData.append('action', 'deleteIngredient');
  formData.append('id', id);
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    console.log('Delete ingredient response:', responseText);
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      console.error('Failed to parse delete response:', e);
      console.error('Response was:', responseText);
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message);
      loadData();
    } else {
      showToast(result.message || 'Échec de la suppression', true);
    }
  } catch (error) {
    console.error('Delete error:', error);
    showToast('Erreur lors de la suppression: ' + error.message, true);
  }
}
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la suppression', true);
  }
}

async function deleteReview(recipeId, reviewId) {
  if (!confirm('Êtes-vous sûr ?')) return;
  
  const formData = new FormData();
  formData.append('action', 'deleteReview');
  formData.append('recipeId', recipeId);
  formData.append('id', reviewId);
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message);
      loadData();
    } else {
      showToast(result.message, true);
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la suppression', true);
  }
}

// ========== GESTION AVIS ==========
function renderReviewsTable() {
  const tbody = document.getElementById('reviewsTableBody');
  let allReviews = [];
  
  recettesDB.forEach(rec => {
    if (rec.avis && Array.isArray(rec.avis)) {
      rec.avis.forEach(avis => {
        allReviews.push({
          recipeId: rec.id,
          reviewId: avis.id,
          utilisateur: avis.utilisateur_nom || avis.utilisateur || 'Anonyme',
          recette: rec.titre,
          note: avis.note,
          commentaire: avis.commentaire || ''
        });
      });
    }
  });
  
  tbody.innerHTML = allReviews.map(avis => `
    <tr>
      <td>${escapeHtml(avis.utilisateur)}</td>
      <td>${escapeHtml(avis.recette)}</td>
      <td>${renderStars(avis.note)}</td>
      <td>${escapeHtml(avis.commentaire)}</td>
      <td class="action-icons">
        <i class="fas fa-trash delete-icon" onclick="deleteReview(${avis.recipeId}, ${avis.reviewId})"></i>
      </td>
    </tr>
  `).join('');
}

// ========== NAVIGATION TABS ==========
const contents = {
  dashboard: document.getElementById('dashboardContent'),
  recipes: document.getElementById('recipesContent'),
  ingredients: document.getElementById('ingredientsContent'),
  reviews: document.getElementById('reviewsContent')
};

function showTab(tabId) {
  Object.values(contents).forEach(content => content.style.display = 'none');
  if (contents[tabId]) contents[tabId].style.display = 'block';
}

document.querySelectorAll('.nav-item').forEach(item => {
  item.addEventListener('click', function() {
    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
    this.classList.add('active');
    showTab(this.dataset.tab);
  });
});

// ========== FORM SUBMISSION HANDLERS ==========

// Recipe Form Submission
document.getElementById('recipeForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const recipeId = document.getElementById('recipeId').value;
  const titre = document.getElementById('recipeTitle').value.trim();
  const instruction = document.getElementById('recipeInstructions').value.trim();
  const temp = parseInt(document.getElementById('recipeTime').value);
  const difficulte = document.getElementById('recipeDifficulty').value;
  const ecoScore = document.getElementById('recipeEcoScore').value;
  
  // Collect ingredients
  const ingredients = [];
  document.querySelectorAll('#ingredientsListContainer .ingredient-row').forEach(row => {
    const ingredientId = row.querySelector('.ingredient-select').value;
    const qty = row.querySelector('.ingredient-qty').value;
    const unite = row.querySelector('.ingredient-unite').value;
    
    if (ingredientId && qty) {
      ingredients.push({
        idIng: parseInt(ingredientId),
        qty: parseFloat(qty),
        unite: unite
      });
    }
  });
  
  if (!titre) {
    showToast('Titre requis', true);
    return;
  }
  
  // Validation 1: Title length (min 3, max 100)
  if (titre.length < 3) {
    showToast('Titre doit avoir au moins 3 caractères', true);
    return;
  }
  
  if (titre.length > 100) {
    showToast('Titre trop long (max 100 caractères)', true);
    return;
  }
  
  // Validation 7: No special characters in title (French characters allowed)
  if (!/^[a-zA-Z0-9\s\-&àâäæçéèêëïîôöœùûüœÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜŒ]+$/.test(titre)) {
    showToast('Titre contient des caractères invalides', true);
    return;
  }
  
  // Validation 4: Instructions not empty
  if (!instruction || instruction.trim().length === 0) {
    showToast('Instructions requises', true);
    return;
  }
  
  // Validation 3: Time range (0-999 minutes)
  if (temp < 0 || temp > 999) {
    showToast('Le temps doit être entre 0 et 999 minutes', true);
    return;
  }
  
  if (ingredients.length === 0) {
    showToast('Au moins un ingrédient requis', true);
    return;
  }
  
  const formData = new FormData();
  formData.append('titre', titre);
  formData.append('instruction', instruction);
  formData.append('temp', temp);
  formData.append('difficulte', difficulte);
  formData.append('ecoScore', ecoScore);
  formData.append('ingredients', JSON.stringify(ingredients));
  formData.append('utilisateurId', 1); // Default user
  
  if (recipeId) {
    formData.append('action', 'updateRecipe');
    formData.append('id', recipeId);
  } else {
    formData.append('action', 'createRecipe');
  }
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    console.log('Save recipe response:', responseText);
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      console.error('Failed to parse response JSON:', e);
      console.error('Response was:', responseText.substring(0, 200));
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message);
      document.getElementById('recipeModal').style.display = 'none';
      loadData();
    } else {
      showToast(result.message || 'Erreur inconnue', true);
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la sauvegarde: ' + error.message, true);
  }
});

// Ingredient Form Submission
document.getElementById('ingredientForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const ingredientId = document.getElementById('ingredientId').value;
  const nom = document.getElementById('ingredientName').value.trim();
  const calories = document.getElementById('ingredientCalories').value.trim() || null;
  const ecoScore = document.getElementById('ingredientEcoScore').value;
  
  if (!nom) {
    showToast('Nom requis', true);
    return;
  }
  
  // Validation 1: Ingredient name length (min 2, max 100)
  if (nom.length < 2) {
    showToast('Nom doit avoir au moins 2 caractères', true);
    return;
  }
  
  if (nom.length > 100) {
    showToast('Nom trop long (max 100 caractères)', true);
    return;
  }
  
  // Validation 7: No special characters in ingredient name
  if (!/^[a-zA-Z0-9\s\-&àâäæçéèêëïîôöœùûüœÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜŒ]+$/.test(nom)) {
    showToast('Nom contient des caractères invalides', true);
    return;
  }
  
  const formData = new FormData();
  formData.append('nom', nom);
  if (calories) formData.append('calories', calories);
  formData.append('ecoScore', ecoScore);
  
  if (ingredientId) {
    formData.append('action', 'updateIngredient');
    formData.append('id', ingredientId);
  } else {
    formData.append('action', 'createIngredient');
  }
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    console.log('Save ingredient response:', responseText);
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      console.error('Failed to parse response JSON:', e);
      console.error('Response was:', responseText.substring(0, 200));
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message);
      document.getElementById('ingredientModal').style.display = 'none';
      loadData();
    } else {
      showToast(result.message || 'Erreur inconnue', true);
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la sauvegarde: ' + error.message, true);
  }
});

// ========== MODAL CLOSE ==========
document.getElementById('closeRecipeModal').onclick = () => document.getElementById('recipeModal').style.display = 'none';
document.getElementById('closeIngredientModal').onclick = () => document.getElementById('ingredientModal').style.display = 'none';
document.getElementById('cancelRecipeBtn').onclick = () => document.getElementById('recipeModal').style.display = 'none';
document.getElementById('cancelIngredientBtn').onclick = () => document.getElementById('ingredientModal').style.display = 'none';
// Ingredient row management
document.getElementById('addIngredientRowBtn').addEventListener('click', (e) => {
  e.preventDefault();
  const container = document.getElementById('ingredientsListContainer');
  const newRow = document.createElement('div');
  newRow.className = 'ingredient-row';
  newRow.innerHTML = `
    <select class="ingredient-select">
      ${ingredientsDB.map(i => `<option value="${i.id}">${escapeHtml(i.nom)}</option>`).join('')}
    </select>
    <input type="number" class="ingredient-qty" value="100">
    <input type="text" class="ingredient-unite" value="g" placeholder="g, ml...">
    <button type="button" class="ingredient-remove">×</button>
  `;
  
  newRow.querySelector('.ingredient-remove').addEventListener('click', (e) => {
    e.preventDefault();
    newRow.remove();
  });
  
  container.appendChild(newRow);
});

// Event delegation for ingredient remove buttons
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('ingredient-remove')) {
    e.preventDefault();
    e.target.closest('.ingredient-row').remove();
  }
});
window.onclick = (e) => {
  if (e.target === document.getElementById('recipeModal')) document.getElementById('recipeModal').style.display = 'none';
  if (e.target === document.getElementById('ingredientModal')) document.getElementById('ingredientModal').style.display = 'none';
};

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', () => {
  loadData();
  showTab('dashboard');
  
  // Attach button click listeners
  document.getElementById('addRecipeBtn').addEventListener('click', () => openRecipeModal());
  document.getElementById('addIngredientBtn').addEventListener('click', () => openIngredientModal());
});
