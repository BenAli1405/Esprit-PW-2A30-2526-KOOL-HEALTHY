<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/PlanNutritionnelController.php';

$utilisateur_id = $_SESSION['user_id'] ?? 1;
$ctrl   = new PlanNutritionnelController();
$erreur = '';
$succes = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = trim($_POST['nom']    ?? '');
    $cal    = (float)($_POST['calories'] ?? 0);
    $debut  = $_POST['date_debut'] ?? '';
    $fin    = $_POST['date_fin']   ?? '';

    if (!$nom || !$cal || !$debut || !$fin) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif (strtotime($fin) <= strtotime($debut)) {
        $erreur = 'La date de fin doit être postérieure à la date de début.';
    } elseif ($cal < 800 || $cal > 5000) {
        $erreur = 'Les calories journalières doivent être entre 800 et 5 000 kcal.';
    } else {
        $plan = new PlanNutritionnel($nom, $cal, $utilisateur_id, $debut, $fin);
        $id   = $ctrl->creerPlan($plan);
        header("Location: detail-plan.php?id=$id&created=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Créer un Plan Nutritionnel</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../CSS/plans.css">
</head>
<body>

<nav class="navbar">
  <div class="logo"><i class="fas fa-seedling"></i><h1>Kool Healthy</h1></div>
  <div class="nav-links">
    <a href="home.php">Accueil</a>
    <a href="mes-plans.php">Mes Plans</a>
    <a href="creer-plan.php" class="active">Nouveau Plan</a>
  </div>
</nav>

<section class="section">
  <div class="form-card">
    <div class="form-title">
      <i class="fas fa-plus-circle" style="color:var(--bleu-tech);"></i>
      Créer un plan nutritionnel
    </div>

    <?php if ($erreur): ?>
      <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <!-- Aide calcul calories -->
    <div class="card-panel" style="margin-bottom:1.5rem;background:var(--bleu-tech-light);border-color:var(--bleu-tech);">
      <div class="panel-header">
        <span class="panel-title" style="color:var(--bleu-tech-dark);">
          <i class="fas fa-calculator"></i> Calculateur de calories recommandées
        </span>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Poids (kg)</label>
          <input type="number" id="inputPoids" placeholder="ex : 70" min="30" max="250">
        </div>
        <div class="form-group">
          <label>Taille (cm)</label>
          <input type="number" id="inputTaille" placeholder="ex : 175" min="100" max="250">
        </div>
        <div class="form-group">
          <label>Âge</label>
          <input type="number" id="inputAge" placeholder="ex : 30" min="10" max="120">
        </div>
        <div class="form-group">
          <label>Genre</label>
          <select id="inputGenre">
            <option value="h">Homme</option>
            <option value="f">Femme</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Niveau d'activité physique</label>
        <select id="inputNiveauActivite">
          <option value="1.2">Sédentaire (peu ou pas d'exercice)</option>
          <option value="1.375" selected>Légèrement actif (1–3 fois/sem)</option>
          <option value="1.55">Modérément actif (3–5 fois/sem)</option>
          <option value="1.725">Très actif (6–7 fois/sem)</option>
        </select>
      </div>
      <div id="calRecommandees" style="font-weight:700;font-size:1rem;margin-top:.5rem;"></div>
    </div>

    <!-- Formulaire plan -->
    <form method="POST" id="planForm">
      <div class="form-group">
        <label for="nom"><i class="fas fa-tag"></i> Nom du plan *</label>
        <input type="text" id="nom" name="nom"
               placeholder="ex : Plan équilibré été 2025"
               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="inputCalories"><i class="fas fa-fire"></i> Calories journalières (kcal) *</label>
        <input type="number" id="inputCalories" name="calories"
               placeholder="ex : 2000" min="800" max="5000" step="50"
               value="<?= htmlspecialchars($_POST['calories'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="inputDateDebut"><i class="fas fa-play"></i> Date de début *</label>
          <input type="date" id="inputDateDebut" name="date_debut"
                 value="<?= htmlspecialchars($_POST['date_debut'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="form-group">
          <label for="inputDateFin"><i class="fas fa-flag-checkered"></i> Date de fin *</label>
          <input type="date" id="inputDateFin" name="date_fin"
                 value="<?= htmlspecialchars($_POST['date_fin'] ?? '') ?>" required>
        </div>
      </div>

      <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1rem;">
        <button type="submit" class="btn-primary">
          <i class="fas fa-save"></i> Créer le plan
        </button>
        <a href="mes-plans.php" class="btn-secondary">
          <i class="fas fa-arrow-left"></i> Annuler
        </a>
      </div>
    </form>
  </div>
</section>

<footer class="footer">
  <p>© 2025 Kool Healthy — Manger mieux, préserver la planète 🌱</p>
</footer>

<script src="../JS/plans.js"></script>
</body>
</html>
