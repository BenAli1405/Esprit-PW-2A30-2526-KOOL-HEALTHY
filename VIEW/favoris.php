<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/RecetteController.php';
require_once __DIR__ . '/../CONTROLLER/AuthController.php';
require_once __DIR__ . '/../config.php';

$controller = new RecetteController();
$authController = new AuthController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();

if (!$utilisateurConnecte || !isset($utilisateurConnecte['id'])) {
    header('Location: auth.php');
    exit();
}

$recettes = $controller->recettesFavoris((int) $utilisateurConnecte['id']);
$success = $_GET['success'] ?? '';
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
    <?php include __DIR__ . '/includes/topbar.php'; ?>
    <section class="section-wrap recipes-section">
    <div class="layout">
        <?php include __DIR__ . '/includes/left-nav.php'; ?>

        <main class="main-col">
            <section class="panel profile-header">
                <h2><?php echo htmlspecialchars($titre_page); ?></h2>
                <p>Vos recettes favorites sauvegardées</p>
            </section>

            <?php if ($success === 'favori_removed'): ?>
                <section class="panel social-feedback success">Recette retiree des favoris.</section>
            <?php endif; ?>

            <div class="feed">
                <?php if (empty($recettes)): ?>
                    <article class="panel post"><p>Vous n'avez pas encore de favoris. <a href="frontoffice.php">Decouvrez des recettes</a> et ajoutez-les en favoris !</p></article>
                <?php else: ?>
                    <?php foreach ($recettes as $recette): ?>
                    <article class="panel post">
                        <div class="post-top">
                            <div class="user">
                                <div class="avatar"><?php echo strtoupper(substr($recette['nom'] ?? 'A', 0, 1)); ?></div>
                                <div class="user-info">
                                    <strong><?php echo htmlspecialchars($recette['nom'] ?? 'Anonyme'); ?></strong>
                                    <small><?php echo htmlspecialchars($recette['date_creation'] ?? ''); ?></small>
                                </div>
                            </div>
                        </div>

                        <h3 class="recipe-title"><?php echo htmlspecialchars($recette['titre'] ?? ''); ?></h3>

                        <?php if (!empty($recette['temps_prep'])): ?>
                            <div class="recipe-meta"><span class="meta-pill">⏱ <?php echo (int)$recette['temps_prep']; ?> min</span></div>
                        <?php endif; ?>

                        <?php if (!empty($recette['ingredients'])): ?>
                            <div class="recipe-block">
                                <strong>Ingredients</strong>
                                <p><?php echo htmlspecialchars($recette['ingredients']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recette['etapes'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($recette['etapes'])); ?></p>
                        <?php endif; ?>

                        <div class="actions">
                            <form method="POST" action="../CONTROLLER/FavoriController.php" class="inline-action-form js-favori-form">
                                <input type="hidden" name="recette_id" value="<?php echo (int)($recette['id'] ?? 0); ?>">
                                <input type="hidden" name="action" value="toggle">
                                <button type="submit" class="action-btn favorite active">❤ Aime</button>
                            </form>
                            <button class="action-btn" data-action="share" data-recipe-title="<?php echo htmlspecialchars($recette['titre'] ?? ''); ?>">&#8679; Partager</button>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/includes/right-sidebar.php'; ?>
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


        document.querySelectorAll('.js-favori-form').forEach(form => {
            form.addEventListener('submit', async function (event) {
                event.preventDefault();

                const button = form.querySelector('button[type="submit"]');
                if (!button) {
                    return;
                }

                const formData = new FormData(form);
                button.disabled = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error('Erreur favoris');
                    }

                    if (!data.is_favorite) {
                        const post = form.closest('.post');
                        if (post) {
                            post.remove();
                        }

                        const feed = document.querySelector('.feed');
                        if (feed && feed.querySelectorAll('.post').length === 0) {
                            const empty = document.createElement('article');
                            empty.className = 'panel post';
                            empty.innerHTML = '<p>Vous n\'avez pas encore de favoris. <a href="<?php echo defined('BASE_URL') ? BASE_URL : 'http://localhost/integweb/'; ?>VIEW/frontoffice.html">Decouvrez des recettes</a> et ajoutez-les en favoris!</p>';
                            feed.appendChild(empty);
                        }
                    }
                } catch (e) {
                    alert('Impossible de mettre a jour le favori pour le moment.');
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
    <script src="../JS/follow-system.js?v=20260506"></script>
</body>
</html>
