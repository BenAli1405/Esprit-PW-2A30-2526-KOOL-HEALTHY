<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/RecetteController.php';
require_once __DIR__ . '/../CONTROLLER/AuthController.php';

$controller = new RecetteController();
$authController = new AuthController();
$authController->exigerFront('backoffice.php');
$utilisateurConnecte = $authController->utilisateurConnecte();

$recettes = $controller->listeRecettes();
$favorisIds = [];
if ($utilisateurConnecte && isset($utilisateurConnecte['id'])) {
    $favorisIds = $controller->recupererIdsFavoris((int) $utilisateurConnecte['id']);
}
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
        <aside class="panel left-nav" aria-label="Navigation recettes">
            <div class="profile-mini">
                <strong><?php echo htmlspecialchars($utilisateurConnecte['nom'] ?? 'Mondher'); ?></strong>
                <small><?php echo htmlspecialchars($utilisateurConnecte['email'] ?? '@monther.healthy'); ?></small>
            </div>
            <nav class="menu">
                <a class="active" href="fil-recettes.php">Fil recettes</a>
                <a href="mes-recettes.php">Mes recettes</a>
                <a href="favoris.php">Favoris</a>
            </nav>
        </aside>

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
                    <input type="file" name="image" accept="image/*">
                    <span id="recetteError" class="password-error" style="display:none;color:red;font-size:0.9rem;margin-bottom:10px;"></span>
                    <div class="composer-footer">
                        <span class="hint">Conseil: precise le temps et les ingredients pour aider la communaute.</span>
                        <button class="btn" type="submit">Publier la recette</button>
                    </div>
                </form>
            </section>

            <section class="feed">
                <?php if (empty($recettes)): ?>
                    <article class="panel post">
                        <p>Aucune recette trouvee. Sois le premier a en partager une!</p>
                    </article>
                <?php else: ?>
                    <?php foreach ($recettes as $recette): ?>
                    <article class="panel post">
                        <div class="user-info">
                            <div class="user-avatar"><?php echo strtoupper(substr($recette['nom'] ?? 'U', 0, 1)); ?></div>
                            <div>
                                <strong><?php echo htmlspecialchars((string) ($recette['auteur'] ?? ($recette['nom'] ?? 'Utilisateur'))); ?></strong>
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
                        <?php $estFavori = in_array((int) ($recette['id'] ?? 0), $favorisIds, true); ?>
                        <div class="actions">
                            <?php if ($utilisateurConnecte): ?>
                                <form method="POST" action="../CONTROLLER/RecetteController.php?action=toggle_favori&format=json" class="inline-action-form js-favori-form">
                                    <input type="hidden" name="recette_id" value="<?php echo (int) ($recette['id'] ?? 0); ?>">
                                    <input type="hidden" name="return_to" value="../VIEW/fil-recettes.php">
                                    <button class="action-btn like <?php echo $estFavori ? 'active' : ''; ?>" type="submit">
                                        <?php echo $estFavori ? '❤ Aime' : '❤ J\'aime'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a class="action-btn" href="auth.php">❤ J'aime</a>
                            <?php endif; ?>
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
</body>
</html>
