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

    public function getByPlanIds(array $planIds)
    {
        if (empty($planIds)) return [];
        $placeholders = implode(',', array_fill(0, count($planIds), '?'));
        $sql = 'SELECT * FROM repas WHERE plan_id IN (' . $placeholders . ') ORDER BY date, heure_prevue';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($planIds);
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

    public function generateForPlan(int $planId, int $duree): bool
    {
        if ($duree < 1) $duree = 7;
        $types = ['petit_dejeuner', 'dejeuner', 'diner'];
        $now = new DateTime();
        try {
            $this->pdo->beginTransaction();
            $insert = $this->pdo->prepare('INSERT INTO repas (plan_id, nom_recette, date, type_repas, statut, calories_consommees, heure_prevue, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            for ($i = 0; $i < $duree; $i++) {
                $date = $now->modify('+'.($i===0?0:1).' day')->format('Y-m-d');
                foreach ($types as $t) {
                    $name = ucfirst(str_replace('_', ' ', $t)) . ' - Suggestion';
                    $cal = ($t === 'petit_dejeuner') ? 350 : (($t === 'dejeuner') ? 700 : 600);
                    $heure = ($t === 'petit_dejeuner') ? '08:00' : (($t === 'dejeuner') ? '12:30' : '19:00');
                    $insert->execute([$planId, $name, $date, $t, 'prevu', $cal, $heure, 'Suggestion générée automatiquement']);
                }
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }
}
