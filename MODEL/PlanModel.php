<?php

include_once __DIR__ . '/Database.php';

class PlanModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all()
    {
        $stmt = $this->pdo->query('SELECT * FROM plan ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM plan WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO plan (nom, objectif, utilisateur_id, duree, preference, allergies) VALUES (?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([
            $data['nom'],
            $data['objectif'],
            $data['utilisateur_id'],
            $data['duree'],
            $data['preference'],
            $data['allergies'],
        ]);
    }

    public function update(int $id, array $data)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE plan SET nom = ?, objectif = ?, utilisateur_id = ?, duree = ?, preference = ?, allergies = ? WHERE id = ?'
        );
        return $stmt->execute([
            $data['nom'],
            $data['objectif'],
            $data['utilisateur_id'],
            $data['duree'],
            $data['preference'],
            $data['allergies'],
            $id,
        ]);
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM plan WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
