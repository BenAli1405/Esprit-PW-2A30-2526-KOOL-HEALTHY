<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';
require_once __DIR__ . '/../Gamification/CONTROLLER/DefiController.php';
require_once __DIR__ . '/../Gamification/CONTROLLER/ParticipationController.php';

$authController = new AuthController();
$utilisateurConnecte = $authController->exigerAdmin('auth.php', 'home.php');

$defiController = new DefiController();
$participationController = new ParticipationController();
$defis = $defiController->listeDefis();
$stats = $defiController->statsDefis();
$participations = $participationController->listeParticipations();
$utilisateurs = $participationController->listeUtilisateurs();
$defisForParticipation = $participationController->listeDefis();
$statsPoints = $participationController->statsParticipationsPoints();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$message = '';
if ($success === 'added') {
    $message = 'Défi ajouté avec succès.';
} elseif ($success === 'edited') {
    $message = 'Défi modifié avec succès.';
} elseif ($success === 'deleted') {
    $message = 'Défi supprimé avec succès.';
} elseif ($success === 'participation_added') {
    $message = 'Participation ajoutée avec succès.';
} elseif ($success === 'participation_edited') {
    $message = 'Participation modifiée avec succès.';
} elseif ($success === 'participation_deleted') {
    $message = 'Participation supprimée avec succès.';
} elseif ($success === 'approved') {
    $message = 'Le défi a été approuvé et publié.';
} elseif ($success === 'rejected') {
    $message = 'Le défi a été refusé.';
} elseif ($error === 'titre') {
    $message = 'Le titre du défi est obligatoire.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Kool Healthy | Back Office - CRUD Complet avec Modales</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <link rel="stylesheet" href="css/backoffice.css?v=20260512">
</head>
<body>
<div class="app-wrapper">
  <aside class="sidebar">
    <div class="logo-area">
      <a class="logo-link" href="home.php" aria-label="Kool Healthy">
        <img src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
      </a>
      <p>administration · nutrition IA</p>
    </div>

    <div class="nav-menu">
      <a class="nav-item" href="backoffice.php" style="text-decoration:none;color:inherit;"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
      <a class="nav-item" href="backoffice.php?tab=users" style="text-decoration:none;color:inherit;"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
      <a class="nav-item" href="backoffice.php?tab=recipes" style="text-decoration:none;color:inherit;"><i class="fas fa-utensils"></i><span>Recettes</span></a>
      <a class="nav-item" href="backoffice.php?tab=ingredients" style="text-decoration:none;color:inherit;"><i class="fas fa-apple-alt"></i><span>Ingrédients</span></a>
      <a class="nav-item" href="backoffice.php?tab=reviews" style="text-decoration:none;color:inherit;"><i class="fas fa-star"></i><span>Avis</span></a>
      <a class="nav-item" href="../plan.php?page=plan-backoffice" style="text-decoration:none;color:inherit;"><i class="fas fa-bowl-food"></i><span>Repas</span></a>
      <a class="nav-item" href="../plan.php?page=plan-nutritionnel" style="text-decoration:none;color:inherit;"><i class="fas fa-clipboard-list"></i><span>Plans</span></a>
      <a class="nav-item active" href="backoffice-gamification.php" style="text-decoration:none;color:inherit;"><i class="fas fa-trophy"></i><span>Gamification</span></a>
      <a class="nav-item" href="../sport/index.php?action=admin_entrainements" style="text-decoration:none;color:inherit;"><i class="fas fa-dumbbell"></i><span>Entraînements</span></a>
      <a class="nav-item" href="../sport/index.php?action=admin_exercices" style="text-decoration:none;color:inherit;"><i class="fas fa-running"></i><span>Exercices</span></a>
      <a class="nav-item" href="../sport/index.php?action=admin_reference_list" style="text-decoration:none;color:inherit;"><i class="fas fa-book"></i><span>Catalogue KNN</span></a>
      <a class="nav-item" href="backoffice.php?tab=analytics" style="text-decoration:none;color:inherit;"><i class="fas fa-chart-line"></i><span>Analytics IA</span></a>
    </div>

    <div class="sidebar-footer">
      <div class="user-badge">
        <div class="user-avatar"><i class="fas fa-user-md"></i></div>
        <div class="user-info">
          <p><?php echo htmlspecialchars((string) ($utilisateurConnecte['nom'] ?? 'Admin')); ?></p>
          <small>backoffice global</small>
        </div>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <div class="top-bar">
      <div class="page-title">
        <h1>Gamification</h1>
        <p>Défis · Participations</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" id="openGlobalDefiBtn"><i class="fas fa-plus-circle"></i> Nouveau défi</button>
        <a class="btn-outline" href="../CONTROLLER/AuthController.php?action=logout">Se déconnecter</a>
      </div>
    </div>
    <?php if ($message): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>


    <div class="unified-container">
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-title"><i class="fas fa-trophy"></i> Défis actifs</div><div class="stat-value" id="statsDefisActifs"><?= htmlspecialchars((string)$stats['total_defis']) ?></div></div>
        <div class="stat-card"><div class="stat-title"><i class="fas fa-users"></i> Participants totaux</div><div class="stat-value" id="statsParticipants"><?= htmlspecialchars((string)$stats['participants']) ?></div></div>
        <div class="stat-card"><div class="stat-title"><i class="fas fa-star"></i> Points distribués</div><div class="stat-value" id="statsPoints"><?= htmlspecialchars((string)$stats['points_distribues']) ?></div></div>
      </div>


      <!-- SECTION DÉFIS EN ATTENTE -->
      <div class="section-card section-card--warning" id="pendingDefisSection">
        <div class="section-header">
          <h2><i class="fas fa-hourglass-half icon-warning"></i> Défis en attente de validation</h2>
        </div>
        <div class="table-wrapper">
          <table class="data-table">
            <thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>Points</th><th>Proposant</th><th>Actions</th></tr></thead>
            <tbody>
              <?php 
              $pendingDefis = array_filter($defis, function($d) { return $d['status'] === 'en_attente'; });
              if (empty($pendingDefis)): ?>
                <tr><td colspan="6" class="empty-table-row">Aucun défi en attente.</td></tr>
              <?php else: 
                foreach ($pendingDefis as $defi): ?>
                <tr id="pending-row-<?= $defi['id'] ?>">
                  <td><?= htmlspecialchars($defi['id']) ?></td>
                  <td><strong><?= htmlspecialchars($defi['titre']) ?></strong></td>
                  <td><span class="badge-tech"><?= htmlspecialchars($defi['type']) ?></span></td>
                  <td><span class="badge-eco"><?= htmlspecialchars($defi['points']) ?> pts</span></td>
                  <td>ID: <?= htmlspecialchars($defi['proposant_id'] ?? 'Admin') ?></td>
                  <td>
                    <a href="../Gamification/CONTROLLER/DefiController.php?action=approve&id=<?= $defi['id'] ?>" class="btn-small btn-success"><i class="fas fa-check"></i> Approuver</a>
                    <a href="../Gamification/CONTROLLER/DefiController.php?action=reject&id=<?= $defi['id'] ?>" class="btn-danger btn-small"><i class="fas fa-times"></i> Refuser</a>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SECTION DÉFIS -->
      <div class="section-card">
        <div class="section-header">
          <h2><i class="fas fa-tasks icon-highlight"></i> Gestion des défis</h2>

        <!-- Barre de recherche pour les défis du backoffice -->
        <div class="search-container">
          <span class="search-label"><i class="fas fa-search"></i> Filtrer par:</span>
          <select id="searchAttributeBackofficeDefis" class="search-select">
            <option value="id">ID</option>
            <option value="titre">Titre du défi</option>
            <option value="type">Type de défi</option>
            <option value="points">Points</option>
          </select>
          <input type="text" id="searchInputBackofficeDefis" class="search-input" placeholder="Entrez votre terme de recherche...">
          <button id="clearSearchBackofficeDefisBtn" class="btn-outline btn-small"><i class="fas fa-times"></i> Réinitialiser</button>
          <span class="search-results-info" id="searchResultsInfoBackofficeDefis"></span>
        </div>

        <div class="table-wrapper">
          <table class="data-table">
            <thead><tr><th>ID</th><th>Titre</th><th>Type</th>
              <th>Points <i class="fas fa-sort sortable" onclick="sortBackofficeDefis('points')"></i></th>
              <th>Date début</th><th>Date fin</th><th>Actions</th></tr></thead>
            <tbody id="defisListUnified">
              <?php foreach ($defis as $defi): ?>
                <tr class="defi-row-backoffice" data-id="<?= htmlspecialchars($defi['id']) ?>" data-titre="<?= htmlspecialchars(strtolower($defi['titre'])) ?>" data-type="<?= htmlspecialchars(strtolower($defi['type'])) ?>" data-points="<?= htmlspecialchars($defi['points']) ?>">
                  <td><?= htmlspecialchars($defi['id']) ?></td>
                  <td><strong><?= htmlspecialchars($defi['titre']) ?></strong></td>
                  <td><span class="badge-tech"><?= htmlspecialchars($defi['type']) ?></span></td>
                  <td><span class="badge-eco"><?= htmlspecialchars($defi['points']) ?> pts</span></td>
                  <td><?= htmlspecialchars($defi['date_debut']) ?></td>
                  <td><?= htmlspecialchars($defi['date_fin']) ?></td>
                  <td>
                    <button type="button" class="btn-edit edit-defi-btn" data-id="<?= $defi['id'] ?>" data-titre="<?= htmlspecialchars($defi['titre'], ENT_QUOTES) ?>" data-type="<?= htmlspecialchars($defi['type'], ENT_QUOTES) ?>" data-points="<?= $defi['points'] ?>" data-date-debut="<?= $defi['date_debut'] ?>" data-date-fin="<?= $defi['date_fin'] ?>"><i class="fas fa-pen"></i> Modifier</button>
                    <a href="../Gamification/CONTROLLER/DefiController.php?action=delete&id=<?= $defi['id'] ?>" class="btn-danger btn-delete-confirm"><i class="fas fa-trash"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div id="defisFooter" class="table-footer"></div>
      </div>

      <!-- PARTICIPATIONS -->
      <div class="section-card">
        <div class="section-header">
          <div>
            <h2><i class="fas fa-clipboard-list icon-highlight"></i> Participations</h2>
            <p class="section-note">Suivi des performances utilisateur par défi. (Lecture seule)</p>
          </div>
        </div>
        <div class="table-wrapper">
          <table class="data-table">
            <thead><tr><th>ID</th><th>Utilisateur</th><th>Défi</th><th>Progression</th><th>Statut</th><th>Points gagnés</th><th>Créé le</th></tr></thead>
            <tbody>
              <?php foreach ($participations as $participation): ?>
                <tr>
                  <td><?= htmlspecialchars($participation['id']) ?></td>
                  <td><?= htmlspecialchars($participation['utilisateur_nom'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($participation['defi_titre'] ?? 'N/A') ?></td>
                  <td><?= htmlspecialchars($participation['progression']) ?>%</td>
                  <td><?= $participation['termine'] ? '<span class="status-active">Terminé</span>' : '<span class="badge-tech">En cours</span>' ?></td>
                  <td><?= htmlspecialchars($participation['points_gagnes']) ?> pts</td>
                  <td><?= htmlspecialchars($participation['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="table-footer"></div>
      </div>

    </div>
  </main>

  <script>
    // ===== LOGIQUE DE FILTRAGE POUR LES DÉFIS DU BACKOFFICE =====
    let allBackofficeDefis = [];
    let filteredBackofficeDefis = [];
    
    // Charger les données des défis du backoffice du DOM
    function loadBackofficeDefisFromTable() {
      const rows = document.querySelectorAll('#defisListUnified .defi-row-backoffice');
      allBackofficeDefis = Array.from(rows).map(row => ({
        id: row.dataset.id,
        titre: row.dataset.titre,
        type: row.dataset.type,
        points: row.dataset.points,
        element: row
      }));
      filteredBackofficeDefis = [...allBackofficeDefis];
    }

    // Fonction de filtrage des défis du backoffice
    function filterBackofficeDefis() {
      const searchTerm = document.getElementById('searchInputBackofficeDefis').value.toLowerCase().trim();
      const searchAttribute = document.getElementById('searchAttributeBackofficeDefis').value;
      
      if (!searchTerm) {
        filteredBackofficeDefis = [...allBackofficeDefis];
        document.getElementById('searchResultsInfoBackofficeDefis').textContent = '';
      } else {
        filteredBackofficeDefis = allBackofficeDefis.filter(defi => {
          let fieldValue = '';
          
          if (searchAttribute === 'id') {
            fieldValue = defi.id;
          } else if (searchAttribute === 'titre') {
            fieldValue = defi.titre;
          } else if (searchAttribute === 'type') {
            fieldValue = defi.type;
          } else if (searchAttribute === 'points') {
            fieldValue = defi.points;
          }
          
          return fieldValue.includes(searchTerm);
        });

        const resultCount = filteredBackofficeDefis.length;
        document.getElementById('searchResultsInfoBackofficeDefis').textContent = resultCount === 0 
          ? 'Aucun résultat trouvé' 
          : resultCount === 1 
            ? '1 défi trouvé' 
            : resultCount + ' défis trouvés';
      }

      renderFilteredBackofficeDefis();
    }

    // Fonction de rendu des défis filtrés du backoffice
    function renderFilteredBackofficeDefis() {
      // Masquer/afficher tous les éléments
      allBackofficeDefis.forEach(d => d.element.style.display = 'none');
      filteredBackofficeDefis.forEach(d => d.element.style.display = 'table-row');
    }

    // Fonction pour réinitialiser la recherche des défis du backoffice
    function clearSearchBackofficeDefis() {
      document.getElementById('searchInputBackofficeDefis').value = '';
      document.getElementById('searchAttributeBackofficeDefis').value = 'titre';
      document.getElementById('searchResultsInfoBackofficeDefis').textContent = '';
      filteredBackofficeDefis = [...allBackofficeDefis];
      renderFilteredBackofficeDefis();
    }

    // Fonction de tri des défis du backoffice
    let sortDirectionBackofficeDefis = {};
    function sortBackofficeDefis(attribute) {
      sortDirectionBackofficeDefis[attribute] = !sortDirectionBackofficeDefis[attribute];
      const direction = sortDirectionBackofficeDefis[attribute] ? 1 : -1;
      
      const tbody = document.getElementById('defisListUnified');
      const rows = Array.from(tbody.querySelectorAll('.defi-row-backoffice'));
      
      rows.sort((a, b) => {
        let valA = parseInt(a.dataset.points, 10) || 0;
        let valB = parseInt(b.dataset.points, 10) || 0;
        
        if (valA < valB) return -1 * direction;
        if (valA > valB) return 1 * direction;
        return 0;
      });
      
      rows.forEach(row => tbody.appendChild(row));
    }

    // Initialisation
    window.addEventListener('DOMContentLoaded', function() {
      loadBackofficeDefisFromTable();

      // Event listeners pour la recherche des défis du backoffice
      const searchInputBackoffice = document.getElementById('searchInputBackofficeDefis');
      const searchAttributeBackoffice = document.getElementById('searchAttributeBackofficeDefis');
      const clearSearchBackofficeBtn = document.getElementById('clearSearchBackofficeDefisBtn');

      if (searchInputBackoffice) {
        searchInputBackoffice.addEventListener('input', filterBackofficeDefis);
        searchAttributeBackoffice.addEventListener('change', filterBackofficeDefis);
        clearSearchBackofficeBtn.addEventListener('click', clearSearchBackofficeDefis);
      }

      // ===== MODALE STATISTIQUES CHART.JS =====
      const statsModalBack = document.getElementById('statsPointsModalBack');
      let barChartBack = null;
      let doughnutChartBack = null;

      const statsDataBack = {
        total:     <?= $statsPoints['total_points'] ?>,
        moyenne:   <?= $statsPoints['moyenne_points'] ?>,
        max:       <?= $statsPoints['max_points'] ?>,
        min:       <?= $statsPoints['min_points'] ?>,
        terminees: <?= $statsPoints['total_terminees'] ?>,
        enCours:   <?= $statsPoints['total_participations'] - $statsPoints['total_terminees'] ?>
      };

      async function loadAdminNotifications() {
        try {
          const res = await fetch('../CONTROLLER/NotificationController.php?action=liste');
          const data = await res.json();
          if (data && data.length > 0) {
            notifList.innerHTML = data.map(n => {
              const match = n.message.match(/\[ID:(\d+)\]/);
              const targetId = match ? `pending-row-${match[1]}` : 'pendingDefisSection';
              return `
                <a href="#${targetId}" class="notif-item ${n.lu == 0 ? 'unread' : ''}" onclick="markAsRead(${n.id}, '${targetId}')">
                  <span class="notif-message-strong">${n.message}</span>
                  <span class="notif-time"><i class="far fa-clock"></i> ${n.created_at}</span>
                </a>
              `;
            }).join('');
            const unread = data.filter(n => n.lu == 0).length;
            if (unread > 0) {
              notifCount.textContent = unread;
              notifCount.style.display = 'block';
            } else {
              notifCount.style.display = 'none';
            }
          } else {
            notifList.innerHTML = '<div class="notif-empty">Aucune notification</div>';
            notifCount.style.display = 'none';
          }
        } catch (e) { console.error("Erreur notifications", e); }
      }

      window.markAsRead = async function(id, targetId) {
        await fetch(`../CONTROLLER/NotificationController.php?action=marquer_lue&id=${id}`);
        loadAdminNotifications();
        const row = document.getElementById(targetId);
        if (row && targetId !== 'pendingDefisSection') {
          row.style.background = '#FFF9C4';
          setTimeout(() => { row.style.background = ''; }, 3000);
        }
      };

      function openStatsModalBack() {
        statsModalBack.style.display = 'flex';
        setTimeout(() => {
          const ctxBar = document.getElementById('statsBarChartBack').getContext('2d');
          if (barChartBack) barChartBack.destroy();
          barChartBack = new Chart(ctxBar, {
            type: 'bar',
            data: {
              labels: ['Total pts', 'Moyenne', 'Maximum', 'Minimum'],
              datasets: [{
                label: 'Points gagnés',
                data: [statsDataBack.total, statsDataBack.moyenne, statsDataBack.max, statsDataBack.min],
                backgroundColor: ['#4CAF50CC','#29B6F6CC','#FFC107CC','#E91E63CC'],
                borderColor:     ['#388E3C','#0288D1','#F57F17','#AD1457'],
                borderWidth: 2, borderRadius: 10,
              }]
            },
            options: {
              responsive: true,
              plugins: { legend: { display: false } },
              scales: {
                y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { family: 'Inter' } } },
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: '600' } } }
              }
            }
          });
          const ctxD = document.getElementById('statsDoughnutChartBack').getContext('2d');
          if (doughnutChartBack) doughnutChartBack.destroy();
          doughnutChartBack = new Chart(ctxD, {
            type: 'doughnut',
            data: {
              labels: ['Terminées', 'En cours'],
              datasets: [{ data: [statsDataBack.terminees, statsDataBack.enCours], backgroundColor: ['#4CAF50CC','#29B6F6CC'], borderColor: ['#388E3C','#0288D1'], borderWidth: 2, hoverOffset: 8 }]
            },
            options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font: { family: 'Inter', weight: '600' }, padding: 16, usePointStyle: true } } } }
          });
        }, 80);
      }

      function closeStatsModalBack() { statsModalBack.style.display = 'none'; }

      document.getElementById('openStatsModalBackBtn').addEventListener('click', openStatsModalBack);
      document.getElementById('closeStatsModalBackBtn').addEventListener('click', closeStatsModalBack);
      statsModalBack.addEventListener('click', (e) => { if (e.target === statsModalBack) closeStatsModalBack(); });
      
      loadAdminNotifications();
      setInterval(loadAdminNotifications, 10000);
    });
  </script>
