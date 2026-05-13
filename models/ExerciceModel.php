<?php

require_once __DIR__ . '/../config/Database.php';

class ExerciceModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new \Database())->getConnection();
    }

    public function getAllByEntrainement(int $idEntrainement): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM exercice WHERE id_entrainement = :id_entrainement ORDER BY ordre ASC');
        $stmt->execute(['id_entrainement' => $idEntrainement]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM exercice WHERE id_exercice = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO exercice (id_entrainement, nom, series, repetitions, repos_secondes, ordre) VALUES (:id_entrainement, :nom, :series, :repetitions, :repos_secondes, :ordre)');
        return $stmt->execute([
            'id_entrainement' => $data['id_entrainement'],
            'nom' => $data['nom'],
            'series' => $data['series'],
            'repetitions' => $data['repetitions'],
            'repos_secondes' => $data['repos_secondes'],
            'ordre' => $data['ordre'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE exercice SET nom = :nom, series = :series, repetitions = :repetitions, repos_secondes = :repos_secondes, ordre = :ordre WHERE id_exercice = :id');
        return $stmt->execute([
            'nom' => $data['nom'],
            'series' => $data['series'],
            'repetitions' => $data['repetitions'],
            'repos_secondes' => $data['repos_secondes'],
            'ordre' => $data['ordre'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM exercice WHERE id_exercice = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function getAllWithSession(): array
    {
        $stmt = $this->pdo->query('SELECT x.*, e.date AS session_date, e.type_sport FROM exercice x LEFT JOIN entrainement e ON x.id_entrainement = e.id_entrainement ORDER BY e.date DESC, x.ordre ASC');
        return $stmt->fetchAll();
    }
}
