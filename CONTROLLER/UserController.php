<?php

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/RecetteController.php";
require_once __DIR__ . "/AuthController.php";

class UserController
{
    private $recetteController;

    private function selectUserColumns($db)
    {
        $columns = 'id, nom, email, role, poids, taille, imc, objectif, created_at';
        $stmt = $db->prepare("SHOW COLUMNS FROM utilisateurs LIKE 'avatar'");
        $stmt->execute();
        if ($stmt->fetchColumn() !== false) {
            $columns .= ', avatar';
        }

        return $columns;
    }

    public function __construct()
    {
        $this->recetteController = new RecetteController();
    }

    public function getUserById($user_id)
    {
        $db = config::getConnexion();
        try {
            $sql = 'SELECT ' . $this->selectUserColumns($db) . ' FROM utilisateurs WHERE id = :id';
            $req = $db->prepare($sql);
            $req->execute(['id' => $user_id]);
            return $req->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserByNom($nom)
    {
        $db = config::getConnexion();
        try {
            $sql = 'SELECT ' . $this->selectUserColumns($db) . ' FROM utilisateurs WHERE nom = :nom';
            $req = $db->prepare($sql);
            $req->execute(['nom' => $nom]);
            return $req->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getAllUsers()
    {
        $db = config::getConnexion();
        try {
            $sql = 'SELECT ' . $this->selectUserColumns($db) . " FROM utilisateurs WHERE role != 'admin' ORDER BY nom ASC";
            $req = $db->query($sql);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function searchUsers($search)
    {
        $db = config::getConnexion();
        try {
            $sql = 'SELECT ' . $this->selectUserColumns($db) . " FROM utilisateurs 
                    WHERE role != 'admin' AND (nom LIKE :search OR email LIKE :search) 
                    ORDER BY nom ASC LIMIT 20";
            $req = $db->prepare($sql);
            $search = '%' . $search . '%';
            $req->execute(['search' => $search]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}

// Handle follow/unfollow actions
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $recetteController = new RecetteController();
    $authController = new AuthController();
    $userController = new UserController();
    
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

    // Action: get_user (récupérer les infos d'un utilisateur)
    if ($action === 'get_user') {
        $getUserId = (int) ($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
        if ($getUserId <= 0) {
            if ($accepteJson) {
                $repondreJson(400, ['success' => false, 'error' => 'user_id required']);
            }
            die('user_id required');
        }
        
        $user = $userController->getUserById($getUserId);
        if ($accepteJson) {
            if (!$user) {
                $repondreJson(404, ['success' => false, 'error' => 'User not found']);
            }

            $isSelf = ($getUserId === $userId);
            $isFollowing = !$isSelf ? $recetteController->isFollowing($userId, $getUserId) : false;
            $isBlocked = !$isSelf ? $recetteController->isBlocked($userId, $getUserId) : false;
            $isBlockedBy = !$isSelf ? $recetteController->isBlocked($getUserId, $userId) : false;

            $repondreJson(200, array_merge($user, [
                'success' => true,
                'is_self' => $isSelf,
                'is_following' => $isFollowing,
                'is_blocked' => $isBlocked,
                'is_blocked_by' => $isBlockedBy,
                'can_follow' => !$isSelf && !$isBlocked && !$isBlockedBy,
                'can_block' => !$isSelf
            ]));
        }
        die('User not found');
    }

    $targetUserId = (int) ($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
    $retour = trim((string) ($_POST['return_to'] ?? $_GET['return_to'] ?? '../VIEW/fil-recettes.php'));
    if ($retour === '' || strpos($retour, 'http') === 0) {
        $retour = '../VIEW/fil-recettes.php';
    }

    if ($targetUserId <= 0) {
        if ($accepteJson) {
            $repondreJson(400, ['success' => false, 'error' => 'invalid_user']);
        }
        header('Location: ' . $retour . '?error=invalid_user');
        exit();
    }

    if ($action === 'follow') {
        if ($targetUserId === $userId) {
            if ($accepteJson) {
                $repondreJson(400, ['success' => false, 'error' => 'cannot_follow_self']);
            }
            header('Location: ' . $retour . '?error=cannot_follow_self');
            exit();
        }
        $recetteController->follow($userId, $targetUserId);
        if ($accepteJson) {
            $repondreJson(200, [
                'success' => true,
                'is_following' => true,
                'is_blocked' => false,
                'is_blocked_by' => $recetteController->isBlocked($targetUserId, $userId)
            ]);
        }
        header('Location: ' . $retour . '?success=user_followed');
        exit();
    }

    if ($action === 'unfollow') {
        if ($targetUserId === $userId) {
            if ($accepteJson) {
                $repondreJson(400, ['success' => false, 'error' => 'cannot_unfollow_self']);
            }
            header('Location: ' . $retour . '?error=cannot_unfollow_self');
            exit();
        }
        $recetteController->unfollow($userId, $targetUserId);
        if ($accepteJson) {
            $repondreJson(200, [
                'success' => true,
                'is_following' => false,
                'is_blocked' => false,
                'is_blocked_by' => $recetteController->isBlocked($targetUserId, $userId)
            ]);
        }
        header('Location: ' . $retour . '?success=user_unfollowed');
        exit();
    }

    if ($action === 'toggle_follow') {
        if ($recetteController->isFollowing($userId, $targetUserId)) {
            $recetteController->unfollow($userId, $targetUserId);
            if ($accepteJson) {
                $repondreJson(200, ['success' => true, 'is_following' => false]);
            }
            header('Location: ' . $retour . '?success=user_unfollowed');
        } else {
            $recetteController->follow($userId, $targetUserId);
            if ($accepteJson) {
                $repondreJson(200, ['success' => true, 'is_following' => true]);
            }
            header('Location: ' . $retour . '?success=user_followed');
        }
        exit();
    }

    if ($action === 'block') {
        if ($targetUserId === $userId) {
            if ($accepteJson) {
                $repondreJson(400, ['success' => false, 'error' => 'cannot_block_self']);
            }
            header('Location: ' . $retour . '?error=cannot_block_self');
            exit();
        }
        $recetteController->block($userId, $targetUserId);
        if ($accepteJson) {
            $repondreJson(200, [
                'success' => true,
                'is_blocked' => true,
                'is_following' => $recetteController->isFollowing($userId, $targetUserId)
            ]);
        }
        header('Location: ' . $retour . '?success=user_blocked');
        exit();
    }

    if ($action === 'unblock') {
        if ($targetUserId === $userId) {
            if ($accepteJson) {
                $repondreJson(400, ['success' => false, 'error' => 'cannot_unblock_self']);
            }
            header('Location: ' . $retour . '?error=cannot_unblock_self');
            exit();
        }
        $recetteController->unblock($userId, $targetUserId);
        if ($accepteJson) {
            $repondreJson(200, [
                'success' => true,
                'is_blocked' => false,
                'is_following' => $recetteController->isFollowing($userId, $targetUserId)
            ]);
        }
        header('Location: ' . $retour . '?success=user_unblocked');
        exit();
    }

    if ($action === 'toggle_block') {
        if ($targetUserId === $userId) {
            if ($accepteJson) {
                $repondreJson(400, ['success' => false, 'error' => 'cannot_block_self']);
            }
            header('Location: ' . $retour . '?error=cannot_block_self');
            exit();
        }
        if ($recetteController->isBlocked($userId, $targetUserId)) {
            $recetteController->unblock($userId, $targetUserId);
            if ($accepteJson) {
                $repondreJson(200, [
                    'success' => true,
                    'is_blocked' => false,
                    'is_following' => $recetteController->isFollowing($userId, $targetUserId)
                ]);
            }
            header('Location: ' . $retour . '?success=user_unblocked');
        } else {
            $recetteController->block($userId, $targetUserId);
            if ($accepteJson) {
                $repondreJson(200, [
                    'success' => true,
                    'is_blocked' => true,
                    'is_following' => $recetteController->isFollowing($userId, $targetUserId)
                ]);
            }
            header('Location: ' . $retour . '?success=user_blocked');
        }
        exit();
    }

    header('Location: ' . $retour);
    exit();
}
?>
