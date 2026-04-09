<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/BackofficeController.php';

$backofficeController = new BackofficeController();
$stats = $backofficeController->statsDashboard();
$seriesUsers = $backofficeController->utilisateursParMois(6);
$roles = $backofficeController->repartitionRoles();
$utilisateursRecents = $backofficeController->utilisateursRecents(6);
$utilisateurs = $backofficeController->listeUtilisateurs();
$msg = $_GET['msg'] ?? '';
$tabFromQuery = $_GET['tab'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kool Healthy - Backoffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../CSS/backoffice.css">
</head>
<body>
    <div class="app-wrapper">
        <aside class="sidebar">
            <div class="logo-area">
                <a class="logo-link" href="index.php?page=fil" aria-label="Kool Healthy">
                    <img src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
                </a>
                <p>administration · nutrition IA</p>
            </div>

            <div class="nav-menu">
                <div class="nav-item active" data-tab="dashboard"><i class="fas fa-chart-pie"></i><span>Dashboard</span></div>
                <div class="nav-item" data-tab="users"><i class="fas fa-users"></i><span>Utilisateurs</span></div>
                <div class="nav-item" data-tab="food"><i class="fas fa-apple-alt"></i><span>Aliments</span></div>
                <div class="nav-item" data-tab="analytics"><i class="fas fa-chart-line"></i><span>Analytics IA</span></div>
            </div>

            <div class="sidebar-footer">
                <div class="user-badge">
                    <div class="user-avatar"><i class="fas fa-user-md"></i></div>
                    <div class="user-info">
                        <p>Admin Kool Healthy</p>
                        <small>backoffice global</small>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Tableau de bord</h1>
                    <p>Vue d'ensemble de la plateforme · IA & nutrition durable</p>
                </div>
                <div class="header-actions">
                    <a class="btn-outline" href="index.php?page=fil">Retour au module RS</a>
                    <a class="btn-primary" href="auth.php"><i class="fas fa-user-circle"></i> Connexion</a>
                </div>
            </div>

            <section id="dashboardContent" class="dashboard-container tab-content active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Utilisateurs actifs</div>
                        <div class="stat-value"><?php echo (int) $stats['total_utilisateurs']; ?></div>
                        <div class="stat-trend">Comptes enregistrés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Nouveaux utilisateurs</div>
                        <div class="stat-value"><?php echo (int) $stats['nouveaux_30j']; ?></div>
                        <div class="stat-trend">Sur les 30 derniers jours</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Recettes publiées</div>
                        <div class="stat-value"><?php echo (int) $stats['total_recettes']; ?></div>
                        <div class="stat-trend">Contenu total de la communauté</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Interactions favoris</div>
                        <div class="stat-value"><?php echo (int) $stats['total_favoris']; ?></div>
                        <div class="stat-trend">Recettes sauvegardées</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Comptes admin</div>
                        <div class="stat-value"><?php echo (int) $stats['total_admins']; ?></div>
                        <div class="stat-trend">Accès administrateur</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Comptes normaux</div>
                        <div class="stat-value"><?php echo (int) $stats['total_normaux']; ?></div>
                        <div class="stat-trend">Utilisateurs standards</div>
                    </div>
                </div>

                <div class="two-columns">
                    <div class="card-panel">
                        <div class="panel-header">
                            <h3><i class="fas fa-chart-line" style="color:var(--bleu-tech);"></i> Nouveaux utilisateurs (6 mois)</h3>
                            <div class="badge-tech">données réelles</div>
                        </div>
                        <canvas id="usersChart" height="180"></canvas>
                    </div>

                    <div class="card-panel">
                        <div class="panel-header">
                            <h3><i class="fas fa-user-tag"></i> Répartition des rôles</h3>
                            <div class="badge-eco">gestion comptes</div>
                        </div>
                        <canvas id="rolesChart" height="180"></canvas>
                    </div>
                </div>

                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-clock"></i> Derniers inscrits</h3>
                        <div class="badge-tech">temps réel</div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Age</th>
                                <th>Besoins caloriques</th>
                                <th>Date création</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($utilisateursRecents)): ?>
                                <tr>
                                    <td colspan="6">Aucun utilisateur récent.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($utilisateursRecents as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['nom'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                        <td><span class="status-active"><?php echo htmlspecialchars($u['role'] ?? 'utilisateur'); ?></span></td>
                                        <td><?php echo htmlspecialchars($u['age'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($u['besoins_caloriques'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($u['created_at'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="usersContent" class="dashboard-container tab-content">
                <div class="users-stats-grid">
                    <div class="users-stat-card">
                        <div class="users-stat-label">Comptes total</div>
                        <div class="users-stat-value"><?php echo (int) $stats['total_utilisateurs']; ?></div>
                    </div>
                    <div class="users-stat-card">
                        <div class="users-stat-label">Nouveaux (30j)</div>
                        <div class="users-stat-value"><?php echo (int) $stats['nouveaux_30j']; ?></div>
                    </div>
                    <div class="users-stat-card">
                        <div class="users-stat-label">Admins</div>
                        <div class="users-stat-value"><?php echo (int) $stats['total_admins']; ?></div>
                    </div>
                    <div class="users-stat-card">
                        <div class="users-stat-label">Normaux</div>
                        <div class="users-stat-value"><?php echo (int) $stats['total_normaux']; ?></div>
                    </div>
                </div>

                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-users"></i> Tableau utilisateurs complet</h3>
                        <div class="badge-eco"><?php echo count($utilisateurs); ?> lignes</div>
                    </div>

                    <?php if ($msg === 'role-updated'): ?>
                        <div class="feedback success">Rôle mis à jour avec succès.</div>
                    <?php elseif ($msg === 'user-deleted'): ?>
                        <div class="feedback success">Compte supprimé avec succès.</div>
                    <?php elseif ($msg === 'user-added'): ?>
                        <div class="feedback success">Utilisateur ajouté avec succès.</div>
                    <?php elseif ($msg === 'role-failed' || $msg === 'delete-failed' || $msg === 'invalid-action'): ?>
                        <div class="feedback error">Action impossible. Vérifiez les données.</div>
                    <?php elseif ($msg === 'add-failed'): ?>
                        <div class="feedback error">Ajout impossible. Vérifiez le formulaire (nom/email uniques).</div>
                    <?php endif; ?>

                    <div class="management-grid">
                        <div class="management-card">
                            <h4>Ajouter un utilisateur</h4>
                            <form method="POST" action="../CONTROLLER/BackofficeController.php" class="management-form">
                                <input type="hidden" name="action" value="add-user">
                                <input type="text" name="nom" placeholder="Nom" required>
                                <input type="email" name="email" placeholder="Email" required>
                                <input type="password" name="mot_de_passe" placeholder="Mot de passe" required>
                                <select name="role">
                                    <option value="normal">normal</option>
                                    <option value="admin">admin</option>
                                </select>
                                <input type="number" step="0.1" name="poids" placeholder="Poids (kg)" value="70">
                                <input type="number" step="0.01" name="taille" placeholder="Taille (m)" value="1.75">
                                <input type="text" name="objectif" placeholder="Objectif" value="equilibre">
                                <input type="number" name="age" placeholder="Age" value="25">
                                <input type="text" name="allergies" placeholder="Allergies">
                                <input type="number" name="besoins_caloriques" placeholder="Besoins caloriques" value="2000">
                                <button type="submit" class="mini-btn">Ajouter</button>
                            </form>
                        </div>

                        <div class="management-card">
                            <h4>Changer un droit d'accès</h4>
                            <form method="POST" action="../CONTROLLER/BackofficeController.php" class="management-form inline-management">
                                <input type="hidden" name="action" value="update-role">
                                <input type="number" name="id" placeholder="ID utilisateur" required>
                                <select name="role">
                                    <option value="normal">normal</option>
                                    <option value="admin">admin</option>
                                </select>
                                <button type="submit" class="mini-btn">Valider</button>
                            </form>

                            <h4>Supprimer un compte</h4>
                            <form method="POST" action="../CONTROLLER/BackofficeController.php" class="management-form inline-management" onsubmit="return confirm('Supprimer ce compte ?');">
                                <input type="hidden" name="action" value="delete-user">
                                <input type="number" name="id" placeholder="ID utilisateur" required>
                                <button type="submit" class="mini-btn danger">Supprimer</button>
                            </form>
                        </div>
                    </div>

                    <div class="users-table-wrap">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Age</th>
                                    <th>Poids</th>
                                    <th>Taille</th>
                                    <th>IMC</th>
                                    <th>Objectif</th>
                                    <th>Allergies</th>
                                    <th>Besoins caloriques</th>
                                    <th>Date création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($utilisateurs)): ?>
                                    <tr>
                                        <td colspan="12">Aucun utilisateur trouvé.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($utilisateurs as $u): ?>
                                        <tr>
                                            <td><?php echo (int) $u['id']; ?></td>
                                            <td><?php echo htmlspecialchars($u['nom'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($u['role'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($u['age'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['poids'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['taille'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['imc'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['objectif'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['allergies'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['besoins_caloriques'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($u['created_at'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="foodContent" class="dashboard-container tab-content">
                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-leaf"></i> Suivi nutritionnel</h3>
                        <div class="badge-eco">indicateurs</div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-label">Qualité moyenne des profils IA</div>
                        <div class="progress-bar-bg"><div class="progress-fill" style="width: <?php echo (int) $stats['score_ia']; ?>%"></div></div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-label">Activation communauté (favoris/utilisateur)</div>
                        <?php $activation = $stats['total_utilisateurs'] > 0 ? min(100, (int) round(($stats['total_favoris'] / $stats['total_utilisateurs']) * 25)) : 0; ?>
                        <div class="progress-bar-bg"><div class="progress-fill progress-fill-blue" style="width: <?php echo $activation; ?>%"></div></div>
                    </div>
                </div>
            </section>

            <section id="analyticsContent" class="dashboard-container tab-content">
                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-brain"></i> Analytics IA</h3>
                        <div class="badge-tech">prédictions</div>
                    </div>
                    <canvas id="iaChart" height="180"></canvas>
                    <p style="margin-top:14px; color: #5b6f5f;">
                        Recommandation automatique: pousser les recettes équilibrées pour soutenir la croissance des nouveaux utilisateurs.
                    </p>
                </div>
            </section>
        </main>
    </div>

    <script>
        const navItems = document.querySelectorAll('.nav-item');
        const tabs = {
            dashboard: document.getElementById('dashboardContent'),
            users: document.getElementById('usersContent'),
            food: document.getElementById('foodContent'),
            analytics: document.getElementById('analyticsContent')
        };

        function showTab(tab) {
            Object.keys(tabs).forEach((key) => {
                tabs[key].classList.toggle('active', key === tab);
            });
            navItems.forEach((item) => {
                item.classList.toggle('active', item.getAttribute('data-tab') === tab);
            });
        }

        navItems.forEach((item) => {
            item.addEventListener('click', () => showTab(item.getAttribute('data-tab')));
        });

        showTab('<?php echo in_array($tabFromQuery, ['dashboard', 'users', 'food', 'analytics'], true) ? $tabFromQuery : 'dashboard'; ?>');

        const usersLabels = <?php echo json_encode($seriesUsers['labels']); ?>;
        const usersValues = <?php echo json_encode($seriesUsers['values']); ?>;
        const roleLabels = <?php echo json_encode($roles['labels']); ?>;
        const roleValues = <?php echo json_encode($roles['values']); ?>;

        const usersCtx = document.getElementById('usersChart').getContext('2d');
        new Chart(usersCtx, {
            type: 'bar',
            data: {
                labels: usersLabels,
                datasets: [{
                    label: 'Nouveaux utilisateurs',
                    data: usersValues,
                    backgroundColor: '#4CAF50'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });

        const rolesCtx = document.getElementById('rolesChart').getContext('2d');
        new Chart(rolesCtx, {
            type: 'doughnut',
            data: {
                labels: roleLabels,
                datasets: [{
                    data: roleValues,
                    backgroundColor: ['#4CAF50', '#29B6F6', '#8BC34A', '#0288D1', '#AED581']
                }]
            },
            options: {
                responsive: true
            }
        });

        const iaCtx = document.getElementById('iaChart').getContext('2d');
        new Chart(iaCtx, {
            type: 'line',
            data: {
                labels: usersLabels,
                datasets: [{
                    label: 'Dynamique utilisateurs',
                    data: usersValues,
                    borderColor: '#29B6F6',
                    backgroundColor: 'rgba(41, 182, 246, 0.2)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html>
