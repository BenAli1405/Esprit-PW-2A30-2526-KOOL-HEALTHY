<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/RecetteController.php';
require_once __DIR__ . '/../CONTROLLER/AuthController.php';
require_once __DIR__ . '/../config.php';

$controller = new RecetteController();
$authController = new AuthController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();

$auteurConnecte = trim((string) ($utilisateurConnecte['nom'] ?? ''));
$recettes = $controller->mesRecettes($auteurConnecte !== '' ? $auteurConnecte : 'Moi');
$titre_page = 'Mes Recettes';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$messagesSuccess = [
    'recipe_created' => 'Recette publiee avec succes.',
    'recipe_updated' => 'Recette modifiee avec succes.',
    'recipe_deleted' => 'Recette supprimee avec succes.'
];

$messagesError = [
    'invalid_data' => 'Veuillez verifier les champs obligatoires (titre, ingredients, temps).',
    'invalid_recipe' => 'Recette introuvable ou invalide.',
    'unauthorized' => 'Action non autorisee sur cette recette.'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Recettes - Kool Healthy</title>
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
                <p>Vos recettes partagées</p>
            </section>

            <?php if ($success !== '' && isset($messagesSuccess[$success])): ?>
                <div class="panel social-feedback success"><?php echo htmlspecialchars($messagesSuccess[$success]); ?></div>
            <?php endif; ?>

            <?php if ($error !== '' && isset($messagesError[$error])): ?>
                <div class="panel social-feedback error"><?php echo htmlspecialchars($messagesError[$error]); ?></div>
            <?php endif; ?>

            <div class="feed">
                <?php if (empty($recettes)): ?>
                    <article class="panel post"><p>Vous n'avez pas encore publie de recettes.</p></article>
                <?php else: ?>
                    <?php foreach ($recettes as $recette): ?>
                    <article class="panel post">
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

                        <form method="POST" action="../CONTROLLER/addRecette.php?action=update" enctype="multipart/form-data" class="recipe-edit-form">
                            <input type="hidden" name="id" value="<?php echo (int) ($recette['id'] ?? 0); ?>">
                            <input type="text" name="titre" value="<?php echo htmlspecialchars($recette['titre'] ?? ''); ?>" placeholder="Titre de la recette">
                            <input type="number" name="temps_prep" value="<?php echo (int) ($recette['temps_prep'] ?? 0); ?>" placeholder="Temps de preparation (minutes)">
                            <input type="text" name="ingredients" value="<?php echo htmlspecialchars($recette['ingredients'] ?? ''); ?>" placeholder="Ingredients">
                            <textarea name="etapes" placeholder="Etapes de preparation..."><?php echo htmlspecialchars($recette['etapes'] ?? ''); ?></textarea>
                            <?php
                                $currentHashtags = $controller->getRecetteHashtags($recette['id'] ?? 0);
                                $hashtagsStr = !empty($currentHashtags) ? '#' . implode(' #', $currentHashtags) : '';
                            ?>
                            <input type="text" name="hashtags" value="<?php echo htmlspecialchars($hashtagsStr); ?>" placeholder="Hashtags (ex: #vegan #rapide)">
                            <input type="file" name="image" accept="image/*">
                            <div class="recipe-owner-actions">
                                <button class="btn" type="submit">Sauvegarder</button>
                            </div>
                        </form>

                        <form method="POST" action="../CONTROLLER/addRecette.php?action=delete" onsubmit="return confirm('Supprimer cette recette ?');">
                            <input type="hidden" name="id" value="<?php echo (int) ($recette['id'] ?? 0); ?>">
                            <div class="recipe-owner-actions">
                                <button class="btn btn-danger" type="submit">Supprimer</button>
                            </div>
                        </form>

                        <div class="actions">
                            <button class="action-btn like" data-action="like" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">❤ J'aime</button>
                            <button class="action-btn" data-action="comment" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">💬 Commenter</button>
                            <button class="action-btn" data-action="share" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">📤 Partager</button>
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

    </script>
    <script src="../JS/follow-system.js?v=20260506"></script>
</body>
</html>
