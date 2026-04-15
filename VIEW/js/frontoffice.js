// ========== FRONTOFFICE DATA & INITIALIZATION ==========
// This file loads data from the API and handles UI interactions

let recipesDB = [];
let ingredientsDB = [];
let currentUser = "Sophie Martin";

// ========== FETCH DATA FROM SERVER ==========
async function loadData() {
  try {
    // Load recipes from API endpoint
    const recipesResponse = await fetch('INDEX.php?action=getAllRecipes');
    recipesDB = await recipesResponse.json();
    
    // Load ingredients from API endpoint
    const ingredientsResponse = await fetch('INDEX.php?action=getAllIngredients');
    ingredientsDB = await ingredientsResponse.json();
    
    renderRecipes();
  } catch (error) {
    console.error('Erreur de chargement des données:', error);
    // Fallback: use data embedded in the page or show error
  }
}

// ========== FONCTIONS UTILITAIRES ==========
function getIngredientName(id) {
  let ing = ingredientsDB.find(i => i.id === id);
  return ing ? ing.nom : "?";
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

function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toastMessage');
  toastMessage.textContent = message;
  toast.style.background = isError ? '#d32f2f' : 'var(--vert-kool-dark)';
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

// Calculer la moyenne des notes pour une recette
function getAverageRating(recette) {
  if (!recette.avis || recette.avis.length === 0) return null;
  const sum = recette.avis.reduce((acc, a) => acc + a.note, 0);
  return (sum / recette.avis.length).toFixed(1);
}

// Rendu des étoiles
function renderStars(note) {
  let stars = '';
  for (let i = 1; i <= 5; i++) {
    stars += `<i class="fas fa-star" style="color: ${i <= note ? '#ffc107' : '#ddd'}; font-size: 0.7rem;"></i>`;
  }
  return stars;
}

// ========== RENDU DES RECETTES ==========
function renderRecipes() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const diffFilter = document.getElementById('difficultyFilter').value;
  const ecoFilter = document.getElementById('ecoScoreFilter').value;
  const timeFilter = parseInt(document.getElementById('timeFilter').value);

  let filtered = recipesDB.filter(rec => {
    const matchTitle = rec.titre.toLowerCase().includes(searchTerm);
    const matchIng = rec.ingredients && rec.ingredients.some(ing => (ing.nom || '').toLowerCase().includes(searchTerm));
    const matchSearch = matchTitle || matchIng;
    const matchDiff = diffFilter === "" || rec.difficulte === diffFilter;
    const matchEco = ecoFilter === "" || rec.eco_score === ecoFilter;
    const matchTime = !timeFilter || rec.temps_preparation <= timeFilter;
    return matchSearch && matchDiff && matchEco && matchTime;
  });

  const container = document.getElementById('recipesContainer');
  if (!container) return;

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>Aucune recette trouvée</h3>
        <p>Essayez d'autres critères de recherche</p>
      </div>
    `;
    return;
  }

  container.innerHTML = filtered.map(rec => {
    const avgRating = getAverageRating(rec);
    const ingredientsHtml = (rec.ingredients || []).map(ing => {
      return `<span class="ingredient-item">${escapeHtml(ing.nom || '')} (${ing.quantite}${ing.unite})</span>`;
    }).join('');

    const reviewsHtml = rec.avis && rec.avis.length > 0 ? rec.avis.slice(0, 2).map(avis => `
      <div class="review-item">
        <div class="review-user">
          <strong>${escapeHtml(avis.utilisateur_nom)}</strong>
          <span class="review-stars">${renderStars(avis.note)}</span>
        </div>
        <div class="review-comment">${escapeHtml(avis.commentaire)}</div>
      </div>
    `).join('') : '';

    return `
      <div class="recipe-card">
        <div class="recipe-header">
          <h3>${escapeHtml(rec.titre)}</h3>
          <span class="eco-badge">${escapeHtml(rec.eco_score)}</span>
        </div>
        <div class="recipe-body">
          <div class="recipe-meta">
            <span><i class="fas fa-clock"></i> ${rec.temps_preparation} min</span>
            <span><i class="fas fa-chart-bar"></i> ${rec.difficulte}</span>
          </div>
          <p class="recipe-instruction">${escapeHtml(rec.instruction)}</p>
          <div class="ingredients-list">
            <strong><i class="fas fa-carrot"></i> Ingrédients:</strong>
            ${ingredientsHtml}
          </div>
          ${rec.avis && rec.avis.length > 0 ? `
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

  // Attacher événements aux boutons d'avis
  document.querySelectorAll('.btn-review').forEach(btn => {
    btn.addEventListener('click', function() {
      openReviewModal(parseInt(this.dataset.recipeId));
    });
  });
}

// ========== MODAL AVIS ==========
let currentReviewRecipeId = null;
let currentRecipeTitle = null;

function openReviewModal(recipeId) {
  const recipe = recipesDB.find(r => r.id === recipeId);
  if (!recipe) return;
  
  currentReviewRecipeId = recipeId;
  currentRecipeTitle = recipe.titre;
  
  document.getElementById('reviewRecipeTitle').textContent = recipe.titre;
  document.getElementById('selectedRating').value = '0';
  document.getElementById('reviewComment').value = '';
  
  // Réinitialiser les étoiles
  document.querySelectorAll('.star-rating').forEach(star => {
    star.classList.remove('active');
    star.innerHTML = '<i class="far fa-star"></i>';
  });
  
  document.getElementById('reviewModal').style.display = 'flex';
}

// Gestion des étoiles
document.querySelectorAll('.star-rating').forEach(star => {
  star.addEventListener('click', function() {
    const value = this.dataset.value;
    document.getElementById('selectedRating').value = value;
    
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

// Soumettre l'avis
document.getElementById('submitReviewBtn').addEventListener('click', () => {
  const rating = parseInt(document.getElementById('selectedRating').value);
  const comment = document.getElementById('reviewComment').value.trim();
  
  if (rating === 0) {
    showToast('Veuillez sélectionner une note', true);
    return;
  }
  
  if (!comment) {
    showToast('Veuillez entrer un commentaire', true);
    return;
  }
  
  const recipe = recipesDB.find(r => r.id === currentReviewRecipeId);
  if (recipe) {
    recipe.avis.push({
      id: recipe.avis.length + 1,
      utilisateur: currentUser,
      note: rating,
      commentaire: comment
    });
    showToast('Avis enregistré avec succès!');
    renderRecipes();
  }
  
  document.getElementById('reviewModal').style.display = 'none';
});

// Fermeture modale
document.getElementById('closeReviewModal').onclick = () => {
  document.getElementById('reviewModal').style.display = 'none';
};
document.getElementById('cancelReviewBtn').onclick = () => {
  document.getElementById('reviewModal').style.display = 'none';
};
window.onclick = (e) => {
  if (e.target === document.getElementById('reviewModal')) {
    document.getElementById('reviewModal').style.display = 'none';
  }
};

// ========== FILTRES ==========
document.getElementById('resetFiltersBtn').addEventListener('click', () => {
  document.getElementById('searchInput').value = '';
  document.getElementById('difficultyFilter').value = '';
  document.getElementById('ecoScoreFilter').value = '';
  document.getElementById('timeFilter').value = '';
  renderRecipes();
});

document.getElementById('searchInput').addEventListener('input', () => renderRecipes());
document.getElementById('difficultyFilter').addEventListener('change', () => renderRecipes());
document.getElementById('ecoScoreFilter').addEventListener('change', () => renderRecipes());
document.getElementById('timeFilter').addEventListener('change', () => renderRecipes());

// ========== INITIALISATION ==========
// Load data on page load
document.addEventListener('DOMContentLoaded', () => {
  loadData();
});
