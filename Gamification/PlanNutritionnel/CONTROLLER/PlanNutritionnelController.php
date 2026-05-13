<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/PlanNutritionnel.php';

class PlanNutritionnelController
{
    // ─── Lister tous les plans d'un utilisateur ──────────────────────────────
    public function listePlans($utilisateur_id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "SELECT * FROM plans_nutritionnels
                 WHERE utilisateur_id = :uid
                 ORDER BY date_debut DESC"
            );
            $req->execute(['uid' => $utilisateur_id]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Lister TOUS les plans (admin) ────────────────────────────────────────
    public function listeTousLesPlans()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query(
                "SELECT p.*, u.nom AS utilisateur_nom
                 FROM plans_nutritionnels p
                 LEFT JOIN utilisateurs u ON p.utilisateur_id = u.id
                 ORDER BY p.created_at DESC"
            );
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Obtenir un plan par ID ───────────────────────────────────────────────
    public function obtenirPlan($planID)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT * FROM plans_nutritionnels WHERE planID = :id");
            $req->execute(['id' => $planID]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Créer un plan ────────────────────────────────────────────────────────
    public function creerPlan(PlanNutritionnel $plan)
    {
        $db  = config::getConnexion();
        $sql = "INSERT INTO plans_nutritionnels
                    (nom, calories_journalieres, utilisateur_id, date_debut, date_fin, statistiques)
                VALUES
                    (:nom, :cal, :uid, :debut, :fin, :stat)";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'nom'  => $plan->getNom(),
                'cal'  => $plan->getCaloriesJournalieres(),
                'uid'  => $plan->getUtilisateurId(),
                'debut'=> $plan->getDateDebut(),
                'fin'  => $plan->getDateFin(),
                'stat' => $plan->getStatistiques(),
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Modifier un plan ─────────────────────────────────────────────────────
    public function modifierPlan(PlanNutritionnel $plan, $planID)
    {
        $db  = config::getConnexion();
        $sql = "UPDATE plans_nutritionnels
                SET nom = :nom,
                    calories_journalieres = :cal,
                    date_debut = :debut,
                    date_fin   = :fin,
                    statistiques = :stat
                WHERE planID = :id";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'nom'  => $plan->getNom(),
                'cal'  => $plan->getCaloriesJournalieres(),
                'debut'=> $plan->getDateDebut(),
                'fin'  => $plan->getDateFin(),
                'stat' => $plan->getStatistiques(),
                'id'   => $planID,
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Supprimer un plan ────────────────────────────────────────────────────
    public function supprimerPlan($planID)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM plans_nutritionnels WHERE planID = :id");
            $req->execute(['id' => $planID]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Vérifier équilibre nutritionnel (admin) ──────────────────────────────
    public function verifierEquilibre($planID)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "SELECT
                    p.calories_journalieres,
                    COUNT(r.id)            AS nb_repas,
                    SUM(CASE WHEN r.statut = 'consommé' THEN 1 ELSE 0 END) AS repas_consommes
                 FROM plans_nutritionnels p
                 LEFT JOIN repas r ON r.planID = p.planID
                 WHERE p.planID = :id
                 GROUP BY p.planID"
            );
            $req->execute(['id' => $planID]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Générer une recommandation simple ───────────────────────────────────
    public function recommandation($calories_journalieres)
    {
        if ($calories_journalieres < 1500) {
            return ['type' => 'warning', 'message' => 'Apport calorique trop bas. Augmentez à au moins 1 500 kcal/jour.'];
        } elseif ($calories_journalieres > 3000) {
            return ['type' => 'warning', 'message' => 'Apport calorique élevé. Consultez un nutritionniste.'];
        } else {
            return ['type' => 'success', 'message' => 'Apport calorique dans la plage recommandée (1 500 – 3 000 kcal/j).'];
        }
    }

    // ─── Statistiques globales (admin dashboard) ─────────────────────────────
    public function statistiquesGlobales()
    {
        $db = config::getConnexion();
        try {
            $row = $db->query(
                "SELECT
                    COUNT(*)                          AS total_plans,
                    AVG(calories_journalieres)        AS moy_calories,
                    SUM(DATEDIFF(date_fin, date_debut)) AS total_jours
                 FROM plans_nutritionnels"
            )->fetch();
            return $row;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
