<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/PlanNutritionnelController.php';
require_once __DIR__ . '/../CONTROLLER/RepasController.php';

// Vérification rôle admin (à connecter à AuthController du projet)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // header('Location: ../VIEW/auth.php'); exit; // décommenter en prod
}

$planCtrl  = new PlanNutritionnelController();
$repasCtrl = new RepasController();

// Actions rapides
if (isset($_GET['supprimer_plan'])) {
    $planCtrl->supprimerPlan((int)$_GET['supprimer_plan']);
    header('Location: backoffice-plans.php');
    exit;
}
if (isset($_GET['statut_repas']) && isset($_GET['rid'])) {
    $repasCtrl->changerStatut((int)$_GET['rid'], $_GET['statut_repas']);
    header('Location: backoffice-plans.php');
    exit;
}

$tousPlans = $planCtrl->listeTousLesPlans();
$stats     = $planCtrl->statistiquesGlobales();

// Filtre de recherche côté serveur
$filtreNom = trim($_GET['search'] ?? '');
if ($filtreNom) {
    $tousPlans = array_filter($tousPlans, fn($p) =>
        stripos($p['nom'], $filtreNom) !== false ||
        stripos($p['utilisateur_nom'] ?? '', $filtreNom) !== false
    );
}

// Stats calculées
$nbPlans  = $stats['total_plans'] ?? 0;
$moyenCal = round($stats['moy_calories'] ?? 0);
$totalJ   = $stats['total_jours'] ?? 0;

