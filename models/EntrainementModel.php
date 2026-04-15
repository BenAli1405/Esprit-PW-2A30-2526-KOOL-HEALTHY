<?php

require_once __DIR__ . '/../config/Database.php';

class EntrainementModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new \Database())->getConnection();
    }

    public function getAllByUser(int $idUtilisateur): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM entrainement WHERE id_utilisateur = :id_utilisateur ORDER BY date DESC');
        $stmt->execute(['id_utilisateur' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    public function getAllWithUser(): array
    {
        $stmt = $this->pdo->query('SELECT e.*, u.nom AS utilisateur_nom FROM entrainement e LEFT JOIN utilisateur u ON e.id_utilisateur = u.id_utilisateur ORDER BY e.date DESC');
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM entrainement WHERE id_entrainement = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO entrainement (id_utilisateur, date, duree_minutes, type_sport, calories_brulees, commentaire) VALUES (:id_utilisateur, :date, :duree_minutes, :type_sport, :calories_brulees, :commentaire)');
        return $stmt->execute([
            'id_utilisateur' => $data['id_utilisateur'],
            'date' => $data['date'],
            'duree_minutes' => $data['duree_minutes'],
            'type_sport' => $data['type_sport'],
            'calories_brulees' => $data['calories_brulees'],
            'commentaire' => $data['commentaire'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE entrainement SET id_utilisateur = :id_utilisateur, date = :date, duree_minutes = :duree_minutes, type_sport = :type_sport, calories_brulees = :calories_brulees, commentaire = :commentaire WHERE id_entrainement = :id');
        return $stmt->execute([
            'id_utilisateur' => $data['id_utilisateur'],
            'date' => $data['date'],
            'duree_minutes' => $data['duree_minutes'],
            'type_sport' => $data['type_sport'],
            'calories_brulees' => $data['calories_brulees'],
            'commentaire' => $data['commentaire'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM entrainement WHERE id_entrainement = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query('SELECT id_utilisateur, nom FROM utilisateur ORDER BY nom');
        return $stmt->fetchAll();
    }
}
