<?php

include_once __DIR__ . '/Database.php';

class RepasModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all()
    {
        $stmt = $this->pdo->query(
            'SELECT r.*, p.nom as plan_nom
             FROM repas r
             LEFT JOIN plan p ON r.plan_id = p.id
             ORDER BY r.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function getByPlanId(int $planId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM repas WHERE plan_id = ? ORDER BY date, heure_prevue');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public function find(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM repas WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO repas (plan_id, nom_recette, date, type_repas, statut, calories_consommees, heure_prevue, heure_reelle, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            return $stmt->execute([
                $data['plan_id'],
                $data['nom_recette'],
                $data['date'],
                $data['type_repas'],
                $data['statut'],
                $data['calories_consommees'],
                $data['heure_prevue'],
                $data['heure_reelle'],
                $data['notes'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update(int $id, array $data)
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE repas SET plan_id = ?, nom_recette = ?, date = ?, type_repas = ?, statut = ?, calories_consommees = ?, heure_prevue = ?, heure_reelle = ?, notes = ? WHERE id = ?'
            );
            return $stmt->execute([
                $data['plan_id'],
                $data['nom_recette'],
                $data['date'],
                $data['type_repas'],
                $data['statut'],
                $data['calories_consommees'],
                $data['heure_prevue'],
                $data['heure_reelle'],
                $data['notes'],
                $id,
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM repas WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
