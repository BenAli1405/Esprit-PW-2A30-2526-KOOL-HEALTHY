// ========== KOOL HEALTHY - SIMPLIFIED ==========
// Features: Similar Recipes, Goal Matching, Weekly Plan, Nutrition Bars, User Learning

let recipesDB = [];
let currentGoal = "";
let userPreferences = {
    savedRecipes: [],
    viewedRecipes: [],
    ratings: {}
};

const GOAL_LABELS = {
    perte_de_poids: 'Perte de poids',
    musculation: 'Musculation',
    equilibre: 'Équilibre',
    prise_de_poids: 'Prendre du poids'
};

// Calorie ranges for goals
const GOAL_CALORIES = {
    perte_de_poids: { min: 0, max: 600 },
    equilibre: { min: 600, max: 800 },
    prise_de_poids: { min: 800, max: 2000 }
};

// Load/Save preferences
function loadPrefs() {
    const saved = localStorage.getItem('kool_prefs');
    if (saved) userPreferences = JSON.parse(saved);
}
function savePrefs() { localStorage.setItem('kool_prefs', JSON.stringify(userPreferences)); }

// ========== DATA LOADING ==========
async function loadData() {
    try {
        const res = await fetch('INDEX.php?action=getAllRecipes');
        recipesDB = await res.json();
        recipesDB = recipesDB.map(r => ({ ...r, nutritionScore: calcNutritionScore(r) }));
        renderRecipes();
    } catch (e) {
        document.getElementById('recipesContainer').innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Erreur de chargement</h3></div>';
    }
}

// ========== GOAL MATCHING ==========
function getGoalMatch(recipe) {
    const kcal = recipe.nutrition?.calories || 0;
    const protein = recipe.nutrition?.proteines || 0;
    
    const matches = {
        perte_de_poids: kcal < 600,
        musculation: protein >= 20,
        equilibre: kcal >= 600 && kcal <= 800,
        prise_de_poids: kcal > 800
    };
    
    let bestGoal = null, bestScore = 0;
    for (const [goal, matched] of Object.entries(matches)) {
        if (matched) {
            let score = 100;
            if (goal === 'perte_de_poids') score = Math.max(0, 100 - (kcal / 6));
            if (goal === 'prise_de_poids') score = Math.min(100, (kcal - 800) / 12);
            if (score > bestScore) { bestScore = score; bestGoal = goal; }
        }
    }
    return { matches, bestGoal, bestScore };
}

// ========== NUTRITION SCORE ==========
function calcNutritionScore(recipe) {
    const n = recipe.nutrition || {};
    let score = 0;
    if (n.proteines >= 20) score += 30;
    else if (n.proteines >= 15) score += 20;
    else if (n.proteines >= 10) score += 10;
    
    if (n.fibres >= 8) score += 25;
    else if (n.fibres >= 5) score += 15;
    
    const variety = new Set(recipe.ingredients?.map(i => i.nom) || []).size;
    if (variety >= 6) score += 25;
    else if (variety >= 4) score += 15;
    
    const ecoBonus = { 'A+': 20, 'A': 15, 'B': 10, 'C': 5, 'D': 0, 'E': 0 };
    score += ecoBonus[recipe.eco_score] || 0;
    return Math.min(100, score);
}

// ========== SIMILAR RECIPES ==========
function getSimilarRecipes(recipe, limit = 4) {
    const target = recipe.nutrition || {};
    return recipesDB.filter(r => r.id !== recipe.id)
        .map(r => {
            const n = r.nutrition || {};
            const diff = Math.abs((target.calories || 0) - (n.calories || 0)) +
                        Math.abs((target.proteines || 0) - (n.proteines || 0)) * 10;
            return { recipe: r, similarity: Math.max(0, 100 - diff / 10) };
        })
        .sort((a, b) => b.similarity - a.similarity)
        .slice(0, limit);
}

