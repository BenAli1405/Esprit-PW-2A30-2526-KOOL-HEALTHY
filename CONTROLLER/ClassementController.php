<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/Classement.php';

class ClassementController
{
    public function listeClassement(): array
    {
        $db = config::getConnexion();
        $sql = "SELECT c.*, u.nom AS utilisateur_nom
                FROM classement c
                LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
                ORDER BY c.rang ASC";
        return $db->query($sql)->fetchAll();
    }

    public function listeClassementDepuisParticipations(): array
    {
        $db = config::getConnexion();
        $sql = "SELECT u.id, u.nom AS utilisateur_nom,
                       COALESCE(SUM(p.points_gagnes),0) AS points_total,
                       COALESCE(SUM(p.termine),0) AS defis_completes
                FROM utilisateurs u
                LEFT JOIN participations p ON p.utilisateur_id = u.id
                GROUP BY u.id, u.nom
                ORDER BY points_total DESC, u.nom ASC";
        $rows = $db->query($sql)->fetchAll();
        
        // Ajouter le rang
        foreach ($rows as $i => &$row) {
            $row['rang'] = $i + 1;
        }
        
        return $rows;
    }

    public function obtenirClassementParUtilisateur(int $utilisateur_id): ?array
    {
        $db = config::getConnexion();
        $sql = "SELECT c.*, u.nom AS utilisateur_nom
                FROM classement c
                LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
                WHERE c.utilisateur_id = :uid";
        $req = $db->prepare($sql);
        $req->execute(['uid' => $utilisateur_id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    public function mettreAJourClassement(int $utilisateur_id): bool
    {
        $db = config::getConnexion();
        
        // Calculer les points totaux et défis complétés
        $sql = "SELECT COALESCE(SUM(p.points_gagnes),0) AS total_points,
                       COALESCE(SUM(p.termine),0) AS defis_completes
                FROM participations p
                WHERE p.utilisateur_id = :uid";
        $req = $db->prepare($sql);
        $req->execute(['uid' => $utilisateur_id]);
        $stats = $req->fetch();
        
        // Vérifier si une entrée existe
        $check = $db->prepare("SELECT id FROM classement WHERE utilisateur_id = :uid");
        $check->execute(['uid' => $utilisateur_id]);
        $exists = $check->fetch();
        
        if ($exists) {
            // Mettre à jour
            $updateSql = "UPDATE classement 
                         SET points_total = :points, 
                             defis_completes = :defis,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE utilisateur_id = :uid";
            $updateReq = $db->prepare($updateSql);
            return $updateReq->execute([
                'points' => $stats['total_points'],
                'defis' => $stats['defis_completes'],
                'uid' => $utilisateur_id
            ]);
        } else {
            // Insérer
            $insertSql = "INSERT INTO classement (utilisateur_id, rang, points_total, defis_completes, updated_at)
                         VALUES (:uid, 0, :points, :defis, CURRENT_TIMESTAMP)";
            $insertReq = $db->prepare($insertSql);
            return $insertReq->execute([
                'uid' => $utilisateur_id,
                'points' => $stats['total_points'],
                'defis' => $stats['defis_completes']
            ]);
        }
    }

    public function recalculerClassementComplet(): bool
    {
        $db = config::getConnexion();
        
        try {
            // Récupérer tous les utilisateurs avec leurs stats
            $sql = "SELECT u.id, u.nom,
                           COALESCE(SUM(p.points_gagnes),0) AS total_points,
                           COALESCE(SUM(p.termine),0) AS defis_completes
                    FROM utilisateurs u
                    LEFT JOIN participations p ON p.utilisateur_id = u.id
                    GROUP BY u.id, u.nom
                    ORDER BY total_points DESC";
            $results = $db->query($sql)->fetchAll();
            
            // Vider la table classement
            $db->query("TRUNCATE TABLE classement");
            
            // Réinsérer les données avec les nouveaux rangs
            $stmt = $db->prepare("INSERT INTO classement (utilisateur_id, rang, points_total, defis_completes, updated_at)
                                 VALUES (:uid, :rang, :points, :defis, CURRENT_TIMESTAMP)");
            
            foreach ($results as $i => $row) {
                $stmt->execute([
                    'uid' => $row['id'],
                    'rang' => $i + 1,
                    'points' => $row['total_points'],
                    'defis' => $row['defis_completes']
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function supprimerUtilisateurClassement(int $utilisateur_id): bool
    {
        $db = config::getConnexion();
        $req = $db->prepare("DELETE FROM classement WHERE utilisateur_id = :uid");
        return $req->execute(['uid' => $utilisateur_id]);
    }
}
?>
