// ========== FRONTOFFICE DATA & INITIALIZATION ==========

let recipesDB = [];
let ingredientsDB = [];
let currentUser = "Sophie Martin";

// ========== FETCH DATA FROM SERVER ==========
async function loadData() {
  try {
    console.log('Loading frontoffice data...');
    
    const recipesResponse = await fetch('INDEX.php?action=getAllRecipes');
    if (!recipesResponse.ok) {
      throw new Error(`HTTP ${recipesResponse.status}`);
    }
    recipesDB = await recipesResponse.json();
    console.log('Recipes loaded:', recipesDB.length);
    
    const ingredientsResponse = await fetch('INDEX.php?action=getAllIngredients');
    if (!ingredientsResponse.ok) {
      throw new Error(`HTTP ${ingredientsResponse.status}`);
    }
    ingredientsDB = await ingredientsResponse.json();
    console.log('Ingredients loaded:', ingredientsDB.length);
    
    renderRecipes();
  } catch (error) {
    console.error('Erreur de chargement des donnees:', error);
    const container = document.getElementById('recipesContainer');
    if (container) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-exclamation-triangle"></i>
          <h3>Erreur de chargement</h3>
          <p>Impossible de charger les recettes. Verifiez que la base de donnees est installee.</p>
        </div>
      `;
    }
  }
}

// ========== FONCTIONS UTILITAIRES ==========
function getIngredientName(id) {
  let ing = ingredientsDB.find(i => i.id === id);
  return ing ? ing.nom : "?";
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

function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toastMessage');
  if (!toast) return;
  toastMessage.textContent = message;
  toast.style.background = isError ? '#d32f2f' : 'var(--vert-kool-dark)';
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

function getAverageRating(recette) {
  if (!recette.avis || recette.avis.length === 0) return null;
  const sum = recette.avis.reduce((acc, a) => acc + a.note, 0);
  return (sum / recette.avis.length).toFixed(1);
}

function renderStars(note) {
  let stars = '';
  for (let i = 1; i <= 5; i++) {
    stars += `<i class="fas fa-star" style="color: ${i <= note ? '#ffc107' : '#ddd'}; font-size: 0.7rem;"></i>`;
  }
  return stars;
}

// ========== VALIDATION DES AVIS ==========
function validateRating(note) {
  const rating = parseInt(note);
  if (isNaN(rating)) {
    return { valid: false, message: 'Veuillez selectionner une note' };
  }
  if (rating < 1 || rating > 5) {
    return { valid: false, message: 'La note doit etre comprise entre 1 et 5' };
  }
  return { valid: true, message: '' };
}

function validateComment(commentaire) {
  if (!commentaire || commentaire.trim() === '') {
    return { valid: false, message: 'Le commentaire est requis' };
  }
  if (commentaire.length < 3) {
    return { valid: false, message: 'Le commentaire doit contenir au moins 3 caracteres' };
  }
  if (commentaire.length > 1000) {
    return { valid: false, message: 'Le commentaire ne peut pas depasser 1000 caracteres' };
  }
  return { valid: true, message: '' };
}

// ========== RENDU DES RECETTES ==========
function renderRecipes() {
  const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
  const diffFilter = document.getElementById('difficultyFilter')?.value || '';
  const ecoFilter = document.getElementById('ecoScoreFilter')?.value || '';
  const timeFilter = parseInt(document.getElementById('timeFilter')?.value) || 0;

  let filtered = recipesDB.filter(rec => {
    const matchTitle = rec.titre?.toLowerCase().includes(searchTerm) || false;
    const matchIng = rec.ingredients && Array.isArray(rec.ingredients) && rec.ingredients.some(ing => 
      (ing.nom || '').toLowerCase().includes(searchTerm)
    );
    const matchSearch = matchTitle || matchIng;
    const matchDiff = diffFilter === "" || rec.difficulte === diffFilter;
    const matchEco = ecoFilter === "" || rec.eco_score === ecoFilter;
    const matchTime = !timeFilter || (rec.temps_preparation || 0) <= timeFilter;
    return matchSearch && matchDiff && matchEco && matchTime;
  });

  const container = document.getElementById('recipesContainer');
  if (!container) return;

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>Aucune recette trouvee</h3>
        <p>Essayez d'autres criteres de recherche</p>
      </div>
    `;
    return;
  }

  container.innerHTML = filtered.map(rec => {
    const avgRating = getAverageRating(rec);
    
    let ingredientsHtml = '';
    if (rec.ingredients && Array.isArray(rec.ingredients) && rec.ingredients.length > 0) {
      ingredientsHtml = rec.ingredients.map(ing => {
        const nom = ing.nom || '?';
        const qty = ing.quantite || ing.qty || 0;
        const unite = ing.unite || 'g';
        return `<span class="ingredient-item">${escapeHtml(nom)} (${qty}${unite})</span>`;
      }).join('');
    } else {
      ingredientsHtml = '<span class="ingredient-item">Aucun ingredient liste</span>';
    }

    let reviewsHtml = '';
    if (rec.avis && Array.isArray(rec.avis) && rec.avis.length > 0) {
      reviewsHtml = rec.avis.slice(0, 2).map(avis => `
        <div class="review-item">
          <div class="review-user">
            <strong>${escapeHtml(avis.utilisateur_nom || avis.utilisateur || 'Anonyme')}</strong>
            <span class="review-stars">${renderStars(avis.note)}</span>
          </div>
          <div class="review-comment">${escapeHtml(avis.commentaire || '')}</div>
        </div>
      `).join('');
    }

    return `
      <div class="recipe-card">
        <div class="recipe-header">
          <h3>${escapeHtml(rec.titre)}</h3>
          <span class="eco-badge">${escapeHtml(rec.eco_score || 'A')}</span>
        </div>
        <div class="recipe-body">
          <div class="recipe-meta">
            <span><i class="fas fa-clock"></i> ${rec.temps_preparation || 0} min</span>
            <span><i class="fas fa-chart-bar"></i> ${rec.difficulte || 'N/A'}</span>
          </div>
          <p class="recipe-instruction">${escapeHtml(rec.instruction || '')}</p>
          <div class="ingredients-list">
            <strong><i class="fas fa-carrot"></i> Ingredients:</strong>
            <div>${ingredientsHtml}</div>
          </div>
          ${reviewsHtml ? `
            <div class="reviews-section">
              <div class="reviews-header">
                <span class="average-rating">
                  <i class="fas fa-star"></i> ${avgRating}/5 (${rec.avis.length})
                </span>
              </div>
              ${reviewsHtml}
            </div>
          ` : ''}
          <button class="btn-review" data-recipe-id="${rec.id}">
            <i class="fas fa-star"></i> Donner mon avis
          </button>
        </div>
      </div>
    `;
  }).join('');

  document.querySelectorAll('.btn-review').forEach(btn => {
    btn.removeEventListener('click', handleReviewClick);
    btn.addEventListener('click', handleReviewClick);
  });
}

