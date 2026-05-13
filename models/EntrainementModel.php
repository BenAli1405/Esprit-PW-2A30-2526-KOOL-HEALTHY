<?php

require_once __DIR__ . '/../config/Database.php';

class EntrainementModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new \Database())->getConnection();
    }

    /**
     * Retourne toutes les séances d'un utilisateur avec filtre optionnel par type de sport et recherche
     */
    public function getAllByUser(int $idUtilisateur, ?string $typeSport = null, ?string $search = null): array
    {
        $query = 'SELECT * FROM entrainement WHERE id_utilisateur = :id_utilisateur';
        $params = ['id_utilisateur' => $idUtilisateur];

        if (!is_null($typeSport) && trim($typeSport) !== '') {
            $query .= ' AND type_sport = :type_sport';
            $params['type_sport'] = $typeSport;
        }

        if (!is_null($search) && trim($search) !== '') {
            $query .= ' AND (type_sport LIKE :search_sport OR commentaire LIKE :search_commentaire)';
            $searchTerm = '%' . trim($search) . '%';
            $params['search_sport'] = $searchTerm;
            $params['search_commentaire'] = $searchTerm;
        }

        $query .= ' ORDER BY date DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Retourne toutes les séances d'un utilisateur filtrées optionnellement par type de sport
     */
    public function getAllByUserAndType(int $idUtilisateur, ?string $typeSport = null): array
    {
        $query = 'SELECT * FROM entrainement WHERE id_utilisateur = :id_utilisateur';
        $params = ['id_utilisateur' => $idUtilisateur];

        if (!is_null($typeSport) && trim($typeSport) !== '') {
            $query .= ' AND type_sport = :type_sport';
            $params['type_sport'] = $typeSport;
        }

        $query .= ' ORDER BY date DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
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

    /**
     * Retourne le nombre d'exercices associés à une séance
     */
    public function getExercicesCount(int $idEntrainement): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM exercice WHERE id_entrainement = :id');
        $stmt->execute(['id' => $idEntrainement]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    /**
     * Retourne toutes les séances formatées pour un select (avec infos détaillées)
     */
    public function getAllForSelect(): array
    {
        $stmt = $this->pdo->prepare('SELECT id_entrainement as id, date, type_sport, duree_minutes FROM entrainement ORDER BY date DESC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Retourne les types de sports distincts (pour le filtre)
     */
    public function getDistinctSportTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT type_sport FROM entrainement WHERE type_sport IS NOT NULL AND type_sport != "" ORDER BY type_sport ASC');
        $results = $stmt->fetchAll();
        return array_map(fn($row) => $row['type_sport'], $results);
    }

    /**
     * Retourne les calories totales par semaine pour un utilisateur
     * @param int $idUtilisateur ID de l'utilisateur
     * @param int $weeks Nombre de semaines à récupérer (défaut: 4)
     * @return array Tableau associatif avec les clés au format "jour au jour mois année" => calories
     */
    public function getCaloriesPerWeek(int $idUtilisateur, int $weeks = 4): array
    {
        $query = "
            SELECT 
                DATE_SUB(e.date, INTERVAL WEEKDAY(e.date) DAY) as week_start,
                DATE_ADD(DATE_SUB(e.date, INTERVAL WEEKDAY(e.date) DAY), INTERVAL 6 DAY) as week_end,
                SUM(e.calories_brulees) as total_calories
            FROM entrainement e
            WHERE e.id_utilisateur = :id_user
            AND e.date >= DATE_SUB(NOW(), INTERVAL :weeks WEEK)
            GROUP BY WEEK(e.date), YEAR(e.date)
            ORDER BY week_start DESC
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'id_user' => $idUtilisateur,
            'weeks' => $weeks
        ]);
        
        $rawData = $stmt->fetchAll();
        $formatted = [];
        
        foreach ($rawData as $row) {
            $startDate = new \DateTime($row['week_start']);
            $endDate = new \DateTime($row['week_end']);
            
            $startDay = $startDate->format('d');
            $endDay = $endDate->format('d');
            $monthName = $this->getMonthName($endDate->format('m'));
            $year = $endDate->format('Y');
            
            $weekLabel = $startDay . ' au ' . $endDay . ' ' . strtolower($monthName) . ' ' . $year;
            $formatted[$weekLabel] = (int)($row['total_calories'] ?? 0);
        }
        
        return $formatted;
    }
    
    /**
     * Retourne le nom du mois en français
     */
    private function getMonthName(string $month): string
    {
        $months = [
            '01' => 'Janvier',
            '02' => 'Février',
            '03' => 'Mars',
            '04' => 'Avril',
            '05' => 'Mai',
            '06' => 'Juin',
            '07' => 'Juillet',
            '08' => 'Août',
            '09' => 'Septembre',
            '10' => 'Octobre',
            '11' => 'Novembre',
            '12' => 'Décembre',
        ];
        return $months[$month] ?? 'Inconnu';
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