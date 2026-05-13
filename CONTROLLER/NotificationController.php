<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../MODEL/Notification.php';

class NotificationController
{
    public function listeNotifications(int $utilisateur_id): array
    {
        $db = config::getConnexion();
        $sql = "SELECT * FROM notifications WHERE utilisateur_id = :uid ORDER BY created_at DESC";
        $req = $db->prepare($sql);
        $req->execute(['uid' => $utilisateur_id]);
        return $req->fetchAll();
    }

    public function ajouterNotification(int $utilisateur_id, string $message): bool
    {
        $db = config::getConnexion();
        $sql = "INSERT INTO notifications (utilisateur_id, message) VALUES (:uid, :msg)";
        $req = $db->prepare($sql);
        return $req->execute(['uid' => $utilisateur_id, 'msg' => $message]);
    }

    public function marquerCommeLue(int $id): bool
    {
        $db = config::getConnexion();
        $sql = "UPDATE notifications SET lu = 1 WHERE id = :id";
        $req = $db->prepare($sql);
        return $req->execute(['id' => $id]);
    }

    public function nbNonLues(int $utilisateur_id): int
    {
        $db = config::getConnexion();
        $sql = "SELECT COUNT(*) FROM notifications WHERE utilisateur_id = :uid AND lu = 0";
        $req = $db->prepare($sql);
        $req->execute(['uid' => $utilisateur_id]);
        return (int) $req->fetchColumn();
    }
}

// Routage Ajax
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $controller = new NotificationController();
    $action = $_GET['action'] ?? '';

    // Utilise l'utilisateur connecté (session)
    $userId = $_SESSION['utilisateur']['id'] ?? $_SESSION['user_id'] ?? 0;
    if ($userId <= 0) {
        echo json_encode([]);
        exit();
    }

    if ($action === 'liste') {
        echo json_encode($controller->listeNotifications($userId));
        exit();
    }

    if ($action === 'marquer_lue') {
        $id = (int)($_GET['id'] ?? 0);
        echo json_encode(['success' => $controller->marquerCommeLue($id)]);
        exit();
    }

    if ($action === 'count') {
        echo json_encode(['count' => $controller->nbNonLues($userId)]);
        exit();
    }
}
?>
