<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/PlanNutritionnelController.php';

// Simuler un utilisateur connecté (id=1) – à remplacer par la session réelle
$utilisateur_id = $_SESSION['user_id'] ?? 1;

$ctrl  = new PlanNutritionnelController();
$plans = $ctrl->listePlans($utilisateur_id);

// Traitement : suppression
if (isset($_GET['supprimer'])) {
    $ctrl->supprimerPlan((int)$_GET['supprimer']);
    header('Location: mes-plans.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Mes Plans Nutritionnels</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../CSS/plans.css">
</head>
<body>

<!-- ── Navbar ─────────────────────────────────────────────────── -->
<nav class="navbar">
  <div class="logo">
    <i class="fas fa-seedling"></i>
    <h1>Kool Healthy</h1>
  </div>
  <div class="nav-links">
    <a href="home.php">Accueil</a>
    <a href="mes-plans.php" class="active">Mes Plans</a>
    <a href="fil-recettes.php">Recettes</a>
    <a href="profil.php">Profil</a>
    <button class="btn-connect" onclick="location.href='auth.php'">
      <i class="fas fa-sign-in-alt"></i> Connexion
    </button>
  </div>
</nav>

<!-- ── Hero ───────────────────────────────────────────────────── -->
<section class="hero-plans">
  <div class="hero-content">
    <h1>Mes <span>Plans Nutritionnels</span> 🥗</h1>
    <p>Gérez vos objectifs caloriques, planifiez vos repas et suivez votre progression nutritionnelle au quotidien.</p>
    <a href="creer-plan.php" class="btn-primary">
      <i class="fas fa-plus-circle"></i> Créer un nouveau plan
    </a>
  </div>
  <div class="hero-stats" style="display:flex;gap:1.5rem;flex-wrap:wrap;">
    <div class="card-panel" style="min-width:160px;text-align:center;">
      <div style="font-size:2rem;font-weight:800;color:var(--vert-kool);"><?= count($plans) ?></div>
      <small style="color:var(--gris-texte);">Plans créés</small>
    </div>
    <div class="card-panel" style="min-width:160px;text-align:center;">
      <?php
        $totalCal = count($plans) ? array_sum(array_column($plans,'calories_journalieres')) / count($plans) : 0;
      ?>
      <div style="font-size:2rem;font-weight:800;color:var(--bleu-tech);"><?= round($totalCal) ?></div>
      <small style="color:var(--gris-texte);">Kcal/j moyen</small>
    </div>
  </div>
</section>

<!-- ── Liste des plans ────────────────────────────────────────── -->
<section class="section">
  <h2 class="section-title"><i class="fas fa-list-ul"></i> Tous mes plans</h2>
  <p class="section-subtitle">Cliquez sur un plan pour le consulter et gérer vos repas.</p>

  <?php if (empty($plans)): ?>
    <div class="empty-state">
      <i class="fas fa-utensils"></i>
      <p>Aucun plan nutritionnel pour l'instant.</p>
      <a href="creer-plan.php" class="btn-primary" style="margin-top:1rem;">
        <i class="fas fa-plus"></i> Créer mon premier plan
      </a>
    </div>
  <?php else: ?>
  <div class="plans-grid">
    <?php foreach ($plans as $plan): ?>
      <?php
        $ctrl2 = $ctrl;
        $reco  = $ctrl2->recommandation($plan['calories_journalieres']);
        $duree = (int)((strtotime($plan['date_fin']) - strtotime($plan['date_debut'])) / 86400);
        $pct   = min(100, round($plan['statistiques']));
      ?>
      <div class="plan-card">
        <div class="plan-card-header">
          <div class="plan-card-title"><?= htmlspecialchars($plan['nom']) ?></div>
          <span class="plan-badge"><?= $duree ?> jours</span>
        </div>

        <div class="plan-calories">
          <?= number_format($plan['calories_journalieres'], 0, ',', ' ') ?>
          <span>kcal/jour</span>
        </div>

        <div class="plan-dates">
          <i class="fas fa-calendar-alt"></i>
          <?= date('d/m/Y', strtotime($plan['date_debut'])) ?>
          &nbsp;→&nbsp;
          <?= date('d/m/Y', strtotime($plan['date_fin'])) ?>
        </div>

        <div class="progress-wrap">
          <div class="progress-label">
            <span>Progression</span>
            <span><?= $pct ?>%</span>
          </div>
          <div class="progress-bar-bg">
            <div class="progress-fill<?= $pct > 70 ? '' : ($pct > 40 ? ' progress-fill-blue' : ' progress-fill-orange') ?>"
                 style="width:<?= $pct ?>%"></div>
          </div>
        </div>

        <div class="alert alert-<?= $reco['type'] ?>" style="font-size:.78rem;padding:.5rem .8rem;">
          <i class="fas fa-<?= $reco['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
          <?= $reco['message'] ?>
        </div>

        <div class="plan-actions">
          <a href="detail-plan.php?id=<?= $plan['planID'] ?>" class="btn-primary btn-sm">
            <i class="fas fa-eye"></i> Consulter
          </a>
          <a href="modifier-plan.php?id=<?= $plan['planID'] ?>" class="btn-secondary btn-sm">
            <i class="fas fa-edit"></i> Modifier
          </a>
          <a href="mes-plans.php?supprimer=<?= $plan['planID'] ?>"
             class="btn-danger btn-sm"
             onclick="return confirm('Supprimer ce plan et tous ses repas ?')">
            <i class="fas fa-trash"></i>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<!-- ── Footer ─────────────────────────────────────────────────── -->
<footer class="footer">
  <p>© 2025 Kool Healthy — Manger mieux, préserver la planète 🌱</p>
</footer>

<script src="../JS/plans.js"></script>
</body>
</html>