function handleReviewClick(e) {
  const recipeId = parseInt(this.dataset.recipeId);
  openReviewModal(recipeId);
}

// ========== MODAL AVIS ==========
let currentReviewRecipeId = null;

function openReviewModal(recipeId) {
  const recipe = recipesDB.find(r => r.id === recipeId);
  if (!recipe) return;
  
  currentReviewRecipeId = recipeId;
  
  const titleEl = document.getElementById('reviewRecipeTitle');
  if (titleEl) titleEl.textContent = recipe.titre;
  
  const ratingInput = document.getElementById('selectedRating');
  if (ratingInput) ratingInput.value = '0';
  
  const commentEl = document.getElementById('reviewComment');
  if (commentEl) commentEl.value = '';
  
  document.querySelectorAll('.star-rating').forEach(star => {
    star.classList.remove('active');
    star.innerHTML = '<i class="far fa-star"></i>';
  });
  
  const modal = document.getElementById('reviewModal');
  if (modal) modal.style.display = 'flex';
}

// Gestion des etoiles
document.querySelectorAll('.star-rating').forEach(star => {
  star.addEventListener('click', function() {
    const value = parseInt(this.dataset.value);
    const ratingInput = document.getElementById('selectedRating');
    if (ratingInput) ratingInput.value = value;
    
    document.querySelectorAll('.star-rating').forEach((s, index) => {
      if (index < value) {
        s.classList.add('active');
        s.innerHTML = '<i class="fas fa-star"></i>';
      } else {
        s.classList.remove('active');
        s.innerHTML = '<i class="far fa-star"></i>';
      }
    });
  });
});