function showSimilarRecipes(recipeId) {
    const recipe = recipesDB.find(r => r.id === recipeId);
    const similar = getSimilarRecipes(recipe);
    
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.innerHTML = `
        <div class="modal-content modal-large">
            <div class="modal-header"><h3><i class="fas fa-magic"></i> Recettes similaires</h3><span class="close-modal">&times;</span></div>
            <div class="modal-body">
                <div class="similar-grid">${similar.map(s => `
                    <div class="similar-card" onclick="scrollToRecipe(${s.recipe.id})">
                        <h4>${escapeHtml(s.recipe.titre)}</h4>
                        <div class="similar-meta">🔍 ${Math.round(s.similarity)}% · ${s.recipe.eco_score}</div>
                        <small>${s.recipe.temps_preparation || 0} min · ${s.recipe.difficulte}</small>
                    </div>
                `).join('')}</div>
            </div>
        </div>
    `;
    modal.querySelector('.close-modal').onclick = () => modal.remove();
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    document.body.appendChild(modal);
}

// ========== WEEKLY PLAN (3 meals/day, 7 days) ==========
function showWeeklyPlan() {
    if (!currentGoal) {
        showToast("Sélectionnez d'abord un objectif", true);
        return;
    }
    
    const matchingRecipes = recipesDB.filter(r => getGoalMatch(r).matches[currentGoal]);
    if (matchingRecipes.length === 0) {
        showToast(`Aucune recette pour "${GOAL_LABELS[currentGoal]}"`, true);
        return;
    }
    
    // Split by meal type
    const breakfasts = matchingRecipes.filter(r => (r.nutrition?.calories || 0) <= 450);
    const lunches = matchingRecipes.filter(r => (r.nutrition?.proteines || 0) >= 15);
    const dinners = matchingRecipes.filter(r => true);
    
    const getMeal = (arr, idx) => arr[idx % arr.length] || matchingRecipes[idx % matchingRecipes.length];
    
    const weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    const meals = ['breakfast', 'lunch', 'dinner'];
    const mealIcons = { breakfast: '🌅', lunch: '☀️', dinner: '🌙' };
    const mealNames = { breakfast: 'Petit-déjeuner', lunch: 'Déjeuner', dinner: 'Dîner' };
    
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.innerHTML = `
        <div class="modal-content modal-large" style="max-width:700px">
            <div class="modal-header"><h3><i class="fas fa-calendar-week"></i> Plan ${GOAL_LABELS[currentGoal]}</h3><span class="close-modal">&times;</span></div>
            <div class="modal-body" style="max-height:70vh;overflow-y:auto">
                ${weekDays.map((day, i) => `
                    <div class="plan-day">
                        <div class="plan-day-header"><strong>${day}</strong></div>
                        ${meals.map(meal => {
                            const recipe = getMeal(meal === 'breakfast' ? breakfasts : meal === 'lunch' ? lunches : dinners, i);
                            return `
                                <div class="plan-meal" onclick="scrollToRecipe(${recipe.id})">
                                    <div class="plan-meal-icon">${mealIcons[meal]}</div>
                                    <div class="plan-meal-content">
                                        <div class="plan-meal-title">${mealNames[meal]}</div>
                                        <div class="plan-meal-name">${escapeHtml(recipe.titre)}</div>
                                        <div class="plan-meal-meta">
                                            ${recipe.temps_preparation || 0} min · ${Math.round(recipe.nutrition?.calories || 0)} kcal · ${recipe.nutrition?.proteines || 0}g prot
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    modal.querySelector('.close-modal').onclick = () => modal.remove();
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    document.body.appendChild(modal);
}

// ========== RENDER RECIPES (with OLD CSS styles for nutrition bars & reviews) ==========
function renderRecipes() {
    const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const difficulty = document.getElementById('difficultyFilter')?.value || '';
    const eco = document.getElementById('ecoScoreFilter')?.value || '';
    const maxTime = parseInt(document.getElementById('timeFilter')?.value) || 0;
    
    let filtered = recipesDB.filter(r => {
        const matchSearch = r.titre.toLowerCase().includes(search) || r.ingredients?.some(i => i.nom.toLowerCase().includes(search));
        const matchDiff = !difficulty || r.difficulte === difficulty;
        const matchEco = !eco || r.eco_score === eco;
        const matchTime = !maxTime || (r.temps_preparation || 0) <= maxTime;
        return matchSearch && matchDiff && matchEco && matchTime;
    });
    
    if (currentGoal) {
        filtered = filtered.filter(r => getGoalMatch(r).matches[currentGoal]);
    }
    
    const container = document.getElementById('recipesContainer');
    if (!filtered.length) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><h3>Aucune recette</h3></div>';
        return;
    }
    
    container.innerHTML = filtered.map(r => {
        const n = r.nutrition || {};
        const goalMatch = getGoalMatch(r);
        const avgRating = r.avis?.length ? (r.avis.reduce((a,b) => a + b.note, 0) / r.avis.length).toFixed(1) : null;
        
        // Calculate percentages for nutrition bars (OLD CSS style)
        const proteinPercent = Math.min(100, (n.proteines || 0) / 40 * 100);
        const carbPercent = Math.min(100, (n.glucides || 0) / 60 * 100);
        const fatPercent = Math.min(100, (n.lipides || 0) / 30 * 100);
        
        // Build ingredients HTML (OLD CSS style)
        let ingredientsHtml = '';
        if (r.ingredients && r.ingredients.length > 0) {
            ingredientsHtml = r.ingredients.map(ing => {
                const qty = ing.quantite || ing.qty || 0;
                const unite = ing.unite || 'g';
                return `<span class="ingredient-tag">${escapeHtml(ing.nom)} (${qty}${unite})</span>`;
            }).join('');
        } else {
            ingredientsHtml = '<span class="ingredient-tag">Aucun ingrédient</span>';
        }
        
        // Build reviews HTML (OLD CSS style)
        let reviewsHtml = '';
        if (r.avis && r.avis.length > 0) {
            reviewsHtml = r.avis.slice(0, 2).map(avis => `
                <div class="review-item">
                    <div class="review-user">
                        <strong>${escapeHtml(avis.utilisateur_nom || avis.utilisateur || 'Anonyme')}</strong>
                        <span class="review-stars">${renderStars(avis.note)}</span>
                    </div>
                    <div class="review-comment">"${escapeHtml(avis.commentaire || '')}"</div>
                </div>
            `).join('');
            if (r.avis.length > 2) {
                reviewsHtml += `<div class="more-reviews">+${r.avis.length - 2} autre(s) avis</div>`;
            }
        }
        
        return `
            <div class="recipe-card" data-id="${r.id}">
                <div class="recipe-header">
                    <h3>${escapeHtml(r.titre)}</h3>
                    <div class="recipe-badges">
                        <span class="eco-badge">${r.eco_score || 'A'}</span>
                        <span class="nutrition-score-badge">🥗 ${r.nutritionScore}/100</span>
                    </div>
                </div>
                
                <div class="recipe-body">
                    <div class="recipe-meta">
                        <span><i class="fas fa-clock"></i> ${r.temps_preparation || 0} min</span>
                        <span><i class="fas fa-chart-bar"></i> ${r.difficulte || 'N/A'}</span>
                        ${avgRating ? `<span><i class="fas fa-star" style="color:#ffc107;"></i> ${avgRating}/5</span>` : ''}
                    </div>
                    
                    <!-- OLD CSS Nutrition Bars -->
                    <div class="nutrition-viz">
                        <div class="nutrition-bar-container">
                            <div class="nutrition-bar-label">💪 Protéines</div>
                            <div class="nutrition-bar"><div class="nutrition-bar-fill protein" style="width: ${proteinPercent}%"></div></div>
                            <div class="nutrition-bar-value">${n.proteines || 0}g</div>
                        </div>
                        <div class="nutrition-bar-container">
                            <div class="nutrition-bar-label">🍚 Glucides</div>
                            <div class="nutrition-bar"><div class="nutrition-bar-fill carbs" style="width: ${carbPercent}%"></div></div>
                            <div class="nutrition-bar-value">${n.glucides || 0}g</div>
                        </div>
                        <div class="nutrition-bar-container">
                            <div class="nutrition-bar-label">🥑 Lipides</div>
                            <div class="nutrition-bar"><div class="nutrition-bar-fill fat" style="width: ${fatPercent}%"></div></div>
                            <div class="nutrition-bar-value">${n.lipides || 0}g</div>
                        </div>
                    </div>
                    
                    <div class="recipe-description">
                        <i class="fas fa-quote-left"></i>
                        ${escapeHtml(r.instruction ? (r.instruction.length > 120 ? r.instruction.substring(0, 120) + '...' : r.instruction) : 'Aucune description')}
                    </div>
                    
                    <div class="ingredients-list">
                        <strong><i class="fas fa-carrot"></i> Ingrédients :</strong>
                        <div class="ingredients-tags">${ingredientsHtml}</div>
                    </div>
                    
                    ${reviewsHtml ? `<div class="reviews-section"><div class="reviews-header"><i class="fas fa-comments"></i> Avis récents</div>${reviewsHtml}</div>` : ''}
                    
                    <div class="recipe-actions">
                        <button class="btn-similar" data-id="${r.id}">
                            <i class="fas fa-magic"></i> Voir recettes similaires
                        </button>
                        <button class="btn-review" data-id="${r.id}">
                            <i class="fas fa-star"></i> Donner mon avis
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Attach events
    document.querySelectorAll('.btn-similar').forEach(btn => {
        btn.onclick = (e) => {
            e.stopPropagation();
            showSimilarRecipes(parseInt(btn.dataset.id));
        };
    });
    document.querySelectorAll('.btn-review').forEach(btn => {
        btn.onclick = (e) => {
            e.stopPropagation();
            openReviewModal(parseInt(btn.dataset.id));
        };
    });
    document.querySelectorAll('.recipe-card').forEach(card => {
        card.onclick = (e) => { 
            if (!e.target.closest('button')) {
                recordView(parseInt(card.dataset.id));
            }
        };
    });
}

// Helper function to render stars (OLD CSS style)
function renderStars(note) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        stars += `<i class="fas fa-star" style="color: ${i <= note ? '#ffc107' : '#ddd'}; font-size: 0.7rem;"></i>`;
    }
    return stars;
}

function recordView(id) {
    if (!userPreferences.viewedRecipes.includes(id)) {
        userPreferences.viewedRecipes.unshift(id);
        savePrefs();
    }
}

function scrollToRecipe(id) {
    document.querySelector(`.recipe-card[data-id="${id}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.querySelectorAll('.modal').forEach(m => m.remove());
}

// ========== REVIEW MODAL ==========
let currentReviewId = null;
function openReviewModal(id) {
    currentReviewId = id;
    const recipe = recipesDB.find(r => r.id === id);
    document.getElementById('reviewRecipeTitle').textContent = recipe.titre;
    document.getElementById('selectedRating').value = '0';
    document.getElementById('reviewComment').value = '';
    document.querySelectorAll('.star-rating').forEach((s, i) => {
        s.innerHTML = '<i class="far fa-star"></i>';
        s.classList.remove('active');
    });
    document.getElementById('reviewModal').style.display = 'flex';
}

document.querySelectorAll('.star-rating').forEach(star => {
    star.onclick = function() {
        const val = parseInt(this.dataset.value);
        document.getElementById('selectedRating').value = val;
        document.querySelectorAll('.star-rating').forEach((s, i) => {
            const active = i < val;
            s.classList.toggle('active', active);
            s.innerHTML = active ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
        });
    };
});

document.getElementById('submitReviewBtn')?.addEventListener('click', async () => {
    const rating = parseInt(document.getElementById('selectedRating').value);
    const comment = document.getElementById('reviewComment').value.trim();
    if (rating < 1 || rating > 5) { showToast('Sélectionnez une note', true); return; }
    if (comment.length < 3) { showToast('Commentaire trop court', true); return; }
    
    const fd = new FormData();
    fd.append('action', 'addReview');
    fd.append('recipeId', currentReviewId);
    fd.append('utilisateur', 'Visiteur');
    fd.append('note', rating);
    fd.append('commentaire', comment);
    
    const res = await fetch('INDEX.php', { method: 'POST', body: fd });
    const result = await res.json();
    if (result.success) {
        showToast('Avis envoyé !');
        userPreferences.ratings[currentReviewId] = rating;
        savePrefs();
        loadData();
    } else showToast('Erreur', true);
    document.getElementById('reviewModal').style.display = 'none';
});

document.getElementById('closeReviewModal')?.addEventListener('click', () => document.getElementById('reviewModal').style.display = 'none');
document.getElementById('cancelReviewBtn')?.addEventListener('click', () => document.getElementById('reviewModal').style.display = 'none');
window.onclick = (e) => { if (e.target === document.getElementById('reviewModal')) document.getElementById('reviewModal').style.display = 'none'; };

// ========== INIT ==========
function initGoalSelector() {
    document.querySelectorAll('.goal-btn').forEach(btn => {
        btn.onclick = () => {
            const goal = btn.dataset.goal;
            if (currentGoal === goal) { 
                currentGoal = ''; 
                btn.classList.remove('active'); 
            } else {
                currentGoal = goal;
                document.querySelectorAll('.goal-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
            renderRecipes();
        };
    });
}

document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
    ['searchInput', 'difficultyFilter', 'ecoScoreFilter', 'timeFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    currentGoal = '';
    document.querySelectorAll('.goal-btn').forEach(b => b.classList.remove('active'));
    renderRecipes();
});

['searchInput', 'difficultyFilter', 'ecoScoreFilter', 'timeFilter'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', renderRecipes);
    document.getElementById(id)?.addEventListener('change', renderRecipes);
});

document.getElementById('generatePlanBtn')?.addEventListener('click', showWeeklyPlan);

function showToast(msg, isError = false) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = msg;
    toast.style.background = isError ? '#d32f2f' : '#388E3C';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' })[m]);
}

loadPrefs();
initGoalSelector();
loadData();

// ========== NUTRITION CALCULATOR MODAL ==========

let selectedIngredients = [];
let ingredientsDBForCalc = [];

// Open calculator modal
function openCalculatorModal() {
    selectedIngredients = [];
    
    fetch('INDEX.php?action=getAllIngredients')
        .then(res => res.json())
        .then(data => {
            ingredientsDBForCalc = data;
            createCalculatorModal();
        })
        .catch(e => console.error('Failed to load ingredients', e));
}

function createCalculatorModal() {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'flex';
    modal.id = 'calculatorModal';
    modal.innerHTML = `
        <div class="modal-content calculator-modal">
            <div class="modal-header">
                <h3><i class="fas fa-calculator"></i> Calculateur nutritionnel</h3>
                <span class="close-modal" id="closeCalculatorModal">&times;</span>
            </div>
            <div class="modal-body calculator-container">
                <div class="calculator-header">
                    <p>Ajoutez des ingrédients pour calculer vos valeurs nutritionnelles</p>
                </div>
                
                <div class="calculator-grid">
                    <div class="calculator-ingredients">
                        <div class="ingredient-search">
                            <input type="text" id="calcIngredientSearch" placeholder="Rechercher un ingrédient..." class="search-input">
                        </div>
                        <div id="calcIngredientsList" class="calculator-ingredients-list"></div>
                        <div class="selected-ingredients">
                            <h4><i class="fas fa-shopping-cart"></i> Votre sélection</h4>
                            <div id="selectedIngredientsContainer" class="selected-ingredients-list"></div>
                        </div>
                    </div>
                    
                    <div class="calculator-results">
                        <div class="nutrition-summary">
                            <h4><i class="fas fa-chart-line"></i> Valeurs nutritionnelles</h4>
                            <div class="macro-circles">
                                <div class="macro-circle"><div class="circle-value" id="totalCalories">0</div><div class="circle-label">Calories</div><div class="circle-unit">kcal</div></div>
                                <div class="macro-circle"><div class="circle-value" id="totalProtein">0</div><div class="circle-label">Protéines</div><div class="circle-unit">g</div></div>
                                <div class="macro-circle"><div class="circle-value" id="totalCarbs">0</div><div class="circle-label">Glucides</div><div class="circle-unit">g</div></div>
                                <div class="macro-circle"><div class="circle-value" id="totalFat">0</div><div class="circle-label">Lipides</div><div class="circle-unit">g</div></div>
                            </div>
                            
                            <div class="macro-bars">
                                <div class="macro-bar-item"><span class="macro-label">🥩 Protéines</span><div class="macro-bar"><div class="macro-fill protein" id="proteinBar" style="width:0%"></div></div><span class="macro-percent" id="proteinPercent">0%</span></div>
                                <div class="macro-bar-item"><span class="macro-label">🍚 Glucides</span><div class="macro-bar"><div class="macro-fill carbs" id="carbsBar" style="width:0%"></div></div><span class="macro-percent" id="carbsPercent">0%</span></div>
                                <div class="macro-bar-item"><span class="macro-label">🥑 Lipides</span><div class="macro-bar"><div class="macro-fill fat" id="fatBar" style="width:0%"></div></div><span class="macro-percent" id="fatPercent">0%</span></div>
                            </div>
                            
                            <div class="goal-comparison">
                                <h4><i class="fas fa-bullseye"></i> Comparaison avec votre objectif</h4>
                                <div id="goalFeedbackMessage"></div>
                            </div>
                            
                            <div class="calculator-actions">
                                <button id="clearCalculatorBtn" class="btn-outline"><i class="fas fa-trash"></i> Tout effacer</button>
                                <button id="saveMealBtn" class="btn-primary"><i class="fas fa-save"></i> Sauvegarder</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelector('#closeCalculatorModal').onclick = () => modal.remove();
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
    
    renderIngredientsListModal();
    
    document.getElementById('clearCalculatorBtn').onclick = () => {
        selectedIngredients = [];
        updateCalculatorDisplayModal();
        showToast('Votre assiette a été vidée');
    };
    document.getElementById('saveMealBtn').onclick = () => saveCurrentMealModal();
    document.getElementById('calcIngredientSearch').oninput = () => renderIngredientsListModal();
}

function renderIngredientsListModal() {
    const searchTerm = document.getElementById('calcIngredientSearch')?.value.toLowerCase() || '';
    const container = document.getElementById('calcIngredientsList');
    if (!container) return;
    
    let filtered = ingredientsDBForCalc;
    if (searchTerm) {
        filtered = ingredientsDBForCalc.filter(i => i.nom.toLowerCase().includes(searchTerm));
    }
    
    container.innerHTML = filtered.map(ing => {
        // Extract calories safely
        let calories = 0;
        if (ing.calories) {
            const match = String(ing.calories).match(/(\d+(?:\.\d+)?)/);
            if (match) calories = parseFloat(match[1]);
        }
        
        // Get nutrition values safely (convert to number, default to 0)
        const protein = parseFloat(ing.proteines) || 0;
        const carbs = parseFloat(ing.glucides) || 0;
        const fat = parseFloat(ing.lipides) || 0;
        
        return `
            <div class="ingredient-item-calc" onclick="addIngredientToMealModal(${ing.id})">
                <div class="ingredient-info">
                    <div class="ingredient-name">${escapeHtml(ing.nom)}</div>
                    <div class="ingredient-nutrition">
                        🔥 ${calories} kcal/100g | 💪 ${protein}g | 🍚 ${carbs}g | 🥑 ${fat}g
                    </div>
                </div>
                <button class="ingredient-add">+</button>
            </div>
        `;
    }).join('');
}

function addIngredientToMealModal(ingredientId) {
    const ingredient = ingredientsDBForCalc.find(i => i.id === ingredientId);
    if (!ingredient) return;
    
    // Extract calories safely
    let caloriesPer100g = 0;
    if (ingredient.calories) {
        const match = String(ingredient.calories).match(/(\d+(?:\.\d+)?)/);
        if (match) caloriesPer100g = parseFloat(match[1]);
    }
    
    // Get nutrition values safely
    const proteinPer100g = parseFloat(ingredient.proteines) || 0;
    const carbsPer100g = parseFloat(ingredient.glucides) || 0;
    const fatPer100g = parseFloat(ingredient.lipides) || 0;
    
    const existing = selectedIngredients.find(s => s.id === ingredientId);
    if (existing) {
        existing.quantity += 100;
        existing.totalCalories = (existing.quantity / 100) * existing.caloriesPer100g;
        existing.totalProtein = (existing.quantity / 100) * existing.proteinPer100g;
        existing.totalCarbs = (existing.quantity / 100) * existing.carbsPer100g;
        existing.totalFat = (existing.quantity / 100) * existing.fatPer100g;
    } else {
        selectedIngredients.push({
            id: ingredient.id,
            name: ingredient.nom,
            quantity: 100,
            caloriesPer100g: caloriesPer100g,
            proteinPer100g: proteinPer100g,
            carbsPer100g: carbsPer100g,
            fatPer100g: fatPer100g,
            totalCalories: caloriesPer100g,
            totalProtein: proteinPer100g,
            totalCarbs: carbsPer100g,
            totalFat: fatPer100g
        });
    }
    updateCalculatorDisplayModal();
}

function updateCalculatorDisplayModal() {
    let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;
    
    selectedIngredients.forEach(ing => {
        totalCalories += ing.totalCalories || 0;
        totalProtein += ing.totalProtein || 0;
        totalCarbs += ing.totalCarbs || 0;
        totalFat += ing.totalFat || 0;
    });
    
    // Update displays - always show numbers (not NaN)
    document.getElementById('totalCalories').textContent = Math.round(totalCalories) || 0;
    document.getElementById('totalProtein').textContent = Math.round(totalProtein) || 0;
    document.getElementById('totalCarbs').textContent = Math.round(totalCarbs) || 0;
    document.getElementById('totalFat').textContent = Math.round(totalFat) || 0;
    
    // Calculate percentages for bars
    const totalMacros = (totalProtein + totalCarbs + totalFat) || 1;
    const proteinPercent = (totalProtein / totalMacros * 100) || 0;
    const carbsPercent = (totalCarbs / totalMacros * 100) || 0;
    const fatPercent = (totalFat / totalMacros * 100) || 0;
    
    document.getElementById('proteinBar').style.width = `${proteinPercent}%`;
    document.getElementById('carbsBar').style.width = `${carbsPercent}%`;
    document.getElementById('fatBar').style.width = `${fatPercent}%`;
    document.getElementById('proteinPercent').textContent = `${Math.round(proteinPercent)}%`;
    document.getElementById('carbsPercent').textContent = `${Math.round(carbsPercent)}%`;
    document.getElementById('fatPercent').textContent = `${Math.round(fatPercent)}%`;
    
    // Update selected ingredients list
    const container = document.getElementById('selectedIngredientsContainer');
    if (container) {
        container.innerHTML = selectedIngredients.map((ing, index) => `
            <div class="selected-item">
                <div class="selected-item-info">
                    <div class="selected-item-name">${escapeHtml(ing.name)}</div>
                    <div class="selected-item-nutrition">
                        ${Math.round(ing.totalCalories || 0)} kcal | ${Math.round(ing.totalProtein || 0)}g prot
                    </div>
                </div>
                <div class="selected-item-qty">
                    <button class="qty-btn" onclick="updateIngredientQuantityModal(${index}, -50)">-</button>
                    <span class="qty-value">${Math.round(ing.quantity)}g</span>
                    <button class="qty-btn" onclick="updateIngredientQuantityModal(${index}, 50)">+</button>
                    <button class="remove-item" onclick="removeIngredientFromMealModal(${index})">×</button>
                </div>
            </div>
        `).join('');
    }
    
    // Update goal comparison message
    updateGoalComparisonMessage(totalCalories, totalProtein);
}

function updateIngredientQuantityModal(index, change) {
    if (index >= 0 && index < selectedIngredients.length) {
        const newQuantity = selectedIngredients[index].quantity + change;
        if (newQuantity >= 0 && newQuantity <= 2000) {
            selectedIngredients[index].quantity = newQuantity;
            selectedIngredients[index].totalCalories = (newQuantity / 100) * selectedIngredients[index].caloriesPer100g;
            selectedIngredients[index].totalProtein = (newQuantity / 100) * selectedIngredients[index].proteinPer100g;
            selectedIngredients[index].totalCarbs = (newQuantity / 100) * selectedIngredients[index].carbsPer100g;
            selectedIngredients[index].totalFat = (newQuantity / 100) * selectedIngredients[index].fatPer100g;
            updateCalculatorDisplayModal();
        }
    }
}

function removeIngredientFromMealModal(index) {
    if (index >= 0 && index < selectedIngredients.length) {
        selectedIngredients.splice(index, 1);
        updateCalculatorDisplayModal();
    }
}

function updateGoalComparisonMessage(calories, protein) {
    const container = document.getElementById('goalFeedbackMessage');
    if (!container) return;
    
    let message = '', className = '';
    
    if (currentGoal === 'perte_de_poids') {
        if (calories < 400) {
            message = `✅ Excellent pour la perte de poids! (${Math.round(calories)} kcal)`;
            className = 'perfect';
        } else if (calories < 600) {
            message = `👍 Bon pour la perte de poids (${Math.round(calories)} kcal)`;
            className = 'perfect';
        } else {
            message = `⚠️ Calorique pour perte de poids (${Math.round(calories)} kcal)`;
            className = 'warning';
        }
    } 
    else if (currentGoal === 'musculation') {
        if (protein >= 25) {
            message = `💪 Parfait pour la musculation! (${Math.round(protein)}g protéines)`;
            className = 'perfect';
        } else if (protein >= 15) {
            message = `👍 Bon, ajoutez plus de protéines (${Math.round(protein)}g)`;
            className = 'warning';
        } else {
            message = `⚠️ Manque de protéines (${Math.round(protein)}g)`;
            className = 'low';
        }
    } 
    else if (currentGoal === 'equilibre') {
        if (calories >= 400 && calories <= 700 && protein >= 12) {
            message = `✅ Repas équilibré!`;
            className = 'perfect';
        } else if (calories < 400) {
            message = `⚠️ Repas léger, ajoutez des ingrédients`;
            className = 'warning';
        } else if (calories > 700) {
            message = `⚠️ Repas calorique (${Math.round(calories)} kcal)`;
            className = 'warning';
        } else {
            message = `👍 Bon équilibre`;
            className = 'perfect';
        }
    } 
    else if (currentGoal === 'prise_de_poids') {
        if (calories >= 700) {
            message = `✅ Parfait pour prendre du poids! (${Math.round(calories)} kcal)`;
            className = 'perfect';
        } else if (calories >= 500) {
            message = `👍 Bon, peut être plus calorique`;
            className = 'warning';
        } else {
            message = `⚠️ Pas assez calorique (${Math.round(calories)} kcal)`;
            className = 'low';
        }
    } 
    else {
        message = `🎯 Sélectionnez un objectif pour voir la comparaison`;
        className = 'perfect';
    }
    
    container.innerHTML = `<div class="goal-message ${className}"><i class="fas fa-info-circle"></i> ${message}</div>`;
}

function saveCurrentMealModal() {
    if (selectedIngredients.length === 0) {
        showToast('Ajoutez d\'abord des ingrédients', true);
        return;
    }
    
    const totalCalories = parseInt(document.getElementById('totalCalories').textContent) || 0;
    const totalProtein = parseInt(document.getElementById('totalProtein').textContent) || 0;
    
    const meal = {
        id: Date.now(),
        date: new Date().toISOString(),
        ingredients: selectedIngredients.map(ing => ({ 
            name: ing.name, 
            quantity: ing.quantity,
            calories: ing.totalCalories,
            protein: ing.totalProtein
        })),
        totals: { 
            calories: totalCalories, 
            protein: totalProtein 
        }
    };
    
    const savedMeals = JSON.parse(localStorage.getItem('savedMeals') || '[]');
    savedMeals.unshift(meal);
    if (savedMeals.length > 20) savedMeals.pop();
    localStorage.setItem('savedMeals', JSON.stringify(savedMeals));
    
    showToast(`Repas sauvegardé! ${totalCalories} kcal, ${totalProtein}g de protéines`);
    document.getElementById('calculatorModal')?.remove();
}

// Make functions global
window.addIngredientToMealModal = addIngredientToMealModal;
window.updateIngredientQuantityModal = updateIngredientQuantityModal;
window.removeIngredientFromMealModal = removeIngredientFromMealModal;

// Add button listener
document.getElementById('openCalculatorBtn')?.addEventListener('click', openCalculatorModal);