// Données pour le graphique (répartition par tranche calorique)
$tranches = ['< 1 500' => 0, '1 500–2 000' => 0, '2 000–2 500' => 0, '> 2 500' => 0];
foreach ($tousPlans as $p) {
    $c = $p['calories_journalieres'];
    if ($c < 1500) $tranches['< 1 500']++;
    elseif ($c < 2000) $tranches['1 500–2 000']++;
    elseif ($c < 2500) $tranches['2 000–2 500']++;
    else $tranches['> 2 500']++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | Back Office – Plans Nutritionnels</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="../CSS/backoffice-plans.css">
</head>
<body>
<div class="app-wrapper">

  <!-- ══ SIDEBAR ══════════════════════════════════════════════════ -->
  <aside class="sidebar">
    <div class="logo-area">
      <h2>
        <i class="fas fa-seedling"></i>
        <span>Kool<br>Healthy</span>
      </h2>
      <p>administration · nutrition IA</p>
    </div>

    <nav class="nav-menu">
      <!-- Liens vers les autres modules du projet -->
      <a class="nav-item" href="../../../Recettes/VIEW/backoffice.php">
        <i class="fas fa-chart-pie"></i><span>Dashboard</span>
      </a>
      <a class="nav-item" href="../../../Recettes/VIEW/backoffice.php">
        <i class="fas fa-utensils"></i><span>Recettes</span>
      </a>
      <a class="nav-item" href="../../../Recettes/VIEW/backoffice.php">
        <i class="fas fa-users"></i><span>Utilisateurs</span>
      </a>

      <div class="nav-section-label">Module 5</div>
      <a class="nav-item active" href="backoffice-plans.php" data-tab="plans">
        <i class="fas fa-clipboard-list"></i><span>Plans nutritionnels</span>
      </a>
      <a class="nav-item" href="backoffice-plans.php#repas" data-tab="repas">
        <i class="fas fa-bowl-food"></i><span>Repas</span>
      </a>
      <a class="nav-item" href="backoffice-plans.php#stats" data-tab="stats">
        <i class="fas fa-chart-line"></i><span>Statistiques</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="user-badge">
        <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
        <div class="user-info">
          <p><?= htmlspecialchars($_SESSION['nom'] ?? 'Admin') ?></p>
          <small>admin@koolhealthy.com</small>
        </div>
      </div>
    </div>
  </aside>

  <!-- ══ MAIN CONTENT ═════════════════════════════════════════════ -->
  <main class="main-content">

    <!-- Top bar -->
    <div class="top-bar">
      <div class="page-title">
        <h1><i class="fas fa-clipboard-list"></i> Plans Nutritionnels</h1>
        <p>Module 5 · Gestion & supervision des plans utilisateurs</p>
      </div>
      <div class="header-actions">
        <form method="GET" style="display:flex;gap:.5rem;">
          <input type="text" name="search" placeholder="Rechercher un plan…"
                 value="<?= htmlspecialchars($filtreNom) ?>"
                 style="padding:.55rem 1rem;border:1.5px solid var(--gris-moyen);border-radius:40px;font-family:inherit;font-size:.85rem;">
          <button type="submit" class="btn-primary btn-sm">
            <i class="fas fa-search"></i>
          </button>
          <?php if ($filtreNom): ?>
          <a href="backoffice-plans.php" class="btn-outline btn-sm">
            <i class="fas fa-times"></i>
          </a>
          <?php endif; ?>
        </form>
        <button class="btn-primary" onclick="openModal('modalCreerPlan')">
          <i class="fas fa-plus-circle"></i> Nouveau plan
        </button>
      </div>
    </div>

    <!-- ─── TAB : PLANS ─────────────────────────────────────────── -->
    <div id="content-plans" class="dashboard-container">

      <!-- Stat cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon stat-icon-green"><i class="fas fa-clipboard-list"></i></div>
          <div class="stat-title">Plans actifs</div>
          <div class="stat-value"><?= $nbPlans ?></div>
          <div class="stat-sub">tous les utilisateurs</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-blue"><i class="fas fa-fire"></i></div>
          <div class="stat-title">Calories moy./jour</div>
          <div class="stat-value"><?= number_format($moyenCal, 0, ',', ' ') ?></div>
          <div class="stat-sub">kcal – moyenne globale</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-orange"><i class="fas fa-calendar-alt"></i></div>
          <div class="stat-title">Total jours planifiés</div>
          <div class="stat-value"><?= number_format($totalJ, 0, ',', ' ') ?></div>
          <div class="stat-sub">tous plans confondus</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon-green"><i class="fas fa-check-circle"></i></div>
          <div class="stat-title">Plans équilibrés</div>
          <div class="stat-value">
            <?php
              $equilibres = count(array_filter($tousPlans ?? [],
                fn($p) => $p['calories_journalieres'] >= 1500 && $p['calories_journalieres'] <= 3000
              ));
              echo $equilibres;
            ?>
          </div>
          <div class="stat-sub">entre 1 500 et 3 000 kcal</div>
        </div>
      </div>

      <!-- Deux colonnes : graphique + top utilisateurs -->
      <div class="two-columns">
        <div class="card-panel">
          <div class="panel-header">
            <span class="panel-title">
              <i class="fas fa-chart-bar" style="color:var(--bleu-tech);"></i>
              Répartition par apport calorique
            </span>
            <span class="badge-tech">IA tracking</span>
          </div>
          <canvas id="caloriesChart" height="220"></canvas>
        </div>

        <div class="card-panel">
          <div class="panel-header">
            <span class="panel-title">
              <i class="fas fa-trophy" style="color:var(--vert-kool);"></i>
              Derniers plans créés
            </span>
            <span class="badge-eco">live</span>
          </div>
          <table class="data-table">
            <thead>
              <tr>
                <th>Nom du plan</th>
                <th>Utilisateur</th>
                <th>Kcal/j</th>
                <th>Équilibre</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice(array_values($tousPlans ?? []), 0, 5) as $p): ?>
                <?php $r = $planCtrl->recommandation($p['calories_journalieres']); ?>
                <tr>
                  <td><?= htmlspecialchars($p['nom']) ?></td>
                  <td><?= htmlspecialchars($p['utilisateur_nom'] ?? '—') ?></td>
                  <td><?= number_format($p['calories_journalieres'], 0, ',', ' ') ?></td>
                  <td>
                    <span class="badge <?= $r['type'] === 'success' ? 'badge-ok' : 'badge-warning' ?>">
                      <?= $r['type'] === 'success' ? '✓ OK' : '⚠ Hors plage' ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tableau complet des plans -->
      <div class="card-panel">
        <div class="panel-header">
          <span class="panel-title">
            <i class="fas fa-table"></i>
            Tous les plans nutritionnels
            <?php if ($filtreNom): ?>
              <small style="color:var(--gris-texte);font-weight:400;">
                — filtré : "<?= htmlspecialchars($filtreNom) ?>"
              </small>
            <?php endif; ?>
          </span>
          <span style="color:var(--gris-texte);font-size:.82rem;">
            <?= count($tousPlans ?? []) ?> résultat(s)
          </span>
        </div>

        <?php if (empty($tousPlans)): ?>
          <div style="text-align:center;padding:2rem;color:var(--gris-texte);">
            <i class="fas fa-search" style="font-size:2rem;display:block;margin-bottom:.8rem;"></i>
            Aucun plan trouvé.
          </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nom du plan</th>
                <th>Utilisateur</th>
                <th>Kcal/j</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Durée</th>
                <th>Progression</th>
                <th>Équilibre</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tousPlans as $p): ?>
                <?php
                  $reco  = $planCtrl->recommandation($p['calories_journalieres']);
                  $duree = (int)((strtotime($p['date_fin']) - strtotime($p['date_debut'])) / 86400);
                  $pct   = min(100, (int)($p['statistiques'] ?? 0));
                  $verif = $planCtrl->verifierEquilibre($p['planID']);
                ?>
                <tr>
                  <td style="color:var(--gris-texte);font-size:.75rem;"><?= $p['planID'] ?></td>
                  <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                  <td><?= htmlspecialchars($p['utilisateur_nom'] ?? '—') ?></td>
                  <td><strong><?= number_format($p['calories_journalieres'], 0, ',', ' ') ?></strong></td>
                  <td><?= date('d/m/Y', strtotime($p['date_debut'])) ?></td>
                  <td><?= date('d/m/Y', strtotime($p['date_fin'])) ?></td>
                  <td><?= $duree ?> j</td>
                  <td style="min-width:100px;">
                    <div class="progress-bar-bg" style="margin-top:4px;">
                      <div class="progress-fill <?= $pct > 70 ? '' : ($pct > 40 ? 'progress-fill-blue' : 'progress-fill-orange') ?>"
                           style="width:<?= $pct ?>%"></div>
                    </div>
                    <small style="color:var(--gris-texte);"><?= $pct ?>%</small>
                  </td>
                  <td>
                    <span class="badge <?= $reco['type'] === 'success' ? 'badge-ok' : 'badge-warning' ?>">
                      <?= $reco['type'] === 'success' ? '✓ Équilibré' : '⚠ Hors plage' ?>
                    </span>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <a href="detail-plan.php?id=<?= $p['planID'] ?>" target="_blank"
                         class="btn-primary btn-sm" title="Voir le plan">
                        <i class="fas fa-eye"></i>
                      </a>
                      <button class="btn-outline btn-sm"
                              onclick="ouvrirModalEquilibre(<?= $p['planID'] ?>, '<?= addslashes($p['nom']) ?>', <?= $p['calories_journalieres'] ?>, <?= $verif['nb_repas'] ?? 0 ?>, <?= $verif['repas_consommes'] ?? 0 ?>)"
                              title="Vérifier l'équilibre">
                        <i class="fas fa-stethoscope"></i>
                      </button>
                      <a href="backoffice-plans.php?supprimer_plan=<?= $p['planID'] ?>"
                         class="btn-danger btn-sm"
                         onclick="return confirm('Supprimer définitivement ce plan et tous ses repas ?')"
                         title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

    </div><!-- /content-plans -->


    <!-- ─── SECTION STATS SUIVI CALORIES ─────────────────────────── -->
    <div id="content-stats" class="dashboard-container" style="display:none;">
      <div class="card-panel">
        <div class="panel-header">
          <span class="panel-title">
            <i class="fas fa-chart-area" style="color:var(--bleu-tech);"></i>
            Analyse globale des apports caloriques
          </span>
          <span class="badge-tech">suivi calories</span>
        </div>
        <canvas id="caloriesLineChart" height="200"></canvas>
      </div>

      <div class="two-columns">
        <div class="card-panel">
          <div class="panel-header">
            <span class="panel-title">Recommandations nutritionnelles</span>
          </div>
          <?php foreach ($tousPlans ?? [] as $p): ?>
            <?php $r = $planCtrl->recommandation($p['calories_journalieres']); ?>
            <?php if ($r['type'] === 'warning'): ?>
            <div class="alert alert-warning" style="margin-bottom:.6rem;">
              <i class="fas fa-exclamation-triangle"></i>
              <strong><?= htmlspecialchars($p['nom']) ?></strong> — <?= $r['message'] ?>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php
            $warnings = count(array_filter($tousPlans ?? [], fn($p) => $planCtrl->recommandation($p['calories_journalieres'])['type'] === 'warning'));
            if ($warnings === 0): ?>
            <div class="alert alert-success">
              <i class="fas fa-check-circle"></i> Tous les plans sont dans la plage recommandée.
            </div>
          <?php endif; ?>
        </div>

        <div class="card-panel">
          <div class="panel-header">
            <span class="panel-title">Objectifs de durabilité</span>
          </div>
          <div class="progress-wrap">
            <div class="progress-label"><span>Plans avec apport équilibré</span><span><?= $nbPlans ? round($equilibres / $nbPlans * 100) : 0 ?>%</span></div>
            <div class="progress-bar-bg">
              <div class="progress-fill" data-width="<?= $nbPlans ? round($equilibres / $nbPlans * 100) : 0 ?>"
                   style="width:0%"></div>
            </div>
          </div>
          <div class="progress-wrap">
            <div class="progress-label"><span>Moyenne calorique vs objectif (2 000 kcal)</span>
              <span><?= $moyenCal ?>/2 000</span></div>
            <div class="progress-bar-bg">
              <div class="progress-fill progress-fill-blue" data-width="<?= min(100, round($moyenCal/2000*100)) ?>"
                   style="width:0%"></div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /content-stats -->

  </main>
