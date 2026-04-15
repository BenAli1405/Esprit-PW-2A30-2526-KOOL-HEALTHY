<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/PlanNutritionnelController.php';

$planID = (int)($_GET['id'] ?? 0);
if (!$planID) { header('Location: mes-plans.php'); exit; }

$ctrl  = new PlanNutritionnelController();
$plan  = $ctrl->obtenirPlan($planID);
if (!$plan) { header('Location: mes-plans.php'); exit; }

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom']   ?? '');
    $cal   = (float)($_POST['calories'] ?? 0);
    $debut = $_POST['date_debut'] ?? '';
    $fin   = $_POST['date_fin']   ?? '';
    $stat  = (float)($_POST['statistiques'] ?? 0);

    if (!$nom || !$cal || !$debut || !$fin) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif (strtotime($fin) <= strtotime($debut)) {
        $erreur = 'La date de fin doit être postérieure à la date de début.';
    } else {
        $planObj = new PlanNutritionnel(
            $nom, $cal, $plan['utilisateur_id'], $debut, $fin, $stat
        );
        $ctrl->modifierPlan($planObj, $planID);
        header("Location: detail-plan.php?id=$planID");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Modifier le plan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../CSS/plans.css">
</head>
<body>

<nav class="navbar">
  <div class="logo"><i class="fas fa-seedling"></i><h1>Kool Healthy</h1></div>
  <div class="nav-links">
    <a href="mes-plans.php">Mes Plans</a>
    <a href="detail-plan.php?id=<?= $planID ?>">
      <i class="fas fa-arrow-left"></i> Retour au plan
    </a>
  </div>
</nav>

<section class="section">
  <div class="form-card">
    <div class="form-title">
      <i class="fas fa-edit" style="color:var(--bleu-tech);"></i>
      Modifier : <?= htmlspecialchars($plan['nom']) ?>
    </div>

    <?php if ($erreur): ?>
      <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <?= htmlspecialchars($erreur) ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="planForm">
      <div class="form-group">
        <label><i class="fas fa-tag"></i> Nom du plan *</label>
        <input type="text" name="nom" required
               value="<?= htmlspecialchars($_POST['nom'] ?? $plan['nom']) ?>">
      </div>

      <div class="form-group">
        <label><i class="fas fa-fire"></i> Calories journalières (kcal) *</label>
        <input type="number" id="inputCalories" name="calories"
               min="800" max="5000" step="50" required
               value="<?= htmlspecialchars($_POST['calories'] ?? $plan['calories_journalieres']) ?>">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label><i class="fas fa-play"></i> Date de début *</label>
          <input type="date" id="inputDateDebut" name="date_debut" required
                 value="<?= htmlspecialchars($_POST['date_debut'] ?? $plan['date_debut']) ?>">
        </div>
        <div class="form-group">
          <label><i class="fas fa-flag-checkered"></i> Date de fin *</label>
          <input type="date" id="inputDateFin" name="date_fin" required
                 value="<?= htmlspecialchars($_POST['date_fin'] ?? $plan['date_fin']) ?>">
        </div>
      </div>

      <div class="form-group">
        <label><i class="fas fa-chart-line"></i> Progression (% accompli)</label>
        <input type="number" name="statistiques" min="0" max="100" step="1"
               value="<?= htmlspecialchars($_POST['statistiques'] ?? $plan['statistiques']) ?>">
      </div>

      <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:1rem;">
        <button type="submit" class="btn-primary">
          <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
        <a href="detail-plan.php?id=<?= $planID ?>" class="btn-secondary">
          <i class="fas fa-times"></i> Annuler
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
