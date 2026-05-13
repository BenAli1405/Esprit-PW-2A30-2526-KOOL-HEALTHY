<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/RecetteController.php';
require_once __DIR__ . '/../CONTROLLER/AuthController.php';
require_once __DIR__ . '/../config.php';

$controller = new RecetteController();
$authController = new AuthController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();

// Initialize tables (lazy initialization)
$controller->getTrendingHashtags(1);
$controller->getFollowersCount(0);

// Get hashtag filter if present
$hashtagFilter = trim($_GET['hashtag'] ?? '');

// Get recipes based on filter or all (exclude current user's own recipes and blocked authors)
$currentUserId = $utilisateurConnecte['id'] ?? 0;
if (!empty($hashtagFilter)) {
    $recettes = $controller->getRecettesByHashtag($hashtagFilter, $currentUserId);
} else {
    $recettes = $controller->listeRecettes($currentUserId);
}

$favorisIds = [];
if ($utilisateurConnecte && isset($utilisateurConnecte['id'])) {
    $favorisIds = $controller->recupererIdsFavoris((int) $utilisateurConnecte['id']);
}

// Get trending hashtags for sidebar
$trendingHashtags = $controller->getTrendingHashtags(10);

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$titre_page = 'Fil Recettes';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fil Recettes - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/styles.css">
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <section class="section-wrap recipes-section">
    <div class="layout">
        <?php include __DIR__ . '/includes/left-nav.php'; ?>

        <main class="main-col">
            <?php if ($success === 'favori_added'): ?>
                <section class="panel social-feedback success">Recette ajoutee aux favoris.</section>
            <?php elseif ($success === 'favori_removed'): ?>
                <section class="panel social-feedback success">Recette retiree des favoris.</section>
            <?php endif; ?>

            <?php if ($error === 'invalid_recipe'): ?>
                <section class="panel social-feedback error">Recette invalide pour l'action favoris.</section>
            <?php endif; ?>

            <section class="panel composer" id="partage-recette">
                <h2>Partager une recette</h2>
                <form method="POST" action="../CONTROLLER/addRecette.php?action=create" enctype="multipart/form-data" id="recetteForm" novalidate>
                    <input type="text" name="titre" placeholder="Titre de la recette (ex: Salade fraiche quinoa)">
                    <input type="number" name="temps_prep" placeholder="Temps de preparation (en minutes)">
                    <input type="text" name="ingredients" placeholder="Ingredients (separes par des virgules)">
                    <textarea name="etapes" placeholder="Etapes de preparation..."></textarea>
                    <div id="recetteError" class="panel social-feedback error" style="display:none;"></div>
                    <button type="submit" class="btn">Publier</button>
                </form>
            </section>

            <div class="feed">
                <?php if (empty($recettes)): ?>
                    <article class="panel post"><p>Aucune recette pour le moment. Soyez le premier a partager !</p></article>
                <?php else: ?>
                    <?php foreach ($recettes as $recette): ?>
                    <article class="panel post">
                        <div class="post-top">
                            <div class="user">
                                <div class="avatar"><?= strtoupper(substr($recette['nom'] ?? 'A', 0, 1)) ?></div>
                                <div class="user-info">
                                    <strong class="username-clickable"
                                        data-user-id="<?= (int)($recette['user_id'] ?? 0) ?>"
                                        data-user-name="<?= htmlspecialchars($recette['nom'] ?? '') ?>"
                                        data-user-avatar="">
                                        <?= htmlspecialchars($recette['nom'] ?? 'Anonyme') ?>
                                    </strong>
                                    <small><?= htmlspecialchars($recette['date_creation'] ?? '') ?></small>
                                </div>
                            </div>
                            <?php if (isset($utilisateurConnecte['id']) && ($recette['user_id'] ?? 0) !== $utilisateurConnecte['id']): ?>
                            <button class="follow-btn"
                                data-user-id="<?= (int)($recette['user_id'] ?? 0) ?>">
                                Suivre
                            </button>
                            <?php endif; ?>
                        </div>

                        <h3 class="recipe-title"><?= htmlspecialchars($recette['titre'] ?? '') ?></h3>

                        <?php if (!empty($recette['temps_prep'])): ?>
                            <div class="recipe-meta"><span class="meta-pill">⏱ <?= (int)$recette['temps_prep'] ?> min</span></div>
                        <?php endif; ?>

                        <?php if (!empty($recette['ingredients'])): ?>
                            <div class="recipe-block">
                                <strong>Ingredients</strong>
                                <p><?= htmlspecialchars($recette['ingredients']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recette['etapes'])): ?>
                            <p><?= nl2br(htmlspecialchars($recette['etapes'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($recette['hashtags'])): ?>
                            <div class="recipe-hashtags">
                                <?php foreach (explode(',', $recette['hashtags']) as $tag): ?>
                                    <?php $tag = trim($tag); if ($tag): ?>
                                    <a href="?hashtag=<?= urlencode(ltrim($tag, '#')) ?>" class="tag-link"><?= htmlspecialchars($tag) ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="actions">
                            <form method="POST" action="../CONTROLLER/FavoriController.php" class="inline-action-form js-favori-form">
                                <input type="hidden" name="recette_id" value="<?= (int)($recette['id'] ?? 0) ?>">
                                <input type="hidden" name="action" value="toggle">
                                <button type="submit" class="action-btn favorite <?= in_array($recette['id'] ?? 0, $favorisIds) ? 'active' : '' ?>">
                                    ❤ <?= in_array($recette['id'] ?? 0, $favorisIds) ? 'Aime' : "J'aime" ?>
                                </button>
                            </form>
                            <button class="action-btn" data-action="share" data-recipe-title="<?= htmlspecialchars($recette['titre'] ?? '') ?>">
                                &#8679; Partager
                            </button>
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
                            alert('Lien copie dans le presse-papiers !');
                        });
                    }
                }
            });
        });

        const recetteForm = document.getElementById('recetteForm');
        const recetteError = document.getElementById('recetteError');

        if (recetteForm && recetteError) {
            recetteForm.addEventListener('submit', function (event) {
                const titre = (recetteForm.querySelector('input[name="titre"]')?.value || '').trim();
                const ingredients = (recetteForm.querySelector('input[name="ingredients"]')?.value || '').trim();
                const tempsPrep = Number(recetteForm.querySelector('input[name="temps_prep"]')?.value || 0);

                if (titre === '' || ingredients === '') {
                    event.preventDefault();
                    recetteError.textContent = 'Titre et ingredients sont obligatoires.';
                    recetteError.style.display = 'block';
                    return;
                }

                if (Number.isNaN(tempsPrep) || tempsPrep < 1) {
                    event.preventDefault();
                    recetteError.textContent = 'Le temps de preparation doit etre superieur ou egal a 1 minute.';
                    recetteError.style.display = 'block';
                    return;
                }

                recetteError.style.display = 'none';
            });
        }

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

                    if (data.is_favorite) {
                        button.classList.add('active');
                        button.textContent = '❤ Aime';
                    } else {
                        button.classList.remove('active');
                        button.textContent = "❤ J'aime";
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
    <script src="../JS/user-modal.js?v=20260506"></script>

    <?php include __DIR__ . '/includes/user-action-modal.php'; ?>
</body>
</html>
