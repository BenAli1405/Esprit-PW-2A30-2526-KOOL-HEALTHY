<?php
// ========== USER CONTROLLER ==========
require_once __DIR__ . '/../MODEL/User.php';

class UserC {
    
    // Get all users
    public static function getAllUsers() {
        return User::getAll();
    }

    // Get user by ID
    public static function getUserById($id) {
        return User::getById($id);
    }

    // Get user by email
    public static function getUserByEmail($email) {
        return User::getByEmail($email);
    }

    // Create new user
    public static function createUser($nom, $email) {
        // Validation
        if (empty($nom) || empty($email)) {
            return ['success' => false, 'message' => 'Nom et email requis'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide'];
        }
        
        if (self::getUserByEmail($email) !== null) {
            return ['success' => false, 'message' => 'Email déjà utilisé'];
        }
        
        $newId = User::create($nom, $email);
        return ['success' => true, 'id' => $newId, 'message' => 'Utilisateur créé'];
    }

    // Update user
    public static function updateUser($id, $nom, $email, $statut) {
        if (empty($nom) || empty($email)) {
            return ['success' => false, 'message' => 'Données requises'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide'];
        }
        
        $success = User::update($id, $nom, $email, $statut);
        return ['success' => $success, 'message' => $success ? 'Utilisateur mis à jour' : 'Utilisateur non trouvé'];
    }

    // Toggle user block status
    public static function toggleBlockUser($id, $block = true) {
        $success = User::toggleBlock($id, $block);
        $status = $block ? 'bloqué' : 'activé';
        return ['success' => $success, 'message' => $success ? "Utilisateur $status" : 'Utilisateur non trouvé'];
    }

    // Delete user
    public static function deleteUser($id) {
        $success = User::delete($id);
        return ['success' => $success, 'message' => $success ? 'Utilisateur supprimé' : 'Échec de la suppression'];
    }

    // Get user with activity info
    public static function getUserInfo($id) {
        $user = self::getUserById($id);
        if ($user) {
            $user['recipeCount'] = User::getRecipeCount($id);
            $user['reviewCount'] = User::getReviewCount($id);
        }
        return $user;
    }

    // Get all users with activity info
    public static function getAllUsersWithInfo() {
        $users = self::getAllUsers();
        foreach ($users as &$user) {
            $user['recipeCount'] = User::getRecipeCount($user['id']);
            $user['reviewCount'] = User::getReviewCount($user['id']);
        }
        return $users;
    }

    // Get active users count
    public static function getActiveUsersCount() {
        $users = self::getAllUsers();
        return count(array_filter($users, function($u) { 
            return $u['statut'] === 'actif'; 
        }));
    }

    // Get blocked users count
    public static function getBlockedUsersCount() {
        $users = self::getAllUsers();
        return count(array_filter($users, function($u) { 
            return $u['statut'] === 'bloque'; 
        }));
    }
}
?>
