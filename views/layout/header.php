<?php
$layout = $layout ?? 'front';
$action = $action ?? $_GET['action'] ?? ($layout === 'back' ? 'admin_entrainements' : 'mes_entrainements');
$pageTitle = $pageTitle ?? ($layout === 'back' ? 'Kool Healthy | Back Office - Entraînement' : 'Kool Healthy | Entraînement & Exercice');

function formatDateFR($date)
{
    return date('d/m/Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <?php if ($layout === 'back'): ?>
    <link rel="stylesheet" href="/integweb/VIEW/css/backoffice.css?v=20260512">
  <?php else: ?>
    <link rel="stylesheet" href="/integweb/CSS/styles.css">
    <link rel="stylesheet" href="/integweb/public/css/style.css">
  <?php endif; ?>
  <?php if ($action === 'statistiques_entrainements'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <?php endif; ?>
</head>
<body class="<?= $layout ?>-layout">
<?php if ($layout === 'front'): ?>
  <?php include __DIR__ . '/../../VIEW/includes/topbar.php'; ?>

  <section id="accueil" class="hero">
    <div class="hero-content">
      <h1>Entraînement &amp; Exercice<br><span>pour rester performant</span></h1>
      <p>Suivez vos séances, organisez vos exercices et laissez l'IA vous recommander le bon enchaînement après votre repas.</p>
      <div class="hero-buttons">
        <button class="btn-primary" onclick="window.location.href='index.php?action=ajouter_entrainement'"><i class="fas fa-dumbbell"></i> Ajouter une séance</button>
        <button class="btn-secondary" onclick="window.location.href='index.php?action=recommander'"><i class="fas fa-robot"></i> Recommandation KNN</button>
        <button class="btn-secondary" onclick="window.location.href='index.php?action=progression'" style="border-color:var(--vert-kool);color:var(--vert-kool);"><i class="fas fa-chart-line"></i> Ma progression</button>
      </div>
    </div>
    <div class="hero-image">
      <div><i class="fas fa-running"></i><p>Suivez vos entraînements et construisez un plan d'exercices simple.</p></div>
    </div>
  </section>

  <section class="section">
    <h2 class="section-title">Mon tableau d'entraînement</h2>
    <p class="section-subtitle">Gérez vos séances, vos exercices et consultez les recommandations personnalisées.</p>
    <div class="content-area">
<?php else: ?>
  <div class="app-wrapper">
    <aside class="sidebar">
      <div class="logo-area">
        <a class="logo-link" href="/integweb/VIEW/home.php" aria-label="Kool Healthy">
          <img src="/integweb/Assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='/integweb/public/images/logo.png';">
        </a>
        <p>administration · nutrition IA</p>
      </div>
      <div class="nav-menu">
        <a class="nav-item" href="/integweb/VIEW/backoffice.php" style="text-decoration:none;color:inherit;"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
        <a class="nav-item" href="/integweb/VIEW/backoffice.php?tab=users" style="text-decoration:none;color:inherit;"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
        <a class="nav-item" href="/integweb/VIEW/backoffice.php?tab=recipes" style="text-decoration:none;color:inherit;"><i class="fas fa-utensils"></i><span>Recettes</span></a>
        <a class="nav-item" href="/integweb/VIEW/backoffice.php?tab=ingredients" style="text-decoration:none;color:inherit;"><i class="fas fa-apple-alt"></i><span>Ingrédients</span></a>
        <a class="nav-item" href="/integweb/VIEW/backoffice.php?tab=reviews" style="text-decoration:none;color:inherit;"><i class="fas fa-star"></i><span>Avis</span></a>
        <a class="nav-item" href="/integweb/plan.php?page=plan-backoffice" style="text-decoration:none;color:inherit;"><i class="fas fa-bowl-food"></i><span>Repas</span></a>
        <a class="nav-item" href="/integweb/plan.php?page=plan-nutritionnel" style="text-decoration:none;color:inherit;"><i class="fas fa-clipboard-list"></i><span>Plans</span></a>
        <a class="nav-item" href="/integweb/VIEW/backoffice-gamification.php" style="text-decoration:none;color:inherit;"><i class="fas fa-trophy"></i><span>Gamification</span></a>
        <a class="nav-item<?= in_array($action, ['admin_entrainements','admin_creer_entrainement','admin_modifier_entrainement']) ? ' active' : '' ?>" href="/integweb/sport/index.php?action=admin_entrainements" style="text-decoration:none;color:inherit;"><i class="fas fa-chart-bar"></i><span>Entraînements</span></a>
        <a class="nav-item<?= in_array($action, ['admin_exercices','admin_creer_exercice','admin_modifier_exercice']) ? ' active' : '' ?>" href="/integweb/sport/index.php?action=admin_exercices" style="text-decoration:none;color:inherit;"><i class="fas fa-dumbbell"></i><span>Exercices</span></a>
        <a class="nav-item<?= in_array($action, ['admin_reference_list','admin_reference_create','admin_reference_edit']) ? ' active' : '' ?>" href="/integweb/sport/index.php?action=admin_reference_list" style="text-decoration:none;color:inherit;"><i class="fas fa-book"></i><span>Catalogue KNN</span></a>
        <a class="nav-item" href="/integweb/VIEW/backoffice.php?tab=analytics" style="text-decoration:none;color:inherit;"><i class="fas fa-chart-line"></i><span>Analytics IA</span></a>
      </div>
      <div class="sidebar-footer">
        <div class="user-badge">
          <div class="user-avatar"><i class="fas fa-user-md"></i></div>
          <div class="user-info"><p>Admin Kool</p><small>administration</small></div>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1><?= $pageTitle ?? 'Kool Healthy | Back Office' ?></h1>
          <p>Contrôlez les séances, exercices et règles de recommandation IA.</p>
        </div>
        <div class="header-actions">
          <a class="btn-outline" href="/integweb/CONTROLLER/AuthController.php?action=logout">Se déconnecter</a>
        </div>
      </div>

      <div class="dashboard-container">
<?php endif; ?>
