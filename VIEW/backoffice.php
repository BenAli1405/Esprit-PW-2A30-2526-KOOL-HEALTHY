<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/AuthController.php';
require_once __DIR__ . '/../CONTROLLER/BackofficeController.php';

$authController = new AuthController();
$utilisateurConnecte = $authController->exigerAdmin('auth.php', 'home.php');
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
                <div class="nav-item active" data-tab="dashboard"><i class="fas fa-chart-pie"></i><span>Dashboard</span></div>
                <div class="nav-item" data-tab="users"><i class="fas fa-users"></i><span>Utilisateurs</span></div>
                <div class="nav-item" data-tab="recipes"><i class="fas fa-utensils"></i><span>Recettes</span></div>
                <div class="nav-item" data-tab="ingredients"><i class="fas fa-apple-alt"></i><span>Ingrédients</span></div>
                <div class="nav-item" data-tab="reviews"><i class="fas fa-star"></i><span>Avis</span></div>
                <a class="nav-item" href="../plan.php?page=plan-backoffice" style="text-decoration:none;color:inherit;"><i class="fas fa-bowl-food"></i><span>Repas</span></a>
                <a class="nav-item" href="../plan.php?page=plan-nutritionnel" style="text-decoration:none;color:inherit;"><i class="fas fa-clipboard-list"></i><span>Plans</span></a>
                <div class="nav-item" data-tab="analytics"><i class="fas fa-chart-line"></i><span>Analytics IA</span></div>
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
                    <h1>Tableau de bord</h1>
                    <p>Vue d'ensemble de la plateforme · IA & nutrition durable</p>
                </div>
                <div class="header-actions">
                    <a class="btn-outline" href="../CONTROLLER/AuthController.php?action=logout">Se déconnecter</a>
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
                    <div class="stat-card">
                        <div class="stat-title">Total Recettes</div>
                        <div class="stat-value" id="statRecettes">—</div>
                        <div class="stat-trend"><i class="fas fa-utensils"></i> créées</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Total Ingrédients</div>
                        <div class="stat-value" id="statIngredients">—</div>
                        <div class="stat-trend"><i class="fas fa-apple-alt"></i> référencés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Avis totaux</div>
                        <div class="stat-value" id="statReviews">—</div>
                        <div class="stat-trend"><i class="fas fa-star"></i> commentaires</div>
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

                <div class="two-columns">
                    <div class="card-panel">
                        <div class="panel-header">
                            <h3><i class="fas fa-ranking-star"></i> Top Recettes</h3>
                            <div class="badge-eco">par note</div>
                        </div>
                        <div id="topRecipesList" class="top-list"></div>
                    </div>
                    <div class="card-panel">
                        <div class="panel-header">
                            <h3><i class="fas fa-chart-bar"></i> Top Ingrédients</h3>
                            <div class="badge-tech">les plus utilisés</div>
                        </div>
                        <div id="topIngredientsList" class="top-list"></div>
                    </div>
                </div>

                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-chart-bar"></i> Activité récente</h3>
                        <div class="badge-tech">derniers avis</div>
                    </div>
                    <div id="recentActivityList" class="activity-list"></div>
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
                            <form method="POST" action="../CONTROLLER/BackofficeController.php" class="management-form" id="addUserForm" novalidate>
                                <input type="hidden" name="action" value="add-user">
                                <input type="text" name="nom" placeholder="Nom">
                                <input type="email" name="email" placeholder="Email">
                                <input type="password" name="mot_de_passe" placeholder="Mot de passe">
                                <select name="role">
                                    <option value="normal">normal</option>
                                    <option value="admin">admin</option>
                                </select>
                                <input type="number" step="0.1" name="poids" placeholder="Poids (kg)" value="70">
                                <input type="number" step="0.01" name="taille" placeholder="Taille (m)" value="1.75">
                                <input type="number" name="age" placeholder="Age" value="25">
                                <input type="text" name="allergies" placeholder="Allergies">
                                <input type="number" name="besoins_caloriques" placeholder="Besoins caloriques" value="2000">
                                <span id="addUserError" class="password-error" style="display:none;color:red;font-size:0.9rem;"></span>
                                <button type="submit" class="mini-btn">Ajouter</button>
                            </form>
                        </div>

                        <div class="management-card">
                            <h4>Changer un droit d'accès</h4>
                            <form method="POST" action="../CONTROLLER/BackofficeController.php" class="management-form inline-management" id="updateRoleForm" novalidate>
                                <input type="hidden" name="action" value="update-role">
                                <input type="number" name="id" placeholder="ID utilisateur">
                                <select name="role">
                                    <option value="normal">normal</option>
                                    <option value="admin">admin</option>
                                </select>
                                <span id="updateRoleError" class="password-error" style="display:none;color:red;font-size:0.9rem;"></span>
                                <button type="submit" class="mini-btn">Valider</button>
                            </form>

                            <h4>Supprimer un compte</h4>
                            <form method="POST" action="../CONTROLLER/BackofficeController.php" class="management-form inline-management" id="deleteUserForm" novalidate onsubmit="return confirm('Supprimer ce compte ?');">
                                <input type="hidden" name="action" value="delete-user">
                                <input type="number" name="id" placeholder="ID utilisateur">
                                <span id="deleteUserError" class="password-error" style="display:none;color:red;font-size:0.9rem;"></span>
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
                                    <th>Allergies</th>
                                    <th>Besoins caloriques</th>
                                    <th>Date création</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($utilisateurs)): ?>
                                    <tr>
                                        <td colspan="11">Aucun utilisateur trouvé.</td>
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

            <!-- RECETTES TAB -->
            <section id="recipesContent" class="dashboard-container tab-content">

                <!-- Calendrier des publications -->
                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-calendar-alt"></i> Calendrier des publications</h3>
                        <div class="calendar-nav">
                            <button id="recCalPrevBtn" class="btn-outline"><i class="fas fa-chevron-left"></i></button>
                            <span id="recCalMonthLabel"></span>
                            <button id="recCalNextBtn" class="btn-outline"><i class="fas fa-chevron-right"></i></button>
                            <button id="recCalViewToggle" class="btn-outline cal-toggle-btn">Vue mois</button>
                        </div>
                    </div>
                    <div id="recipeCalendar" class="review-calendar"></div>
                </div>

                <!-- CRUD Table -->
                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-database"></i> Gestion des recettes</h3>
                        <button class="btn-primary" id="addRecipeBtn"><i class="fas fa-plus"></i> Ajouter une recette</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table" id="recipesTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Titre</th>
                                    <th>Difficulté</th>
                                    <th>Temps</th>
                                    <th>Eco</th>
                                    <th>Calories</th>
                                    <th>Avis</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recipesTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- INGREDIENTS TAB -->
            <section id="ingredientsContent" class="dashboard-container tab-content">
                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-apple-alt"></i> Gestion des ingrédients</h3>
                        <button class="btn-primary" id="addIngredientBtn"><i class="fas fa-plus"></i> Ajouter un ingrédient</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table" id="ingredientsTable">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Calories</th>
                                    <th>Eco</th>
                                    <th>Protéines</th>
                                    <th>Glucides</th>
                                    <th>Lipides</th>
                                    <th>Utilisations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ingredientsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- AVIS TAB -->
            <section id="reviewsContent" class="dashboard-container tab-content">

                <!-- CRUD Table -->
                <div class="card-panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-star"></i> Gestion des avis</h3>
                        <button class="btn-primary" id="addReviewBtn"><i class="fas fa-plus"></i> Ajouter un avis</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="data-table" id="reviewsTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Recette</th>
                                    <th>Note</th>
                                    <th>Commentaire</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reviewsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL RECETTE -->
    <div id="recipeModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="recipeModalTitle"><i class="fas fa-carrot"></i> Ajouter une recette</h3>
                <span class="close-modal" id="closeRecipeModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="recipeForm">
                    <input type="hidden" id="recipeId">
                    <div class="form-group">
                        <label>Titre *</label>
                        <input type="text" id="recipeTitle" required placeholder="Ex: Buddha Bowl protéiné">
                    </div>
                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea id="recipeInstructions" rows="3" placeholder="Détails de la préparation..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Temps (min)</label>
                            <input type="number" id="recipeTime" value="30">
                        </div>
                        <div class="form-group">
                            <label>Difficulté</label>
                            <select id="recipeDifficulty">
                                <option>Facile</option><option>Moyen</option><option>Difficile</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Eco-Score</label>
                            <select id="recipeEcoScore">
                                <option>A+</option><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ingrédients</label>
                        <div id="ingredientsListContainer"></div>
                        <button type="button" id="addIngredientRowBtn" class="btn-small"><i class="fas fa-plus"></i> Ajouter un ingrédient</button>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Sauvegarder</button>
                        <button type="button" class="btn-outline" id="cancelRecipeBtn">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL INGREDIENT -->
    <div id="ingredientModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="ingredientModalTitle"><i class="fas fa-apple-alt"></i> Ajouter un ingrédient</h3>
                <span class="close-modal" id="closeIngredientModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="ingredientForm">
                    <input type="hidden" id="ingredientId">
                    <div class="form-row">
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Nom *</label>
                            <input type="text" id="ingredientName" required placeholder="Ex: Quinoa">
                        </div>
                        <div class="form-group">
                            <label>Eco-Score</label>
                            <select id="ingredientEcoScore">
                                <option>A+</option><option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Calories <small style="color:#888;">(ex: 120kcal/100g)</small></label>
                        <input type="text" id="ingredientCalories" placeholder="Ex: 120kcal/100g">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Protéines (g/100g)</label>
                            <input type="number" id="ingredientProteines" value="0" min="0" step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Glucides (g/100g)</label>
                            <input type="number" id="ingredientGlucides" value="0" min="0" step="0.1">
                        </div>
                        <div class="form-group">
                            <label>Lipides (g/100g)</label>
                            <input type="number" id="ingredientLipides" value="0" min="0" step="0.1">
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Sauvegarder</button>
                        <button type="button" class="btn-outline" id="cancelIngredientBtn">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL AVIS -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="reviewModalTitle"><i class="fas fa-star"></i> Ajouter un avis</h3>
                <span class="close-modal" id="closeReviewModal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" id="reviewId">
                    <input type="hidden" id="reviewRecipeId">
                    <div class="form-group" id="reviewRecipeGroup">
                        <label>Recette *</label>
                        <select id="reviewRecipeSelect" required></select>
                    </div>
                    <div class="form-group" id="reviewUtilisateurGroup">
                        <label>Utilisateur *</label>
                        <input type="text" id="reviewUtilisateur" required placeholder="Nom de l'utilisateur">
                    </div>
                    <div class="form-group">
                        <label>Note *</label>
                        <div class="star-rating-input" id="starRatingInput">
                            <i class="fas fa-star star-input" data-value="1"></i>
                            <i class="fas fa-star star-input" data-value="2"></i>
                            <i class="fas fa-star star-input" data-value="3"></i>
                            <i class="fas fa-star star-input" data-value="4"></i>
                            <i class="fas fa-star star-input" data-value="5"></i>
                        </div>
                        <input type="hidden" id="reviewNote" value="0">
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <textarea id="reviewCommentaire" rows="3" placeholder="Votre avis..."></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Sauvegarder</button>
                        <button type="button" class="btn-outline" id="cancelReviewBtn">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="toast" class="toast"><i class="fas fa-check-circle"></i><span id="toastMessage">Action réussie</span></div>

    <script>
        const navItems = document.querySelectorAll('.nav-item[data-tab]');
        const tabs = {
            dashboard: document.getElementById('dashboardContent'),
            users: document.getElementById('usersContent'),
            food: document.getElementById('foodContent'),
            recipes: document.getElementById('recipesContent'),
            ingredients: document.getElementById('ingredientsContent'),
            reviews: document.getElementById('reviewsContent'),
            analytics: document.getElementById('analyticsContent')
        };

        function showTab(tab) {
            Object.keys(tabs).forEach((key) => {
                if (tabs[key]) tabs[key].classList.toggle('active', key === tab);
            });
            document.querySelectorAll('.nav-item').forEach((item) => {
                item.classList.toggle('active', item.getAttribute('data-tab') === tab);
            });
        }

        navItems.forEach((item) => {
            item.addEventListener('click', () => showTab(item.getAttribute('data-tab')));
        });

        showTab('<?php echo in_array($tabFromQuery, ['dashboard', 'users', 'food', 'recipes', 'ingredients', 'reviews', 'analytics'], true) ? $tabFromQuery : 'dashboard'; ?>');

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

        const addUserForm = document.getElementById('addUserForm');
        const updateRoleForm = document.getElementById('updateRoleForm');
        const deleteUserForm = document.getElementById('deleteUserForm');

        const addUserError = document.getElementById('addUserError');
        const updateRoleError = document.getElementById('updateRoleError');
        const deleteUserError = document.getElementById('deleteUserError');

        const isValidEmail = function (email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        };

        if (addUserForm && addUserError) {
            addUserForm.addEventListener('submit', function (event) {
                const nom = (addUserForm.querySelector('input[name="nom"]')?.value || '').trim();
                const email = (addUserForm.querySelector('input[name="email"]')?.value || '').trim();
                const motDePasse = addUserForm.querySelector('input[name="mot_de_passe"]')?.value || '';

                if (nom === '' || email === '' || motDePasse === '') {
                    event.preventDefault();
                    addUserError.textContent = 'Nom, email et mot de passe sont obligatoires.';
                    addUserError.style.display = 'block';
                    return;
                }

                if (!isValidEmail(email)) {
                    event.preventDefault();
                    addUserError.textContent = 'Adresse email invalide.';
                    addUserError.style.display = 'block';
                    return;
                }

                addUserError.style.display = 'none';
            });
        }

        if (updateRoleForm && updateRoleError) {
            updateRoleForm.addEventListener('submit', function (event) {
                const id = Number(updateRoleForm.querySelector('input[name="id"]')?.value || 0);

                if (!Number.isInteger(id) || id < 1) {
                    event.preventDefault();
                    updateRoleError.textContent = 'ID utilisateur invalide.';
                    updateRoleError.style.display = 'block';
                    return;
                }

                updateRoleError.style.display = 'none';
            });
        }

        if (deleteUserForm && deleteUserError) {
            deleteUserForm.addEventListener('submit', function (event) {
                const id = Number(deleteUserForm.querySelector('input[name="id"]')?.value || 0);

                if (!Number.isInteger(id) || id < 1) {
                    event.preventDefault();
                    deleteUserError.textContent = 'ID utilisateur invalide.';
                    deleteUserError.style.display = 'block';
                    return;
                }

                deleteUserError.style.display = 'none';
            });
        }
    </script>
    <script src="js/backoffice.js"></script>
</body>
</html>
