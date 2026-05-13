<?php
session_start();
require_once __DIR__ . "/../CONTROLLER/RecetteController.php";
require_once __DIR__ . "/../CONTROLLER/AuthController.php";

$authController = new AuthController();
$utilisateurConnecte = $authController->utilisateurConnecte();

if (!$utilisateurConnecte) {
    header("Location: auth.php");
    exit;
}

$recetteController = new RecetteController();
$comptesFollowed = $recetteController->getFollowedAccounts((int) $utilisateurConnecte['id']);
$publicationsSuivies = $recetteController->getRecettesDesComptesSuivis((int) $utilisateurConnecte['id'], 100);
$favorisIds = $recetteController->recupererIdsFavoris((int) $utilisateurConnecte['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptes suivis - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/styles.css?v=20260506.2">
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <section class="section-wrap recipes-section">
    <div class="layout">
        <?php include __DIR__ . '/includes/left-nav.php'; ?>

        <main class="main-col">
            <section class="recipes-container">
                <h2 class="section-title left" style="margin-top: 32px;">Publications de vos comptes suivis</h2>

                <?php if (!empty($publicationsSuivies)): ?>
                    <div class="feed">
                        <?php foreach ($publicationsSuivies as $recette): ?>
                            <article class="panel post">
                                <div class="user-info">
                                    <?php if (!empty($recette['auteur_avatar'])): ?>
                                        <img src="<?php echo htmlspecialchars($recette['auteur_avatar']); ?>" alt="Avatar de <?php echo htmlspecialchars((string) ($recette['auteur_nom'] ?? $recette['nom'] ?? 'Compte')); ?>" class="user-avatar-img">
                                    <?php else: ?>
                                        <div class="user-avatar"><?php echo strtoupper(substr((string) ($recette['auteur_nom'] ?? $recette['nom'] ?? 'U'), 0, 1)); ?></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong class="username-clickable" data-user-id="<?php echo (int) ($recette['user_id'] ?? 0); ?>" data-user-name="<?php echo htmlspecialchars((string) ($recette['auteur_nom'] ?? $recette['nom'] ?? '')); ?>" data-user-avatar="<?php echo htmlspecialchars((string) ($recette['auteur_avatar'] ?? '')); ?>" style="cursor: pointer;">
                                            <?php echo htmlspecialchars((string) ($recette['auteur_nom'] ?? $recette['nom'] ?? 'Utilisateur')); ?>
                                        </strong>
                                        <small><?php echo htmlspecialchars((string) ($recette['auteur_email'] ?? $recette['email'] ?? '')); ?></small>
                                    </div>
                                </div>

                                <?php if (!empty($recette['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($recette['image']); ?>" alt="<?php echo htmlspecialchars($recette['titre']); ?>" class="recipe-photo">
                                <?php endif; ?>

                                <h3 class="recipe-title"><?php echo htmlspecialchars($recette['titre']); ?></h3>
                                <div class="recipe-meta">
                                    <span class="meta-pill">⏱ <?php echo htmlspecialchars($recette['temps_prep'] ?? 'N/A'); ?> min</span>
                                </div>

                                <?php $hashtags = $recetteController->getRecetteHashtags($recette['id'] ?? 0); ?>
                                <?php if (!empty($hashtags)): ?>
                                    <div class="recipe-hashtags">
                                        <?php foreach ($hashtags as $tag): ?>
                                            <a href="comptes-suivis.php?hashtag=<?php echo urlencode($tag); ?>" class="tag-link">#<?php echo htmlspecialchars($tag); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="recipe-block">
                                    <strong>Ingredients:</strong>
                                    <p><?php echo htmlspecialchars($recette['ingredients']); ?></p>
                                </div>

                                <p><?php echo htmlspecialchars($recette['etapes']); ?></p>

                                <?php $estFavori = in_array((int) ($recette['id'] ?? 0), $favorisIds, true); ?>
                                <div class="actions">
                                    <form method="POST" action="../CONTROLLER/FavoriController.php" class="inline-action-form js-favori-form">
                                        <input type="hidden" name="recette_id" value="<?php echo (int) ($recette['id'] ?? 0); ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <button type="submit" class="action-btn favorite <?php echo $estFavori ? 'active' : ''; ?>">
                                            ❤ <?php echo $estFavori ? 'Aime' : "J'aime"; ?>
                                        </button>
                                    </form>
                                    <button class="action-btn" data-action="share" data-recipe-title="<?php echo htmlspecialchars($recette['titre'] ?? ''); ?>">&#8679; Partager</button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-state">Aucune publication de vos comptes suivis pour le moment.</p>
                <?php endif; ?>
            </section>
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
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const action = this.dataset.action;
                const recipeTitle = this.dataset.recipeTitle;
                if (action === 'share') {
                    if (navigator.share) {
                        navigator.share({ title: 'Kool Healthy', text: 'Regarde cette recette: ' + recipeTitle, url: window.location.href });
                    } else {
                        const shareText = 'Regarde cette recette: ' + recipeTitle + ' - ' + window.location.href;
                        navigator.clipboard.writeText(shareText).then(() => {
                            alert('Lien copie dans le presse-papiers !');
                        });
                    }
                }
            });
        });

        document.querySelectorAll('.js-favori-form').forEach(form => {
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                if (!button) return;
                const formData = new FormData(form);
                button.disabled = true;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST', body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) throw new Error('Erreur favoris');
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