</div>

<!-- MODALES POUR AFFICHER TOUT -->
<div id="modalDefis" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-tasks"></i> Tous les défis</h3><span class="close-modal" data-modal="defis">&times;</span></div><div class="modal-body"><table class="full-table" id="modalDefisTable"><thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>Points</th><th>Date début</th><th>Date fin</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>

<!-- ===== MODALE STATISTIQUES BACKOFFICE ===== -->
<div id="statsPointsModalBack" class="modal modal-stats">
  <div class="modal-content modal-large">
    <div class="modal-header modal-stats-header">
      <h3><i class="fas fa-chart-bar"></i> Statistiques — Points gagnés</h3>
      <span id="closeStatsModalBackBtn" class="close-modal">&times;</span>
    </div>
    <div class="modal-body modal-stats-body">
      <div class="stats-grid stats-metrics-grid">
        <div class="stats-metric-card total">
          <div class="metric-label"><i class="fas fa-star"></i> Total</div>
          <div class="metric-value"><?= number_format($statsPoints['total_points']) ?></div>
          <div class="metric-caption">pts distribués</div>
        </div>
        <div class="stats-metric-card average">
          <div class="metric-label"><i class="fas fa-calculator"></i> Moyenne</div>
          <div class="metric-value"><?= $statsPoints['moyenne_points'] ?></div>
          <div class="metric-caption">pts / participation</div>
        </div>
        <div class="stats-metric-card max">
          <div class="metric-label"><i class="fas fa-arrow-up"></i> Maximum</div>
          <div class="metric-value"><?= $statsPoints['max_points'] ?></div>
          <div class="metric-caption">pts en 1 participation</div>
        </div>
        <div class="stats-metric-card min">
          <div class="metric-label"><i class="fas fa-arrow-down"></i> Minimum</div>
          <div class="metric-value"><?= $statsPoints['min_points'] ?></div>
          <div class="metric-caption">pts en 1 participation</div>
        </div>
        <div class="stats-metric-card top">
          <div class="metric-label"><i class="fas fa-crown"></i> Top joueur</div>
          <div class="metric-value metric-value--small"><?= htmlspecialchars($statsPoints['top_user']) ?></div>
          <div class="metric-caption"><?= $statsPoints['top_user_pts'] ?> pts cumulés</div>
        </div>
      </div>
      <div class="stats-chart-grid">
        <div class="stats-chart-card">
          <h4><i class="fas fa-chart-bar"></i> Comparaison des indicateurs (pts)</h4>
          <canvas id="statsBarChartBack" height="200"></canvas>
        </div>
        <div class="stats-chart-card">
          <h4><i class="fas fa-circle-half-stroke"></i> Taux de complétion</h4>
          <canvas id="statsDoughnutChartBack"></canvas>
          <p class="stats-caption"><?= $statsPoints['total_terminees'] ?> terminées / <?= $statsPoints['total_participations'] - $statsPoints['total_terminees'] ?> en cours</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="addDefiModal" class="modal">
  <div class="modal-content modal-large">
    <div class="modal-header">
      <h3><i class="fas fa-plus-circle"></i> Nouveau défi</h3>
      <span class="close-modal" id="closeDefiModal">&times;</span>
    </div>
    <div class="modal-body">
      <form id="addDefiForm" action="../Gamification/CONTROLLER/DefiController.php?action=add" method="POST">
        <div class="form-stack">
          <input type="text" name="titre" placeholder="Titre du défi" class="modal-form-input">
          <select name="type" class="modal-form-select">
            <option value="nutrition">Nutrition</option>
            <option value="ecologie">Écologie</option>
            <option value="recette">Recette</option>
            <option value="social">Social</option>
          </select>
          <input type="number" name="points" placeholder="Points" min="0" class="modal-form-input">
          <div class="modal-form-row">
            <input type="date" name="date_debut" class="modal-form-input">
            <input type="date" name="date_fin" class="modal-form-input">
          </div>
          <button class="btn-primary" type="submit">Créer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div id="editDefiModal" class="modal">
  <div class="modal-content modal-large">
    <div class="modal-header">
      <h3><i class="fas fa-pen"></i> Modifier le défi</h3>
      <span class="close-modal" id="closeEditDefiModal">&times;</span>
    </div>
    <div class="modal-body">
      <form id="editDefiForm" action="../Gamification/CONTROLLER/DefiController.php?action=edit" method="POST">
        <input type="hidden" name="id">
        <div class="form-stack">
          <input type="text" name="titre" placeholder="Titre du défi" class="modal-form-input">
          <select name="type" class="modal-form-select">
            <option value="nutrition">Nutrition</option>
            <option value="ecologie">Écologie</option>
            <option value="recette">Recette</option>
            <option value="social">Social</option>
          </select>
          <input type="number" name="points" placeholder="Points" min="0" class="modal-form-input">
          <div class="modal-form-row">
            <input type="date" name="date_debut" class="modal-form-input">
            <input type="date" name="date_fin" class="modal-form-input">
          </div>
          <button class="btn-primary" type="submit">Mettre à jour</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- MODALES CRUD – logique dans gamification.js -->
<script src="../JS/gamification.js"></script>
</body>
</html>