</div>

<!-- ══ MODAL : Créer un plan (admin) ════════════════════════════ -->
<div id="modalCreerPlan" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-plus-circle" style="color:var(--vert-kool);"></i> Créer un plan</span>
      <button class="close-modal" onclick="closeModal('modalCreerPlan')">&times;</button>
    </div>
    <p style="color:var(--gris-texte);font-size:.84rem;margin-bottom:1rem;">
      Créer un plan depuis le back office (pour un utilisateur existant).
    </p>
    <a href="../VIEW/creer-plan.php" class="btn-primary" style="width:100%;justify-content:center;">
      <i class="fas fa-external-link-alt"></i> Ouvrir le formulaire de création
    </a>
  </div>
</div>

<!-- ══ MODAL : Vérifier l'équilibre nutritionnel ════════════════ -->
<div id="modalEquilibre" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="modal-title">
        <i class="fas fa-stethoscope" style="color:var(--bleu-tech);"></i>
        Vérification nutritionnelle
      </span>
      <button class="close-modal" onclick="closeModal('modalEquilibre')">&times;</button>
    </div>
    <div id="equilibreContent" style="margin-top:1rem;"></div>
  </div>
</div>

<script src="../JS/plans.js"></script>
<script>
/* ── Navigation tabs back office ─────────────────────────────── */
const tabs = {
  plans : document.getElementById('content-plans'),
  stats : document.getElementById('content-stats'),
};

