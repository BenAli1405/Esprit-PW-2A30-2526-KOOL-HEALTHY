<?php

require_once __DIR__ . '/../config.php';

class BackofficeController
{
    private function scalar($sql)
    {
        $db = config::getConnexion();
        $stmt = $db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function statsDashboard()
    {
        $db = config::getConnexion();
        try {
            $totalUtilisateurs = $this->scalar("SELECT COUNT(*) FROM utilisateurs");
            $nouveaux30j = $this->scalar("SELECT COUNT(*) FROM utilisateurs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $totalRecettes = $this->scalar("SELECT COUNT(*) FROM recettes");
            $totalFavoris = $this->scalar("SELECT COUNT(*) FROM favoris");
            $totalAdmins = $this->scalar("SELECT COUNT(*) FROM utilisateurs WHERE LOWER(role) = 'admin'");
            $totalNormaux = $this->scalar("SELECT COUNT(*) FROM utilisateurs WHERE LOWER(role) IN ('normal', 'utilisateur') OR role IS NULL OR role = ''");

            $scoreIa = 0;
            if ($totalUtilisateurs > 0) {
                $sqlScore = "SELECT AVG(IFNULL(p.besoins_caloriques, 0)) FROM profil_nutritif p";
                $avg = (float) $db->query($sqlScore)->fetchColumn();
                $scoreIa = (int) round(min(100, max(0, $avg / 30)));
            }

            return [
                'total_utilisateurs' => $totalUtilisateurs,
                'nouveaux_30j' => $nouveaux30j,
                'total_recettes' => $totalRecettes,
                'total_favoris' => $totalFavoris,
                'total_admins' => $totalAdmins,
                'total_normaux' => $totalNormaux,
                'score_ia' => $scoreIa
            ];
        } catch (Exception $e) {
            die('Erreur stats dashboard: ' . $e->getMessage());
        }
    }

    public function utilisateursParMois($mois = 6)
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS periode, COUNT(*) AS total
                    FROM utilisateurs
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :mois MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY periode ASC";
            $req = $db->prepare($sql);
            $req->execute(['mois' => $mois]);

            $raw = $req->fetchAll();
            $map = [];
            foreach ($raw as $r) {
                $map[$r['periode']] = (int) $r['total'];
            }

            $labels = [];
            $values = [];
            for ($i = $mois - 1; $i >= 0; $i--) {
                $periode = date('Y-m', strtotime("-{$i} month"));
                $labels[] = date('M Y', strtotime($periode . '-01'));
                $values[] = $map[$periode] ?? 0;
            }

            return ['labels' => $labels, 'values' => $values];
        } catch (Exception $e) {
            die('Erreur stats utilisateurs/mois: ' . $e->getMessage());
        }
    }

    public function repartitionRoles()
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT role, COUNT(*) AS total
                    FROM utilisateurs
                    GROUP BY role
                    ORDER BY total DESC";
            $req = $db->query($sql);
            $rows = $req->fetchAll();

            $labels = [];
            $values = [];
            foreach ($rows as $r) {
                $labels[] = $r['role'] ?: 'non defini';
                $values[] = (int) $r['total'];
            }

            if (empty($labels)) {
                $labels = ['Aucun'];
                $values = [0];
            }

            return ['labels' => $labels, 'values' => $values];
        } catch (Exception $e) {
            die('Erreur repartition roles: ' . $e->getMessage());
        }
    }

    public function utilisateursRecents($limite = 5)
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT u.nom, u.email, u.role, u.created_at,
                           p.age, p.allergies, p.besoins_caloriques
                    FROM utilisateurs u
                    LEFT JOIN profil_nutritif p ON p.utilisateur = u.id
                    ORDER BY u.created_at DESC
                    LIMIT :limite";
            $req = $db->prepare($sql);
            $req->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur utilisateurs recents: ' . $e->getMessage());
        }
    }

    public function listeUtilisateurs()
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT
                        u.id,
                        u.nom,
                        u.email,
                        u.role,
                        u.poids,
                        u.taille,
                        u.imc,
                        u.objectif,
                        u.created_at,
                        p.age,
                        p.allergies,
                        p.besoins_caloriques
                    FROM utilisateurs u
                    LEFT JOIN profil_nutritif p ON p.utilisateur = u.id
                    ORDER BY u.id DESC";

            $req = $db->query($sql);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur backoffice: ' . $e->getMessage());
        }
    }

    public function changerRoleUtilisateur($id, $role)
    {
        $db = config::getConnexion();
        try {
            $roleNormalise = strtolower(trim($role));
            if (!in_array($roleNormalise, ['admin', 'normal'], true)) {
                return false;
            }

            $sql = "UPDATE utilisateurs SET role = :role WHERE id = :id";
            $req = $db->prepare($sql);
            $req->execute([
                'role' => $roleNormalise,
                'id' => (int) $id
            ]);

            return $req->rowCount() > 0;
        } catch (Exception $e) {
            die('Erreur changement role: ' . $e->getMessage());
        }
    }

    public function supprimerUtilisateur($id)
    {
        $db = config::getConnexion();
        try {
            $sql = "DELETE FROM utilisateurs WHERE id = :id";
            $req = $db->prepare($sql);
            $req->execute(['id' => (int) $id]);
            return $req->rowCount() > 0;
        } catch (Exception $e) {
            die('Erreur suppression utilisateur: ' . $e->getMessage());
        }
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    require_once __DIR__ . '/AuthController.php';
    require_once __DIR__ . '/../MODEL/Utilisateur.php';
    require_once __DIR__ . '/../MODEL/ProfilNutritif.php';

    $controller = new BackofficeController();
    $authController = new AuthController();
    $action = $_POST['action'] ?? '';

    if ($action === 'add-user') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';
        $role = trim($_POST['role'] ?? 'normal');
        $poids = (float) ($_POST['poids'] ?? 0);
        $taille = (float) ($_POST['taille'] ?? 0);
        $objectif = trim($_POST['objectif'] ?? 'equilibre');
        $age = (int) ($_POST['age'] ?? 18);
        $allergies = trim($_POST['allergies'] ?? '');
        $besoins = (int) ($_POST['besoins_caloriques'] ?? 2000);

        if ($nom === '' || $email === '' || $mot_de_passe === '') {
            header('Location: ../VIEW/backoffice.php?tab=users&msg=add-failed');
            exit();
        }

        $utilisateur = new Utilisateur(
            $nom,
            $email,
            $mot_de_passe,
            $role,
            $poids,
            $taille,
            $objectif
        );

        $profilNutritif = new ProfilNutritif(null, $age, $allergies, $besoins);

        $ok = $authController->inscrire($utilisateur, $profilNutritif);
        header('Location: ../VIEW/backoffice.php?tab=users&msg=' . ($ok ? 'user-added' : 'add-failed'));
        exit();
    }

    if ($action === 'update-role') {
        $id = (int) ($_POST['id'] ?? 0);
        $role = trim($_POST['role'] ?? '');

        $ok = $controller->changerRoleUtilisateur($id, $role);
        header('Location: ../VIEW/backoffice.php?tab=users&msg=' . ($ok ? 'role-updated' : 'role-failed'));
        exit();
    }

    if ($action === 'delete-user') {
        $id = (int) ($_POST['id'] ?? 0);

        $ok = $controller->supprimerUtilisateur($id);
        header('Location: ../VIEW/backoffice.php?tab=users&msg=' . ($ok ? 'user-deleted' : 'delete-failed'));
        exit();
    }

    header('Location: ../VIEW/backoffice.php?tab=users&msg=invalid-action');
    exit();
}
?>
