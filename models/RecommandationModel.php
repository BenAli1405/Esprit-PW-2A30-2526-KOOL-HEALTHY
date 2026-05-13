<?php

require_once __DIR__ . '/../config/Database.php';

class RecommandationModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM recommandation_regle ORDER BY type_repas, exercice_suggere');
        return $stmt->fetchAll();
    }

    public function getById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recommandation_regle WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function findByTypeRepas(string $typeRepas): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM recommandation_regle WHERE type_repas = :type_repas ORDER BY exercice_suggere');
        $stmt->execute(['type_repas' => $typeRepas]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO recommandation_regle (type_repas, exercice_suggere, series, repetitions) VALUES (:type_repas, :exercice_suggere, :series, :repetitions)');
        return $stmt->execute([
            'type_repas' => $data['type_repas'],
            'exercice_suggere' => $data['exercice_suggere'],
            'series' => $data['series'],
            'repetitions' => $data['repetitions'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE recommandation_regle SET type_repas = :type_repas, exercice_suggere = :exercice_suggere, series = :series, repetitions = :repetitions WHERE id = :id');
        return $stmt->execute([
            'type_repas' => $data['type_repas'],
            'exercice_suggere' => $data['exercice_suggere'],
            'series' => $data['series'],
            'repetitions' => $data['repetitions'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM recommandation_regle WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