document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
  item.addEventListener('click', e => {
    e.preventDefault();
    const tab = item.dataset.tab;
    Object.entries(tabs).forEach(([k, el]) => {
      if (el) el.style.display = k === tab ? 'block' : 'none';
    });
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    item.classList.add('active');
    if (tab === 'stats') initLineChart();
  });
});

/* ── Graphique donut répartition calorique ───────────────────── */
(function () {
  const ctx = document.getElementById('caloriesChart')?.getContext('2d');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_keys($tranches)) ?>,
      datasets: [{
        data: <?= json_encode(array_values($tranches)) ?>,
        backgroundColor: ['#29B6F6', '#4CAF50', '#FF9800', '#F44336'],
        borderWidth: 2,
        borderColor: '#fff',
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label} : ${ctx.parsed} plan(s)`
          }
        }
      }
    }
  });
})();

/* ── Graphique ligne évolution (tab stats) ───────────────────── */
let lineChartInit = false;
function initLineChart() {
  if (lineChartInit) return;
  lineChartInit = true;
  const ctx = document.getElementById('caloriesLineChart')?.getContext('2d');
  if (!ctx) return;

  const plans = <?= json_encode(array_values($tousPlans ?? [])) ?>;
  const labels = plans.map((p, i) => 'Plan ' + (i + 1));
  const data   = plans.map(p => p.calories_journalieres);

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Calories journalières (kcal)',
        data,
        borderColor: '#4CAF50',
        backgroundColor: 'rgba(76,175,80,.1)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: data.map(v =>
          v < 1500 || v > 3000 ? '#F44336' : '#4CAF50'
        ),
        pointRadius: 5,
      }, {
        label: 'Seuil recommandé max (3 000 kcal)',
        data: plans.map(() => 3000),
        borderColor: '#FF9800',
        borderDash: [6, 4],
        borderWidth: 1.5,
        pointRadius: 0,
      }, {
        label: 'Seuil recommandé min (1 500 kcal)',
        data: plans.map(() => 1500),
        borderColor: '#29B6F6',
        borderDash: [6, 4],
        borderWidth: 1.5,
        pointRadius: 0,
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: false, min: 800 }
      }
    }
  });
}

/* ── Modale vérification équilibre ───────────────────────────── */
function ouvrirModalEquilibre(id, nom, cal, nbRepas, nbCons) {
  const ok = cal >= 1500 && cal <= 3000;
  const taux = nbRepas > 0 ? Math.round(nbCons / nbRepas * 100) : 0;
  document.getElementById('equilibreContent').innerHTML = `
    <h3 style="margin-bottom:.8rem;color:var(--vert-kool-dark);">${nom}</h3>
    <div class="alert alert-${ok ? 'success' : 'warning'}" style="margin-bottom:1rem;">
      <i class="fas fa-${ok ? 'check-circle' : 'exclamation-triangle'}"></i>
      ${ok
        ? 'Apport calorique équilibré (' + cal.toLocaleString('fr-FR') + ' kcal/j).'
        : 'Apport hors plage recommandée (' + cal.toLocaleString('fr-FR') + ' kcal/j). Plage : 1 500 – 3 000 kcal/j.'
      }
    </div>
    <div style="margin-bottom:.8rem;">
      <strong><i class="fas fa-utensils" style="color:var(--bleu-tech);"></i> Repas :</strong>
      ${nbRepas} planifiés — ${nbCons} consommés
    </div>
    <div class="progress-wrap">
      <div class="progress-label"><span>Taux de consommation</span><span>${taux}%</span></div>
      <div class="progress-bar-bg">
        <div class="progress-fill ${taux > 70 ? '' : taux > 40 ? 'progress-fill-blue' : 'progress-fill-orange'}"
             style="width:${taux}%"></div>
      </div>
    </div>
    <a href="detail-plan.php?id=${id}" target="_blank" class="btn-primary"
       style="margin-top:1rem;width:100%;justify-content:center;">
      <i class="fas fa-eye"></i> Voir le plan complet
    </a>`;
  openModal('modalEquilibre');
}

/* ── Animer les barres au chargement ─────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.progress-fill[data-width]').forEach(b => {
    setTimeout(() => { b.style.width = b.dataset.width + '%'; }, 300);
  });
});
</script>
</body>
</html>
