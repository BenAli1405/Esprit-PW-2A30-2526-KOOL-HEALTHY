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

    private function tableExiste($table)
    {
        $db = config::getConnexion();
        $stmt = $db->prepare('SHOW TABLES LIKE :table_name');
        $stmt->execute(['table_name' => $table]);
        return $stmt->fetchColumn() !== false;
    }

    public function listeRecettes()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM recettes ORDER BY date_creation DESC");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die("Erreur: " . $e->getMessage());
        }
    }

    public function mesRecettes($auteur = "Moi")
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM recettes WHERE auteur = :auteur ORDER BY date_creation DESC";
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

            $sql = "SELECT r.* FROM recettes r 
                    INNER JOIN favoris f ON r.id = f.recette_id 
                    WHERE f.user_id = :user_id 
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
        $sql = "INSERT INTO recettes (titre, temps_prep, ingredients, etapes, image, auteur, date_creation) 
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
        $sql = "UPDATE recettes SET titre=:titre, temps_prep=:temps_prep, ingredients=:ingredients, 
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
        $sql = "DELETE FROM recettes WHERE id = :id";
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
            $sql = "SELECT * FROM recettes WHERE id = :id";
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
