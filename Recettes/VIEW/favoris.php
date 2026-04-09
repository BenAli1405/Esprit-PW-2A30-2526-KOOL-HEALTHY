<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/RecetteController.php';
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$controller = new RecetteController();
$authController = new AuthController();
$utilisateurConnecte = $authController->utilisateurConnecte();

$recettes = $controller->recettesFavoris(1);
$titre_page = 'Mes Favoris';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Favoris - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="home.php" aria-label="Kool Healthy">
            <img class="brand-logo" src="../assets/logo-kool-healthy.png" alt="Kool Healthy" onerror="this.onerror=null;this.src='../assets/logo-kh.svg';">
        </a>

        <nav class="top-nav" aria-label="Navigation principale">
            <a href="home.php">Accueil</a>
            <a class="disabled-control" href="home.php#features">Fonctionnalites</a>
            <a class="disabled-control" href="fil-recettes.php">Recettes</a>
            <a class="disabled-control" href="home.php#impact">Impact</a>
            <a href="profil.php">Profils</a>
            <a href="fil-recettes.php">Partage</a>
        </nav>

        <div class="topbar-tools">
            <a class="auth-link" href="auth.php">Connexion</a>
        </div>
    </header>

    <section class="section-wrap recipes-section">
    <div class="layout">
        <aside class="panel left-nav" aria-label="Navigation recettes">
            <div class="profile-mini">
                <strong><?php echo htmlspecialchars($utilisateurConnecte['nom'] ?? 'Mondher'); ?></strong>
                <small><?php echo htmlspecialchars($utilisateurConnecte['email'] ?? '@monther.healthy'); ?></small>
            </div>
            <nav class="menu">
                <a href="fil-recettes.php">Fil recettes</a>
                <a href="mes-recettes.php">Mes recettes</a>
                <a class="active" href="favoris.php">Favoris</a>
            </nav>
        </aside>

        <main class="main-col">
            <section class="panel profile-header">
                <h2><?php echo htmlspecialchars($titre_page); ?></h2>
                <p>Vos recettes favorites sauvegardées</p>
            </section>

            <section class="feed">
                <?php if (empty($recettes)): ?>
                    <article class="panel post">
                        <p>Vous n'avez pas encore de favoris. <a href="fil-recettes.php">Découvrez des recettes</a> et ajoutez-les en favoris!</p>
                    </article>
                <?php else: ?>
                    <?php foreach ($recettes as $recette): ?>
                    <article class="panel post">
                        <div class="user-info">
                            <div class="user-avatar"><?php echo strtoupper(substr($recette['nom'] ?? 'U', 0, 1)); ?></div>
                            <div>
                                <strong><?php echo htmlspecialchars($recette['nom'] ?? 'Utilisateur'); ?></strong>
                                <small><?php echo htmlspecialchars($recette['email'] ?? ''); ?></small>
                            </div>
                        </div>
                        <?php if ($recette['image']): ?>
                            <img src="<?php echo htmlspecialchars($recette['image']); ?>" alt="<?php echo htmlspecialchars($recette['titre']); ?>" class="recipe-photo">
                        <?php endif; ?>
                        <h3 class="recipe-title"><?php echo htmlspecialchars($recette['titre']); ?></h3>
                        <div class="recipe-meta">
                            <span class="meta-pill">⏱ <?php echo htmlspecialchars($recette['temps_prep'] ?? 'N/A'); ?> min</span>
                        </div>
                        <div class="recipe-block">
                            <strong>Ingredients:</strong>
                            <p><?php echo htmlspecialchars($recette['ingredients']); ?></p>
                        </div>
                        <p><?php echo htmlspecialchars($recette['etapes']); ?></p>
                        <div class="actions">
                            <button class="action-btn like active" data-action="like" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">❤ J'aime</button>
                            <button class="action-btn" data-action="comment" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">💬 Commenter</button>
                            <button class="action-btn" data-action="share" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">📤 Partager</button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>

        <aside class="panel right-sidebar" aria-label="Tendances">
            <h3 class="card-title">Tendances</h3>
            <div class="tag-list">
                <span class="tag">#Sante</span>
                <span class="tag">#Nutrition</span>
                <span class="tag">#Recettes</span>
                <span class="tag">#Durable</span>
            </div>

            <h3 class="card-title" style="margin-top: 20px;">A suivre</h3>
            <div class="suggest-list">
                <div class="suggest-item">
                    <div>
                        <strong>Nutrition Experts</strong>
                        <small>2.3K followers</small>
                    </div>
                    <button class="follow-btn">Suivre</button>
                </div>
                <div class="suggest-item">
                    <div>
                        <strong>Cuisine Verte</strong>
                        <small>1.2K followers</small>
                    </div>
                    <button class="follow-btn">Suivre</button>
                </div>
            </div>
        </aside>
    </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 Kool Healthy. Mangez mieux, preservez la planete.</p>
        </div>
    </footer>

    <script>
        let currentRecipeTitle = '';

        // Gestion des boutons d'action
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const action = this.dataset.action;
                const recipeTitle = this.dataset.recipeTitle;
                
                if (action === 'like') {
                    this.classList.toggle('active');
                } else if (action === 'comment') {
                    this.classList.toggle('active');
                } else if (action === 'share') {
                    if (navigator.share) {
                        navigator.share({
                            title: 'Kool Healthy',
                            text: 'Regarde cette recette: ' + recipeTitle,
                            url: window.location.href
                        });
                    } else {
                        const shareText = 'Regarde cette recette: ' + recipeTitle + ' - ' + window.location.href;
                        navigator.clipboard.writeText(shareText).then(() => {
                            alert('✅ Lien copié dans le presse-papiers!');
                        });
                    }
                }
            });
        });

        // Boutons de suivi
        document.querySelectorAll('.follow-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.textContent.includes('Suivre')) {
                    this.textContent = 'Suivi ✓';
                    this.style.background = 'var(--vert-kool-dark)';
                } else {
                    this.textContent = 'Suivre';
                    this.style.background = 'var(--vert-kool)';
                }
            });
        });
    </script>
</body>
</html>
