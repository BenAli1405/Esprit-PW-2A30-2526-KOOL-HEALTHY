<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/Participation.php';

class ParticipationController
{
    public function listeParticipations(): array
    {
        $db = config::getConnexion();
        $sql = "SELECT p.*, u.nom AS utilisateur_nom, d.titre AS defi_titre, d.points AS defi_points
                FROM participations p
                LEFT JOIN utilisateurs u ON u.id = p.utilisateur_id
                LEFT JOIN defis d ON d.id = p.defi_id
                ORDER BY p.created_at DESC";
        return $db->query($sql)->fetchAll();
    }

    public function participer(int $utilisateur_id, int $defi_id): bool
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "INSERT IGNORE INTO participations (utilisateur_id, defi_id, progression, termine, points_gagnes)
                 VALUES (:uid, :did, 0, 0, 0)"
            );
            return $req->execute(['uid' => $utilisateur_id, 'did' => $defi_id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function classement(): array
    {
        $db = config::getConnexion();
        $sql = "SELECT u.id, u.nom,
                       COALESCE(SUM(p.points_gagnes),0) AS total_points,
                       COALESCE(SUM(p.termine),0) AS defis_completes
                FROM utilisateurs u
                LEFT JOIN participations p ON p.utilisateur_id = u.id
                GROUP BY u.id, u.nom
                ORDER BY total_points DESC
                LIMIT 10";
        $rows = $db->query($sql)->fetchAll();
        foreach ($rows as $i => &$row) {
            $row['rang'] = $i + 1;
        }
        return $rows;
    }

    public function ajouterParticipation(Participation $participation): bool
    {
        $db = config::getConnexion();
        
        // Vérifier s'il y a déjà une participation EN COURS
        $check = $db->prepare("SELECT COUNT(*) FROM participations WHERE utilisateur_id = :uid AND defi_id = :did AND termine = 0");
        $check->execute([
            'uid' => $participation->getUtilisateurId(),
            'did' => $participation->getDefiId()
        ]);
        if ($check->fetchColumn() > 0) {
            throw new PDOException("Duplicate active participation", 23000);
        }

        $sql = "INSERT INTO participations (utilisateur_id, defi_id, progression, termine, points_gagnes)
                VALUES (:uid, :did, :progression, :termine, :points_gagnes)";
        $req = $db->prepare($sql);
        return $req->execute([
            'uid' => $participation->getUtilisateurId(),
            'did' => $participation->getDefiId(),
            'progression' => $participation->getProgression(),
            'termine' => $participation->getTermine() ? 1 : 0,
            'points_gagnes' => $participation->getPointsGagnes(),
        ]);
    }

    public function modifierParticipation(int $id, array $data): bool
    {
        $db = config::getConnexion();
        
        // Vérifier s'il y a déjà une participation EN COURS autre que l'actuelle si on veut définir termine = 0
        if (empty($data['termine'])) {
            $check = $db->prepare("SELECT COUNT(*) FROM participations WHERE utilisateur_id = :uid AND defi_id = :did AND termine = 0 AND id != :id");
            $check->execute([
                'uid' => $data['utilisateur_id'],
                'did' => $data['defi_id'],
                'id' => $id
            ]);
            if ($check->fetchColumn() > 0) {
                throw new PDOException("Duplicate active participation", 23000);
            }
        }

        $sql = "UPDATE participations SET utilisateur_id = :uid, defi_id = :did, progression = :progression, termine = :termine, points_gagnes = :points_gagnes WHERE id = :id";
        $req = $db->prepare($sql);
        return $req->execute([
            'id' => $id,
            'uid' => $data['utilisateur_id'],
            'did' => $data['defi_id'],
            'progression' => $data['progression'],
            'termine' => $data['termine'] ? 1 : 0,
            'points_gagnes' => $data['points_gagnes'],
        ]);
    }

    public function supprimerParticipation(int $id): bool
    {
        $db = config::getConnexion();
        $req = $db->prepare("DELETE FROM participations WHERE id = :id");
        return $req->execute(['id' => $id]);
    }

    public function trouverParticipation(int $id): ?array
    {
        $db = config::getConnexion();
        $req = $db->prepare("SELECT p.*, u.nom AS utilisateur_nom, d.titre AS defi_titre FROM participations p LEFT JOIN utilisateurs u ON u.id = p.utilisateur_id LEFT JOIN defis d ON d.id = p.defi_id WHERE p.id = :id LIMIT 1");
        $req->execute(['id' => $id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    public function listeUtilisateurs(): array
    {
        $db = config::getConnexion();
        $stmt = $db->query("SELECT id, nom FROM utilisateurs ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function listeDefis(): array
    {
        $db = config::getConnexion();
        $stmt = $db->query("SELECT id, titre FROM defis ORDER BY titre ASC");
        return $stmt->fetchAll();
    }
}

// ---- Routage direct ----
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $controller = new ParticipationController();
    $action     = $_GET['action'] ?? '';

    if ($action === 'participer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $defi_id = (int) ($_POST['defi_id'] ?? 0);
        $db = config::getConnexion();
        $defaultUserId = (int) $db->query("SELECT id FROM utilisateurs ORDER BY id ASC LIMIT 1")->fetchColumn();
        if ($defaultUserId <= 0) {
            $db->exec("INSERT INTO utilisateurs (nom, email, role, mot_de_passe, created_at) VALUES ('Visiteur', 'visiteur@local', 'utilisateur', '', NOW())");
            $defaultUserId = (int) $db->lastInsertId();
        }
        $controller->participer($defaultUserId, $defi_id);
        header('Location: ../VIEW/gamification.php?success=participating');
        exit();
    }

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $utilisateur_id = (int) ($_POST['utilisateur_id'] ?? 0);
        $defi_id = (int) ($_POST['defi_id'] ?? 0);
        $progression = max(0, min(100, (int) ($_POST['progression'] ?? 0)));
        $termine = isset($_POST['termine']) ? 1 : 0;
        $points_gagnes = max(0, (int) ($_POST['points_gagnes'] ?? 0));

        $participation = new Participation($utilisateur_id, $defi_id, $progression, $termine, $points_gagnes);
        
        try {
            $controller->ajouterParticipation($participation);
            header('Location: ../VIEW/gamification.php?success=participation_added');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                header('Location: ../VIEW/gamification.php?error=duplicate_participation');
            } else {
                header('Location: ../VIEW/gamification.php?error=db_error');
            }
        }
        exit();
    }

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        $utilisateur_id = (int) ($_POST['utilisateur_id'] ?? 0);
        $defi_id = (int) ($_POST['defi_id'] ?? 0);
        $progression = max(0, min(100, (int) ($_POST['progression'] ?? 0)));
        $termine = isset($_POST['termine']) ? 1 : 0;
        $points_gagnes = max(0, (int) ($_POST['points_gagnes'] ?? 0));

        try {
            $controller->modifierParticipation($id, [
                'utilisateur_id' => $utilisateur_id,
                'defi_id' => $defi_id,
                'progression' => $progression,
                'termine' => $termine,
                'points_gagnes' => $points_gagnes,
            ]);
            header('Location: ../VIEW/gamification.php?success=participation_edited');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                header('Location: ../VIEW/gamification.php?error=duplicate_participation');
            } else {
                header('Location: ../VIEW/gamification.php?error=db_error');
            }
        }
        exit();
    }

    if ($action === 'delete' && isset($_GET['id'])) {
        $controller->supprimerParticipation((int) $_GET['id']);
        header('Location: ../VIEW/gamification.php?success=participation_deleted');
        exit();
    }

    header('Location: ../VIEW/gamification.php');
    exit();
}
?>
