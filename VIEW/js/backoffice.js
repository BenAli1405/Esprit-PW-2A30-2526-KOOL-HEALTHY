// ========== BACKOFFICE DATA & INITIALIZATION ==========
console.log('Backoffice.js loaded!');
let ingredientsDB = [];
let recettesDB = [];

// ========== FETCH DATA ==========
async function loadData() {
  try {
    console.log('Loading data...');
    
    const recipesResponse = await fetch('INDEX.php?action=getAllRecipes');
    if (!recipesResponse.ok) {
      throw new Error(`HTTP ${recipesResponse.status}`);
    }
    const recipesText = await recipesResponse.text();
    
    try {
      recettesDB = JSON.parse(recipesText);
      console.log('Recipes loaded:', recettesDB.length);
    } catch (e) {
      console.error('Failed to parse recipes JSON:', e);
      showToast('Erreur: Impossible de charger les recettes', true);
      return;
    }
    
    const ingredientsResponse = await fetch('INDEX.php?action=getAllIngredients');
    if (!ingredientsResponse.ok) {
      throw new Error(`HTTP ${ingredientsResponse.status}`);
    }
    const ingredientsText = await ingredientsResponse.text();
    
    try {
      ingredientsDB = JSON.parse(ingredientsText);
      console.log('Ingredients loaded:', ingredientsDB.length);
    } catch (e) {
      console.error('Failed to parse ingredients JSON:', e);
      showToast('Erreur: Impossible de charger les ingrédients', true);
      return;
    }
    
    updateDashboard();
    renderRecipesTable();
    renderIngredientsTable();
    renderReviewsTable();
    
  } catch (error) {
    console.error('Erreur chargement:', error);
    showToast('Erreur lors du chargement des données: ' + error.message, true);
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
  return String(str).replace(/[&<>]/g, function(m) {
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

// ========== FONCTIONS DE VALIDATION ==========

/**
 * Valide le titre d'une recette
 */
function validateRecipeTitle(titre) {
  if (!titre || titre.trim() === '') {
    return { valid: false, message: 'Le titre est requis' };
  }
  if (titre.length < 3) {
    return { valid: false, message: 'Le titre doit contenir au moins 3 caractères' };
  }
  if (titre.length > 100) {
    return { valid: false, message: 'Le titre ne peut pas dépasser 100 caractères' };
  }
  // Autorise lettres, chiffres, espaces, tirets, apostrophes, accents
  const regex = /^[a-zA-Z0-9\s\-'àâäæçéèêëïîôöœùûüüÿÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜŸ]+$/;
  if (!regex.test(titre)) {
    return { valid: false, message: 'Le titre contient des caractères non autorisés' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide les instructions d'une recette
 */
function validateRecipeInstructions(instructions) {
  if (!instructions || instructions.trim() === '') {
    return { valid: false, message: 'Les instructions sont requises' };
  }
  if (instructions.length < 10) {
    return { valid: false, message: 'Les instructions doivent contenir au moins 10 caractères' };
  }
  if (instructions.length > 5000) {
    return { valid: false, message: 'Les instructions ne peuvent pas dépasser 5000 caractères' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide le temps de préparation
 */
function validateRecipeTime(temp) {
  const time = parseInt(temp);
  if (isNaN(time)) {
    return { valid: false, message: 'Le temps doit être un nombre' };
  }
  if (time < 0) {
    return { valid: false, message: 'Le temps ne peut pas être négatif' };
  }
  if (time > 999) {
    return { valid: false, message: 'Le temps ne peut pas dépasser 999 minutes' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide la difficulté
 */
function validateDifficulty(difficulte) {
  const validDifficulties = ['Facile', 'Moyen', 'Difficile'];
  if (!validDifficulties.includes(difficulte)) {
    return { valid: false, message: 'Difficulté invalide' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide l'éco-score
 */
function validateEcoScore(ecoScore) {
  const validScores = ['A+', 'A', 'B', 'C', 'D', 'E'];
  if (!validScores.includes(ecoScore)) {
    return { valid: false, message: 'Eco-score invalide' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide les ingrédients
 */
function validateIngredients(ingredients) {
  if (!ingredients || ingredients.length === 0) {
    return { valid: false, message: 'Au moins un ingrédient est requis' };
  }
  
  for (let i = 0; i < ingredients.length; i++) {
    const ing = ingredients[i];
    if (!ing.idIng || ing.idIng <= 0) {
      return { valid: false, message: `Ingrédient ${i+1}: sélection invalide` };
    }
    if (!ing.qty || isNaN(ing.qty) || ing.qty <= 0) {
      return { valid: false, message: `Ingrédient ${i+1}: quantité invalide (doit être > 0)` };
    }
    if (ing.qty > 9999) {
      return { valid: false, message: `Ingrédient ${i+1}: quantité trop élevée (max 9999)` };
    }
    if (ing.unite && ing.unite.length > 20) {
      return { valid: false, message: `Ingrédient ${i+1}: unité trop longue (max 20 caractères)` };
    }
  }
  return { valid: true, message: '' };
}

/**
 * Valide le nom d'un ingrédient
 */
function validateIngredientName(nom) {
  if (!nom || nom.trim() === '') {
    return { valid: false, message: 'Le nom de l\'ingrédient est requis' };
  }
  if (nom.length < 2) {
    return { valid: false, message: 'Le nom doit contenir au moins 2 caractères' };
  }
  if (nom.length > 100) {
    return { valid: false, message: 'Le nom ne peut pas dépasser 100 caractères' };
  }
  const regex = /^[a-zA-Z0-9\s\-'àâäæçéèêëïîôöœùûüüÿÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜŸ]+$/;
  if (!regex.test(nom)) {
    return { valid: false, message: 'Le nom contient des caractères non autorisés' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide les calories
 */
function validateCalories(calories) {
  if (!calories || calories.trim() === '') {
    return { valid: true, message: '' }; // Optionnel
  }
  // Format: "120kcal/100g" ou "52kcal"
  const regex = /^(\d+(?:\.\d+)?)(kcal(?:\/\d+g)?)?$/i;
  if (!regex.test(calories.trim())) {
    return { valid: false, message: 'Format calories invalide (ex: 120kcal/100g)' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide une note (avis)
 */
function validateRating(note) {
  const rating = parseInt(note);
  if (isNaN(rating)) {
    return { valid: false, message: 'Veuillez sélectionner une note' };
  }
  if (rating < 1 || rating > 5) {
    return { valid: false, message: 'La note doit être comprise entre 1 et 5' };
  }
  return { valid: true, message: '' };
}

/**
 * Valide un commentaire (avis)
 */
function validateComment(commentaire) {
  if (!commentaire || commentaire.trim() === '') {
    return { valid: false, message: 'Le commentaire est requis' };
  }
  if (commentaire.length < 3) {
    return { valid: false, message: 'Le commentaire doit contenir au moins 3 caractères' };
  }
  if (commentaire.length > 1000) {
    return { valid: false, message: 'Le commentaire ne peut pas dépasser 1000 caractères' };
  }
  return { valid: true, message: '' };
}

// ========== DASHBOARD ==========
function updateDashboard() {
  document.getElementById('statRecettes').textContent = recettesDB.length;
  document.getElementById('statIngredients').textContent = ingredientsDB.length;
  
  let totalReviews = 0;
  recettesDB.forEach(rec => {
    if (rec.avis && Array.isArray(rec.avis)) {
      totalReviews += rec.avis.length;
    } else if (rec.nombre_avis) {
      totalReviews += rec.nombre_avis;
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
  
  // Top ingrédients
  let ingredientCount = {};
  recettesDB.forEach(rec => {
    if (rec.ingredients && Array.isArray(rec.ingredients)) {
      rec.ingredients.forEach(ing => {
        let ingId = ing.ingredient_id || ing.idIng || ing.id;
        if (ingId) {
          ingredientCount[ingId] = (ingredientCount[ingId] || 0) + 1;
        }
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
  
  // Activité récente
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
}

// ========== GESTION RECETTES ==========
function renderRecipesTable() {
  const tbody = document.getElementById('recipesTableBody');
  if (!tbody) return;
  
  if (recettesDB.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Aucune recette trouvée</td></tr>';
    return;
  }
  
  tbody.innerHTML = recettesDB.map(rec => `
    <tr>
      <td>${escapeHtml(rec.titre)}</td>
      <td>${rec.difficulte || 'N/A'}</td>
      <td>${rec.temps_preparation || 0} min</td>
      <td>${rec.eco_score || 'A'}</td>
      <td>${rec.nombre_avis || 0}</td>
      <td class="action-icons">
        <i class="fas fa-edit edit-icon" onclick="openRecipeModal(${rec.id})" style="cursor:pointer; color:#29B6F6; margin:0 5px;"></i>
        <i class="fas fa-trash delete-icon" onclick="deleteRecipe(${rec.id})" style="cursor:pointer; color:#ef5350; margin:0 5px;"></i>
      </td>
    </tr>
  `).join('');
}

function openRecipeModal(recipeId = null) {
  const modal = document.getElementById('recipeModal');
  const title = document.getElementById('recipeModalTitle');
  const ingredientContainer = document.getElementById('ingredientsListContainer');
  
  // Réinitialiser les erreurs
  clearRecipeErrors();
  
  if (recipeId) {
    const recipe = recettesDB.find(r => r.id === recipeId);
    if (!recipe) {
      showToast('Recette non trouvée', true);
      return;
    }
    
    title.textContent = 'Modifier une recette';
    document.getElementById('recipeId').value = recipe.id;
    document.getElementById('recipeTitle').value = recipe.titre || '';
    document.getElementById('recipeInstructions').value = recipe.instruction || '';
    document.getElementById('recipeTime').value = recipe.temps_preparation || 0;
    document.getElementById('recipeDifficulty').value = recipe.difficulte || 'Facile';
    document.getElementById('recipeEcoScore').value = recipe.eco_score || 'A';
    
    if (recipe.ingredients && recipe.ingredients.length > 0) {
      ingredientContainer.innerHTML = recipe.ingredients.map(ing => {
        const ingId = ing.ingredient_id || ing.idIng || ing.id;
        const qty = ing.quantite || ing.qty || 0;
        const unite = ing.unite || 'g';
        return `
          <div class="ingredient-row">
            <select class="ingredient-select">
              ${ingredientsDB.map(i => `<option value="${i.id}" ${i.id === ingId ? 'selected' : ''}>${escapeHtml(i.nom)}</option>`).join('')}
            </select>
            <input type="number" class="ingredient-qty" value="${qty}" step="0.01" min="0.01" max="9999">
            <input type="text" class="ingredient-unite" value="${escapeHtml(unite)}" placeholder="g, ml..." maxlength="20">
            <button type="button" class="ingredient-remove">×</button>
          </div>
        `;
      }).join('');
    } else {
      addEmptyIngredientRow();
    }
  } else {
    title.textContent = 'Ajouter une recette';
    document.getElementById('recipeId').value = '';
    document.getElementById('recipeForm').reset();
    document.getElementById('recipeTitle').value = '';
    document.getElementById('recipeInstructions').value = '';
    document.getElementById('recipeTime').value = '30';
    addEmptyIngredientRow();
  }
  
  modal.style.display = 'flex';
}

function addEmptyIngredientRow() {
  const container = document.getElementById('ingredientsListContainer');
  container.innerHTML = `
    <div class="ingredient-row">
      <select class="ingredient-select">
        ${ingredientsDB.map(i => `<option value="${i.id}">${escapeHtml(i.nom)}</option>`).join('')}
      </select>
      <input type="number" class="ingredient-qty" value="100" step="0.01" min="0.01" max="9999">
      <input type="text" class="ingredient-unite" value="g" placeholder="g, ml..." maxlength="20">
      <button type="button" class="ingredient-remove">×</button>
    </div>
  `;
}

function clearRecipeErrors() {
  // Supprimer les messages d'erreur existants
  document.querySelectorAll('.error-message').forEach(el => el.remove());
  // Réinitialiser les styles des champs
  document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(field => {
    field.style.borderColor = '';
  });
}

function showFieldError(fieldId, message) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = '#d32f2f';
    // Ajouter un message d'erreur
    let errorDiv = field.parentElement.querySelector('.error-message');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.className = 'error-message';
      errorDiv.style.color = '#d32f2f';
      errorDiv.style.fontSize = '0.7rem';
      errorDiv.style.marginTop = '4px';
      field.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
  }
}

async function deleteRecipe(id) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette recette ?')) return;
  
  const formData = new FormData();
  formData.append('action', 'deleteRecipe');
  formData.append('id', id);
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message || 'Recette supprimée');
      loadData();
    } else {
      showToast(result.message || 'Échec de la suppression', true);
    }
  } catch (error) {
    console.error('Delete error:', error);
    showToast('Erreur lors de la suppression', true);
  }
}

// ========== GESTION INGRÉDIENTS ==========
function renderIngredientsTable() {
  const tbody = document.getElementById('ingredientsTableBody');
  if (!tbody) return;
  
  if (ingredientsDB.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Aucun ingrédient trouvé</td></tr>';
    return;
  }
  
  tbody.innerHTML = ingredientsDB.map(ing => {
    let usage = 0;
    recettesDB.forEach(rec => {
      if (rec.ingredients && Array.isArray(rec.ingredients)) {
        if (rec.ingredients.some(i => (i.ingredient_id === ing.id) || (i.idIng === ing.id) || (i.id === ing.id))) {
          usage++;
        }
      }
    });
    
    return `
      <tr>
        <td>${escapeHtml(ing.nom)}</td>
        <td>${ing.calories || '-'}</td>
        <td>${ing.eco_score || 'A'}</td>
        <td>${usage}</td>
        <td class="action-icons">
          <i class="fas fa-edit edit-icon" onclick="openIngredientModal(${ing.id})" style="cursor:pointer; color:#29B6F6; margin:0 5px;"></i>
          <i class="fas fa-trash delete-icon" onclick="deleteIngredient(${ing.id})" style="cursor:pointer; color:#ef5350; margin:0 5px;"></i>
        </td>
      </tr>
    `;
  }).join('');
}

function openIngredientModal(ingredientId = null) {
  const modal = document.getElementById('ingredientModal');
  const title = document.getElementById('ingredientModalTitle');
  
  // Réinitialiser les erreurs
  clearIngredientErrors();
  
  if (ingredientId) {
    const ing = ingredientsDB.find(i => i.id === ingredientId);
    if (!ing) {
      showToast('Ingrédient non trouvé', true);
      return;
    }
    
    title.textContent = 'Modifier l\'ingrédient';
    document.getElementById('ingredientId').value = ing.id;
    document.getElementById('ingredientName').value = ing.nom || '';
    document.getElementById('ingredientCalories').value = ing.calories || '';
    document.getElementById('ingredientEcoScore').value = ing.eco_score || 'A';
  } else {
    title.textContent = 'Ajouter un ingrédient';
    document.getElementById('ingredientForm').reset();
    document.getElementById('ingredientId').value = '';
    document.getElementById('ingredientName').value = '';
    document.getElementById('ingredientCalories').value = '';
  }
  
  modal.style.display = 'flex';
}

function clearIngredientErrors() {
  document.querySelectorAll('#ingredientForm .error-message').forEach(el => el.remove());
  document.querySelectorAll('#ingredientForm input, #ingredientForm select').forEach(field => {
    field.style.borderColor = '';
  });
}

async function deleteIngredient(id) {
  let usage = 0;
  recettesDB.forEach(rec => {
    if (rec.ingredients && Array.isArray(rec.ingredients)) {
      if (rec.ingredients.some(i => (i.ingredient_id === id) || (i.idIng === id) || (i.id === id))) {
        usage++;
      }
    }
  });
  
  let confirmMessage = usage > 0 
    ? `Cet ingrédient est utilisé dans ${usage} recette(s). Êtes-vous sûr de vouloir le supprimer ?`
    : 'Êtes-vous sûr de vouloir supprimer cet ingrédient ?';
  
  if (!confirm(confirmMessage)) return;
  
  const formData = new FormData();
  formData.append('action', 'deleteIngredient');
  formData.append('id', id);
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message || 'Ingrédient supprimé');
      loadData();
    } else {
      showToast(result.message || 'Échec de la suppression', true);
    }
  } catch (error) {
    console.error('Delete error:', error);
    showToast('Erreur lors de la suppression', true);
  }
}

async function deleteReview(recipeId, reviewId) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')) return;
  
  const formData = new FormData();
  formData.append('action', 'deleteReview');
  formData.append('recipeId', recipeId);
  formData.append('id', reviewId);
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message || 'Avis supprimé');
      loadData();
    } else {
      showToast(result.message || 'Échec de la suppression', true);
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la suppression', true);
  }
}

// ========== GESTION AVIS ==========
function renderReviewsTable() {
  const tbody = document.getElementById('reviewsTableBody');
  if (!tbody) return;
  
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
  
  if (allReviews.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Aucun avis trouvé</td></tr>';
    return;
  }
  
  tbody.innerHTML = allReviews.map(avis => `
    <tr>
      <td>${escapeHtml(avis.utilisateur)}</td>
      <td>${escapeHtml(avis.recette)}</td>
      <td>${renderStars(avis.note)}</td>
      <td>${escapeHtml(avis.commentaire)}</td>
      <td class="action-icons">
        <i class="fas fa-trash delete-icon" onclick="deleteReview(${avis.recipeId}, ${avis.reviewId})" style="cursor:pointer; color:#ef5350; margin:0 5px;"></i>
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
  Object.values(contents).forEach(content => {
    if (content) content.style.display = 'none';
  });
  if (contents[tabId]) contents[tabId].style.display = 'block';
}

// ========== FORM SUBMISSION HANDLERS AVEC VALIDATION ==========

// Recipe Form Submission avec validation complète
document.getElementById('recipeForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  // Réinitialiser les erreurs
  clearRecipeErrors();
  
  // Récupérer les valeurs
  const titre = document.getElementById('recipeTitle').value.trim();
  const instruction = document.getElementById('recipeInstructions').value.trim();
  const temp = document.getElementById('recipeTime').value;
  const difficulte = document.getElementById('recipeDifficulty').value;
  const ecoScore = document.getElementById('recipeEcoScore').value;
  
  // VALIDATION TITRE
  const titleValidation = validateRecipeTitle(titre);
  if (!titleValidation.valid) {
    showFieldError('recipeTitle', titleValidation.message);
    showToast(titleValidation.message, true);
    document.getElementById('recipeTitle').focus();
    return;
  }
  
  // VALIDATION INSTRUCTIONS
  const instructionsValidation = validateRecipeInstructions(instruction);
  if (!instructionsValidation.valid) {
    showFieldError('recipeInstructions', instructionsValidation.message);
    showToast(instructionsValidation.message, true);
    document.getElementById('recipeInstructions').focus();
    return;
  }
  
  // VALIDATION TEMPS
  const timeValidation = validateRecipeTime(temp);
  if (!timeValidation.valid) {
    showFieldError('recipeTime', timeValidation.message);
    showToast(timeValidation.message, true);
    document.getElementById('recipeTime').focus();
    return;
  }
  
  // VALIDATION DIFFICULTE
  const diffValidation = validateDifficulty(difficulte);
  if (!diffValidation.valid) {
    showToast(diffValidation.message, true);
    return;
  }
  
  // VALIDATION ECO-SCORE
  const ecoValidation = validateEcoScore(ecoScore);
  if (!ecoValidation.valid) {
    showToast(ecoValidation.message, true);
    return;
  }
  
  // Collecter et valider les ingrédients
  const ingredients = [];
  let ingredientError = null;
  
  document.querySelectorAll('#ingredientsListContainer .ingredient-row').forEach((row, index) => {
    const ingredientId = row.querySelector('.ingredient-select').value;
    const qty = row.querySelector('.ingredient-qty').value;
    const unite = row.querySelector('.ingredient-unite').value;
    
    if (ingredientId && qty) {
      ingredients.push({
        idIng: parseInt(ingredientId),
        qty: parseFloat(qty),
        unite: unite || 'g'
      });
    }
  });
  
  // VALIDATION INGREDIENTS
  const ingredientsValidation = validateIngredients(ingredients);
  if (!ingredientsValidation.valid) {
    showToast(ingredientsValidation.message, true);
    return;
  }
  
  // Préparer et envoyer la requête
  const recipeId = document.getElementById('recipeId').value;
  const formData = new FormData();
  formData.append('titre', titre);
  formData.append('instruction', instruction);
  formData.append('temp', parseInt(temp));
  formData.append('difficulte', difficulte);
  formData.append('ecoScore', ecoScore);
  formData.append('ingredients', JSON.stringify(ingredients));
  formData.append('utilisateurId', 1);
  
  if (recipeId) {
    formData.append('action', 'updateRecipe');
    formData.append('id', recipeId);
  } else {
    formData.append('action', 'createRecipe');
  }
  
  try {
    const response = await fetch('INDEX.php', { method: 'POST', body: formData });
    const responseText = await response.text();
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message || 'Recette sauvegardée');
      document.getElementById('recipeModal').style.display = 'none';
      loadData();
    } else {
      showToast(result.message || 'Erreur inconnue', true);
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la sauvegarde', true);
  }
});

// Ingredient Form Submission avec validation
document.getElementById('ingredientForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  clearIngredientErrors();
  
  const nom = document.getElementById('ingredientName').value.trim();
  const calories = document.getElementById('ingredientCalories').value.trim();
  const ecoScore = document.getElementById('ingredientEcoScore').value;
  
  // VALIDATION NOM
  const nameValidation = validateIngredientName(nom);
  if (!nameValidation.valid) {
    showToast(nameValidation.message, true);
    document.getElementById('ingredientName').focus();
    return;
  }
  
  // VALIDATION CALORIES (optionnelle)
  if (calories) {
    const caloriesValidation = validateCalories(calories);
    if (!caloriesValidation.valid) {
      showToast(caloriesValidation.message, true);
      document.getElementById('ingredientCalories').focus();
      return;
    }
  }
  
  const ingredientId = document.getElementById('ingredientId').value;
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
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (e) {
      showToast('Erreur serveur: Réponse invalide', true);
      return;
    }
    
    if (result.success) {
      showToast(result.message || 'Ingrédient sauvegardé');
      document.getElementById('ingredientModal').style.display = 'none';
      loadData();
    } else {
      showToast(result.message || 'Erreur inconnue', true);
    }
  } catch (error) {
    console.error('Erreur:', error);
    showToast('Erreur lors de la sauvegarde', true);
  }
});

// ========== MODAL CLOSE & WINDOW CLICK ==========
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
    <input type="number" class="ingredient-qty" value="100" step="0.01" min="0.01" max="9999">
    <input type="text" class="ingredient-unite" value="g" placeholder="g, ml..." maxlength="20">
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
  if (e.target.classList && e.target.classList.contains('ingredient-remove')) {
    e.preventDefault();
    const row = e.target.closest('.ingredient-row');
    if (row) row.remove();
  }
});

window.onclick = (e) => {
  const recipeModal = document.getElementById('recipeModal');
  const ingredientModal = document.getElementById('ingredientModal');
  if (e.target === recipeModal) recipeModal.style.display = 'none';
  if (e.target === ingredientModal) ingredientModal.style.display = 'none';
};

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', () => {
  console.log('DOM loaded, initializing...');
  loadData();
  showTab('dashboard');
  
  const addRecipeBtn = document.getElementById('addRecipeBtn');
  const addIngredientBtn = document.getElementById('addIngredientBtn');
  const globalAddBtn = document.getElementById('globalAddBtn');
  
  if (addRecipeBtn) addRecipeBtn.addEventListener('click', () => openRecipeModal());
  if (addIngredientBtn) addIngredientBtn.addEventListener('click', () => openIngredientModal());
  if (globalAddBtn) globalAddBtn.addEventListener('click', () => openRecipeModal());
  
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
      document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
      this.classList.add('active');
      showTab(this.dataset.tab);
    });
  });
});