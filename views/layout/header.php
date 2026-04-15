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
  <link rel="stylesheet" href="/kool_healthy3/public/css/style.css">
</head>
<body class="<?= $layout ?>-layout">
<?php if ($layout === 'front'): ?>
  <nav class="navbar">
    <div class="logo"><a href="index.php?action=mes_entrainements"><img src="/kool_healthy3/public/images/logo.png" alt="Kool Healthy"></a></div>
    <div class="nav-links">
      <a href="index.php?action=mes_entrainements">Mes séances</a>
      <a href="index.php?action=recommander_ia">Recommander IA</a>
      <a href="index.php?action=mes_entrainements">Mes exercices</a>
      <button class="btn-outline" onclick="window.location.href='index.php?action=admin_entrainements'">Admin</button>
    </div>
  </nav>

  <section id="accueil" class="hero">
    <div class="hero-content">
      <h1>Entraînement &amp; Exercice<br><span>pour rester performant</span></h1>
      <p>Suivez vos séances, organisez vos exercices et laissez l'IA vous recommander le bon enchaînement après votre repas.</p>
      <div class="hero-buttons">
        <button class="btn-primary" onclick="window.location.href='index.php?action=ajouter_entrainement'"><i class="fas fa-dumbbell"></i> Ajouter une séance</button>
        <button class="btn-secondary" onclick="window.location.href='index.php?action=recommander_ia'"><i class="fas fa-brain"></i> Recommander IA</button>
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
        <img src="/kool_healthy3/public/images/logo.png" alt="Kool Healthy">
        <p>administration · nutrition IA</p>
      </div>
      <div class="nav-menu">
        <a class="nav-item<?= $action === 'admin_entrainements' ? ' active' : '' ?>" href="index.php?action=admin_entrainements"><i class="fas fa-chart-pie"></i><span>Entraînements</span></a>
        <a class="nav-item<?= in_array($action, ['admin_exercices','admin_creer_exercice','admin_modifier_exercice']) ? ' active' : '' ?>" href="index.php?action=admin_exercices"><i class="fas fa-dumbbell"></i><span>Exercices</span></a>
        <a class="nav-item<?= in_array($action, ['admin_regles','admin_creer_regle','admin_modifier_regle']) ? ' active' : '' ?>" href="index.php?action=admin_regles"><i class="fas fa-brain"></i><span>IA Rules</span></a>
      </div>
      <div class="sidebar-footer">
        <div class="user-badge">
          <div class="user-avatar"><i class="fas fa-user-md"></i></div>
          <div class="user-info"><p>Admin Kool</p><small>admin@koolhealthy.com</small></div>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Module Plan Nutritionnel – Gestion back-office</h1>
          <p>Contrôlez les séances, exercices et règles de recommandation IA.</p>
        </div>
        <div class="header-actions">
          <a class="btn-outline" href="index.php?action=mes_entrainements">Voir côté utilisateur</a>
        </div>
      </div>

      <div class="dashboard-container">
<?php endif; ?>