// Soumettre l'avis avec validation
const submitBtn = document.getElementById('submitReviewBtn');
if (submitBtn) {
  submitBtn.addEventListener('click', async () => {
    const rating = document.getElementById('selectedRating')?.value || '0';
    const comment = document.getElementById('reviewComment')?.value.trim() || '';
    
    // Validation de la note
    const ratingValidation = validateRating(rating);
    if (!ratingValidation.valid) {
      showToast(ratingValidation.message, true);
      return;
    }
    
    // Validation du commentaire
    const commentValidation = validateComment(comment);
    if (!commentValidation.valid) {
      showToast(commentValidation.message, true);
      return;
    }
    
    const formData = new FormData();
    formData.append('action', 'addReview');
    formData.append('recipeId', currentReviewRecipeId);
    formData.append('utilisateur', currentUser);
    formData.append('note', parseInt(rating));
    formData.append('commentaire', comment);
    
    try {
      const response = await fetch('INDEX.php', { method: 'POST', body: formData });
      const result = await response.json();
      
      if (result.success) {
        showToast('Avis envoye avec succes !');
        await loadData();
      } else {
        showToast(result.message || 'Erreur lors de l\'envoi', true);
      }
    } catch (error) {
      console.error('Erreur:', error);
      showToast('Erreur lors de l\'envoi de l\'avis', true);
    }
    
    document.getElementById('reviewModal').style.display = 'none';
  });
}

// Fermeture modale
const closeReviewBtn = document.getElementById('closeReviewModal');
const cancelReviewBtn = document.getElementById('cancelReviewBtn');

if (closeReviewBtn) {
  closeReviewBtn.onclick = () => {
    document.getElementById('reviewModal').style.display = 'none';
  };
}
if (cancelReviewBtn) {
  cancelReviewBtn.onclick = () => {
    document.getElementById('reviewModal').style.display = 'none';
  };
}

window.onclick = (e) => {
  const modal = document.getElementById('reviewModal');
  if (e.target === modal) {
    modal.style.display = 'none';
  }
};

// ========== FILTRES ==========
const resetBtn = document.getElementById('resetFiltersBtn');
if (resetBtn) {
  resetBtn.addEventListener('click', () => {
    const searchInput = document.getElementById('searchInput');
    const diffFilter = document.getElementById('difficultyFilter');
    const ecoFilter = document.getElementById('ecoScoreFilter');
    const timeFilter = document.getElementById('timeFilter');
    
    if (searchInput) searchInput.value = '';
    if (diffFilter) diffFilter.value = '';
    if (ecoFilter) ecoFilter.value = '';
    if (timeFilter) timeFilter.value = '';
    renderRecipes();
  });
}

const searchInput = document.getElementById('searchInput');
if (searchInput) searchInput.addEventListener('input', () => renderRecipes());

const diffFilter = document.getElementById('difficultyFilter');
if (diffFilter) diffFilter.addEventListener('change', () => renderRecipes());

const ecoFilter = document.getElementById('ecoScoreFilter');
if (ecoFilter) ecoFilter.addEventListener('change', () => renderRecipes());

const timeFilter = document.getElementById('timeFilter');
if (timeFilter) timeFilter.addEventListener('change', () => renderRecipes());

// ========== INITIALISATION ==========
document.addEventListener('DOMContentLoaded', () => {
  console.log('Frontoffice DOM loaded');
  loadData();
});