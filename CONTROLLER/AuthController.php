<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/Utilisateur.php';
require_once __DIR__ . '/../MODEL/ProfilNutritif.php';

class AuthController
{
    public function inscrire($utilisateur, $profilNutritif)
    {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            $check = $db->prepare("SELECT id FROM utilisateurs WHERE email = :email LIMIT 1");
            $check->execute(['email' => $utilisateur->getEmail()]);

            if ($check->fetch()) {
                $db->rollBack();
                return false;
            }

            $checkNom = $db->prepare("SELECT id FROM utilisateurs WHERE nom = :nom LIMIT 1");
            $checkNom->execute(['nom' => $utilisateur->getNom()]);

            if ($checkNom->fetch()) {
                $db->rollBack();
                return false;
            }

                $sql = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, poids, taille, imc, created_at)
                    VALUES (:nom, :email, :mot_de_passe, :role, :poids, :taille, :imc, :created_at)";
            $req = $db->prepare($sql);
            $req->execute([
                'nom' => $utilisateur->getNom(),
                'email' => $utilisateur->getEmail(),
                'mot_de_passe' => password_hash($utilisateur->getMotDePasse(), PASSWORD_DEFAULT),
                'role' => $utilisateur->getRole(),
                'poids' => $utilisateur->getPoids(),
                'taille' => $utilisateur->getTaille(),
                'imc' => $utilisateur->getImc(),
                'created_at' => $utilisateur->getCreatedAt()
            ]);

            $utilisateurId = (int) $db->lastInsertId();

            $profilSql = "INSERT INTO profil_nutritif (utilisateur, age, allergies, besoins_caloriques)
                          VALUES (:utilisateur, :age, :allergies, :besoins_caloriques)";
            $profilReq = $db->prepare($profilSql);
            $profilReq->execute([
                'utilisateur' => $utilisateurId,
                'age' => $profilNutritif->getAge(),
                'allergies' => $profilNutritif->getAllergies(),
                'besoins_caloriques' => $profilNutritif->getBesoinsCaloriques()
            ]);

            $db->commit();
            return $utilisateurId;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die('Erreur inscription: ' . $e->getMessage());
        }
    }

        public function connecter($identifiant, $mot_de_passe)
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT u.*, p.age, p.allergies, p.besoins_caloriques
                    FROM utilisateurs u
                    LEFT JOIN profil_nutritif p ON p.utilisateur = u.id
                WHERE u.nom = :identifiant OR u.email = :identifiant
                    LIMIT 1";
            $req = $db->prepare($sql);
            $req->execute(['identifiant' => $identifiant]);
            $utilisateur = $req->fetch();

            if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['utilisateur'] = [
                    'id' => $utilisateur['id'],
                    'nom' => $utilisateur['nom'],
                    'email' => $utilisateur['email'],
                    'role' => $utilisateur['role'],
                    'poids' => $utilisateur['poids'],
                    'taille' => $utilisateur['taille'],
                    'imc' => $utilisateur['imc'],
                    'age' => $utilisateur['age']
                    ,
                    'allergies' => $utilisateur['allergies'],
                    'besoins_caloriques' => $utilisateur['besoins_caloriques']
                ];
                return true;
            }

            return false;
        } catch (Exception $e) {
            die('Erreur connexion: ' . $e->getMessage());
        }
    }

    public function deconnecter()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset($_SESSION['utilisateur']);
        session_destroy();
    }

    public function utilisateurConnecte()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['utilisateur'] ?? null;
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $controller = new AuthController();
    $action = $_GET['action'] ?? '';

    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
        $role = 'utilisateur';
        $poids = (float) ($_POST['poids'] ?? 0);
        $taille = (float) ($_POST['taille'] ?? 0);
        $age = (int) ($_POST['age'] ?? 0);
        $allergies = trim($_POST['allergies'] ?? '');
        $besoins_caloriques = (int) ($_POST['besoins_caloriques'] ?? 0);

        if ($mot_de_passe !== $confirmer_mot_de_passe) {
            header('Location: ../VIEW/register.php?error=password_mismatch');
            exit();
        }

        $utilisateur = new Utilisateur(
            $nom,
            $email,
            $mot_de_passe,
            $role,
            $poids,
            $taille,
            null
        );
        $profilNutritif = new ProfilNutritif(null, $age, $allergies, $besoins_caloriques);
        $resultat = $controller->inscrire($utilisateur, $profilNutritif);

        if ($resultat === false) {
            header('Location: ../VIEW/register.php?error=register');
            exit();
        }

        header('Location: ../VIEW/auth.php?success=register');
        exit();
    }

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $identifiant = trim($_POST['nom'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';

        if ($controller->connecter($identifiant, $mot_de_passe)) {
            $utilisateurConnecte = $controller->utilisateurConnecte();
            if (($utilisateurConnecte['role'] ?? '') === 'admin') {
                header('Location: ../VIEW/backoffice.php');
            } else {
                header('Location: ../VIEW/home.php');
            }
            exit();
        }

        header('Location: ../VIEW/auth.php?error=login');
        exit();
    }

    if ($action === 'logout') {
        $controller->deconnecter();
        header('Location: ../VIEW/auth.php');
        exit();
    }

    header('Location: ../VIEW/auth.php');
    exit();
}
?>
