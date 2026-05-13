<?php
// ========== USER MODEL ==========
class User {
    private $id;
    private $nom;
    private $email;
    private $dateInscription;
    private $statut;
    private $recettesCrees;
    private $avisDonnes;

    // Constructor
    public function __construct($id = null, $nom = '', $email = '', $dateInscription = '', 
                                $statut = 'actif', $recettesCrees = [], $avisDonnes = []) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->dateInscription = $dateInscription;
        $this->statut = $statut;
        $this->recettesCrees = $recettesCrees;
        $this->avisDonnes = $avisDonnes;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getDateInscription() { return $this->dateInscription; }
    public function getStatut() { return $this->statut; }
    public function getRecettesCrees() { return $this->recettesCrees; }
    public function getAvisDonnes() { return $this->avisDonnes; }

    // Setters
    public function setNom($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setStatut($statut) { $this->statut = $statut; }

    // Static database
    private static $usersDB = [
        ['id' => 1, 'nom' => 'Admin Kool', 'email' => 'admin@koolhealthy.com', 'dateInscription' => '2024-01-01', 'statut' => 'actif', 'recettesCrees' => [1, 2, 4], 'avisDonnes' => []],
        ['id' => 2, 'nom' => 'Sophie Martin', 'email' => 'sophie@email.com', 'dateInscription' => '2024-02-15', 'statut' => 'actif', 'recettesCrees' => [3], 'avisDonnes' => [1, 3]],
        ['id' => 3, 'nom' => 'Marie Dubois', 'email' => 'marie@email.com', 'dateInscription' => '2024-03-10', 'statut' => 'actif', 'recettesCrees' => [5], 'avisDonnes' => [2]],
        ['id' => 4, 'nom' => 'Thomas Leroy', 'email' => 'thomas@email.com', 'dateInscription' => '2024-04-05', 'statut' => 'bloque', 'recettesCrees' => [], 'avisDonnes' => []]
    ];

    private static $nextUserId = 5;

    // Get all users
    public static function getAll() {
        return self::$usersDB;
    }

    // Get user by ID
    public static function getById($id) {
        foreach (self::$usersDB as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    // Get user by email
    public static function getByEmail($email) {
        foreach (self::$usersDB as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    // Create user
    public static function create($nom, $email, $dateInscription = null) {
        if ($dateInscription === null) {
            $dateInscription = date('Y-m-d');
        }
        $newId = self::$nextUserId++;
        self::$usersDB[] = [
            'id' => $newId,
            'nom' => $nom,
            'email' => $email,
            'dateInscription' => $dateInscription,
            'statut' => 'actif',
            'recettesCrees' => [],
            'avisDonnes' => []
        ];
        return $newId;
    }

    // Update user
    public static function update($id, $nom, $email, $statut) {
        foreach (self::$usersDB as &$user) {
            if ($user['id'] == $id) {
                $user['nom'] = $nom;
                $user['email'] = $email;
                $user['statut'] = $statut;
                return true;
            }
        }
        return false;
    }

    // Toggle user status (block/unblock)
    public static function toggleBlock($id, $block = true) {
        foreach (self::$usersDB as &$user) {
            if ($user['id'] == $id) {
                $user['statut'] = $block ? 'bloque' : 'actif';
                return true;
            }
        }
        return false;
    }

    // Delete user
    public static function delete($id) {
        self::$usersDB = array_filter(self::$usersDB, function($user) use ($id) {
            return $user['id'] != $id;
        });
        return true;
    }

    // Count total recipes per user
    public static function getRecipeCount($id) {
        $user = self::getById($id);
        return $user ? count($user['recettesCrees']) : 0;
    }

    // Count total reviews per user
    public static function getReviewCount($id) {
        $user = self::getById($id);
        return $user ? count($user['avisDonnes']) : 0;
    }
}
?>
