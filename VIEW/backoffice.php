<?php
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Location: ../index.php?page=backoffice');
    exit;
}

$message = $message ?? '';
$messageType = $messageType ?? 'success';
$plans = $plans ?? [];

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function getObjectifLabel($value)
{
    if ($value === 'perte-poids') {
        return 'Perte de poids';
    }
    if ($value === 'prise-muscle') {
        return 'Prise de muscle';
    }
    if ($value === 'maintien') {
        return 'Maintien';
    }
    return 'Autre';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Backoffice CRUD</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --vert-kool: #4CAF50;
      --vert-kool-dark: #388E3C;
      --vert-kool-light: #E8F5E9;
      --bleu-tech: #29B6F6;
      --bleu-tech-dark: #0288D1;
      --bleu-tech-light: #E1F5FE;
      --blanc: #FFFFFF;
      --gris-clair: #F5F5F5;
      --gris-moyen: #E0E0E0;
      --gris-texte: #616161;
      --ombre-legere: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: "Inter", sans-serif;
      background: var(--gris-clair);
      color: #2C3E2F;
    }

    .app-wrapper { display: flex; min-height: 100vh; }

    .sidebar {
      width: 280px;
      background: var(--blanc);
      border-right: 1px solid var(--gris-moyen);
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
    }

    .logo-area {
      padding: 24px;
      border-bottom: 1px solid var(--gris-moyen);
      margin-bottom: 14px;
    }

    .logo-area h2 {
      font-size: 1.6rem;
      color: var(--vert-kool);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-area h2 i { color: var(--bleu-tech); }
    .logo-area p { color: var(--gris-texte); font-size: 0.75rem; margin-top: 8px; }

    .nav-menu { flex: 1; padding: 0 14px; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; margin: 6px 0; color: #4A5B4E; cursor: pointer; user-select: none; font-weight: 500; }
    .nav-item i { width: 20px; color: var(--bleu-tech); }
    .nav-item.active { background: var(--vert-kool-light); color: var(--vert-kool-dark); }

    .sidebar-footer { border-top: 1px solid var(--gris-moyen); padding: 16px; }
    .user-badge { display: flex; align-items: center; gap: 10px; }
    .user-avatar { width: 40px; height: 40px; border-radius: 999px; background: linear-gradient(135deg, var(--vert-kool), var(--bleu-tech)); color: white; display: flex; align-items: center; justify-content: center; }

    .main-content { flex: 1; overflow-x: auto; }
    .top-bar { background: var(--blanc); border-bottom: 1px solid var(--gris-moyen); padding: 18px 28px; display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; }
    .page-title h1 { color: var(--vert-kool); font-size: 1.6rem; }
    .page-title p { color: var(--gris-texte); font-size: 0.86rem; margin-top: 4px; }
    .btn-outline { border: 1px solid var(--bleu-tech); color: var(--bleu-tech); border-radius: 999px; padding: 10px 16px; text-decoration: none; font-weight: 600; background: #fff; }

    .dashboard-container { padding: 24px 28px; display: none; }
    .dashboard-container.active { display: block; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 22px; }
    .stat-card { background: var(--blanc); border: 1px solid var(--gris-moyen); border-radius: 16px; padding: 16px; box-shadow: var(--ombre-legere); }
    .stat-title { color: var(--gris-texte); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.7px; }
    .stat-value { color: var(--vert-kool-dark); font-size: 1.9rem; font-weight: 800; margin-top: 6px; }
    .card-panel { background: var(--blanc); border: 1px solid var(--gris-moyen); border-radius: 16px; padding: 16px; box-shadow: var(--ombre-legere); margin-bottom: 18px; }
    .panel-header { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 14px; }
    .badge-tech { background: var(--bleu-tech-light); color: var(--bleu-tech-dark); font-size: 0.72rem; border-radius: 999px; padding: 4px 10px; font-weight: 700; }
    .data-table-wrap { overflow-x: auto; border: 1px solid var(--gris-moyen); border-radius: 12px; }
    .data-table { width: 100%; min-width: 880px; border-collapse: collapse; background: var(--blanc); }
    .data-table th, .data-table td { border-bottom: 1px solid var(--gris-moyen); text-align: left; padding: 10px; font-size: 0.86rem; }
    .data-table th { background: #f6fbf7; color: #24523b; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.6px; }
    .data-table tbody tr { cursor: pointer; transition: background 0.2s; }
    .data-table tbody tr:hover { background: #f1f8f3; }
    .data-table tbody tr.selected { background: #dcedc8; outline: 2px solid var(--vert-kool); }
    .crud-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 14px; margin-top: 14px; }
    .crud-form { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .crud-form .full { grid-column: span 2; }
    .crud-form input, .crud-form select, .crud-form textarea { width: 100%; border: 1px solid var(--gris-moyen); border-radius: 10px; padding: 10px; font: inherit; background: white; }
    .btn-row { display: flex; gap: 8px; flex-wrap: wrap; }
    .mini-btn { border: 0; border-radius: 999px; padding: 9px 14px; font-weight: 700; cursor: pointer; color: white; background: var(--vert-kool); }
    .mini-btn.secondary { background: var(--bleu-tech); }
    .mini-btn.danger { background: #d9534f; }
    .form-message { padding: 14px 18px; border-radius: 14px; margin-bottom: 18px; font-weight: 600; }
    .form-message.success { background: #e8f5e9; color: #2f7a34; border: 1px solid #c8e6c9; }
    .form-message.error { background: #fbe9e7; color: #b71c1c; border: 1px solid #f5c6cb; }
    #formErrors { display: none; margin-top: 10px; color: #b71c1c; font-size: 0.95rem; }
    #formErrors ul { padding-left: 18px; }
    .status-chip { display: inline-block; border-radius: 999px; font-size: 0.7rem; padding: 3px 8px; font-weight: 700; background: #fff4e5; color: #9c6800; }
    .status-chip.none { background: #e8f5e9; color: #2f7a34; }
    .meal-container { padding: 18px 0 0; }
    .meal-header .meal-controls { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 10px; }
    .filter-group { display: flex; flex-wrap: wrap; gap: 10px; }
    .filter-btn { border: 1px solid var(--gris-moyen); border-radius: 999px; background: var(--blanc); color: var(--text); font-weight: 600; padding: 10px 14px; cursor: pointer; transition: background 0.2s, border-color 0.2s; }
    .filter-btn.active, .filter-btn:hover { border-color: var(--vert-kool); background: var(--vert-kool-light); }
    .week-selector { display: inline-flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 999px; background: var(--gris-clair); }
    .arrow-btn { width: 34px; height: 34px; border: 1px solid var(--gris-moyen); border-radius: 50%; background: var(--blanc); color: var(--text); cursor: pointer; font-size: 1.1rem; }
    .meal-body { display: grid; grid-template-columns: 1.8fr 1fr; gap: 18px; margin-top: 18px; }
    .calendar-panel { overflow-x: auto; }
    .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(160px, 1fr)); gap: 14px; }
    .day-column { background: var(--blanc); border: 1px solid var(--gris-moyen); border-radius: 18px; padding: 14px; min-height: 360px; display: flex; flex-direction: column; }
    .day-column .day-header { font-weight: 800; margin-bottom: 12px; color: #31543b; }
    .meal-card { background: var(--blanc); border-radius: 18px; border: 1px solid transparent; box-shadow: var(--ombre-legere); padding: 12px 14px; margin-bottom: 12px; cursor: pointer; transition: transform 0.18s ease, border-color 0.18s ease; }
    .meal-card:hover { transform: translateY(-1px); border-color: rgba(0,0,0,0.08); }
    .meal-card.selected { border-color: var(--bleu-tech); box-shadow: 0 0 0 2px rgba(41, 182, 246, 0.18); }
    .meal-badge { display: inline-flex; align-items: center; gap: 8px; font-size: 0.78rem; font-weight: 700; margin-bottom: 10px; }
    .meal-badge span { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .meal-title { font-weight: 700; margin-bottom: 8px; color: #2d4739; }
    .meal-meta { color: #5b6f5f; font-size: 0.86rem; line-height: 1.5; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .status-dot.planifie { background: #9e9e9e; }
    .status-dot.consomme { background: #34a853; }
    .status-dot.saute { background: #fb8c00; }
    .badge-yellow { color: #9c6a00; background: #fff7d6; border-radius: 999px; padding: 4px 10px; }
    .badge-green { color: #1f5f2f; background: #e8f7e9; border-radius: 999px; padding: 4px 10px; }
    .badge-blue { color: #044f8b; background: #e5f2ff; border-radius: 999px; padding: 4px 10px; }
    .badge-pink { color: #a1234d; background: #fde7f0; border-radius: 999px; padding: 4px 10px; }
    .meal-details-panel { display: grid; gap: 18px; }
    .detail-card, .chart-card { background: var(--blanc); border: 1px solid var(--gris-moyen); border-radius: 18px; padding: 18px; box-shadow: var(--ombre-legere); }
    .detail-title { font-size: 1.1rem; font-weight: 800; margin-bottom: 10px; color: #244f37; }
    .detail-text { color: #5b6f5f; font-size: 0.94rem; line-height: 1.6; }
    .detail-row { margin-bottom: 12px; }
    .detail-row strong { display: inline-block; min-width: 120px; color: #3e5d45; }
    .detail-row span { color: #5b6f5f; font-size: 0.92rem; }
    .mini-chart { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; align-items: end; min-height: 180px; margin-top: 18px; }
    .chart-bar { width: 100%; border-radius: 16px 16px 0 0; background: linear-gradient(180deg, rgba(76,175,80,0.95), rgba(76,175,80,0.6)); display: flex; align-items: flex-end; justify-content: center; color: #fff; font-size: 0.72rem; padding-bottom: 6px; min-height: 22px; }
    .chart-day-label { margin-top: 8px; text-align: center; font-size: 0.78rem; color: #5b6f5f; }
    @media (max-width: 980px) { .meal-body { display: block; } .calendar-grid { grid-template-columns: repeat(2, minmax(160px, 1fr)); } }
    @media (max-width: 660px) { .calendar-grid { grid-template-columns: 1fr; } .meal-header .meal-controls { flex-direction: column; align-items: stretch; } }
    @media (max-width: 520px) { .filter-group { justify-content: center; } .week-selector { width: 100%; justify-content: center; } }
    @media (max-width: 980px) { .sidebar { width: 84px; } .logo-area h2 span, .logo-area p, .nav-item span, .user-info { display: none; } .nav-item { justify-content: center; } .crud-grid { grid-template-columns: 1fr; } .data-table { min-width: 0; } }
  </style>
</head>
<body>
  <div class="app-wrapper">
    <aside class="sidebar">
      <div class="logo-area">
        <h2><i class="fas fa-seedling"></i><span>Kool Healthy</span></h2>
        <p>backoffice CRUD PHP</p>
      </div>

      <div class="nav-menu">
        <div class="nav-item active" data-tab="dashboard"><i class="fas fa-chart-pie"></i><span>Dashboard</span></div>
        <div class="nav-item" data-tab="users"><i class="fas fa-users"></i><span>Utilisateurs</span></div>
        <div class="nav-item" data-tab="plan"><i class="fas fa-clipboard-list"></i><span>Plan</span></div>
        <div class="nav-item" data-tab="repas"><i class="fas fa-utensils"></i><span>Repas</span></div>
        <div class="nav-item" data-tab="analytics"><i class="fas fa-chart-line"></i><span>Analytics IA</span></div>
      </div>

      <div class="sidebar-footer">
        <div class="user-badge">
          <div class="user-avatar"><i class="fas fa-user-md"></i></div>
          <div class="user-info">
            <p>Admin</p>
            <small>admin@koolhealthy.com</small>
          </div>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Backoffice</h1>
          <p>Gestion et CRUD des plans nutritionnels</p>
        </div>
        <button class="btn-outline" onclick="window.location.href='index.php?page=plan-nutritionnel'">Retour module plan</button>
      </div>

      <?php if ($message !== '') : ?>
        <div class="card-panel form-message <?= $messageType === 'success' ? 'success' : 'error' ?>">
          <?= h($message) ?>
        </div>
      <?php endif; ?>

      <section id="dashboardContent" class="dashboard-container active">
        <div class="stats-grid">
          <div class="stat-card"><div class="stat-title">Plans enregistrés</div><div class="stat-value" id="plansCount"><?= count($plans) ?></div></div>
          <div class="stat-card"><div class="stat-title">Perte poids</div><div class="stat-value" id="goalLoss"><?= count(array_filter($plans, fn($p) => $p['objectif'] === 'perte-poids')) ?></div></div>
          <div class="stat-card"><div class="stat-title">Maintien</div><div class="stat-value" id="goalKeep"><?= count(array_filter($plans, fn($p) => $p['objectif'] === 'maintien')) ?></div></div>
          <div class="stat-card"><div class="stat-title">Prise muscle</div><div class="stat-value" id="goalGain"><?= count(array_filter($plans, fn($p) => $p['objectif'] === 'prise-muscle')) ?></div></div>
        </div>
      </section>

      <section id="usersContent" class="dashboard-container">
        <div class="card-panel">
          <div class="panel-header">
            <h3><i class="fas fa-users"></i> Liste clients</h3>
            <span class="badge-tech">lecture simple</span>
          </div>
          <div class="data-table-wrap">
            <table class="data-table" id="clientsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Utilisateurs</th>
                  <th>Objectif</th>
                  <th>Durée</th>
                  <th>Préférence</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($plans as $plan) : ?>
                  <tr>
                    <td><?= h($plan['id']) ?></td>
                    <td><?= h($plan['nom']) ?></td>
                    <td><?= h(getObjectifLabel($plan['objectif'])) ?></td>
                    <td><?= h($plan['duree']) ?></td>
                    <td><?= h($plan['preference']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section id="planContent" class="dashboard-container">
        <div class="card-panel">
          <div class="panel-header">
            <h3><i class="fas fa-table"></i> Tableau des plans</h3>
            <span class="badge-tech">gestion admin</span>
          </div>
          <p style="color:#5b6f5f; font-size:0.88rem; margin-bottom:14px;">La création et modification des plans se fait depuis le <a href="index.php?page=plan-nutritionnel" style="color:#29B6F6;font-weight:600;">module plan</a>.</p>
          <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
              <input type="text" id="searchPlanInput" placeholder="🔍 Rechercher par nom ou ID utilisateur..." style="flex:2; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
              <select id="filterObjectif" style="padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; background: white;">
                  <option value="all">📊 Tous les objectifs</option>
                  <option value="perte-poids">🎯 Perte de poids</option>
                  <option value="maintien">⚖️ Maintien</option>
                  <option value="prise-muscle">💪 Prise de muscle</option>
              </select>
              <button type="button" id="resetPlanFilters" style="padding: 10px 16px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer;">🔄 Réinitialiser</button>
          </div>
          <div class="data-table-wrap">
            <table class="data-table" id="plansTable">
              <thead>
                <tr>
                  <th>ID plan</th>
                  <th>Nom</th>
                  <th>Objectif</th>
                  <th>Utilisateur</th>
                  <th>Durée</th>
                  <th>Préférence</th>
                  <th>Allergies</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($plans as $plan) : ?>
                  <tr>
                    <td><?= h($plan['id']) ?></td>
                    <td><?= h($plan['nom']) ?></td>
                    <td><?= h(getObjectifLabel($plan['objectif'])) ?></td>
                    <td><?= h($plan['utilisateur_id']) ?></td>
                    <td><?= h($plan['duree']) ?></td>
                    <td><?= h($plan['preference']) ?></td>
                    <td><?= h($plan['allergies']) ?></td>
                    <td>
                      <form method="post" style="display:inline-block; margin:0;" onsubmit="return confirmDeletePlan();">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= h($plan['id']) ?>">
                        <button type="submit" class="mini-btn danger" style="padding:6px 12px; font-size:0.75rem;">
                          <i class="fas fa-trash"></i> Supprimer
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section id="repasContent" class="dashboard-container">
        <div class="card-panel meal-container">
          <div class="panel-header meal-header">
            <div>
              <h3><i class="fas fa-utensils"></i> Repas hebdomadaire</h3>
              <p style="color:#5b6f5f; margin:8px 0 0;">Vue calendrier des repas avec filtres, statut et détails.</p>
            </div>
            <div class="meal-controls">
              <div class="filter-group" id="mealFilters">
                <button type="button" class="filter-btn active" data-filter="all">Tous</button>
                <button type="button" class="filter-btn" data-filter="petit-dejeuner">Petit-déj</button>
                <button type="button" class="filter-btn" data-filter="dejeuner">Déjeuner</button>
                <button type="button" class="filter-btn" data-filter="diner">Dîner</button>
                <button type="button" class="filter-btn" data-filter="collation">Collation</button>
              </div>
              <div class="week-selector">
                <button type="button" class="arrow-btn" id="prevWeekBtn">&lsaquo;</button>
                <span id="weekLabel">Semaine actuelle</span>
                <button type="button" class="arrow-btn" id="nextWeekBtn">&rsaquo;</button>
              </div>
            </div>
          </div>

          <div class="meal-body">
            <div class="calendar-panel">
              <div class="calendar-grid" id="calendarGrid"></div>
            </div>
            <aside class="meal-details-panel">
              <div class="detail-card" id="mealDetailCard">
                <div class="detail-title">Sélectionnez un repas</div>
                <div class="detail-text">Cliquez sur une carte repas pour afficher les informations ici.</div>
              </div>
              <div class="chart-card">
                <div class="panel-header">
                  <h4><i class="fas fa-chart-bar"></i> Calories par jour</h4>
                  <span class="badge-tech">consommées</span>
                </div>
                <div class="mini-chart" id="caloriesChart"></div>
              </div>
            </aside>
          </div>
        </div>

        <!-- CRUD Repas Table -->
        <div class="card-panel" style="margin-top:18px;">
          <div class="panel-header">
            <h3><i class="fas fa-table"></i> Gestion des Repas (CRUD)</h3>
            <div class="btn-row">
              <button type="button" class="mini-btn" id="btnAjouterRepas" onclick="openRepasModal('create')">
                <i class="fas fa-plus"></i> Ajouter
              </button>
              <button type="button" class="mini-btn secondary" id="btnModifierRepas" onclick="openRepasModal('update')">
                <i class="fas fa-pen"></i> Modifier
              </button>
              <form method="post" id="deleteRepasForm" style="display:inline;" onsubmit="return confirmDeleteRepas();">
                <input type="hidden" name="action_type" value="repas">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteRepasId" value="">
                <button type="submit" class="mini-btn danger" id="btnSupprimerRepas">
                  <i class="fas fa-trash"></i> Supprimer
                </button>
              </form>
            </div>
          </div>
          <?php
          // Calcul des statistiques des repas
          $totalCaloriesConsommees = 0;
          $totalCaloriesPrevues = 0;
          $totalRepasConsommes = 0;
          $totalRepasAnnules = 0;
          $totalRepasStats = count($repasList ?? []);

          if (!empty($repasList)) {
              foreach ($repasList as $r) {
                  if ($r['statut'] === 'consomme') {
                      $totalCaloriesConsommees += (int)($r['calories_consommees'] ?? 0);
                      $totalRepasConsommes++;
                  } elseif ($r['statut'] === 'prevu') {
                      $totalCaloriesPrevues += (int)($r['calories_consommees'] ?? 0);
                  } elseif ($r['statut'] === 'annule') {
                      $totalRepasAnnules++;
                  }
              }
          }
          $pourcentageConsommation = $totalRepasStats > 0 ? round(($totalRepasConsommes / $totalRepasStats) * 100) : 0;
          ?>
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
              <div style="background: #e8f5e9; padding: 15px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 28px; font-weight: bold; color: #2e7d32;"><?= $totalCaloriesConsommees ?></div>
                  <div style="font-size: 12px; color: #555;">kcal consommées</div>
              </div>
              <div style="background: #fff3e0; padding: 15px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 28px; font-weight: bold; color: #e65100;"><?= $totalCaloriesPrevues ?></div>
                  <div style="font-size: 12px; color: #555;">kcal prévues</div>
              </div>
              <div style="background: #e3f2fd; padding: 15px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 28px; font-weight: bold; color: #1565c0;"><?= $totalRepasConsommes ?> / <?= $totalRepasStats ?></div>
                  <div style="font-size: 12px; color: #555;">repas consommés</div>
              </div>
              <div style="background: #fce4ec; padding: 15px; border-radius: 12px; text-align: center;">
                  <div style="font-size: 28px; font-weight: bold; color: #c62828;"><?= $pourcentageConsommation ?>%</div>
                  <div style="font-size: 12px; color: #555;">taux de complétion</div>
              </div>
          </div>
          <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
              <input type="text" id="searchRepasInput" placeholder="🔍 Rechercher par recette ou plan..." style="flex:2; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
              <select id="filterTypeRepas" style="padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; background: white;">
                  <option value="all">🍽️ Tous les types</option>
                  <option value="petit_dejeuner">🍳 Petit-déjeuner</option>
                  <option value="dejeuner">🍲 Déjeuner</option>
                  <option value="diner">🍝 Dîner</option>
                  <option value="collation">🍎 Collation</option>
              </select>
              <select id="filterStatutRepas" style="padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; background: white;">
                  <option value="all">📊 Tous les statuts</option>
                  <option value="prevu">⏳ Prévu</option>
                  <option value="consomme">✅ Consommé</option>
                  <option value="annule">❌ Annulé</option>
              </select>
              <button type="button" id="resetRepasFilters" style="padding: 10px 16px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer;">🔄 Réinitialiser</button>
          </div>
          <div class="data-table-wrap">
            <table class="data-table" id="repasTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Plan</th>
                  <th>Nom recette</th>
                  <th>Date</th>
                  <th>Type repas</th>
                  <th>Statut</th>
                  <th>Calories</th>
                  <th>Heure prévue</th>
                  <th>Heure réelle</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $typeRepasLabels = [
                  'petit_dejeuner' => 'Petit-déj',
                  'dejeuner'       => 'Déjeuner',
                  'diner'          => 'Dîner',
                  'collation'      => 'Collation',
                ];
                $statutLabels = [
                  'prevu'    => 'Prévu',
                  'consomme' => 'Consommé',
                  'annule'   => 'Annulé',
                ];
                if (!empty($repasList)) : foreach ($repasList as $repas) : ?>
                <tr onclick="selectRepasRow(this, <?= (int)$repas['id'] ?>)"
                    data-repas='<?= htmlspecialchars(json_encode($repas, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>'>
                  <td><?= h($repas['id']) ?></td>
                  <td><?= h($repas['plan_nom'] ?? 'Plan #' . $repas['plan_id']) ?></td>
                  <td><?= h($repas['nom_recette'] ?? '—') ?></td>
                  <td><?= h($repas['date']) ?></td>
                  <td><?= h($typeRepasLabels[$repas['type_repas']] ?? $repas['type_repas']) ?></td>
                  <td><?= h($statutLabels[$repas['statut']] ?? $repas['statut']) ?></td>
                  <td><?= h($repas['calories_consommees'] ?? '—') ?></td>
                  <td><?= h($repas['heure_prevue'] ?? '—') ?></td>
                  <td><?= h($repas['heure_reelle'] ?? '—') ?></td>
                  <td><?= h(mb_strimwidth($repas['notes'] ?? '', 0, 50, '…')) ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="10" style="text-align:center;color:#5b6f5f;padding:20px;">Aucun repas enregistré.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Repas -->
        <div id="repasModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
          <div style="background:#fff; border-radius:18px; padding:28px; width:100%; max-width:580px; box-shadow:0 8px 40px rgba(0,0,0,0.18); position:relative; margin:auto; max-height:90vh; overflow-y:auto; top:50%; transform:translateY(-50%);">
            <h3 style="color:var(--vert-kool-dark); margin-bottom:18px;" id="repasModalTitle"><i class="fas fa-utensils"></i> Ajouter un repas</h3>
            <form method="post" id="repasForm" onsubmit="return validateRepasFormJS(this);">
              <?php if ($message && $messageType === 'error' && str_contains($message, 'repas')) : ?>
                <div class="form-message error" style="margin-bottom: 15px;">
                  <?= h($message) ?>
                </div>
              <?php endif; ?>
              <input type="hidden" name="action_type" value="repas">
              <input type="hidden" name="action" id="repasActionInput" value="create">
              <input type="hidden" name="id" id="repasIdInput" value="">

              <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Plan <span style="color:red">*</span></label>
                  <select name="plan_id" id="r_plan_id" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;background:white;">
                    <option value="">-- Choisir un plan --</option>
                    <?php foreach ($plans as $p): ?>
                    <option value="<?= h($p['id']) ?>"><?= h($p['id']) ?> — <?= h($p['nom']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Nom de la recette <span style="color:red">*</span></label>
                  <input type="text" name="nom_recette" id="r_nom_recette" placeholder="Ex: Salade quinoa" maxlength="255" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;">
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Date <span style="color:red">*</span></label>
                  <input type="text" name="date" id="r_date" placeholder="AAAA-MM-JJ" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;">
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Type de repas <span style="color:red">*</span></label>
                  <select name="type_repas" id="r_type_repas" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;background:white;">
                    <option value="">-- Choisir --</option>
                    <option value="petit_dejeuner">Petit-déjeuner</option>
                    <option value="dejeuner">Déjeuner</option>
                    <option value="diner">Dîner</option>
                    <option value="collation">Collation</option>
                  </select>
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Statut <span style="color:red">*</span></label>
                  <select name="statut" id="r_statut" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;background:white;">
                    <option value="prevu">Prévu</option>
                    <option value="consomme">Consommé</option>
                    <option value="annule">Annulé</option>
                  </select>
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Calories consommées (min 1400)</label>
                  <input type="text" name="calories_consommees" id="r_calories" placeholder="ex: 1800" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;">
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Heure prévue (optionnel)</label>
                  <input type="text" name="heure_prevue" id="r_heure_prevue" placeholder="HH:MM" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;">
                </div>
                <div>
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Heure réelle (optionnel)</label>
                  <input type="text" name="heure_reelle" id="r_heure_reelle" placeholder="HH:MM" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;">
                </div>
                <div style="grid-column:span 2;">
                  <label style="font-size:0.82rem;font-weight:600;color:#3e5d45;">Notes (max 1000 caractères)</label>
                  <textarea name="notes" id="r_notes" rows="3" maxlength="1000" placeholder="Notes optionnelles…" style="width:100%;border:1px solid var(--gris-moyen);border-radius:10px;padding:10px;font:inherit;resize:vertical;"></textarea>
                </div>
              </div>

              <div id="repasFormErrors" style="display:none;margin-top:10px;color:#b71c1c;font-size:0.92rem;background:#fbe9e7;border:1px solid #f5c6cb;border-radius:10px;padding:10px 14px;"></div>

              <div class="btn-row" style="margin-top:16px;">
                <button type="submit" class="mini-btn" id="repasSubmitBtn">Enregistrer</button>
                <button type="button" class="mini-btn secondary" onclick="closeRepasModal()">Annuler</button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <section id="analyticsContent" class="dashboard-container">
        <div class="card-panel">
          <div class="panel-header">
            <h3><i class="fas fa-brain"></i> Analytics IA</h3>
            <span class="badge-tech">mock data</span>
          </div>
          <p style="color:#5b6f5f;">Suggestion IA: augmenter les legumes secs pour reduire l'empreinte carbone des plans de 16%.</p>
        </div>
      </section>
    </main>
  </div>

  <script>
    const tabs = {
      dashboard: document.getElementById('dashboardContent'),
      users: document.getElementById('usersContent'),
      plan: document.getElementById('planContent'),
      repas: document.getElementById('repasContent'),
      analytics: document.getElementById('analyticsContent'),
    };

    document.querySelectorAll('.nav-item').forEach((item) => {
      item.addEventListener('click', () => {
        const tab = item.getAttribute('data-tab');
        Object.keys(tabs).forEach((key) => tabs[key].classList.toggle('active', key === tab));
        document.querySelectorAll('.nav-item').forEach((n) => n.classList.toggle('active', n === item));
      });
    });
  </script>

 <script>
    const plansData = <?= json_encode($plans, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    // Filtrage et recherche pour le tableau des plans
    function filterPlansTable() {
        const searchText = document.getElementById('searchPlanInput').value.toLowerCase();
        const filterObjectif = document.getElementById('filterObjectif').value;
        const rows = document.querySelectorAll('#plansTable tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const nom = row.cells[1]?.textContent.toLowerCase() || '';
            const utilisateurId = row.cells[3]?.textContent.toLowerCase() || '';
            const objectif = row.cells[2]?.textContent.toLowerCase() || '';
            
            let objectifValue = '';
            if (objectif.includes('perte')) objectifValue = 'perte-poids';
            else if (objectif.includes('maintien')) objectifValue = 'maintien';
            else if (objectif.includes('prise')) objectifValue = 'prise-muscle';
            
            const matchSearch = searchText === '' || nom.includes(searchText) || utilisateurId.includes(searchText);
            const matchObjectif = filterObjectif === 'all' || objectifValue === filterObjectif;
            
            row.style.display = (matchSearch && matchObjectif) ? '' : 'none';
            if (matchSearch && matchObjectif) visibleCount++;
        });
        
        // Afficher un message si aucun résultat
        const tbody = document.querySelector('#plansTable tbody');
        let noResultMsg = document.getElementById('noPlanResult');
        if (!noResultMsg) {
            noResultMsg = document.createElement('tr');
            noResultMsg.id = 'noPlanResult';
            noResultMsg.innerHTML = '<td colspan="8" style="text-align:center; padding:30px; color:#999;">Aucun plan trouvé</td>';
            tbody.appendChild(noResultMsg);
        }
        noResultMsg.style.display = visibleCount === 0 ? '' : 'none';
    }

    document.getElementById('searchPlanInput').addEventListener('keyup', filterPlansTable);
    document.getElementById('filterObjectif').addEventListener('change', filterPlansTable);
    document.getElementById('resetPlanFilters').addEventListener('click', function() {
        document.getElementById('searchPlanInput').value = '';
        document.getElementById('filterObjectif').value = 'all';
        filterPlansTable();
    });

    // Tri des colonnes du tableau des plans
    let sortColumn = null;
    let sortDirection = 'asc';

    function sortPlansTable(columnIndex) {
        const tbody = document.querySelector('#plansTable tbody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(#noPlanResult)'));
        const noResultMsg = document.getElementById('noPlanResult');
        
        if (sortColumn === columnIndex) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = columnIndex;
            sortDirection = 'asc';
        }
        
        rows.sort((a, b) => {
            let aVal = a.cells[columnIndex]?.textContent.trim() || '';
            let bVal = b.cells[columnIndex]?.textContent.trim() || '';
            
            if (columnIndex === 0 || columnIndex === 4) { // ID ou Durée
                aVal = parseInt(aVal) || 0;
                bVal = parseInt(bVal) || 0;
            } else {
                aVal = aVal.toLowerCase();
                bVal = bVal.toLowerCase();
            }
            
            if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
            if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
        
        rows.forEach(row => tbody.appendChild(row));
        if (noResultMsg) tbody.appendChild(noResultMsg);
        
        // Mettre à jour les icônes de tri
        document.querySelectorAll('#plansTable th').forEach((th, idx) => {
            th.style.cursor = 'pointer';
            th.title = 'Cliquer pour trier';
            th.innerHTML = th.innerHTML.replace(' ▲', '').replace(' ▼', '');
            if (idx === columnIndex) {
                th.innerHTML += sortDirection === 'asc' ? ' ▲' : ' ▼';
            }
        });
    }

    // Ajouter les écouteurs sur les en-têtes
    document.querySelectorAll('#plansTable th').forEach((th, idx) => {
        if (idx < 7) { // Pas de tri sur la colonne Actions
            th.style.cursor = 'pointer';
            th.addEventListener('click', () => sortPlansTable(idx));
        }
    });

    // Filtrage pour le tableau des repas
    function filterRepasTable() {
        const searchText = document.getElementById('searchRepasInput').value.toLowerCase();
        const filterType = document.getElementById('filterTypeRepas').value;
        const filterStatut = document.getElementById('filterStatutRepas').value;
        const rows = document.querySelectorAll('#repasTable tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const recette = row.cells[2]?.textContent.toLowerCase() || '';
            const plan = row.cells[1]?.textContent.toLowerCase() || '';
            const type = row.cells[4]?.getAttribute('data-type') || row.cells[4]?.textContent.toLowerCase() || '';
            const statut = row.cells[5]?.textContent.toLowerCase() || '';
            
            let statutValue = '';
            if (statut.includes('prévu')) statutValue = 'prevu';
            else if (statut.includes('consommé')) statutValue = 'consomme';
            else if (statut.includes('annulé')) statutValue = 'annule';
            
            let typeValue = '';
            if (type.includes('petit')) typeValue = 'petit_dejeuner';
            else if (type.includes('déjeuner')) typeValue = 'dejeuner';
            else if (type.includes('dîner')) typeValue = 'diner';
            else if (type.includes('collation')) typeValue = 'collation';
            
            const matchSearch = searchText === '' || recette.includes(searchText) || plan.includes(searchText);
            const matchType = filterType === 'all' || typeValue === filterType;
            const matchStatut = filterStatut === 'all' || statutValue === filterStatut;
            
            row.style.display = (matchSearch && matchType && matchStatut) ? '' : 'none';
            if (matchSearch && matchType && matchStatut) visibleCount++;
        });
        
        let noResultMsg = document.getElementById('noRepasResult');
        if (!noResultMsg) {
            noResultMsg = document.createElement('tr');
            noResultMsg.id = 'noRepasResult';
            noResultMsg.innerHTML = '<td colspan="10" style="text-align:center; padding:30px; color:#999;">Aucun repas trouvé</td>';
            document.querySelector('#repasTable tbody').appendChild(noResultMsg);
        }
        noResultMsg.style.display = visibleCount === 0 ? '' : 'none';
    }

    document.getElementById('searchRepasInput').addEventListener('keyup', filterRepasTable);
    document.getElementById('filterTypeRepas').addEventListener('change', filterRepasTable);
    document.getElementById('filterStatutRepas').addEventListener('change', filterRepasTable);
    document.getElementById('resetRepasFilters').addEventListener('click', function() {
        document.getElementById('searchRepasInput').value = '';
        document.getElementById('filterTypeRepas').value = 'all';
        document.getElementById('filterStatutRepas').value = 'all';
        filterRepasTable();
    });

    // Utiliser les vrais repas de la base de données
    const realRepasData = <?= json_encode($repasList ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    const mealTypeMap = {
      'petit-dejeuner': { label: 'Petit-déj', badge: 'badge-yellow' },
      'dejeuner': { label: 'Déjeuner', badge: 'badge-green' },
      'diner': { label: 'Dîner', badge: 'badge-blue' },
      'collation': { label: 'Collation', badge: 'badge-pink' },
    };

    const statusLabels = {
      planifie: 'Planifié',
      consomme: 'Consommé',
      saute: 'Sauté',
    };

    const weekNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
    const baseDate = new Date();
    const mondayOffset = baseDate.getDay() === 0 ? -6 : 1 - baseDate.getDay();
    const weekStartBase = new Date(baseDate);
    weekStartBase.setDate(baseDate.getDate() + mondayOffset);

    let currentFilter = 'all';
    let weekOffset = 0;
    let selectedMealId = null;

    function formatWeekLabel(offset) {
      const monday = new Date(weekStartBase);
      monday.setDate(monday.getDate() + offset * 7);
      const sunday = new Date(monday);
      sunday.setDate(monday.getDate() + 6);
      return `Semaine du ${monday.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })} au ${sunday.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })}`;
    }

    // Modifier la fonction renderCalendar() pour utiliser realRepasData
    function renderCalendar() {
        const grid = document.getElementById('calendarGrid');
        if (!grid) return;
        grid.innerHTML = '';
        
        // Calculer les dates de la semaine
        const weekStart = new Date(weekStartBase);
        weekStart.setDate(weekStartBase.getDate() + weekOffset * 7);
        
        for (let day = 0; day < 7; day++) {
            const currentDate = new Date(weekStart);
            currentDate.setDate(weekStart.getDate() + day);
            const dateKey = currentDate.toISOString().split('T')[0];
            
            const column = document.createElement('div');
            column.className = 'day-column';
            column.innerHTML = `<div class="day-header">${weekNames[day]} ${currentDate.getDate()}/${currentDate.getMonth()+1}</div>`;
            
            const dayMeals = realRepasData
                .filter(repas => repas.date === dateKey && (currentFilter === 'all' || repas.type_repas === currentFilter))
                .sort((a, b) => (a.heure_prevue || '00:00').localeCompare(b.heure_prevue || '00:00'));
            
            if (dayMeals.length === 0) {
                const empty = document.createElement('div');
                empty.style.cssText = 'color:#73857a; font-size:0.92rem; margin-top:10px; text-align:center;';
                empty.textContent = '📭 Aucun repas';
                column.appendChild(empty);
            }
            
            dayMeals.forEach(repas => {
                const card = document.createElement('div');
                card.className = 'meal-card';
                card.dataset.mealid = repas.id;
                
                let repastype = repas.type_repas ? repas.type_repas.replace('_', '-') : '';
                const typeMeta = mealTypeMap[repastype] || { label: 'Repas', badge: 'badge-yellow' };
                const statutClass = repas.statut === 'consomme' ? 'consomme' : (repas.statut === 'annule' ? 'saute' : 'planifie');
                const statutText = { 'prevu': 'Prévu', 'consomme': 'Consommé', 'annule': 'Annulé' }[repas.statut] || 'Prévu';
                
                card.innerHTML = `
                    <div class="meal-badge"><span class="${typeMeta.badge}"></span>${typeMeta.label}</div>
                    <div class="meal-title">${repas.nom_recette || 'Repas'}</div>
                    <div class="meal-meta">${repas.calories_consommees || 0} kcal • ${repas.heure_prevue || '--:--'}</div>
                    <div class="meal-meta"><span class="status-dot ${statutClass}"></span>${statutText}</div>
                `;
                card.addEventListener('click', () => selectMeal(repas.id));
                column.appendChild(card);
            });
            grid.appendChild(column);
        }
    }

    function renderDetail(meal) {
      const detailCard = document.getElementById('mealDetailCard');
      if (!detailCard) return;
      detailCard.innerHTML = `
        <div class="detail-title">${meal.title}</div>
        <div class="detail-row"><strong>Type :</strong><span>${mealTypeMap[meal.type]?.label || meal.type}</span></div>
        <div class="detail-row"><strong>Recette :</strong><span>${meal.recipe}</span></div>
        <div class="detail-row"><strong>Calories :</strong><span>${meal.calories} kcal</span></div>
        <div class="detail-row"><strong>Heure prévue :</strong><span>${meal.planned}</span></div>
        <div class="detail-row"><strong>Heure réelle :</strong><span>${meal.actual || 'Non enregistrée'}</span></div>
        <div class="detail-row"><strong>Statut :</strong><span>${statusLabels[meal.status] || 'Planifié'}</span></div>
        <div class="detail-row"><strong>Progression :</strong><span>${meal.progress}</span></div>
        <div class="detail-row"><strong>Détails :</strong><span>${meal.description}</span></div>
      `;
    }

    function selectMeal(id, focus = true) {
      selectedMealId = id;
      document.querySelectorAll('.meal-card').forEach((card) => card.classList.remove('selected'));
      const selectedCard = document.querySelector(`.meal-card[data-mealid="${id}"]`);
      if (selectedCard) {
        selectedCard.classList.add('selected');
      }
      const meal = mealData.find((item) => item.id === id);
      if (meal) renderDetail(meal);
      if (focus && selectedCard) selectedCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Modifier la fonction renderChart() pour utiliser realRepasData
    function renderChart() {
        const chart = document.getElementById('caloriesChart');
        if (!chart) return;
        
        const repasData = realRepasData;
        const weekStart = new Date(weekStartBase);
        weekStart.setDate(weekStartBase.getDate() + weekOffset * 7);
        
        const caloriesParJour = {};
        for (let i = 0; i < 7; i++) {
            const dayDate = new Date(weekStart);
            dayDate.setDate(weekStart.getDate() + i);
            const dateKey = dayDate.toISOString().split('T')[0];
            caloriesParJour[dateKey] = 0;
        }
        
        repasData.forEach(repas => {
            if (repas.statut === 'consomme' && repas.calories_consommees) {
                if (caloriesParJour[repas.date] !== undefined) {
                    caloriesParJour[repas.date] += parseInt(repas.calories_consommees);
                }
            }
        });
        
        const values = [];
        let maxCalories = 0;
        for (let i = 0; i < 7; i++) {
            const dayDate = new Date(weekStart);
            dayDate.setDate(weekStart.getDate() + i);
            const dateKey = dayDate.toISOString().split('T')[0];
            const calories = caloriesParJour[dateKey] || 0;
            values.push({ label: weekNames[i], value: calories });
            if (calories > maxCalories) maxCalories = calories;
        }
        
        const maxHeight = 150;
        chart.innerHTML = values.map(entry => {
            let height = 20;
            if (maxCalories > 0) height = Math.max(20, Math.min(maxHeight, (entry.value / maxCalories) * maxHeight));
            return `<div style="flex:1; text-align:center;"><div style="height:${height}px; background:linear-gradient(180deg, #4caf50, #2e7d32); border-radius:8px 8px 0 0; margin-bottom:5px; display:flex; align-items:flex-end; justify-content:center; color:white; font-size:11px; padding-bottom:3px;">${entry.value || ''}</div><div style="font-size:11px; color:#555;">${entry.label}</div></div>`;
        }).join('');
    }

    function updateWeekNavigation() {
      const label = document.getElementById('weekLabel');
      if (!label) return;
      label.textContent = formatWeekLabel(weekOffset);
      renderCalendar();
      renderChart();
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.filter-btn').forEach((button) => {
        button.addEventListener('click', () => {
          currentFilter = button.getAttribute('data-filter');
          document.querySelectorAll('.filter-btn').forEach((btn) => btn.classList.toggle('active', btn === button));
          renderCalendar();
        });
      });

      const prevButton = document.getElementById('prevWeekBtn');
      const nextButton = document.getElementById('nextWeekBtn');
      if (prevButton) prevButton.addEventListener('click', () => {
        weekOffset -= 1;
        updateWeekNavigation();
      });
      if (nextButton) nextButton.addEventListener('click', () => {
        weekOffset += 1;
        updateWeekNavigation();
      });

      updateWeekNavigation();
    });


  </script>

  <script src="assets/form-validation.js?v=<?= time() ?>"></script>

  <script>
    /* ======= REPAS CRUD JS ======= */

    let selectedRepasId = null;
    let selectedRepasData = null;

    function selectRepasRow(tr, id) {
      document.querySelectorAll('#repasTable tbody tr').forEach(r => r.classList.remove('selected'));
      tr.classList.add('selected');
      selectedRepasId = id;
      selectedRepasData = JSON.parse(tr.getAttribute('data-repas'));
      document.getElementById('deleteRepasId').value = id;
    }

    function openRepasModal(mode) {
      if (mode === 'update') {
        if (!selectedRepasId) {
          alert('Veuillez sélectionner un repas dans le tableau avant de modifier.');
          return;
        }
        document.getElementById('repasModalTitle').innerHTML = '<i class="fas fa-pen"></i> Modifier le repas';
        document.getElementById('repasActionInput').value = 'update';
        document.getElementById('repasIdInput').value = selectedRepasData.id;
        document.getElementById('r_plan_id').value = selectedRepasData.plan_id || '';
        document.getElementById('r_nom_recette').value = selectedRepasData.nom_recette || '';
        document.getElementById('r_date').value = selectedRepasData.date || '';
        document.getElementById('r_type_repas').value = selectedRepasData.type_repas || '';
        document.getElementById('r_statut').value = selectedRepasData.statut || 'prevu';
        document.getElementById('r_calories').value = selectedRepasData.calories_consommees || '';
        document.getElementById('r_heure_prevue').value = selectedRepasData.heure_prevue || '';
        document.getElementById('r_heure_reelle').value = selectedRepasData.heure_reelle || '';
        document.getElementById('r_notes').value = selectedRepasData.notes || '';
      } else {
        document.getElementById('repasModalTitle').innerHTML = '<i class="fas fa-plus"></i> Ajouter un repas';
        document.getElementById('repasActionInput').value = 'create';
        document.getElementById('repasIdInput').value = '';
        document.getElementById('repasForm').reset();
      }
      document.getElementById('repasFormErrors').style.display = 'none';
      document.getElementById('repasFormErrors').innerHTML = '';
      document.getElementById('repasModal').style.display = 'flex';
    }

    function closeRepasModal() {
      document.getElementById('repasModal').style.display = 'none';
    }

    // Fermer le modal en cliquant en dehors
    document.getElementById('repasModal').addEventListener('click', function(e) {
      if (e.target === this) closeRepasModal();
    });

    function confirmDeletePlan() {
        return confirm('Voulez-vous vraiment supprimer ce plan ? Tous les repas associés seront également supprimés.');
    }

    function confirmDeleteRepas() {
      if (!selectedRepasId) {
        alert('Veuillez sélectionner un repas dans le tableau avant de supprimer.');
        return false;
      }
      return confirm('Voulez-vous vraiment supprimer ce repas ?');
    }

    function validateRepasFormJS(form) {
      const errors = [];

      const planId = document.getElementById('r_plan_id').value;
      if (!planId || planId === '') {
        errors.push('Le plan est obligatoire.');
      }

      const nomRecette = document.getElementById('r_nom_recette').value.trim();
      if (!nomRecette) {
        errors.push('Le nom de la recette est obligatoire.');
      } else if (nomRecette.length > 255) {
        errors.push('Le nom de la recette ne peut pas dépasser 255 caractères.');
      }

      const dateVal = document.getElementById('r_date').value;
      const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
      if (!dateVal || !dateRegex.test(dateVal)) {
        errors.push('La date est obligatoire (format AAAA-MM-JJ).');
      } else {
        const entered = new Date(dateVal);
        const farPast = new Date();
        farPast.setFullYear(farPast.getFullYear() - 5);
        if (entered < farPast) {
          errors.push('La date ne peut pas être dans un passé trop lointain (plus de 5 ans).');
        }
      }

      const typeRepas = document.getElementById('r_type_repas').value;
      if (!typeRepas) {
        errors.push('Le type de repas est obligatoire.');
      }

      const statut = document.getElementById('r_statut').value;
      if (!statut) {
        errors.push('Le statut est obligatoire.');
      }

      const caloriesVal = document.getElementById('r_calories').value.trim();
      if (caloriesVal !== '') {
        const calories = parseInt(caloriesVal, 10);
        if (isNaN(calories) || calories < 1400) {
          errors.push('Les calories doivent être un nombre positif (minimum 1400).');
        }
      }

      const heurePrevue = document.getElementById('r_heure_prevue').value;
      const heureReelle = document.getElementById('r_heure_reelle').value;
      const timeRegex = /^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/;

      if (heurePrevue && !timeRegex.test(heurePrevue)) {
        errors.push("Format d'heure prévue invalide (HH:MM).");
      }
      if (heureReelle && !timeRegex.test(heureReelle)) {
        errors.push("Format d'heure réelle invalide (HH:MM).");
      }
      if (heurePrevue && heureReelle) {
        if (heureReelle < heurePrevue) {
          errors.push("L'heure réelle ne peut pas être antérieure à l'heure prévue.");
        }
      }

      const notes = document.getElementById('r_notes').value;
      if (notes.length > 1000) {
        errors.push('Les notes ne peuvent pas dépasser 1000 caractères.');
      }

      const errBox = document.getElementById('repasFormErrors');
      if (errors.length > 0) {
        errBox.innerHTML = '<ul style="margin:0;padding-left:18px;">' +
          errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
        errBox.style.display = 'block';
        return false;
      }
      errBox.style.display = 'none';
      return true;
    }
  </script>
</body>
</html>
