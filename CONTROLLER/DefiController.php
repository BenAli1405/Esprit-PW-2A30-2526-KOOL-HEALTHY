<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/Defi.php';

class DefiController
{
    public function listeDefis(): array
    {
        $db = config::getConnexion();
        $sql = "SELECT d.id, d.titre, d.type, d.points, d.date_debut, d.date_fin, d.created_at,
                       COUNT(p.id) AS participants,
                       ROUND(COALESCE(AVG(p.progression), 0)) AS progression
                FROM defis d
                LEFT JOIN participations p ON p.defi_id = d.id
                GROUP BY d.id
                ORDER BY d.created_at DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public function ajouterDefi(Defi $defi): int
    {
        $db = config::getConnexion();
        $sql = "INSERT INTO defis (titre, type, points, date_debut, date_fin)
                VALUES (:titre, :type, :points, :date_debut, :date_fin)";
        $req = $db->prepare($sql);
        $req->execute([
            'titre'      => $defi->getTitre(),
            'type'       => $defi->getType(),
            'points'     => $defi->getPoints(),
            'date_debut' => $defi->getDateDebut(),
            'date_fin'   => $defi->getDateFin(),
        ]);
        return (int) $db->lastInsertId();
    }

    public function modifierDefi(int $id, array $data): bool
    {
        $db = config::getConnexion();
        $sql = "UPDATE defis SET titre=:titre, type=:type, points=:points,
                date_debut=:date_debut, date_fin=:date_fin WHERE id=:id";
        $req = $db->prepare($sql);
        return $req->execute([
            'id'         => $id,
            'titre'      => $data['titre'],
            'type'       => $data['type'],
            'points'     => $data['points'],
            'date_debut' => $data['date_debut'],
            'date_fin'   => $data['date_fin'],
        ]);
    }

    public function supprimerDefi(int $id): bool
    {
        $db = config::getConnexion();
        $req = $db->prepare("DELETE FROM defis WHERE id = :id");
        return $req->execute(['id' => $id]);
    }

    public function trouverDefi(int $id): ?array
    {
        $db = config::getConnexion();
        $req = $db->prepare("SELECT * FROM defis WHERE id = :id LIMIT 1");
        $req->execute(['id' => $id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    public function statsDefis(): array
    {
        $db = config::getConnexion();
        $total       = (int) $db->query("SELECT COUNT(*) FROM defis")->fetchColumn();
        $participants = (int) $db->query("SELECT COUNT(*) FROM participations")->fetchColumn();
        $points      = (int) $db->query("SELECT COALESCE(SUM(points_gagnes),0) FROM participations")->fetchColumn();
        return [
            'total_defis'    => $total,
            'participants'   => $participants,
            'points_distribues' => $points,
        ];
    }

    public function searchDefis(string $searchTerm, string $searchBy = 'titre'): array
    {
        $db = config::getConnexion();
        $allowedFields = ['titre', 'type', 'points'];
        
        if (!in_array($searchBy, $allowedFields)) {
            $searchBy = 'titre';
        }
        
        $sql = "SELECT d.id, d.titre, d.type, d.points, d.date_debut, d.date_fin, d.created_at,
                       COUNT(p.id) AS participants,
                       ROUND(COALESCE(AVG(p.progression), 0)) AS progression
                FROM defis d
                LEFT JOIN participations p ON p.defi_id = d.id
                WHERE d.$searchBy LIKE :search
                GROUP BY d.id
                ORDER BY d.created_at DESC";
        
        $req = $db->prepare($sql);
        $req->execute(['search' => '%' . $searchTerm . '%']);
        return $req->fetchAll();
    }
}

// ---- Routage direct ----
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $controller = new DefiController();
    $action     = $_GET['action'] ?? '';

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $titre = trim($_POST['titre'] ?? '');
        if (!$titre) {
            header('Location: ../VIEW/backoffice-gamification.php?error=titre');
            exit();
        }
        $defi = new Defi(
            $titre,
            trim($_POST['type'] ?? 'nutrition'),
            (int) ($_POST['points'] ?? 0),
            $_POST['date_debut'] ?? null,
            $_POST['date_fin']   ?? null
        );
        $controller->ajouterDefi($defi);
        header('Location: ../VIEW/backoffice-gamification.php?tab=defis&success=added');
        exit();
    }

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = (int) ($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        if (!$titre) {
            header('Location: ../VIEW/backoffice-gamification.php?error=titre');
            exit();
        }
        $controller->modifierDefi($id, [
            'titre'      => $titre,
            'type'       => trim($_POST['type'] ?? 'nutrition'),
            'points'     => (int) ($_POST['points'] ?? 0),
            'date_debut' => $_POST['date_debut'] ?? null,
            'date_fin'   => $_POST['date_fin']   ?? null,
        ]);
        header('Location: ../VIEW/backoffice-gamification.php?tab=defis&success=edited');
        exit();
    }

    if ($action === 'delete' && isset($_GET['id'])) {
        $controller->supprimerDefi((int) $_GET['id']);
        header('Location: ../VIEW/backoffice-gamification.php?tab=defis&success=deleted');
        exit();
    }

    header('Location: ../VIEW/backoffice-gamification.php');
    exit();
}
?>
