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
    <link rel="stylesheet" href="/Recettes/CSS/styles.css">
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
            <a href="fil-recettes.php">Partage</a>
        </nav>

        <div class="topbar-tools">
            <?php if ($utilisateurConnecte): ?>
                <details class="profile-menu">
                    <summary class="profile-menu-trigger" aria-label="Menu profil">
                        <span class="profile-avatar"><?php echo strtoupper(substr($utilisateurConnecte['nom'] ?? 'U', 0, 1)); ?></span>
                    </summary>
                    <div class="profile-menu-dropdown">
                        <div class="profile-menu-user">
                            <strong><?php echo htmlspecialchars($utilisateurConnecte['nom'] ?? 'Utilisateur'); ?></strong>
                            <small><?php echo htmlspecialchars($utilisateurConnecte['email'] ?? ''); ?></small>
                        </div>
                        <a href="profil.php">Mon profil</a>
                        <?php if (($utilisateurConnecte['role'] ?? '') === 'admin'): ?>
                            <a href="backoffice.php">Backoffice</a>
                        <?php endif; ?>
                        <a class="danger" href="../CONTROLLER/AuthController.php?action=logout">Se deconnecter</a>
                    </div>
                </details>
            <?php else: ?>
                <a class="auth-link" href="auth.php">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

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
                            <form method="POST" action="../CONTROLLER/RecetteController.php?action=toggle_favori&format=json" class="inline-action-form js-favori-form">
                                <input type="hidden" name="recette_id" value="<?php echo (int) ($recette['id'] ?? 0); ?>">
                                <input type="hidden" name="return_to" value="../VIEW/favoris.php">
                                <button class="action-btn like active" type="submit">❤ Aime</button>
                            </form>
                            <button class="action-btn" data-action="comment" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">💬 Commenter</button>
                            <button class="action-btn" data-action="share" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">📤 Partager</button>
                        </div>
                    </article>
                    <?php endforeach; ?>
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
                            empty.innerHTML = '<p>Vous n\'avez pas encore de favoris. <a href="fil-recettes.php">Decouvrez des recettes</a> et ajoutez-les en favoris!</p>';
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
    <script src="/Recettes/JS/follow-system.js?v=20260506"></script>
</body>
</html>
