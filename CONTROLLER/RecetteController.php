<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../MODEL/Recette.php";
require_once __DIR__ . "/AuthController.php";

class RecetteController
{
    private function initialiserTableFavoris()
    {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS `favoris` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `recette_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_favori` (`user_id`, `recette_id`),
            INDEX `idx_user` (`user_id`),
            INDEX `idx_recette` (`recette_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function initialiserTableFollows()
    {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS `follows` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `follower_id` INT NOT NULL,
            `following_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_follow` (`follower_id`, `following_id`),
            INDEX `idx_follower` (`follower_id`),
            INDEX `idx_following` (`following_id`),
            CONSTRAINT `fk_follower` FOREIGN KEY (`follower_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_following` FOREIGN KEY (`following_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function initialiserTableHashtags()
    {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS `hashtags` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nom` VARCHAR(255) NOT NULL UNIQUE,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_nom` (`nom`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->exec("CREATE TABLE IF NOT EXISTS `recette_hashtags` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `recette_id` INT NOT NULL,
            `hashtag_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_recette_hashtag` (`recette_id`, `hashtag_id`),
            INDEX `idx_recette` (`recette_id`),
            INDEX `idx_hashtag` (`hashtag_id`),
            CONSTRAINT `fk_recette_hashtag_recette` FOREIGN KEY (`recette_id`) REFERENCES `publication` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_recette_hashtag_hashtag` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function initialiserTableBlocks()
    {
        $db = config::getConnexion();
        $db->exec("CREATE TABLE IF NOT EXISTS `blocks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `blocked_user_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_block` (`user_id`, `blocked_user_id`),
            INDEX `idx_user` (`user_id`),
            INDEX `idx_blocked` (`blocked_user_id`),
            CONSTRAINT `fk_block_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_block_blocked` FOREIGN KEY (`blocked_user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private function tableExiste($table)
    {
        $db = config::getConnexion();
        $stmt = $db->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute(['table_name' => $table]);
        return $stmt->fetchColumn() !== false;
    }

    private function utilisateurAColonneAvatar()
    {
        $db = config::getConnexion();
        $stmt = $db->prepare("SHOW COLUMNS FROM utilisateurs LIKE 'avatar'");
        $stmt->execute();
        return $stmt->fetchColumn() !== false;
    }

    public function listeRecettes($current_user_id = 0)
    {
        $db = config::getConnexion();
        try {
            $this->initialiserTableBlocks();

            if ((int) $current_user_id > 0) {
                $sql = "SELECT r.*, u.id AS user_id, u.nom AS nom, u.email AS email
                            FROM publication r
                        LEFT JOIN utilisateurs u ON r.auteur = u.nom
                        WHERE (u.id IS NULL OR u.id != :current_user_id)
                          AND (u.id IS NULL OR u.id NOT IN (SELECT blocked_user_id FROM blocks WHERE user_id = :current_user_id))
                          AND (u.id IS NULL OR u.id NOT IN (SELECT user_id FROM blocks WHERE blocked_user_id = :current_user_id))
                        ORDER BY r.date_creation DESC";

                $req = $db->prepare($sql);
                $req->execute(['current_user_id' => (int) $current_user_id]);
                return $req->fetchAll();
            }

            $liste = $db->query("SELECT r.*, u.id AS user_id, u.nom AS nom, u.email AS email
                                  FROM publication r
                                  LEFT JOIN utilisateurs u ON r.auteur = u.nom
                                  ORDER BY r.date_creation DESC");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function mesRecettes($auteur = "Moi")
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM publication WHERE auteur = :auteur ORDER BY date_creation DESC";
            $req = $db->prepare($sql);
            $req->execute(['auteur' => $auteur]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function recettesFavoris($user_id)
    {
        $db = config::getConnexion();
        try {
            $this->initialiserTableFavoris();

            $this->initialiserTableBlocks();

                $sql = "SELECT r.*, u.id AS user_id, u.nom AS nom, u.email AS email
                    FROM publication r
                    INNER JOIN favoris f ON r.id = f.recette_id
                    LEFT JOIN utilisateurs u ON r.auteur = u.nom
                    WHERE f.user_id = :user_id
                      AND (u.id IS NULL OR u.id NOT IN (SELECT blocked_user_id FROM blocks WHERE user_id = :user_id))
                      AND (u.id IS NULL OR u.id NOT IN (SELECT user_id FROM blocks WHERE blocked_user_id = :user_id))
                    ORDER BY r.date_creation DESC";

            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function ajouterRecette($recette)
    {
        $db = config::getConnexion();
            $sql = "INSERT INTO publication (titre, temps_prep, ingredients, etapes, image, auteur, date_creation) 
                VALUES (:titre, :temps_prep, :ingredients, :etapes, :image, :auteur, :date_creation)";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'titre' => $recette->getTitre(),
                'temps_prep' => $recette->getTempsPrep(),
                'ingredients' => $recette->getIngredients(),
                'etapes' => $recette->getEtapes(),
                'image' => $recette->getImage(),
                'auteur' => $recette->getAuteur(),
                'date_creation' => $recette->getDateCreation()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function modifierRecette($recette, $id)
    {
        $db = config::getConnexion();
        $sql = "UPDATE publication SET titre=:titre, temps_prep=:temps_prep, ingredients=:ingredients, 
                etapes=:etapes, image=:image, auteur=:auteur WHERE id=:id";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'titre' => $recette->getTitre(),
                'temps_prep' => $recette->getTempsPrep(),
                'ingredients' => $recette->getIngredients(),
                'etapes' => $recette->getEtapes(),
                'image' => $recette->getImage(),
                'auteur' => $recette->getAuteur(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerRecette($id)
    {
        $db = config::getConnexion();
        $sql = "DELETE FROM publication WHERE id = :id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function obtenirRecette($id)
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM publication WHERE id = :id";
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function ajouterFavori($user_id, $recette_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFavoris();

        $sql = "INSERT IGNORE INTO favoris (user_id, recette_id) VALUES (:user_id, :recette_id)";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'recette_id' => $recette_id]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerFavori($user_id, $recette_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFavoris();

        $sql = "DELETE FROM favoris WHERE user_id = :user_id AND recette_id = :recette_id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'recette_id' => $recette_id]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function recupererIdsFavoris($user_id)
    {
        $db = config::getConnexion();
        try {
            $this->initialiserTableFavoris();
            $sql = "SELECT recette_id FROM favoris WHERE user_id = :user_id";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return array_map('intval', array_column($req->fetchAll(), 'recette_id'));
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ==== FOLLOWERS METHODS ====
    public function follow($follower_id, $following_id)
    {
        if ($follower_id === $following_id) {
            return false; // Cannot follow yourself
        }
        
        $db = config::getConnexion();
        $this->initialiserTableFollows();

        $sql = "INSERT IGNORE INTO follows (follower_id, following_id) VALUES (:follower_id, :following_id)";
        try {
            $req = $db->prepare($sql);
            $req->execute(['follower_id' => $follower_id, 'following_id' => $following_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function unfollow($follower_id, $following_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();

        $sql = "DELETE FROM follows WHERE follower_id = :follower_id AND following_id = :following_id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['follower_id' => $follower_id, 'following_id' => $following_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function isFollowing($follower_id, $following_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();

        $sql = "SELECT COUNT(*) FROM follows WHERE follower_id = :follower_id AND following_id = :following_id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['follower_id' => $follower_id, 'following_id' => $following_id]);
            return (int) $req->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getFollowersCount($user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();

        $sql = "SELECT COUNT(*) FROM follows WHERE following_id = :user_id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return (int) $req->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getFollowingCount($user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();

        $sql = "SELECT COUNT(*) FROM follows WHERE follower_id = :user_id";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return (int) $req->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getFollowers($user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();

        $sql = "SELECT u.* FROM utilisateurs u 
                INNER JOIN follows f ON u.id = f.follower_id 
                WHERE f.following_id = :user_id 
                ORDER BY f.created_at DESC";
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // ==== HASHTAGS METHODS ====
    public function addHashtagsToRecette($recette_id, $hashtags_string)
    {
        if (empty($hashtags_string)) {
            return true;
        }

        $db = config::getConnexion();
        $this->initialiserTableHashtags();

        // Extract hashtags from string (split by comma or space)
        $hashtags = array_filter(array_map('trim', preg_split('/[,\s]+/', $hashtags_string)));
        
        foreach ($hashtags as $hashtag) {
            // Remove # if present
            $hashtag = ltrim($hashtag, '#');
            if (empty($hashtag)) continue;

            try {
                // Insert or get hashtag ID
                $sql = "INSERT IGNORE INTO hashtags (nom) VALUES (:nom)";
                $req = $db->prepare($sql);
                $req->execute(['nom' => strtolower($hashtag)]);

                // Get hashtag ID
                $sql = "SELECT id FROM hashtags WHERE nom = :nom";
                $req = $db->prepare($sql);
                $req->execute(['nom' => strtolower($hashtag)]);
                $hashtag_id = $req->fetchColumn();

                if ($hashtag_id) {
                        // Link hashtag to recette
                        $sql = "INSERT IGNORE INTO recette_hashtags (recette_id, hashtag_id) VALUES (:recette_id, :hashtag_id)";
                        $req = $db->prepare($sql);
                        $req->execute(['recette_id' => $recette_id, 'hashtag_id' => $hashtag_id]);
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return true;
    }

    public function getRecetteHashtags($recette_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableHashtags();

        $sql = "SELECT h.nom FROM hashtags h 
            INNER JOIN recette_hashtags rh ON h.id = rh.hashtag_id 
            WHERE rh.recette_id = :recette_id 
            ORDER BY h.nom ASC";
        try {
            $req = $db->prepare($sql);
            $req->execute(['recette_id' => $recette_id]);
            return array_column($req->fetchAll(), 'nom');
        } catch (Exception $e) {
            return [];
        }
    }

    public function getRecettesByHashtag($hashtag, $current_user_id = 0)
    {
        $db = config::getConnexion();
        $this->initialiserTableHashtags();

        $hashtag = strtolower(ltrim($hashtag, '#'));
        
        if ((int) $current_user_id > 0) {
                $sql = "SELECT r.*, u.id AS user_id, u.nom AS nom, u.email AS email
                                FROM publication r
                                INNER JOIN recette_hashtags rh ON r.id = rh.recette_id
                                INNER JOIN hashtags h ON rh.hashtag_id = h.id
                    LEFT JOIN utilisateurs u ON (r.auteur = u.nom OR r.auteur = CAST(u.id AS CHAR))
                    WHERE h.nom = :hashtag
                      AND (u.id IS NULL OR u.id NOT IN (SELECT blocked_user_id FROM blocks WHERE user_id = :current_user_id))
                      AND (u.id IS NULL OR u.id NOT IN (SELECT user_id FROM blocks WHERE blocked_user_id = :current_user_id))
                    ORDER BY r.date_creation DESC";
        } else {
                $sql = "SELECT r.*, u.id AS user_id, u.nom AS auteur_nom, u.email AS auteur_email
                    FROM publication r
                                INNER JOIN recette_hashtags rh ON r.id = rh.recette_id
                                INNER JOIN hashtags h ON rh.hashtag_id = h.id
                    LEFT JOIN utilisateurs u ON (r.auteur = u.nom OR r.auteur = CAST(u.id AS CHAR))
                    WHERE h.nom = :hashtag
                    ORDER BY r.date_creation DESC";
        }
        try {
            $req = $db->prepare($sql);
            if ((int) $current_user_id > 0) {
                $req->execute(['hashtag' => $hashtag, 'current_user_id' => (int) $current_user_id]);
            } else {
                $req->execute(['hashtag' => $hashtag]);
            }
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getTrendingHashtags($limit = 10)
    {
        $db = config::getConnexion();
        $this->initialiserTableHashtags();

        $sql = "SELECT h.nom, COUNT(rh.id) as count FROM hashtags h 
            LEFT JOIN recette_hashtags rh ON h.id = rh.hashtag_id 
                GROUP BY h.id, h.nom 
                HAVING count > 0
                ORDER BY count DESC 
                LIMIT :limit";
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':limit', $limit, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // ==== BLOCAGE METHODS ====
    public function block($user_id, $blocked_user_id)
    {
        if ($user_id === $blocked_user_id) {
            return false;
        }
        
        $db = config::getConnexion();
        $this->initialiserTableBlocks();
        
        try {
            $sql = "INSERT INTO blocks (user_id, blocked_user_id) VALUES (:user_id, :blocked_user_id)";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'blocked_user_id' => $blocked_user_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function unblock($user_id, $blocked_user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableBlocks();
        
        try {
            $sql = "DELETE FROM blocks WHERE user_id = :user_id AND blocked_user_id = :blocked_user_id";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'blocked_user_id' => $blocked_user_id]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function isBlocked($user_id, $blocked_user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableBlocks();
        
        try {
            $sql = "SELECT COUNT(*) FROM blocks WHERE user_id = :user_id AND blocked_user_id = :blocked_user_id";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id, 'blocked_user_id' => $blocked_user_id]);
            return $req->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getFollowedAccounts($user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();
        $hasAvatar = $this->utilisateurAColonneAvatar();

        try {
            $avatarSelect = $hasAvatar ? "u.avatar," : "NULL AS avatar,";
            $avatarGroup = $hasAvatar ? ", u.avatar" : '';
            $sql = "SELECT 
                        u.id,
                        u.nom,
                        u.email,
                        {$avatarSelect}
                        (SELECT COUNT(*) FROM follows ff WHERE ff.following_id = u.id) AS followers_count,
                        (SELECT COUNT(*) FROM publication rr WHERE rr.auteur = u.nom) AS recipes_count
                    FROM follows f
                    INNER JOIN utilisateurs u ON u.id = f.following_id
                    WHERE f.follower_id = :user_id
                      AND u.id != :user_id
                    GROUP BY u.id, u.nom, u.email{$avatarGroup}
                    ORDER BY u.nom ASC";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getRecettesDesComptesSuivis($user_id, $limit = 50)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();
        $this->initialiserTableBlocks();

        $hasAvatar = $this->utilisateurAColonneAvatar();
        $avatarSelect = $hasAvatar ? "u.avatar AS auteur_avatar" : "NULL AS auteur_avatar";

        try {
            // Récupérer d'abord les IDs des comptes suivis
            $stmtF = $db->prepare('SELECT following_id FROM follows WHERE follower_id = :user_id');
            $stmtF->execute(['user_id' => (int) $user_id]);
            $following = array_column($stmtF->fetchAll(), 'following_id');

            if (empty($following)) {
                return [];
            }

            // Construire placeholders pour la clause IN
            $placeholders = [];
            $params = [];
            foreach ($following as $i => $fid) {
                $ph = ':fid' . $i;
                $placeholders[] = $ph;
                $params[$ph] = (int) $fid;
            }

            $inList = implode(',', $placeholders);

            $sql = "SELECT r.*, u.id AS user_id, u.nom AS auteur_nom, u.email AS auteur_email, {$avatarSelect}
                    FROM publication r
                    INNER JOIN utilisateurs u ON r.auteur = u.nom
                    WHERE u.id IN ($inList)
                      AND u.id != :user_id
                      AND u.id NOT IN (SELECT blocked_user_id FROM blocks WHERE user_id = :user_id_b1)
                      AND u.id NOT IN (SELECT user_id FROM blocks WHERE blocked_user_id = :user_id_b2)
                    ORDER BY r.date_creation DESC";

            $req = $db->prepare($sql);
            foreach ($params as $k => $v) {
                $req->bindValue($k, $v, PDO::PARAM_INT);
            }
            $req->bindValue(':user_id', (int) $user_id, PDO::PARAM_INT);
            $req->bindValue(':user_id_b1', (int) $user_id, PDO::PARAM_INT);
            $req->bindValue(':user_id_b2', (int) $user_id, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getBlockedUsers($user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableBlocks();
        $hasAvatar = $this->utilisateurAColonneAvatar();

        try {
            $avatarSelect = $hasAvatar ? "u.avatar," : "NULL AS avatar,";
            $sql = "SELECT 
                        u.id,
                        u.nom,
                        u.email,
                        {$avatarSelect}
                        (SELECT COUNT(*) FROM follows ff WHERE ff.following_id = u.id) AS followers_count,
                        (SELECT COUNT(*) FROM publication rr WHERE rr.auteur = u.nom) AS recipes_count
                    FROM blocks b
                    INNER JOIN utilisateurs u ON u.id = b.blocked_user_id
                    WHERE b.user_id = :user_id
                    ORDER BY u.nom ASC";
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    public function getTendances()
    {
        $hashtags = $this->getTrendingHashtags(10);
        if (empty($hashtags)) {
            return ['#Healthy', '#Nutrition', '#Recettes', '#Durable'];
        }
        return array_map(function($h) { return '#' . $h['nom']; }, $hashtags);
    }

    public function getSuggestions($user_id)
    {
        $db = config::getConnexion();
        $this->initialiserTableFollows();
        $this->initialiserTableBlocks();
        
        $hasAvatar = $this->utilisateurAColonneAvatar();
        $avatarSelect = $hasAvatar ? "u.avatar," : "NULL AS avatar,";

        $sql = "SELECT 
                    u.id, 
                    u.nom, 
                    u.email,
                    {$avatarSelect}
                    (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers_count,
                    (SELECT COUNT(*) FROM publication WHERE auteur = u.nom) as recipes_count
                FROM utilisateurs u 
                WHERE u.role != 'admin' 
                AND u.id != :user_id 
                AND u.id NOT IN (SELECT following_id FROM follows WHERE follower_id = :user_id)
                AND u.id NOT IN (SELECT blocked_user_id FROM blocks WHERE user_id = :user_id)
                AND u.id NOT IN (SELECT user_id FROM blocks WHERE blocked_user_id = :user_id)
                ORDER BY (SELECT COUNT(*) FROM follows WHERE following_id = u.id) DESC, u.nom ASC
                LIMIT 5";
        
        try {
            $req = $db->prepare($sql);
            $req->execute(['user_id' => $user_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $controller = new RecetteController();
    $authController = new AuthController();
    $action = $_GET['action'] ?? '';
    $utilisateur = $_SESSION['utilisateur'] ?? null;
    $userId = (int) ($utilisateur['id'] ?? 0);
    $accepteJson = (($_GET['format'] ?? '') === 'json')
        || (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
        || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest');

    $repondreJson = function ($statusCode, $payload) {
        http_response_code((int) $statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit();
    };

    if ($authController->estAdmin($utilisateur)) {
        if ($accepteJson) {
            $repondreJson(403, ['success' => false, 'error' => 'frontoffice_forbidden_for_admin']);
        }
        header('Location: ../VIEW/backoffice.php');
        exit();
    }

    if ($userId <= 0) {
        if ($accepteJson) {
            $repondreJson(401, ['success' => false, 'error' => 'not_authenticated']);
        }
        header('Location: ../VIEW/auth.php');
        exit();
    }

    $recetteId = (int) ($_POST['recette_id'] ?? $_GET['recette_id'] ?? 0);
    $retour = trim((string) ($_POST['return_to'] ?? $_GET['return_to'] ?? '../VIEW/fil-recettes.php'));
    if ($retour === '' || strpos($retour, 'http') === 0) {
        $retour = '../VIEW/fil-recettes.php';
    }

    if ($recetteId <= 0) {
        if ($accepteJson) {
            $repondreJson(400, ['success' => false, 'error' => 'invalid_recipe']);
        }
        header('Location: ' . $retour . '?error=invalid_recipe');
        exit();
    }

    if ($action === 'add_favori') {
        $controller->ajouterFavori($userId, $recetteId);
        if ($accepteJson) {
            $repondreJson(200, ['success' => true, 'is_favorite' => true]);
        }
        header('Location: ' . $retour . '?success=favori_added');
        exit();
    }

    if ($action === 'remove_favori') {
        $controller->supprimerFavori($userId, $recetteId);
        if ($accepteJson) {
            $repondreJson(200, ['success' => true, 'is_favorite' => false]);
        }
        header('Location: ' . $retour . '?success=favori_removed');
        exit();
    }

    if ($action === 'toggle_favori') {
        $idsFavoris = $controller->recupererIdsFavoris($userId);
        if (in_array($recetteId, $idsFavoris, true)) {
            $controller->supprimerFavori($userId, $recetteId);
            if ($accepteJson) {
                $repondreJson(200, ['success' => true, 'is_favorite' => false]);
            }
            header('Location: ' . $retour . '?success=favori_removed');
        } else {
            $controller->ajouterFavori($userId, $recetteId);
            if ($accepteJson) {
                $repondreJson(200, ['success' => true, 'is_favorite' => true]);
            }
            header('Location: ' . $retour . '?success=favori_added');
        }
        exit();
    }

    header('Location: ' . $retour);
    exit();
}
?>
