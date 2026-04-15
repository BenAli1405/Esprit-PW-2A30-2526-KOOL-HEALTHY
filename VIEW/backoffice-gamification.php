<?php
require_once __DIR__ . '/../CONTROLLER/DefiController.php';
require_once __DIR__ . '/../CONTROLLER/ParticipationController.php';
$defiController = new DefiController();
$participationController = new ParticipationController();
$defis = $defiController->listeDefis();
$stats = $defiController->statsDefis();
$participations = $participationController->listeParticipations();
$utilisateurs = $participationController->listeUtilisateurs();
$defisForParticipation = $participationController->listeDefis();
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
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #F5F5F5; color: #1E2A22; }
    :root {
      --vert-kool: #4CAF50;
      --vert-kool-dark: #2E7D32;
      --vert-kool-light: #E8F5E9;
      --bleu-tech: #29B6F6;
      --bleu-tech-dark: #0288D1;
      --bleu-tech-light: #E1F5FE;
      --blanc: #FFFFFF;
      --gris-clair: #F5F5F5;
      --gris-moyen: #E9ECEF;
      --gris-texte: #5F6B66;
      --ombre-legere: 0 12px 28px rgba(0, 0, 0, 0.05);
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
      overflow-y: auto;
    }
    .logo-area { padding: 32px 24px; border-bottom: 1px solid var(--gris-moyen); margin-bottom: 24px; }
    .logo-area h2 { font-weight: 800; font-size: 1.7rem; display: flex; align-items: center; gap: 10px; color: var(--vert-kool); }
    .logo-area h2 i { color: var(--bleu-tech); font-size: 2rem; }
    .logo-area p { font-size: 0.7rem; color: var(--gris-texte); margin-top: 8px; }
    .nav-menu { flex: 1; padding: 0 16px; }
    .nav-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px 18px;
      margin: 6px 0;
      border-radius: 16px;
      font-weight: 500;
      transition: all 0.2s;
      color: #4A5B4E;
      cursor: pointer;
    }
    .nav-item i { width: 24px; font-size: 1.2rem; color: var(--bleu-tech); }
    .nav-item.active { background: var(--vert-kool-light); color: var(--vert-kool-dark); }
    .nav-item.active i { color: var(--vert-kool); }
    .nav-item:hover:not(.active) { background: var(--gris-clair); }
    .sidebar-footer { padding: 20px 20px 30px; border-top: 1px solid var(--gris-moyen); margin-top: 20px; }
    .user-badge { display: flex; align-items: center; gap: 12px; }
    .user-avatar { width: 44px; height: 44px; background: linear-gradient(135deg, var(--vert-kool), var(--bleu-tech)); border-radius: 32px; display: flex; align-items: center; justify-content: center; color: white; }
    .main-content { flex: 1; background: #F8F9FA; overflow-x: auto; }
    .top-bar {
      background: var(--blanc);
      padding: 20px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      border-bottom: 1px solid var(--gris-moyen);
    }
    .page-title h1 { font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, var(--vert-kool), var(--bleu-tech)); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .page-title p { color: var(--gris-texte); font-size: 0.85rem; margin-top: 6px; }
    .btn-primary {
      background: var(--vert-kool);
      border: none;
      padding: 10px 22px;
      border-radius: 40px;
      font-weight: 600;
      color: white;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: 0.2s;
      font-size: 0.85rem;
    }
    .btn-primary:hover { background: var(--vert-kool-dark); transform: translateY(-1px); }
    .btn-outline {
      background: transparent;
      border: 1px solid var(--bleu-tech);
      color: var(--bleu-tech-dark);
      padding: 8px 18px;
      border-radius: 40px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: 0.2s;
    }
    .btn-outline:hover { background: var(--bleu-tech-light); }
    .btn-danger {
      background: #ef5350;
      border: none;
      padding: 6px 14px;
      border-radius: 40px;
      font-weight: 600;
      color: white;
      cursor: pointer;
      font-size: 0.7rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-edit {
      background: var(--bleu-tech);
      border: none;
      padding: 6px 14px;
      border-radius: 40px;
      font-weight: 600;
      color: white;
      cursor: pointer;
      font-size: 0.7rem;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-right: 8px;
    }
    .btn-show-more {
      background: transparent;
      border: 1px solid var(--vert-kool);
      color: var(--vert-kool-dark);
      padding: 8px 20px;
      border-radius: 40px;
      font-weight: 500;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: 0.2s;
      margin-top: 16px;
      font-size: 0.8rem;
    }
    .btn-show-more:hover { background: var(--vert-kool-light); border-color: var(--vert-kool); }
    .unified-container { padding: 28px 32px; max-width: 1600px; margin: 0 auto; }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 24px;
      margin-bottom: 32px;
    }
    .stat-card {
      background: var(--blanc);
      border-radius: 28px;
      padding: 22px 24px;
      box-shadow: var(--ombre-legere);
      border: 1px solid rgba(0,0,0,0.02);
    }
    .stat-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gris-texte); margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
    .stat-value { font-size: 2.4rem; font-weight: 800; color: var(--vert-kool); }
    .section-card {
      background: var(--blanc);
      border-radius: 28px;
      padding: 24px 28px;
      box-shadow: var(--ombre-legere);
      margin-bottom: 32px;
      border: 1px solid var(--gris-moyen);
    }
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 24px;
      border-bottom: 2px solid var(--gris-moyen);
      padding-bottom: 16px;
    }
    .section-header h2 { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; color: #2C3E2F; }
    .badge-tech { background: var(--bleu-tech-light); color: var(--bleu-tech-dark); padding: 4px 14px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
    .badge-eco { background: var(--vert-kool-light); color: var(--vert-kool-dark); padding: 4px 14px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; }
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { text-align: left; padding: 14px 8px 12px 0; font-weight: 600; font-size: 0.75rem; color: #6C7A73; border-bottom: 1px solid var(--gris-moyen); }
    .data-table td { padding: 14px 8px 14px 0; border-bottom: 1px solid #F0F2F0; font-size: 0.85rem; vertical-align: middle; }
    .progress-bar-bg { background: var(--gris-moyen); border-radius: 20px; height: 8px; overflow: hidden; width: 120px; }
    .progress-fill { background: var(--vert-kool); height: 100%; border-radius: 20px; }
    .flex-progress { display: flex; align-items: center; gap: 12px; }
    .rank-badge { background: linear-gradient(135deg, #FFC107, #FF8F00); color: white; padding: 4px 14px; border-radius: 40px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
    .status-active { background: var(--vert-kool-light); color: var(--vert-kool-dark); padding: 4px 12px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; }
    .badges-grid { display: flex; flex-wrap: wrap; gap: 24px; margin-top: 16px; }
    .badge-card {
      background: #FFFFFF;
      border-radius: 24px;
      width: 200px;
      text-align: center;
      padding: 20px 16px;
      transition: all 0.2s;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
      border: 1px solid var(--gris-moyen);
    }
    .badge-card i { font-size: 2.8rem; background: linear-gradient(145deg, var(--vert-kool), var(--bleu-tech)); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .table-footer { text-align: center; margin-top: 16px; }
    
    /* MODALES */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 2000;
      backdrop-filter: blur(3px);
    }
    .modal-content {
      background: white;
      border-radius: 32px;
      width: 90%;
      max-width: 1100px;
      max-height: 85vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
      animation: modalFadeIn 0.2s ease;
    }
    @keyframes modalFadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    .modal-header {
      padding: 20px 28px;
      border-bottom: 2px solid var(--gris-moyen);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: var(--blanc);
      border-radius: 32px 32px 0 0;
    }
    .modal-header h3 { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; color: var(--vert-kool); }
    .close-modal {
      font-size: 1.8rem;
      cursor: pointer;
      color: var(--gris-texte);
      transition: 0.2s;
      line-height: 1;
    }
    .close-modal:hover { color: #ef5350; transform: scale(1.1); }
    .modal-body {
      padding: 24px 28px;
      overflow-y: auto;
      flex: 1;
    }
    .full-table { width: 100%; border-collapse: collapse; }
    .full-table th { text-align: left; padding: 12px 8px; background: #F8F9FA; position: sticky; top: 0; }
    .full-table td { padding: 12px 8px; border-bottom: 1px solid var(--gris-moyen); }
    .badges-modal-grid { display: flex; flex-wrap: wrap; gap: 20px; justify-content: flex-start; }
    
    @media (max-width: 900px) {
      .sidebar { width: 85px; }
      .sidebar .logo-area h2 span, .sidebar .nav-item span, .sidebar .user-info { display: none; }
      .nav-item { justify-content: center; }
      .unified-container { padding: 20px; }
    }
  </style>
</head>
<body>
<div class="app-wrapper">
  <aside class="sidebar">
    <div class="logo-area">
<img src="../Assets/kool.png" alt="" style="width: 190px; height: 190px; border-radius: 8px;">
<p>back office ·</p>
    </div>
    <div class="nav-menu">
      <div class="nav-item active" data-tab="unified"><i class="fas fa-chart-simple"></i><span>Gamification</span></div>
    </div>
    <div class="sidebar-footer">
      <div class="user-badge">
        <div class="user-avatar"><i class="fas fa-user-md"></i></div>
        <div class="user-info"><p>Dr. Emma Green</p><small>admin@koolhealthy.com</small></div>
      </div>
    </div>
  </aside>

  <main class="main-content">
    <div class="top-bar">
      <div class="page-title">
        <h1><i class="fas fa-gamepad"></i> Gamification</h1>
        <p>Défis · Classement · Participations</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" id="openGlobalDefiBtn"><i class="fas fa-plus-circle"></i> Nouveau défi</button>
      </div>
    </div>
    <?php if ($message): ?>
      <div style="margin: 0 32px 24px; padding: 16px 20px; border-radius: 18px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #256029;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>


    <div class="unified-container">
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-title"><i class="fas fa-trophy"></i> Défis actifs</div><div class="stat-value" id="statsDefisActifs"><?= htmlspecialchars((string)$stats['total_defis']) ?></div></div>
        <div class="stat-card"><div class="stat-title"><i class="fas fa-users"></i> Participants totaux</div><div class="stat-value" id="statsParticipants"><?= htmlspecialchars((string)$stats['participants']) ?></div></div>
        <div class="stat-card"><div class="stat-title"><i class="fas fa-star"></i> Points distribués</div><div class="stat-value" id="statsPoints"><?= htmlspecialchars((string)$stats['points_distribues']) ?></div></div>
      </div>

      <!-- SECTION DÉFIS -->
      <div class="section-card">
        <div class="section-header">
          <h2><i class="fas fa-tasks" style="color: var(--vert-kool);"></i> Gestion des défis</h2>
          <button class="btn-outline" id="quickAddDefi"><i class="fas fa-plus"></i> Ajouter un défi</button>
        </div>
        <div style="overflow-x: auto;">
          <table class="data-table" style="width:100%">
            <thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>Points</th><th>Date début</th><th>Date fin</th><th>Actions</th></tr></thead>
            <tbody id="defisListUnified">
              <?php foreach ($defis as $defi): ?>
                <tr>
                  <td><?= htmlspecialchars($defi['id']) ?></td>
                  <td><strong><?= htmlspecialchars($defi['titre']) ?></strong></td>
                  <td><span class="badge-tech"><?= htmlspecialchars($defi['type']) ?></span></td>
                  <td><span class="badge-eco"><?= htmlspecialchars($defi['points']) ?> pts</span></td>
                  <td><?= htmlspecialchars($defi['date_debut']) ?></td>
                  <td><?= htmlspecialchars($defi['date_fin']) ?></td>
                  <td>
                    <button type="button" class="btn-edit edit-defi-btn" data-id="<?= $defi['id'] ?>" data-titre="<?= htmlspecialchars($defi['titre'], ENT_QUOTES) ?>" data-type="<?= htmlspecialchars($defi['type'], ENT_QUOTES) ?>" data-points="<?= $defi['points'] ?>" data-date-debut="<?= $defi['date_debut'] ?>" data-date-fin="<?= $defi['date_fin'] ?>"><i class="fas fa-pen"></i> Modifier</button>
                    <a href="../CONTROLLER/DefiController.php?action=delete&id=<?= $defi['id'] ?>" class="btn-danger btn-delete-confirm"><i class="fas fa-trash"></i></a>
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
            <h2><i class="fas fa-clipboard-list" style="color: var(--vert-kool);"></i> Participations</h2>
            <p style="font-size:0.9rem; color: var(--gris-texte); margin-top: 6px;">Suivi des performances utilisateur par défi.</p>
          </div>
          <button class="btn-outline" id="openAddParticipationBtn"><i class="fas fa-plus"></i> Ajouter une participation</button>
        </div>
        <div style="overflow-x: auto;">
          <table class="data-table" style="width:100%">
            <thead><tr><th>ID</th><th>Utilisateur</th><th>Défi</th><th>Progression</th><th>Statut</th><th>Points gagnés</th><th>Créé le</th><th>Actions</th></tr></thead>
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
                  <td>
                    <button type="button" class="btn-edit edit-participation-btn" data-id="<?= $participation['id'] ?>" data-utilisateur-id="<?= $participation['utilisateur_id'] ?>" data-defi-id="<?= $participation['defi_id'] ?>" data-progression="<?= $participation['progression'] ?>" data-points="<?= $participation['points_gagnes'] ?>" data-termine="<?= $participation['termine'] ?>"><i class="fas fa-pen"></i> Modifier</button>
                    <a href="../CONTROLLER/ParticipationController.php?action=delete&id=<?= $participation['id'] ?>" class="btn-danger btn-delete-confirm"><i class="fas fa-trash"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="table-footer"></div>
      </div>

      <!-- CLASSEMENT -->
      <div style="display: flex; flex-wrap: wrap; gap: 32px; margin-bottom: 32px;">
        <div style="flex: 1; min-width: 400px;">
          <div class="section-card" style="height: 100%;">
            <div class="section-header">
              <h2><i class="fas fa-chart-line" style="color: var(--bleu-tech);"></i> Classement général</h2>
              <div class="badge-tech"><i class="fas fa-fire"></i> Top performants</div>
            </div>
            <div style="overflow-x: auto;">
              <table class="data-table" style="width:100%">
                <thead><tr><th>Rang</th><th>Utilisateur</th><th>Points totaux</th><th>Défis complétés</th></tr></thead>
                <tbody id="classementListUnified"></tbody>
              </table>
            </div>
            <div id="classementFooter" class="table-footer"></div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<!-- MODALES POUR AFFICHER TOUT -->
<div id="modalDefis" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-tasks"></i> Tous les défis</h3><span class="close-modal" data-modal="defis">&times;</span></div><div class="modal-body"><table class="full-table" id="modalDefisTable"><thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>Points</th><th>Date début</th><th>Date fin</th><th>Actions</th></tr></thead><tbody></tbody></table></div></div></div>
<div id="modalClassement" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-chart-line"></i> Classement complet</h3><span class="close-modal" data-modal="classement">&times;</span></div><div class="modal-body"><table class="full-table" id="modalClassementTable"><thead><tr><th>Rang</th><th>Utilisateur</th><th>Points totaux</th><th>Défis complétés</th></tr></thead><tbody></tbody></table></div></div></div>

<div id="addDefiModal" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-plus-circle"></i> Nouveau défi</h3><span class="close-modal" id="closeDefiModal">&times;</span></div><div class="modal-body"><form id="addDefiForm" action="../CONTROLLER/DefiController.php?action=add" method="POST"><div style="display:flex; flex-direction:column; gap:14px;"><input type="text" name="titre" placeholder="Titre du défi" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><select name="type" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><option value="nutrition">Nutrition</option><option value="ecologie">Écologie</option><option value="recette">Recette</option><option value="social">Social</option></select><input type="number" name="points" placeholder="Points" min="0" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><div style="display:flex; gap:12px;"><input type="date" name="date_debut" style="flex:1; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><input type="date" name="date_fin" style="flex:1; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"></div><button class="btn-primary" type="submit" style="width:100%;">Créer</button></div></form></div></div></div>
<div id="editDefiModal" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-pen"></i> Modifier le défi</h3><span class="close-modal" id="closeEditDefiModal">&times;</span></div><div class="modal-body"><form id="editDefiForm" action="../CONTROLLER/DefiController.php?action=edit" method="POST"><input type="hidden" name="id"><div style="display:flex; flex-direction:column; gap:14px;"><input type="text" name="titre" placeholder="Titre du défi" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><select name="type" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><option value="nutrition">Nutrition</option><option value="ecologie">Écologie</option><option value="recette">Recette</option><option value="social">Social</option></select><input type="number" name="points" placeholder="Points" min="0" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><div style="display:flex; gap:12px;"><input type="date" name="date_debut" style="flex:1; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><input type="date" name="date_fin" style="flex:1; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"></div><button class="btn-primary" type="submit" style="width:100%;">Mettre à jour</button></div></form></div></div></div>
<div id="addParticipationModal" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-plus-circle"></i> Nouvelle participation</h3><span class="close-modal" id="closeAddParticipationModal">&times;</span></div><div class="modal-body"><form id="addParticipationForm" action="../CONTROLLER/ParticipationController.php?action=add" method="POST"><div style="display:flex; flex-direction:column; gap:14px;"><select name="utilisateur_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><option value="">Sélectionner un utilisateur</option><?php foreach ($utilisateurs as $user): ?><option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></option><?php endforeach; ?></select><select name="defi_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><option value="">Sélectionner un défi</option><?php foreach ($defisForParticipation as $defiSelect): ?><option value="<?= $defiSelect['id'] ?>"><?= htmlspecialchars($defiSelect['titre']) ?></option><?php endforeach; ?></select><input type="number" name="progression" placeholder="Progression (%)" min="0" max="100" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><label style="display:flex; align-items:center; gap:10px;"><input type="checkbox" name="termine" value="1"> Terminé</label><input type="number" name="points_gagnes" placeholder="Points gagnés" min="0" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><button class="btn-primary" type="submit" style="width:100%;">Créer</button></div></form></div></div></div>
<div id="editParticipationModal" class="modal"><div class="modal-content"><div class="modal-header"><h3><i class="fas fa-pen"></i> Modifier participation</h3><span class="close-modal" id="closeEditParticipationModal">&times;</span></div><div class="modal-body"><form id="editParticipationForm" action="../CONTROLLER/ParticipationController.php?action=edit" method="POST"><input type="hidden" name="id"><div style="display:flex; flex-direction:column; gap:14px;"><select name="utilisateur_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><option value="">Sélectionner un utilisateur</option><?php foreach ($utilisateurs as $user): ?><option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></option><?php endforeach; ?></select><select name="defi_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><option value="">Sélectionner un défi</option><?php foreach ($defisForParticipation as $defiSelect): ?><option value="<?= $defiSelect['id'] ?>"><?= htmlspecialchars($defiSelect['titre']) ?></option><?php endforeach; ?></select><input type="number" name="progression" placeholder="Progression (%)" min="0" max="100" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><label style="display:flex; align-items:center; gap:10px;"><input type="checkbox" name="termine" value="1"> Terminé</label><input type="number" name="points_gagnes" placeholder="Points gagnés" min="0" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);"><button class="btn-primary" type="submit" style="width:100%;">Mettre à jour</button></div></form></div></div></div>

<!-- MODALES CRUD – logique dans gamification.js -->
<script src="../JS/gamification.js"></script>
</body>
</html>
