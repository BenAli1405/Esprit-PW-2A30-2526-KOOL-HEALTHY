<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../CONTROLLER/DefiController.php';
require_once __DIR__ . '/../CONTROLLER/ParticipationController.php';

$defiController = new DefiController();
$participationController = new ParticipationController();
$defis = $defiController->listeDefis();
$classement = $participationController->classement();
$participations = $participationController->listeParticipations();
$utilisateurs = $participationController->listeUtilisateurs();
$defisForParticipation = $participationController->listeDefis();
$statsPoints = $participationController->statsParticipationsPoints();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$message = '';
$messageType = 'success';

if ($success === 'participation_added') {
    $message = 'Participation ajoutée avec succès.';
} elseif ($success === 'participation_edited') {
    $message = 'Participation modifiée avec succès.';
} elseif ($success === 'participation_deleted') {
    $message = 'Participation supprimée avec succès.';
} elseif ($error === 'duplicate_participation') {
    $message = 'Cet utilisateur a déjà une participation en cours pour ce défi !';
    $messageType = 'error';
} elseif ($error === 'db_error') {
    $message = 'Une erreur est survenue lors de l\'enregistrement en base de données.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Kool Healthy | Défis & Récompenses</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F5F5F5; color: #2C3E2F; scroll-behavior: smooth; }
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
            --ombre-legere: 0 8px 20px rgba(0,0,0,0.05);
            --shadow-hover: 0 12px 28px rgba(0,0,0,0.08);
            --rouge-notif: #FF5252;
        }
        .navbar { background: var(--blanc); padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; box-shadow: var(--ombre-legere); position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid var(--gris-moyen); }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo i { font-size: 2rem; color: var(--vert-kool); }
        .logo h1 { font-size: 1.6rem; font-weight: 700; color: var(--vert-kool); }
        .nav-icons { display: flex; align-items: center; gap: 1.5rem; margin-right: 1rem; }
        .notif-wrapper { position: relative; cursor: pointer; }
        .notif-badge { position: absolute; top: -5px; right: -8px; background: var(--rouge-notif); color: white; font-size: 0.65rem; padding: 2px 5px; border-radius: 50%; font-weight: 700; border: 2px solid white; }
        .notif-dropdown { position: absolute; top: 100%; right: 0; width: 300px; background: white; border-radius: 16px; box-shadow: var(--shadow-hover); display: none; z-index: 2000; margin-top: 10px; border: 1px solid var(--gris-moyen); overflow: hidden; }
        .notif-header { padding: 12px 15px; border-bottom: 1px solid var(--gris-moyen); font-weight: 700; font-size: 0.9rem; background: var(--gris-clair); }
        .notif-list { max-height: 300px; overflow-y: auto; }
        .notif-item { padding: 12px 15px; border-bottom: 1px solid var(--gris-clair); font-size: 0.85rem; transition: 0.2s; }
        .notif-item:hover { background: var(--vert-kool-light); }
        .notif-item.unread { border-left: 4px solid var(--vert-kool); background: #f0fff1; }
        .notif-empty { padding: 20px; text-align: center; color: var(--gris-texte); font-size: 0.85rem; }
        .nav-links { display: flex; gap: 2rem; align-items: center; flex-wrap: wrap; }
        .nav-links a { text-decoration: none; color: #4A5B4E; font-weight: 500; transition: 0.2s; cursor: pointer; }
        .nav-links a:hover { color: var(--bleu-tech); transform: translateY(-2px); }
        .btn-connect { background: var(--vert-kool); color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .btn-connect:hover { background: var(--vert-kool-dark); transform: scale(0.98); }
        .btn-outline { background: transparent; border: 1.5px solid var(--bleu-tech); color: var(--bleu-tech); padding: 0.6rem 1.5rem; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-outline:hover { background: var(--bleu-tech-light); border-color: var(--bleu-tech-dark); }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: white;
            border-bottom: 1px solid var(--gris-moyen);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            padding: 10px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .brand {
            display: flex;
            align-items: center;
            color: var(--vert-kool-dark);
            text-decoration: none;
            gap: 12px;
        }
        .brand-logo {
            height: 48px;
            width: auto;
            display: block;
        }
        .top-nav {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            margin-left: auto;
        }
        .top-nav a {
            text-decoration: none;
            color: #4A5B4E;
            font-weight: 500;
            font-size: 0.95rem;
            transition: 0.2s;
        }
        .top-nav a:hover { color: var(--bleu-tech-dark); }
        .top-nav a.active { color: var(--vert-kool); font-weight: 700; }
        .topbar-tools {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .auth-link {
            text-decoration: none;
            color: var(--bleu-tech-dark);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 18px;
            border: 1.5px solid var(--bleu-tech);
            border-radius: 999px;
            transition: 0.2s;
        }
        .auth-link:hover { background: var(--bleu-tech-light); }

        .hero { background: linear-gradient(135deg, var(--vert-kool-light) 0%, var(--bleu-tech-light) 100%); padding: 3rem 5%; text-align: center; border-radius: 0 0 40px 40px; margin-bottom: 1rem; }
        .hero h1 { font-size: 2.5rem; font-weight: 800; color: var(--vert-kool-dark); letter-spacing: -0.02em; }
        .hero p { margin-top: 1rem; color: #4A5B4E; font-size: 1.1rem; }
        .section { padding: 2.5rem 5%; }
        .section-title { font-size: 1.8rem; font-weight: 700; color: var(--vert-kool); margin-bottom: 1.8rem; display: flex; align-items: center; gap: 12px; border-left: 5px solid var(--bleu-tech); padding-left: 18px; }
        .stats-user { background: var(--blanc); border-radius: 32px; padding: 1.8rem; display: flex; justify-content: space-around; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 2rem; border: 1px solid var(--gris-moyen); box-shadow: var(--ombre-legere); }
        .stat-user-item { text-align: center; background: var(--gris-clair); padding: 0.8rem 1.5rem; border-radius: 48px; min-width: 140px; transition: all 0.2s; }
        .stat-user-value { font-size: 2.2rem; font-weight: 800; color: var(--vert-kool); }
        .badge-list { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem; }
        .badge-item { background: var(--bleu-tech-light); border-radius: 60px; padding: 0.6rem 1.4rem; display: flex; align-items: center; gap: 8px; font-weight: 600; color: var(--bleu-tech-dark); box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s; }
        .badge-item:hover { transform: translateY(-2px); background: #cceefc; }
        .defis-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; margin-top: 1rem; }
        .defi-card { background: var(--blanc); border-radius: 28px; padding: 1.5rem; box-shadow: var(--ombre-legere); border: 1px solid var(--gris-moyen); transition: all 0.25s ease; }
        .defi-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-hover); border-color: var(--bleu-tech); }
        .defi-points { background: var(--vert-kool-light); color: var(--vert-kool-dark); padding: 5px 14px; border-radius: 40px; font-size: 0.8rem; font-weight: 700; display: inline-block; }
        .progress-bar { background: var(--gris-moyen); border-radius: 20px; height: 8px; margin: 12px 0; overflow: hidden; }
        .progress-fill { background: linear-gradient(90deg, var(--vert-kool), var(--bleu-tech)); height: 100%; border-radius: 20px; width: 0%; }
        .btn-participate { background: var(--bleu-tech); color: white; border: none; padding: 10px 20px; border-radius: 60px; font-weight: 600; cursor: pointer; margin-top: 16px; width: 100%; transition: 0.2s; }
        .btn-participate:hover { background: var(--bleu-tech-dark); transform: scale(0.98); }
        .ranking-list { display: flex; flex-direction: column; gap: 1rem; }
        .ranking-card { background: var(--blanc); border-radius: 20px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1.2rem; border: 1px solid var(--gris-moyen); transition: all 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
        .ranking-card:hover { background: #fafeff; transform: translateX(5px); border-left: 4px solid var(--vert-kool); }
        .rank-num { font-size: 1.6rem; font-weight: 800; width: 55px; color: var(--bleu-tech); background: var(--bleu-tech-light); border-radius: 40px; text-align: center; padding: 6px 0; }
        .tab-container { background: var(--blanc); border-radius: 60px; display: inline-flex; margin-bottom: 2rem; box-shadow: var(--ombre-legere); padding: 5px; background: #f0f2f0; }
        .tab-btn { background: transparent; border: none; padding: 12px 28px; border-radius: 40px; font-weight: 600; cursor: pointer; transition: 0.2s; color: #4a5b4e; font-size: 1rem; }
        .tab-btn.active { background: var(--vert-kool); color: white; box-shadow: 0 2px 8px rgba(76,175,80,0.3); }
        .tab-pane { display: none; animation: fadeIn 0.3s ease; }
        .tab-pane.active-pane { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 14px 8px 12px 0; font-weight: 600; font-size: 0.75rem; color: #6C7A73; border-bottom: 1px solid var(--gris-moyen); }
        .data-table td { padding: 14px 8px 14px 0; border-bottom: 1px solid #F0F2F0; font-size: 0.85rem; vertical-align: middle; }
        .btn-danger { background: #ef5350; border: none; padding: 6px 14px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-edit { background: var(--bleu-tech); border: none; padding: 6px 14px; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; font-size: 0.7rem; display: inline-flex; align-items: center; gap: 6px; margin-right: 8px; }
        .btn-primary { background: var(--vert-kool); border: none; padding: 10px 22px; border-radius: 40px; font-weight: 600; color: white; display: inline-flex; justify-content: center; align-items: center; gap: 8px; cursor: pointer; transition: 0.2s; font-size: 0.85rem; }
        .btn-primary:hover { background: var(--vert-kool-dark); transform: translateY(-1px); }
        .status-active { background: var(--vert-kool-light); color: var(--vert-kool-dark); padding: 4px 12px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; }
        .badge-tech { background: var(--bleu-tech-light); color: var(--bleu-tech-dark); padding: 4px 14px; border-radius: 40px; font-size: 0.7rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(4px); }
        .modal-content { background: white; border-radius: 36px; width: 90%; max-width: 500px; border-top: 6px solid var(--vert-kool); box-shadow: 0 25px 40px rgba(0,0,0,0.2); overflow: hidden; }
        .modal-header { padding: 20px 28px; border-bottom: 2px solid var(--gris-moyen); display: flex; justify-content: space-between; align-items: center; background: var(--blanc); }
        .modal-header h3 { font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 12px; color: var(--vert-kool); margin: 0; }
        .modal-body { padding: 24px 28px; overflow-y: auto; flex: 1; text-align: left; }
        .close-modal { font-size: 1.8rem; cursor: pointer; transition: 0.2s; color: #9e9e9e; }
        .close-modal:hover { color: #ef5350; }
        .footer { background: #1E3A2E; color: #C6E0D4; padding: 3rem 5% 2rem; margin-top: 3rem; border-radius: 40px 40px 0 0; }
        .search-container { background: var(--blanc); border-radius: 28px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--ombre-legere); border: 1px solid var(--gris-moyen); display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        .search-input { flex: 1; min-width: 200px; padding: 12px 18px; border-radius: 60px; border: 1px solid var(--gris-moyen); font-family: inherit; font-size: 0.95rem; transition: 0.2s; }
        .search-input:focus { outline: none; border-color: var(--bleu-tech); box-shadow: 0 0 0 3px var(--bleu-tech-light); }
        .search-select { padding: 12px 16px; border-radius: 60px; border: 1px solid var(--gris-moyen); font-family: inherit; font-weight: 500; cursor: pointer; background: white; transition: 0.2s; }
        .search-label { font-weight: 600; color: #4a5b4e; white-space: nowrap; }
        .search-results-info { color: var(--gris-texte); font-size: 0.9rem; font-weight: 500; }
        .no-results { text-align: center; padding: 2rem; color: var(--gris-texte); background: var(--gris-clair); border-radius: 20px; }
        @media (max-width: 768px) { .hero h1 { font-size: 1.8rem; } .tab-btn { padding: 8px 18px; font-size: 0.9rem; } .section-title { font-size: 1.5rem; } .search-container { flex-direction: column; } .search-input { width: 100%; } .search-select { width: 100%; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <?php if ($message): ?>
        <?php if ($messageType === 'error'): ?>
            <script>alert("<?= addslashes($message) ?>");</script>
        <?php else: ?>
            <div style="margin: 20px 5%; padding: 16px 20px; border-radius: 18px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #256029; text-align: center; font-weight: bold;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- SECTION UNIFIEE -->
    <div id="defisUnifiedSection" class="section">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; margin-bottom:0.5rem;">
            <h2 class="section-title" style="margin-bottom:0;"><i class="fas fa-chalkboard-user"></i> Défis, Classement & Récompenses</h2>
            <button id="openStatsModalBtn" style="background:linear-gradient(135deg,var(--bleu-tech),var(--bleu-tech-dark)); color:white; border:none; padding:10px 22px; border-radius:40px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; font-size:0.9rem; box-shadow:0 4px 14px rgba(41,182,246,0.35); transition:0.2s;"><i class="fas fa-chart-bar"></i> Statistiques Points</button>
        </div>

        <div class="tab-container">
            <button class="tab-btn active" data-tab="defisTab">🏁 Défis actifs</button>
            <button class="tab-btn" data-tab="communityTab">👥 Communauté</button>
            <button class="tab-btn" data-tab="classementTab">📈 Classement</button>
            <button class="tab-btn" data-tab="participationsTab">📋 Participations</button>
        </div>

        <!-- Pane Défis -->
        <div id="defisTab" class="tab-pane active-pane">
            <div class="search-container">
                <span class="search-label"><i class="fas fa-search"></i> Rechercher par:</span>
                <select id="searchAttribute" class="search-select">
                    <option value="titre">Titre du défi</option>
                    <option value="type">Type de défi</option>
                    <option value="points">Points</option>
                </select>
                <input type="text" id="searchInput" class="search-input" placeholder="Entrez votre terme de recherche...">
                <button id="clearSearchBtn" class="btn-outline" style="white-space: nowrap;"><i class="fas fa-times"></i> Réinitialiser</button>
                <span class="search-results-info" id="searchResultsInfo"></span>
            </div>
            <div class="defis-grid" id="allDefisUnifiedGrid"></div>
            <div id="noResultsMessage" class="no-results" style="display: none;">
                <i class="fas fa-search" style="font-size: 2rem; color: var(--gris-moyen); margin-bottom: 0.5rem;"></i>
                <p>Aucun défi ne correspond à votre recherche.</p>
            </div>
        </div>

        <!-- Pane Communauté -->
        <div id="communityTab" class="tab-pane">
            <div style="margin-bottom: 2rem;">
                <h3 style="color:var(--vert-kool); font-weight:700; margin-bottom:10px;"><i class="fas fa-users"></i> Défis de la communauté</h3>
                <p style="color:var(--gris-texte); margin-bottom:20px;">Défis proposés par les membres de Kool Healthy. Gagnez des points et encouragez vos amis !</p>
                <div class="defis-grid" id="communityDefisGrid"></div>

                <h3 id="aiDefisTitle" style="display:none; margin:2rem 0 1rem; color:var(--bleu-tech); font-weight:700;"><i class="fas fa-robot"></i> Défis recommandés par l'IA</h3>
                <div class="defis-grid" id="aiDefisGrid"></div>
                <div id="noCommunityDefis" class="no-results" style="display:none;">
                    <i class="fas fa-ghost" style="font-size:2rem; color:var(--gris-moyen); margin-bottom:0.5rem;"></i>
                    <p>Aucun défi communautaire pour le moment. Proposez le vôtre via le chatbot !</p>
                </div>
            </div>
        </div>

        <!-- Pane Classement -->
        <div id="classementTab" class="tab-pane">
            <div class="ranking-list" id="rankingList"></div>
        </div>

        <!-- Pane Participations -->
        <div id="participationsTab" class="tab-pane">
            <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <p class="badge-tech" style="color: var(--bleu-tech-dark); margin:0;"><i class="fas fa-clipboard-list"></i> Suivi des performances utilisateur par défi.</p>
                <button class="btn-outline" id="openAddParticipationBtn"><i class="fas fa-plus"></i> Ajouter</button>
            </div>

            <div class="search-container">
                <span class="search-label"><i class="fas fa-search"></i> Filtrer par:</span>
                <select id="searchAttributeParticipation" class="search-select">
                    <option value="id">ID</option>
                    <option value="defi_titre">Défi</option>
                    <option value="progression">Progression</option>
                </select>
                <input type="text" id="searchInputParticipation" class="search-input" placeholder="Entrez votre terme de recherche...">
                <button id="clearSearchParticipationBtn" class="btn-outline" style="white-space: nowrap;"><i class="fas fa-times"></i> Réinitialiser</button>
                <span class="search-results-info" id="searchResultsInfoParticipation"></span>
            </div>

            <div style="background: var(--blanc); border-radius: 28px; padding: 24px; box-shadow: var(--ombre-legere); border: 1px solid var(--gris-moyen); overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Défi</th>
                            <th>Progression <i class="fas fa-sort" style="cursor:pointer; color:var(--bleu-tech);" onclick="sortParticipations('progression')"></i></th>
                            <th>Statut <i class="fas fa-sort" style="cursor:pointer; color:var(--bleu-tech);" onclick="sortParticipations('termine')"></i></th>
                            <th>Points <i class="fas fa-sort" style="cursor:pointer; color:var(--bleu-tech);" onclick="sortParticipations('points_gagnes')"></i></th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="participationsTableBody">
                        <?php foreach ($participations as $participation): ?>
                            <tr class="participation-row" data-id="<?= htmlspecialchars($participation['id']) ?>" data-defi-titre="<?= htmlspecialchars(strtolower($participation['defi_titre'] ?? 'N/A')) ?>" data-progression="<?= htmlspecialchars($participation['progression']) ?>" data-termine="<?= $participation['termine'] ?>" data-points="<?= htmlspecialchars($participation['points_gagnes']) ?>">
                                <td><?= htmlspecialchars($participation['id']) ?></td>
                                <td><?= htmlspecialchars($participation['utilisateur_nom'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($participation['defi_titre'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($participation['progression']) ?>%</td>
                                <td><?= $participation['termine'] ? '<span class="status-active">Terminé</span>' : '<span class="badge-tech">En cours</span>' ?></td>
                                <td><?= htmlspecialchars($participation['points_gagnes']) ?> pts</td>
                                <td><?= htmlspecialchars($participation['created_at']) ?></td>
                                <td>
                                    <button type="button" class="btn-edit edit-participation-btn" data-id="<?= $participation['id'] ?>" data-utilisateur-id="<?= $participation['utilisateur_id'] ?>" data-defi-id="<?= $participation['defi_id'] ?>" data-progression="<?= $participation['progression'] ?>" data-points="<?= $participation['points_gagnes'] ?>" data-termine="<?= $participation['termine'] ?>"><i class="fas fa-pen"></i> Modifier</button>
                                    <a href="../CONTROLLER/ParticipationController.php?action=delete&id=<?= $participation['id'] ?>" class="btn-danger btn-delete-confirm"><i class="fas fa-trash"></i> Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="noParticipationResults" class="no-results" style="display: none; margin-top: 1rem;">
                <i class="fas fa-search" style="font-size: 2rem; color: var(--gris-moyen); margin-bottom: 0.5rem;"></i>
                <p>Aucune participation ne correspond à votre recherche.</p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div>
            <h4>Kool Healthy</h4>
            <p>Gamification & nutrition durable — Manger mieux, gagner des points, préserver la planète 🌍</p>
        </div>
        <div style="margin-top:1rem;">
            <p>© 2025 Kool Healthy — Ensemble pour un futur healthy</p>
        </div>
    </footer>

    <!-- Modales -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-sign-in-alt"></i> Connexion</h3>
                <span class="close-modal" id="closeLoginModal">&times;</span>
            </div>
            <div class="modal-body">
                <input type="email" placeholder="Email" id="loginEmail" style="width:100%; padding:12px; margin-bottom:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                <input type="password" placeholder="Mot de passe" id="loginPwd" style="width:100%; padding:12px; margin-bottom:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                <button class="btn-connect" style="width:100%;" id="doLoginBtn">Se connecter</button>
            </div>
        </div>
    </div>

    <div id="signupModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Inscription</h3>
                <span class="close-modal" id="closeSignupModal">&times;</span>
            </div>
            <div class="modal-body">
                <input type="text" placeholder="Nom complet" id="signupName" style="width:100%; padding:12px; margin-bottom:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                <input type="email" placeholder="Email" id="signupEmail" style="width:100%; padding:12px; margin-bottom:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                <input type="password" placeholder="Mot de passe" id="signupPwd" style="width:100%; padding:12px; margin-bottom:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                <button class="btn-connect" style="width:100%;" id="doSignupBtn">S'inscrire</button>
            </div>
        </div>
    </div>

    <!-- Modale Statistiques -->
    <div id="statsPointsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:3000; justify-content:center; align-items:center; backdrop-filter:blur(6px);">
        <div style="background:#fff; border-radius:36px; width:92%; max-width:860px; max-height:88vh; overflow-y:auto; box-shadow:0 30px 60px rgba(0,0,0,0.25);">
            <div style="padding:22px 28px; border-bottom:2px solid #f0f2f0; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#fff; border-radius:36px 36px 0 0; z-index:1;">
                <h3 style="font-size:1.4rem; font-weight:800; color:#388E3C; display:flex; align-items:center; gap:10px;"><i class="fas fa-chart-bar" style="color:#29B6F6;"></i> Statistiques — Points gagnés</h3>
                <span id="closeStatsModalBtn" style="font-size:2rem; cursor:pointer; color:#9e9e9e; line-height:1; transition:0.2s;">&times;</span>
            </div>
            <div style="padding:28px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:14px; margin-bottom:28px;">
                    <div style="background:linear-gradient(135deg,#4CAF50,#388E3C); border-radius:20px; padding:1rem; text-align:center; color:#fff;">
                        <div style="font-size:0.65rem; text-transform:uppercase; letter-spacing:1px; opacity:0.85; margin-bottom:6px;"><i class="fas fa-star"></i> Total</div>
                        <div style="font-size:2rem; font-weight:800;"><?= number_format($statsPoints['total_points']) ?></div>
                        <div style="font-size:0.7rem; opacity:0.8;">pts distribués</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#29B6F6,#0288D1); border-radius:20px; padding:1rem; text-align:center; color:#fff;">
                        <div style="font-size:0.65rem; text-transform:uppercase; letter-spacing:1px; opacity:0.85; margin-bottom:6px;"><i class="fas fa-calculator"></i> Moyenne</div>
                        <div style="font-size:2rem; font-weight:800;"><?= $statsPoints['moyenne_points'] ?></div>
                        <div style="font-size:0.7rem; opacity:0.8;">pts / participation</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#FFC107,#F57F17); border-radius:20px; padding:1rem; text-align:center; color:#fff;">
                        <div style="font-size:0.65rem; text-transform:uppercase; letter-spacing:1px; opacity:0.85; margin-bottom:6px;"><i class="fas fa-arrow-up"></i> Maximum</div>
                        <div style="font-size:2rem; font-weight:800;"><?= $statsPoints['max_points'] ?></div>
                        <div style="font-size:0.7rem; opacity:0.8;">pts en 1 participation</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#E91E63,#AD1457); border-radius:20px; padding:1rem; text-align:center; color:#fff;">
                        <div style="font-size:0.65rem; text-transform:uppercase; letter-spacing:1px; opacity:0.85; margin-bottom:6px;"><i class="fas fa-arrow-down"></i> Minimum</div>
                        <div style="font-size:2rem; font-weight:800;"><?= $statsPoints['min_points'] ?></div>
                        <div style="font-size:0.7rem; opacity:0.8;">pts en 1 participation</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#009688,#00695C); border-radius:20px; padding:1rem; text-align:center; color:#fff;">
                        <div style="font-size:0.65rem; text-transform:uppercase; letter-spacing:1px; opacity:0.85; margin-bottom:6px;"><i class="fas fa-crown"></i> Top joueur</div>
                        <div style="font-size:1rem; font-weight:800; line-height:1.3;"><?= htmlspecialchars($statsPoints['top_user']) ?></div>
                        <div style="font-size:0.7rem; opacity:0.8;"><?= $statsPoints['top_user_pts'] ?> pts cumulés</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px; align-items:start;">
                    <div style="background:#f8f9fa; border-radius:20px; padding:20px;">
                        <h4 style="font-size:0.85rem; font-weight:700; color:#4a5b4e; margin-bottom:14px; text-transform:uppercase; letter-spacing:0.5px;"><i class="fas fa-chart-bar" style="color:#29B6F6;"></i> Comparaison des indicateurs (pts)</h4>
                        <canvas id="statsBarChart" height="200"></canvas>
                    </div>
                    <div style="background:#f8f9fa; border-radius:20px; padding:20px;">
                        <h4 style="font-size:0.85rem; font-weight:700; color:#4a5b4e; margin-bottom:14px; text-transform:uppercase; letter-spacing:0.5px;"><i class="fas fa-circle-half-stroke" style="color:#9C27B0;"></i> Taux de complétion</h4>
                        <canvas id="statsDoughnutChart"></canvas>
                        <p style="text-align:center; font-size:0.8rem; color:#616161; margin-top:10px;"><?= $statsPoints['total_terminees'] ?> terminées / <?= $statsPoints['total_participations'] - $statsPoints['total_terminees'] ?> en cours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales Participations -->
    <div id="addParticipationModal" class="modal">
        <div class="modal-content" style="max-width: 600px; padding: 0;">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Nouvelle participation</h3>
                <span class="close-modal" id="closeAddParticipationModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addParticipationForm" action="../CONTROLLER/ParticipationController.php?action=add" method="POST">
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <select name="utilisateur_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                            <option value="">Sélectionner un utilisateur</option>
                            <?php foreach ($utilisateurs as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="defi_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                            <option value="">Sélectionner un défi</option>
                            <?php foreach ($defisForParticipation as $defiSelect): ?>
                                <option value="<?= $defiSelect['id'] ?>"><?= htmlspecialchars($defiSelect['titre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="progression" placeholder="Progression (%)" min="0" max="100" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                        <label style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="termine" value="1"> Terminé
                        </label>
                        <input type="number" name="points_gagnes" placeholder="Points gagnés" min="0" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                        <button class="btn-primary" type="submit" style="width:100%;">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editParticipationModal" class="modal">
        <div class="modal-content" style="max-width: 600px; padding: 0;">
            <div class="modal-header">
                <h3><i class="fas fa-pen"></i> Modifier participation</h3>
                <span class="close-modal" id="closeEditParticipationModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editParticipationForm" action="../CONTROLLER/ParticipationController.php?action=edit" method="POST">
                    <input type="hidden" name="id">
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <select name="utilisateur_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                            <option value="">Sélectionner un utilisateur</option>
                            <?php foreach ($utilisateurs as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="defi_id" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                            <option value="">Sélectionner un défi</option>
                            <?php foreach ($defisForParticipation as $defiSelect): ?>
                                <option value="<?= $defiSelect['id'] ?>"><?= htmlspecialchars($defiSelect['titre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="progression" placeholder="Progression (%)" min="0" max="100" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                        <label style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" name="termine" value="1"> Terminé
                        </label>
                        <input type="number" name="points_gagnes" placeholder="Points gagnés" min="0" style="width:100%; padding:12px; border-radius:16px; border:1px solid var(--gris-moyen);">
                        <button class="btn-primary" type="submit" style="width:100%;">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Chatbot Intelligent -->
    <div id="aiChatbotContainer" style="position:fixed; bottom:20px; right:20px; z-index:4000; display:flex; flex-direction:column; align-items:flex-end;">
        <div id="aiChatbotWindow" style="display:none; width:350px; background:white; border-radius:24px; box-shadow:0 15px 35px rgba(0,0,0,0.2); overflow:hidden; margin-bottom:15px; border:1px solid var(--gris-moyen);">
            <div style="background:var(--vert-kool); color:white; padding:15px 20px; display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0; font-size:1.1rem; display:flex; align-items:center; gap:8px;"><i class="fas fa-robot"></i> IA Santé</h4>
                <i class="fas fa-times" id="closeChatbotBtn" style="cursor:pointer; font-size:1.2rem;"></i>
            </div>
            <div id="chatbotMessages" style="height:300px; overflow-y:auto; padding:15px; background:#f9fcf9; display:flex; flex-direction:column; gap:10px;">
                <div style="background:#e8f5e9; padding:10px 15px; border-radius:15px 15px 15px 0; max-width:85%; color:#2c3e2f; font-size:0.9rem; align-self:flex-start;">
                    Bonjour ! Je suis votre assistant de santé IA. Quel problème rencontrez-vous aujourd'hui ? Je peux vous proposer un défi adapté.
                </div>
            </div>
            <div style="padding:15px; background:white; border-top:1px solid var(--gris-moyen); display:flex; gap:10px;">
                <input type="text" id="chatbotInput" placeholder="Ex: J'ai mal au dos..." style="flex:1; padding:10px 15px; border-radius:20px; border:1px solid var(--gris-moyen); outline:none;">
                <button id="chatbotSendBtn" style="background:var(--bleu-tech); color:white; border:none; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <button id="toggleChatbotBtn" style="width:60px; height:60px; border-radius:50%; background:var(--bleu-tech); color:white; border:none; box-shadow:0 8px 20px rgba(41,182,246,0.4); cursor:pointer; font-size:1.5rem; display:flex; align-items:center; justify-content:center; transition:transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
            <i class="fas fa-robot"></i>
        </button>
    </div>

    <script>
        // ---------- DATA MODEL ----------
        let currentUser = { 
            name: "Sophie M.", 
            points: 980, 
            badges: [{nom:"Éco-citoyen", icone:"fa-leaf"},{nom:"Chef végétal", icone:"fa-carrot"}], 
            defisCompletes: 4 
        };
        
        let allDefis = <?= json_encode($defis, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
        let classement = <?= json_encode($classement, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

        // Variables pour la recherche
        let filteredDefis = [...allDefis];
        let currentSearchTerm = '';
        let currentSearchAttribute = 'titre';

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        function filterDefis() {
            const searchInput = document.getElementById('searchInput');
            if (!searchInput) return;
            
            const searchTerm = searchInput.value.toLowerCase().trim();
            const searchAttribute = document.getElementById('searchAttribute');
            const attribute = searchAttribute ? searchAttribute.value : 'titre';
            
            currentSearchTerm = searchTerm;
            currentSearchAttribute = attribute;

            if (!searchTerm) {
                filteredDefis = [...allDefis];
                const resultsInfo = document.getElementById('searchResultsInfo');
                if (resultsInfo) resultsInfo.textContent = '';
            } else {
                filteredDefis = allDefis.filter(defi => {
                    let fieldValue = '';
                    
                    if (attribute === 'titre') {
                        fieldValue = (defi.titre || '').toLowerCase();
                    } else if (attribute === 'type') {
                        fieldValue = (defi.type || '').toLowerCase();
                    } else if (attribute === 'points') {
                        fieldValue = (defi.points || 0).toString();
                    }
                    
                    return fieldValue.includes(searchTerm);
                });

                const resultCount = filteredDefis.length;
                const resultsInfo = document.getElementById('searchResultsInfo');
                if (resultsInfo) {
                    resultsInfo.textContent = resultCount === 0 
                        ? 'Aucun résultat trouvé' 
                        : resultCount === 1 
                            ? '1 défi trouvé' 
                            : resultCount + ' défis trouvés';
                }
            }

            renderFilteredDefis();
        }

        function renderFilteredDefis() {
            const containerAll = document.getElementById('allDefisUnifiedGrid');
            const containerAI = document.getElementById('aiDefisGrid');
            const noResultsDiv = document.getElementById('noResultsMessage');
            const titleAI = document.getElementById('aiDefisTitle');
            const titleOther = document.getElementById('otherDefisTitle');

            if (!containerAll || !containerAI) return;

            if (!filteredDefis || filteredDefis.length === 0) {
                containerAll.innerHTML = '';
                containerAI.innerHTML = '';
                if(titleAI) titleAI.style.display = 'none';
                if(titleOther) titleOther.style.display = 'none';
                if (noResultsDiv) noResultsDiv.style.display = 'block';
            } else {
                if (noResultsDiv) noResultsDiv.style.display = 'none';
                
                // Ne montrer que les défis officiels (proposant_id null) et approuvés dans l'onglet "Défis actifs"
                const otherDefis = filteredDefis.filter(d => 
                    d.status === 'approuve' && 
                    (d.proposant_id === null || d.proposant_id === undefined) && 
                    !(d.type || '').toUpperCase().startsWith('IA -')
                );

                if(titleOther) titleOther.style.display = otherDefis.length > 0 ? 'block' : 'none';

                containerAll.innerHTML = otherDefis.map(d => generateDefiCardHTML(d, false)).join('');
                
                // On cache la section IA de cet onglet car elle est maintenant dans "Communauté"
                if(containerAI) containerAI.innerHTML = '';
                if(titleAI) titleAI.style.display = 'none';
            }
        }

        function generateDefiCardHTML(d, isAI) {
            return `
                <div id="defi-${d.id}" class="defi-card" style="${isAI ? 'border-left: 5px solid var(--bleu-tech);' : ''} scroll-margin-top: 100px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                        <h3 style="font-size:1.25rem;">${escapeHtml(d.titre)}</h3>
                        <span class="defi-points">${d.points || 0} pts</span>
                    </div>
                    <div style="margin: 8px 0 6px;">
                        <span class="badge-tech" style="${isAI ? 'background:var(--bleu-tech); color:white;' : 'background:var(--bleu-tech-light);'} padding:2px 10px; border-radius:30px; font-size:0.75rem;">${escapeHtml(d.type)}</span>
                        <span style="margin-left:8px;">👥 ${d.participants || 0}</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill" style="width:${d.progression || 0}%"></div></div>
                    <p style="font-size:0.8rem; margin-bottom:8px;">Progression: ${d.progression || 0}%</p>
                    <button class="btn-participate" onclick="participateDefi(${d.id})">${(d.progression || 0) >= 100 ? '✅ Complété' : '▶ Participer'}</button>
                </div>
            `;
        }

        function renderUnifiedRanking() {
            const rankingContainer = document.getElementById('rankingList');
            if (!rankingContainer) return;
            
            if (!classement || classement.length === 0) {
                rankingContainer.innerHTML = '<div class="no-results">Aucun classement disponible</div>';
                return;
            }
            
            rankingContainer.innerHTML = classement.map((user, index) => `
                <div class="ranking-card">
                    <div class="rank-num">${index + 1}</div>
                    <div><strong>${escapeHtml(user.nom || 'Anonyme')}</strong><br><small>${user.points || 0} points</small></div>
                </div>
            `).join('');
        }

        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            const searchAttribute = document.getElementById('searchAttribute');
            
            if (searchInput) searchInput.value = '';
            if (searchAttribute) searchAttribute.value = 'titre';
            
            const resultsInfo = document.getElementById('searchResultsInfo');
            if (resultsInfo) resultsInfo.textContent = '';
            
            filteredDefis = [...allDefis];
            renderFilteredDefis();
        }

        // Participations filtering
        let allParticipations = [];
        let filteredParticipations = [];
        
        function loadParticipationsFromTable() {
            const rows = document.querySelectorAll('#participationsTableBody .participation-row');
            allParticipations = Array.from(rows).map(row => ({
                id: row.dataset.id,
                defi_titre: (row.dataset.defiTitre || '').toLowerCase(),
                progression: row.dataset.progression,
                element: row
            }));
            filteredParticipations = [...allParticipations];
        }

        function filterParticipations() {
            const searchInput = document.getElementById('searchInputParticipation');
            if (!searchInput) return;
            
            const searchTerm = searchInput.value.toLowerCase().trim();
            const searchAttribute = document.getElementById('searchAttributeParticipation');
            const attribute = searchAttribute ? searchAttribute.value : 'id';
            
            if (!searchTerm) {
                filteredParticipations = [...allParticipations];
                const resultsInfo = document.getElementById('searchResultsInfoParticipation');
                if (resultsInfo) resultsInfo.textContent = '';
            } else {
                filteredParticipations = allParticipations.filter(participation => {
                    let fieldValue = '';
                    
                    if (attribute === 'id') {
                        fieldValue = participation.id;
                    } else if (attribute === 'defi_titre') {
                        fieldValue = participation.defi_titre || '';
                    } else if (attribute === 'progression') {
                        fieldValue = participation.progression || '';
                    }
                    
                    return fieldValue && fieldValue.toString().toLowerCase().includes(searchTerm);
                });

                const resultCount = filteredParticipations.length;
                const resultsInfo = document.getElementById('searchResultsInfoParticipation');
                if (resultsInfo) {
                    resultsInfo.textContent = resultCount === 0 
                        ? 'Aucun résultat trouvé' 
                        : resultCount === 1 
                            ? '1 participation trouvée' 
                            : resultCount + ' participations trouvées';
                }
            }

            renderFilteredParticipations();
        }

        function renderFilteredParticipations() {
            const noResultsDiv = document.getElementById('noParticipationResults');
            
            allParticipations.forEach(p => {
                if (p.element) p.element.style.display = 'none';
            });
            filteredParticipations.forEach(p => {
                if (p.element) p.element.style.display = 'table-row';
            });

            if (filteredParticipations.length === 0 && noResultsDiv) {
                noResultsDiv.style.display = 'block';
            } else if (noResultsDiv) {
                noResultsDiv.style.display = 'none';
            }
        }

        function clearSearchParticipation() {
            const searchInput = document.getElementById('searchInputParticipation');
            const searchAttribute = document.getElementById('searchAttributeParticipation');
            
            if (searchInput) searchInput.value = '';
            if (searchAttribute) searchAttribute.value = 'id';
            
            const resultsInfo = document.getElementById('searchResultsInfoParticipation');
            if (resultsInfo) resultsInfo.textContent = '';
            
            filteredParticipations = [...allParticipations];
            renderFilteredParticipations();
        }
        
        let sortDirectionParticipations = {};
        function sortParticipations(attribute) {
            sortDirectionParticipations[attribute] = !sortDirectionParticipations[attribute];
            const direction = sortDirectionParticipations[attribute] ? 1 : -1;
            
            const tbody = document.getElementById('participationsTableBody');
            if (!tbody) return;
            
            const rows = Array.from(tbody.querySelectorAll('.participation-row'));
            
            rows.sort((a, b) => {
                let valA, valB;
                if (attribute === 'progression') {
                    valA = parseInt(a.dataset.progression, 10) || 0;
                    valB = parseInt(b.dataset.progression, 10) || 0;
                } else if (attribute === 'termine') {
                    valA = parseInt(a.dataset.termine, 10) || 0;
                    valB = parseInt(b.dataset.termine, 10) || 0;
                } else if (attribute === 'points_gagnes') {
                    valA = parseInt(a.dataset.points, 10) || 0;
                    valB = parseInt(b.dataset.points, 10) || 0;
                } else {
                    return 0;
                }
                
                if (valA < valB) return -1 * direction;
                if (valA > valB) return 1 * direction;
                return 0;
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }
        
        function updateUserUI() {
            const userPoints = document.getElementById('userPoints');
            const userDefisCompleted = document.getElementById('userDefisCompleted');
            const achievementsContainer = document.getElementById('userAchievementsList');
            
            if (userPoints) userPoints.innerText = currentUser.points;
            if (userDefisCompleted) userDefisCompleted.innerText = currentUser.defisCompletes;
            
            if (achievementsContainer) {
                achievementsContainer.innerHTML = currentUser.defisCompletes > 0 
                    ? `<div class="badge-item"><i class="fas fa-trophy"></i> ${currentUser.defisCompletes} défi(s) complété(s)</div>` 
                    : '<div class="badge-item" style="background:#E0E0E0;">Aucun succès pour l\'instant, relevez des défis !</div>';
            }
        }
        
        window.participateDefi = (id) => {
            const defi = allDefis.find(d => d.id === id);
            if (defi) {
                if ((defi.progression || 0) >= 100) {
                    alert(`🎉 Défi "${defi.titre}" déjà complété ! Continuez sur d'autres défis.`);
                } else {
                    let gain = Math.floor((defi.points || 0) * 0.3);
                    alert(`🎉 Vous avez rejoint le défi : "${defi.titre}" ! Réalisez les actions pour gagner ${defi.points || 0} pts. +${gain} pts de participation (bonus de motivation).`);
                    currentUser.points += gain;
                    updateUserUI();
                    renderUnifiedDefis();
                    renderUnifiedRanking();
                }
            } else {
                alert(`Défi en préparation !`);
            }
        };
        
        function renderUnifiedDefis() {
            renderFilteredDefis();
        }
        
        function initTabs() {
            const tabs = document.querySelectorAll('.tab-btn');
            const panes = document.querySelectorAll('.tab-pane');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    panes.forEach(pane => pane.classList.remove('active-pane'));
                    const activePane = document.getElementById(tabId);
                    if (activePane) activePane.classList.add('active-pane');
                    if (tabId === 'defisTab') renderUnifiedDefis();
                    if (tabId === 'communityTab') loadCommunityDefis();
                    if (tabId === 'classementTab') renderUnifiedRanking();
                });
            });
        }

        function showSection(section) {
            const accueilDiv = document.getElementById('accueilSection');
            const defisUnifiedDiv = document.getElementById('defisUnifiedSection');
            
            if (section === 'accueil') {
                if (accueilDiv) accueilDiv.style.display = 'block';
                if (defisUnifiedDiv) defisUnifiedDiv.style.display = 'none';
            } else if (section === 'defisUnified') {
                if (accueilDiv) accueilDiv.style.display = 'none';
                if (defisUnifiedDiv) defisUnifiedDiv.style.display = 'block';
                renderUnifiedDefis();
                renderUnifiedRanking();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation - RENDU FONCTIONNEL
            const navDefisUnified = document.getElementById('navDefisUnified');
            
            if (navDefisUnified) {
                navDefisUnified.addEventListener('click', function(e) {
                    e.preventDefault();
                    showSection('defisUnified');
                    // S'assurer que l'onglet Défis est actif
                    const defiTabBtn = document.querySelector('.tab-btn[data-tab="defisTab"]');
                    if (defiTabBtn && !defiTabBtn.classList.contains('active')) {
                        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                        defiTabBtn.classList.add('active');
                        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active-pane'));
                        const defisTab = document.getElementById('defisTab');
                        if (defisTab) defisTab.classList.add('active-pane');
                        renderUnifiedDefis();
                    }
                });
            }
            
            // Search listeners
            const searchInput = document.getElementById('searchInput');
            const searchAttribute = document.getElementById('searchAttribute');
            const clearSearchBtn = document.getElementById('clearSearchBtn');

            if (searchInput) searchInput.addEventListener('input', filterDefis);
            if (searchAttribute) searchAttribute.addEventListener('change', filterDefis);
            if (clearSearchBtn) clearSearchBtn.addEventListener('click', clearSearch);

            const searchInputParticipation = document.getElementById('searchInputParticipation');
            const searchAttributeParticipation = document.getElementById('searchAttributeParticipation');
            const clearSearchParticipationBtn = document.getElementById('clearSearchParticipationBtn');

            if (searchInputParticipation) searchInputParticipation.addEventListener('input', filterParticipations);
            if (searchAttributeParticipation) searchAttributeParticipation.addEventListener('change', filterParticipations);
            if (clearSearchParticipationBtn) clearSearchParticipationBtn.addEventListener('click', clearSearchParticipation);
            
            loadParticipationsFromTable();
            
            // Modales
            const loginModal = document.getElementById('loginModal');
            const signupModal = document.getElementById('signupModal');
            const openLoginBtn = document.getElementById('openLoginBtn');
            const openSignupBtn = document.getElementById('openSignupBtn');
            const closeLoginModal = document.getElementById('closeLoginModal');
            const closeSignupModal = document.getElementById('closeSignupModal');
            const doLoginBtn = document.getElementById('doLoginBtn');
            const doSignupBtn = document.getElementById('doSignupBtn');
            
            if (openLoginBtn) openLoginBtn.onclick = () => { if (loginModal) loginModal.style.display = 'flex'; };
            if (openSignupBtn) openSignupBtn.onclick = () => { if (signupModal) signupModal.style.display = 'flex'; };
            if (closeLoginModal) closeLoginModal.onclick = () => { if (loginModal) loginModal.style.display = 'none'; };
            if (closeSignupModal) closeSignupModal.onclick = () => { if (signupModal) signupModal.style.display = 'none'; };
            
            if (doLoginBtn) {
                doLoginBtn.onclick = () => {
                    alert('Connexion réussie ! Bienvenue Sophie.');
                    if (loginModal) loginModal.style.display = 'none';
                    currentUser = { name: "Sophie M.", points: 980, defisCompletes: 4 };
                    updateUserUI();
                    renderUnifiedRanking();
                };
            }
            
            if (doSignupBtn) {
                doSignupBtn.onclick = () => {
                    alert('Inscription réussie ! Vous pouvez maintenant vous connecter.');
                    if (signupModal) signupModal.style.display = 'none';
                };
            }
            
            if (loginModal) loginModal.addEventListener('click', (e) => { if (e.target === loginModal) loginModal.style.display = 'none'; });
            if (signupModal) signupModal.addEventListener('click', (e) => { if (e.target === signupModal) signupModal.style.display = 'none'; });

            updateUserUI();
            renderUnifiedDefis();
            renderUnifiedRanking();
            initTabs();

            const urlParams = new URLSearchParams(window.location.search);
            const successMsg = urlParams.get('success');
            const errorMsg = urlParams.get('error');

            if ((successMsg && successMsg.includes('participation')) || (errorMsg && errorMsg.includes('participation')) || errorMsg === 'db_error') {
                showSection('defisUnified');
                const partTabBtn = document.querySelector('.tab-btn[data-tab="participationsTab"]');
                if (partTabBtn) {
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    partTabBtn.classList.add('active');
                    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active-pane'));
                    const participationsTab = document.getElementById('participationsTab');
                    if (participationsTab) participationsTab.classList.add('active-pane');
                }
            } else {
                showSection('defisUnified');
            }

            // CRUD Participations
            const addParticipationModal = document.getElementById('addParticipationModal');
            const editParticipationModal = document.getElementById('editParticipationModal');
            const openAddParticipationBtn = document.getElementById('openAddParticipationBtn');
            const closeAddParticipationModal = document.getElementById('closeAddParticipationModal');
            const closeEditParticipationModal = document.getElementById('closeEditParticipationModal');

            if (openAddParticipationBtn) openAddParticipationBtn.onclick = () => { if (addParticipationModal) addParticipationModal.style.display = 'flex'; };
            if (closeAddParticipationModal) closeAddParticipationModal.onclick = () => { if (addParticipationModal) addParticipationModal.style.display = 'none'; };
            if (closeEditParticipationModal) closeEditParticipationModal.onclick = () => { if (editParticipationModal) editParticipationModal.style.display = 'none'; };

            document.querySelectorAll('.edit-participation-btn').forEach(btn => {
                btn.onclick = function() {
                    const form = document.getElementById('editParticipationForm');
                    if (form) {
                        form.querySelector('[name="id"]').value = this.dataset.id;
                        form.querySelector('[name="utilisateur_id"]').value = this.dataset.utilisateurId;
                        form.querySelector('[name="defi_id"]').value = this.dataset.defiId;
                        form.querySelector('[name="progression"]').value = this.dataset.progression;
                        form.querySelector('[name="points_gagnes"]').value = this.dataset.points;
                        const termineCheckbox = form.querySelector('[name="termine"]');
                        if (termineCheckbox) termineCheckbox.checked = this.dataset.termine === '1';
                        if (editParticipationModal) editParticipationModal.style.display = 'flex';
                    }
                };
            });

            function validateParticipationForm(form) {
                const utilisateur = form.querySelector('[name="utilisateur_id"]').value;
                const defi = form.querySelector('[name="defi_id"]').value;
                const progression = parseInt(form.querySelector('[name="progression"]').value, 10);
                const points = parseInt(form.querySelector('[name="points_gagnes"]').value, 10);
                
                if (!utilisateur) { alert('Veuillez sélectionner un utilisateur.'); return false; }
                if (!defi) { alert('Veuillez sélectionner un défi.'); return false; }
                if (isNaN(progression) || progression < 0 || progression > 100) { alert('La progression doit être entre 0 et 100.'); return false; }
                if (isNaN(points) || points < 0) { alert('Les points gagnés doivent être un nombre positif.'); return false; }
                return true;
            }

            const addForm = document.getElementById('addParticipationForm');
            const editForm = document.getElementById('editParticipationForm');
            
            if (addForm) addForm.onsubmit = function(e) { if (!validateParticipationForm(this)) e.preventDefault(); };
            if (editForm) editForm.onsubmit = function(e) { if (!validateParticipationForm(this)) e.preventDefault(); };

            document.querySelectorAll('.btn-delete-confirm').forEach(link => {
                link.onclick = function(event) {
                    if (!confirm('Voulez-vous vraiment supprimer cet enregistrement ?')) {
                        event.preventDefault();
                    }
                };
            });

            if (addParticipationModal) addParticipationModal.addEventListener('click', (e) => { if (e.target === addParticipationModal) addParticipationModal.style.display = 'none'; });
            if (editParticipationModal) editParticipationModal.addEventListener('click', (e) => { if (e.target === editParticipationModal) editParticipationModal.style.display = 'none'; });

            // Statistiques Chart.js
            const statsModal = document.getElementById('statsPointsModal');
            let barChartInstance = null;
            let doughnutChartInstance = null;

            const statsData = {
                total:   <?= $statsPoints['total_points'] ?? 0 ?>,
                moyenne: <?= $statsPoints['moyenne_points'] ?? 0 ?>,
                max:     <?= $statsPoints['max_points'] ?? 0 ?>,
                min:     <?= $statsPoints['min_points'] ?? 0 ?>,
                terminees: <?= $statsPoints['total_terminees'] ?? 0 ?>,
                enCours:   <?= ($statsPoints['total_participations'] ?? 0) - ($statsPoints['total_terminees'] ?? 0) ?>
            };

            function openStatsModal() {
                if (!statsModal) return;
                statsModal.style.display = 'flex';
                setTimeout(() => {
                    const ctxBar = document.getElementById('statsBarChart');
                    const ctxDoughnut = document.getElementById('statsDoughnutChart');
                    
                    if (ctxBar && ctxBar.getContext) {
                        if (barChartInstance) barChartInstance.destroy();
                        barChartInstance = new Chart(ctxBar.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: ['Total pts', 'Moyenne', 'Maximum', 'Minimum'],
                                datasets: [{
                                    label: 'Points gagnés',
                                    data: [statsData.total, statsData.moyenne, statsData.max, statsData.min],
                                    backgroundColor: ['#4CAF50CC','#29B6F6CC','#FFC107CC','#E91E63CC'],
                                    borderColor: ['#388E3C','#0288D1','#F57F17','#AD1457'],
                                    borderWidth: 2,
                                    borderRadius: 10,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { family: 'Inter' } } },
                                    x: { grid: { display: false }, ticks: { font: { family: 'Inter', weight: '600' } } }
                                }
                            }
                        });
                    }

                    if (ctxDoughnut && ctxDoughnut.getContext) {
                        if (doughnutChartInstance) doughnutChartInstance.destroy();
                        doughnutChartInstance = new Chart(ctxDoughnut.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: ['Terminées', 'En cours'],
                                datasets: [{
                                    data: [statsData.terminees, statsData.enCours],
                                    backgroundColor: ['#4CAF50CC','#29B6F6CC'],
                                    borderColor: ['#388E3C','#0288D1'],
                                    borderWidth: 2,
                                    hoverOffset: 8
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                cutout: '65%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { font: { family: 'Inter', weight: '600' }, padding: 16, usePointStyle: true }
                                    }
                                }
                            }
                        });
                    }
                }, 80);
            }

            function closeStatsModal() {
                if (statsModal) statsModal.style.display = 'none';
            }

            const openStatsModalBtn = document.getElementById('openStatsModalBtn');
            const closeStatsModalBtn = document.getElementById('closeStatsModalBtn');
            
            if (openStatsModalBtn) openStatsModalBtn.addEventListener('click', openStatsModal);
            if (closeStatsModalBtn) closeStatsModalBtn.addEventListener('click', closeStatsModal);
            if (statsModal) statsModal.addEventListener('click', (e) => { if (e.target === statsModal) closeStatsModal(); });

            // --- Notifications logic ---
            const notifWrapper = document.getElementById('notifWrapper');
            const notifDropdown = document.getElementById('notifDropdown');
            const notifList = document.getElementById('notifList');
            const notifCount = document.getElementById('notifCount');

            if(notifWrapper) {
                notifWrapper.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
                });
            }
            document.addEventListener('click', () => { if(notifDropdown) notifDropdown.style.display = 'none'; });

            async function loadNotifications() {
                try {
                    const res = await fetch('../CONTROLLER/NotificationController.php?action=liste');
                    const data = await res.json();
                    if(data.length > 0) {
                        notifList.innerHTML = data.map(n => {
                            const match = n.message.match(/\[ID:(\d+)\]/);
                            const targetId = match ? `defi-${match[1]}` : null;
                            
                            return `
                                <div class="notif-item ${n.lu == 0 ? 'unread' : ''}" onclick="markNotifRead(${n.id}, '${targetId}')">
                                    <a href="${targetId ? '#' + targetId : 'javascript:void(0)'}" style="text-decoration:none; color:inherit;">
                                        ${n.message}
                                        <div style="font-size:0.7rem; color:#999; margin-top:5px;">${n.created_at}</div>
                                    </a>
                                </div>
                            `;
                        }).join('');
                        const unreadCount = data.filter(n => n.lu == 0).length;
                        if(unreadCount > 0) {
                            notifCount.textContent = unreadCount;
                            notifCount.style.display = 'block';
                        } else {
                            notifCount.style.display = 'none';
                        }
                    } else {
                        notifList.innerHTML = '<div class="notif-empty">Aucune notification</div>';
                        notifCount.style.display = 'none';
                    }
                } catch(e) { console.error("Error loading notifications", e); }
            }

            window.markNotifRead = async function(id, targetId) {
                await fetch(`../CONTROLLER/NotificationController.php?action=marquer_lue&id=${id}`);
                loadNotifications();
                
                if (targetId && targetId !== 'null') {
                    // Switch to community tab first if it's a community defi
                    const communityTabBtn = document.querySelector('.tab-btn[data-tab="communityTab"]');
                    if (communityTabBtn) communityTabBtn.click();
                    
                    // Small delay to allow tab content to render
                    setTimeout(() => {
                        const target = document.getElementById(targetId);
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            target.style.transition = '0.5s';
                            target.style.boxShadow = '0 0 20px var(--vert-kool)';
                            setTimeout(() => { target.style.boxShadow = ''; }, 3000);
                        }
                    }, 300);
                }
            }

            loadNotifications();
            setInterval(loadNotifications, 10000); 

            // --- Community Challenges logic ---
            window.loadCommunityDefis = function() {
                const communityGrid = document.getElementById('communityDefisGrid');
                const aiGrid = document.getElementById('aiDefisGrid');
                const titleAI = document.getElementById('aiDefisTitle');
                const noCommunityDefis = document.getElementById('noCommunityDefis');
                
                if(!communityGrid) return;
                
                // Défis proposés par les utilisateurs (proposant_id non nul, inclut les défis IA acceptés par les utilisateurs)
                const communityDefis = allDefis.filter(d => d.status === 'approuve' && d.proposant_id !== null);
                
                // Défis proposés par l'IA
                const aiDefis = allDefis.filter(d => d.status === 'approuve' && (d.type || '').toUpperCase().startsWith('IA -'));
                
                // Rendu Communauté
                if(communityDefis.length > 0) {
                    communityGrid.innerHTML = communityDefis.map(d => generateDefiCardHTML(d, false)).join('');
                } else {
                    communityGrid.innerHTML = '';
                }

                // Rendu IA
                if(aiGrid) {
                    if(aiDefis.length > 0) {
                        aiGrid.innerHTML = aiDefis.map(d => generateDefiCardHTML(d, true)).join('');
                        if(titleAI) titleAI.style.display = 'block';
                    } else {
                        aiGrid.innerHTML = '';
                        if(titleAI) titleAI.style.display = 'none';
                    }
                }

                // Message vide global si rien dans les deux
                if(noCommunityDefis) {
                    noCommunityDefis.style.display = (communityDefis.length === 0 && aiDefis.length === 0) ? 'block' : 'none';
                }
            }

            loadCommunityDefis();

            // --- Enhanced Chatbot Logic ---
            let chatHistory = [];
            let chatbotState = 'default'; 
            let newChallenge = {};

            const chatbotMessages = document.getElementById('chatbotMessages');
            
            function appendMessage(text, isUser) {
                if(!chatbotMessages) return;
                const msg = document.createElement('div');
                msg.style.background = isUser ? '#e3f2fd' : '#e8f5e9';
                msg.style.padding = '10px 15px';
                msg.style.borderRadius = isUser ? '15px 15px 0 15px' : '15px 15px 15px 0';
                msg.style.maxWidth = '85%';
                msg.style.alignSelf = isUser ? 'flex-end' : 'flex-start';
                msg.style.fontSize = '0.9rem';
                msg.style.color = '#2c3e2f';
                msg.innerHTML = text;
                chatbotMessages.appendChild(msg);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
            window.appendMessage = appendMessage;

            const toggleChatbotBtn = document.getElementById('toggleChatbotBtn');
            const aiChatbotWindow = document.getElementById('aiChatbotWindow');
            const closeChatbotBtn = document.getElementById('closeChatbotBtn');
            const chatbotSendBtn = document.getElementById('chatbotSendBtn');
            const chatbotInput = document.getElementById('chatbotInput');

            if(toggleChatbotBtn) {
                toggleChatbotBtn.addEventListener('click', () => {
                    aiChatbotWindow.style.display = aiChatbotWindow.style.display === 'none' ? 'block' : 'none';
                });
            }
            if(closeChatbotBtn) {
                closeChatbotBtn.addEventListener('click', () => {
                    aiChatbotWindow.style.display = 'none';
                });
            }

            window.startChallengeProposal = function() {
                chatbotState = 'proposing_title';
                appendMessage("Super ! Quel est le titre de votre défi ?", false);
                aiChatbotWindow.style.display = 'block';
            };

            window.sendChatbotMessage = async function() {
                const text = chatbotInput.value.trim();
                if(!text && chatbotState === 'default') return;
                
                if (chatbotState === 'proposing_title') {
                    appendMessage(text, true);
                    newChallenge.titre = text;
                    chatbotState = 'proposing_points';
                    appendMessage("Combien de points vaut ce défi (ex: 50) ?", false);
                    chatbotInput.value = '';
                    return;
                }

                if (chatbotState === 'proposing_points') {
                    appendMessage(text, true);
                    newChallenge.points = parseInt(text) || 50;
                    chatbotState = 'proposing_type';
                    appendMessage("Quel est le type (Nutrition, Sport, Écologie...) ?", false);
                    chatbotInput.value = '';
                    return;
                }

                if (chatbotState === 'proposing_type') {
                    appendMessage(text, true);
                    newChallenge.type = text;
                    chatbotState = 'default';
                    appendMessage("Merci ! Votre défi est envoyé pour validation. Vous recevrez une notification quand il sera approuvé.", false);
                    
                    const formData = new FormData();
                    formData.append('titre', newChallenge.titre);
                    formData.append('points', newChallenge.points);
                    formData.append('type', newChallenge.type);
                    formData.append('status', 'en_attente');
                    formData.append('proposant_id', '1'); 
                    formData.append('ajax', '1');
                    
                    fetch('../CONTROLLER/DefiController.php?action=add', { method: 'POST', body: formData });
                    
                    chatbotInput.value = '';
                    return;
                }

                if(!text) return;
                appendMessage(text, true);
                chatHistory.push({ role: "user", content: text });
                
                chatbotInput.value = '';
                chatbotSendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                chatbotSendBtn.disabled = true;

                try {
                    const response = await fetch('../CONTROLLER/ChatbotAIController.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ messages: chatHistory })
                    });
                    const data = await response.json();
                    if (data && data.message) {
                        chatHistory.push({ role: "assistant", content: data.message });
                        let htmlResponse = data.message;
                        if (data.challenge_proposed && data.titre) {
                            htmlResponse += `<div style='margin-top:10px; padding:10px; background:white; border-radius:12px; border:1px solid var(--bleu-tech);'>
                                <strong>Proposition :</strong> ${escapeHtml(data.titre)}<br>
                                <button onclick='addChallengeFromChatbot(${JSON.stringify(data).replace(/'/g, "&#39;")})' style='margin-top:10px; background:var(--bleu-tech); color:white; border:none; padding:6px 12px; border-radius:20px; cursor:pointer; font-size:0.8rem;'>Ajouter</button>
                            </div>`;
                        }
                        appendMessage(htmlResponse, false);
                    }
                } catch (err) {
                    appendMessage("Erreur de connexion IA.", false);
                } finally {
                    chatbotSendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    chatbotSendBtn.disabled = false;
                }
            };

            if(chatbotSendBtn && chatbotInput) {
                chatbotSendBtn.addEventListener('click', sendChatbotMessage);
                chatbotInput.addEventListener('keypress', (e) => {
                    if(e.key === 'Enter') sendChatbotMessage();
                });
            }

            appendMessage(`Bonjour ! Je suis votre assistant de santé IA. Pour vous proposer des défis adaptés, j'ai besoin de mieux vous connaître.<br><br>
                <strong>Avez-vous des maladies chroniques, des allergies ou des contre-indications médicales ?</strong>`, false);


            window.addChallengeFromChatbot = async function(challengeData) {
                const formData = new FormData();
                formData.append('titre', challengeData.titre);
                formData.append('type', 'IA - ' + (challengeData.type || 'Santé'));
                formData.append('points', challengeData.points || 50);
                formData.append('ajax', '1');
                const today = new Date().toISOString().split('T')[0];
                formData.append('date_debut', today);
                formData.append('date_fin', today); 
                formData.append('status', 'en_attente');
                formData.append('proposant_id', '1'); // ID utilisateur statique pour la démo

                try {
                    const res = await fetch('../CONTROLLER/DefiController.php?action=add', { method: 'POST', body: formData });
                    const result = await res.json();
                    if(result.success) { 
                        alert('✅ Défi envoyé à l\'administration pour validation !'); 
                        window.location.reload(); 
                    }
                    else { alert("❌ Erreur : " + result.message); }
                } catch(e) { alert("❌ Erreur réseau."); }
            }
        });
    </script>
</body>
</html>