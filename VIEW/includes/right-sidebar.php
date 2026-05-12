<?php
/**
 * Barre latérale droite - Tendances & Comptes à suivre
 * À inclure dans tous les onglets du réseau social
 * 
 * Requis: $controller (RecetteController), $utilisateurConnecte
 */

if (!isset($controller) || !($controller instanceof RecetteController)) {
    if (!class_exists('RecetteController')) {
        require_once __DIR__ . '/../../CONTROLLER/RecetteController.php';
    }
    $controller = new RecetteController();
}

if (!isset($utilisateurConnecte) || !is_array($utilisateurConnecte)) {
    if (!class_exists('AuthController')) {
        require_once __DIR__ . '/../../CONTROLLER/AuthController.php';
    }
    $authController = new AuthController();
    $utilisateurConnecte = $authController->utilisateurConnecte();
}

// Initialiser les tables
$controller->getTendances();

$trendingHashtags = $controller->getTendances();
$db = config::getConnexion();
$userId = $utilisateurConnecte['id'] ?? 0;
?>

<aside class="panel right-sidebar" aria-label="Tendances">
    <h3 class="card-title">🔥 Tendances</h3>
    <div class="tag-list">
        <?php if (!empty($trendingHashtags)): ?>
            <?php foreach (array_slice($trendingHashtags, 0, 10) as $hashtag): ?>
                <a href="fil-recettes.php?hashtag=<?php echo urlencode(ltrim($hashtag, '#')); ?>" class="tag tag-link" style="display: inline-block; margin: 4px; text-decoration: none; color: inherit;">
                    <?php echo htmlspecialchars($hashtag); ?>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #999; font-size: 0.9rem;">Aucune tendance pour le moment</p>
        <?php endif; ?>
    </div>

    <h3 class="card-title" style="margin-top: 20px;">👥 À suivre</h3>
    <div class="suggest-list">
        <?php
            if ($userId > 0) {
                // Comptes les plus actifs (par nombre de recettes + followers)
                $sql = "SELECT 
                            u.id, 
                            u.nom, 
                            COUNT(DISTINCT f.id) as followers_count,
                            COUNT(DISTINCT r.id) as recipes_count,
                            (COUNT(DISTINCT f.id) + COUNT(DISTINCT r.id)) as activity_score
                        FROM utilisateurs u 
                        LEFT JOIN follows f ON u.id = f.following_id
                        LEFT JOIN publication r ON u.nom = r.auteur
                        WHERE u.role != 'admin' 
                        AND u.id != :user_id 
                        AND u.id NOT IN (SELECT following_id FROM follows WHERE follower_id = :user_id)
                        GROUP BY u.id, u.nom
                        HAVING activity_score > 0
                        ORDER BY activity_score DESC, u.nom ASC
                        LIMIT 5";
                
                $req = $db->prepare($sql);
                $req->execute(['user_id' => $userId]);
                $suggestedUsers = $req->fetchAll();
                
                if (!empty($suggestedUsers)):
                    foreach ($suggestedUsers as $suggested):
        ?>
                        <div class="suggest-item">
                            <div class="suggest-user-copy">
                                <strong><?php echo htmlspecialchars($suggested['nom']); ?></strong>
                                <small><?php echo $suggested['followers_count']; ?> followers • <?php echo $suggested['recipes_count']; ?> recettes</small>
                            </div>
                            <button class="follow-btn" data-user-id="<?php echo $suggested['id']; ?>" type="button">Suivre</button>
                        </div>
        <?php
                    endforeach;
                else:
        ?>
                    <p style="color: #999; font-size: 0.9rem;">Tous les comptes pertinents sont déjà suivis</p>
        <?php
                endif;
            } else {
                // Comptes les plus actifs si non connecté
                $sql = "SELECT 
                            u.id, 
                            u.nom, 
                            COUNT(DISTINCT f.id) as followers_count,
                            COUNT(DISTINCT r.id) as recipes_count,
                            (COUNT(DISTINCT f.id) + COUNT(DISTINCT r.id)) as activity_score
                        FROM utilisateurs u 
                        LEFT JOIN follows f ON u.id = f.following_id
                        LEFT JOIN publication r ON u.nom = r.auteur
                        WHERE u.role != 'admin' 
                        GROUP BY u.id, u.nom
                        HAVING activity_score > 0
                        ORDER BY activity_score DESC
                        LIMIT 5";
                
                $req = $db->query($sql);
                $topUsers = $req->fetchAll();
                
                foreach ($topUsers as $user):
        ?>
                    <div class="suggest-item">
                        <div class="suggest-user-copy">
                            <strong><?php echo htmlspecialchars($user['nom']); ?></strong>
                            <small><?php echo $user['followers_count']; ?> followers • <?php echo $user['recipes_count']; ?> recettes</small>
                        </div>
                        <a href="../VIEW/auth.php" class="follow-btn">Suivre</a>
                    </div>
        <?php
                endforeach;
            }
        ?>
    </div>
</aside>
<?php include __DIR__ . '/user-action-modal.php'; ?>
