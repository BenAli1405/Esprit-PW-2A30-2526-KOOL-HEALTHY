<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../MODEL/Repas.php';

class RepasController
{
    // ─── Lister les repas d'un plan ───────────────────────────────────────────
    public function listeRepas($planID)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "SELECT * FROM repas WHERE planID = :pid ORDER BY date ASC, type_repas ASC"
            );
            $req->execute(['pid' => $planID]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Obtenir un repas ────────────────────────────────────────────────────
    public function obtenirRepas($id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT * FROM repas WHERE id = :id");
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Ajouter un repas ────────────────────────────────────────────────────
    public function ajouterRepas(Repas $repas)
    {
        $db  = config::getConnexion();
        $sql = "INSERT INTO repas (planID, recette, date, type_repas, statut)
                VALUES (:pid, :recette, :date, :type, :statut)";
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'pid'    => $repas->getPlanID(),
                'recette'=> $repas->getRecette(),
                'date'   => $repas->getDate(),
                'type'   => $repas->getTypeRepas(),
                'statut' => $repas->getStatut(),
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Modifier le statut d'un repas ───────────────────────────────────────
    public function changerStatut($id, $statut)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("UPDATE repas SET statut = :statut WHERE id = :id");
            $req->execute(['statut' => $statut, 'id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Supprimer un repas ──────────────────────────────────────────────────
    public function supprimerRepas($id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM repas WHERE id = :id");
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ─── Suivre les calories d'une journée ───────────────────────────────────
    public function suivreCalories($planID, $date)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "SELECT type_repas, recette, statut
                 FROM repas
                 WHERE planID = :pid AND date = :date
                 ORDER BY FIELD(type_repas, 'petit-déjeuner','déjeuner','dîner','collation')"
            );
            $req->execute(['pid' => $planID, 'date' => $date]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
