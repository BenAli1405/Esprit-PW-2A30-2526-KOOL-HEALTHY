<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$authController = new AuthController();
$utilisateurConnecte = $authController->utilisateurConnecte();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Kool Healthy | Front Office - Recettes & Ingrédients</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../CSS/styles.css">
  <link rel="stylesheet" href="css/frontoffice.css">
</head>
<body>

  <?php include __DIR__ . '/includes/topbar.php'; ?>

  <!-- Hero -->
  <section class="hero-recettes">
    <h1><i class="fas fa-utensils"></i> Découvrez nos recettes durables</h1>
    <p>Valeurs nutritionnelles calculées automatiquement · Filtrez par objectif santé</p>
  </section>

  <!-- Objective Selector -->
  <div class="goal-selector-wrapper">
    <div class="goal-selector">
      <button class="goal-btn" data-goal="perte_de_poids">
        <i class="fas fa-weight-scale"></i>
        <span>Perte de poids</span>
        <small>&lt; 600 kcal</small>
      </button>
      <button class="goal-btn" data-goal="musculation">
        <i class="fas fa-dumbbell"></i>
        <span>Musculation</span>
        <small>&gt; 20g protéines</small>
      </button>
      <button class="goal-btn" data-goal="equilibre">
        <i class="fas fa-chart-line"></i>
        <span>Équilibre</span>
        <small>600–800 kcal</small>
      </button>
      <button class="goal-btn" data-goal="prise_de_poids">
        <i class="fas fa-chart-simple"></i>
        <span>Prendre du poids</span>
        <small>&gt; 800 kcal</small>
      </button>
    </div>
    <div class="goal-feedback" id="goalFeedback"></div>
  </div>

  <!-- Main Module -->
  <div class="module-container">
    <!-- Filter bar -->
    <div class="filter-bar">
      <div class="search-group">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Rechercher par nom ou ingrédient...">
      </div>
      <select id="difficultyFilter" class="filter-select">
        <option value="">Toutes difficultés</option>
        <option value="Facile">Facile</option>
        <option value="Moyen">Moyen</option>
        <option value="Difficile">Difficile</option>
      </select>
      <select id="ecoScoreFilter" class="filter-select">
        <option value="">Tous éco-scores</option>
        <option value="A+">A+</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
      </select>
      <select id="timeFilter" class="filter-select">
        <option value="">Tous temps</option>
        <option value="15">Moins de 15 min</option>
        <option value="30">Moins de 30 min</option>
        <option value="45">Moins de 45 min</option>
        <option value="60">Moins de 60 min</option>
      </select>
      <button id="resetFiltersBtn" class="btn-outline"><i class="fas fa-undo"></i> Réinitialiser</button>
    </div>

    <!-- AI Controls -->
    <div class="ai-controls">
      <div class="ai-stats" id="aiStats"></div>
      <button id="generatePlanBtn" class="btn-primary">
        <i class="fas fa-calendar-week"></i> Plan de repas IA
      </button>
      <button id="openCalculatorBtn" class="btn-primary">
        <i class="fas fa-calculator"></i> Calculateur nutritionnel
      </button>
      <button id="historyBtn" class="btn-primary">
        <i class="fas fa-history"></i> Historique
      </button>
    </div>

    <!-- Recipe grid -->
    <div id="recipesContainer" class="recipes-grid"></div>
  </div>

  <!-- Review Modal -->
  <div id="reviewModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3><i class="fas fa-star"></i> Donner mon avis</h3>
        <span class="close-modal" id="closeReviewModal">&times;</span>
      </div>
      <div class="modal-body">
        <h4 id="reviewRecipeTitle" class="review-recipe-title"></h4>
        <div class="rating-section">
          <label>Votre note :</label>
          <div class="stars-container">
            <i class="far fa-star star-rating" data-value="1"></i>
            <i class="far fa-star star-rating" data-value="2"></i>
            <i class="far fa-star star-rating" data-value="3"></i>
            <i class="far fa-star star-rating" data-value="4"></i>
            <i class="far fa-star star-rating" data-value="5"></i>
          </div>
          <input type="hidden" id="selectedRating" value="0">
        </div>
        <div class="comment-section">
          <label>Votre commentaire :</label>
          <textarea id="reviewComment" rows="4" placeholder="Partagez votre expérience avec cette recette..."></textarea>
        </div>
        <div class="modal-actions">
          <button id="submitReviewBtn" class="btn-primary">Envoyer mon avis</button>
          <button id="cancelReviewBtn" class="btn-outline">Annuler</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Avis envoyé !</span>
  </div>

  <script src="js/frontoffice.js"></script>
</body>
</html>
