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
    <link rel="stylesheet" href="/Recettes/CSS/styles.css?v=20260506.2">
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
        </div>
    </header>

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
                                    <form method="POST" action="../CONTROLLER/RecetteController.php?action=toggle_favori&format=json" class="inline-action-form js-favori-form">
                                        <input type="hidden" name="recette_id" value="<?php echo (int) ($recette['id'] ?? 0); ?>">
                                        <input type="hidden" name="return_to" value="../VIEW/comptes-suivis.php">
                                        <button class="action-btn like <?php echo $estFavori ? 'active' : ''; ?>" type="submit">
                                            <?php echo $estFavori ? '❤ Aime' : '❤ J\'aime'; ?>
                                        </button>
                                    </form>
                                    <button class="action-btn" data-action="comment" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">💬 Commenter</button>
                                    <button class="action-btn" data-action="share" data-recipe-title="<?php echo htmlspecialchars($recette['titre']); ?>">📤 Partager</button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Aucune publication pour le moment sur les comptes suivis.</p>
                        <a href="fil-recettes.php" class="follow-btn" style="display:inline-flex; margin-top:12px; text-decoration:none;">Découvrir des recettes</a>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <!-- Right Sidebar -->
        <?php include __DIR__ . '/includes/right-sidebar.php'; ?>
    </div>
    </section>

    <?php include __DIR__ . '/includes/user-action-modal.php'; ?>

    <script src="/Recettes/JS/follow-system.js?v=20260506.1"></script>
    <script src="/Recettes/JS/user-modal.js?v=20260506.1"></script>
</body>
</html>
