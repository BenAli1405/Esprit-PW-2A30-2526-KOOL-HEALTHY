<?php

include_once __DIR__ . '/Database.php';

class JumeauModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getFullStats(array $plan): array
    {
        $planId = (int)$plan['id'];
        // Récupère les repas du plan
        $stmt = $this->pdo->prepare('SELECT date, SUM(IFNULL(calories_consommees,0)) AS daily_calories FROM repas WHERE plan_id = ? GROUP BY date ORDER BY date');
        $stmt->execute([$planId]);
        $daily = $stmt->fetchAll();

        // Calculs simples
        $tot = 0;
        $count = 0;
        $perDay = [];
        foreach ($daily as $d) {
            $c = (int)($d['daily_calories'] ?? 0);
            $perDay[] = ['date' => $d['date'], 'calories' => $c];
            $tot += $c;
            $count++;
        }
        $average = $count > 0 ? (int)round($tot / $count) : 0;

        // Prévision basique (utilise besoin calorique du profil si présent)
        $besoins = isset($plan['besoins_caloriques']) ? (int)$plan['besoins_caloriques'] : 2000;
        $difference = $average - $besoins; // positif = surplus

        // Simple forecast: weight change per day (approx 7700 kcal ~= 1kg)
        $dailyDeltaKg = -$difference / 7700.0;

        $forecast = [
            'average_calories' => $average,
            'calories_needed' => $besoins,
            'daily_caloric_difference' => $difference,
            'daily_weight_change_kg' => round($dailyDeltaKg, 4)
        ];

        // Predictions next 14 days
        $predictions = [];
        $runningKg = 0.0;
        for ($i = 1; $i <= 14; $i++) {
            $runningKg += $dailyDeltaKg;
            $predictions[] = [
                'day' => $i,
                'expected_weight_change_kg' => round($runningKg, 4),
                'expected_calories' => $average
            ];
        }

        return ['forecast' => $forecast, 'predictions' => $predictions];
    }

    public function simulerEcart(array $plan, int $ecart): array
    {
        // ecart: calorie difference to apply per day (positive = surplus)
        $duree = isset($plan['duree']) ? (int)$plan['duree'] : 7;
        if ($duree < 1) $duree = 7;

        // total caloric change over period
        $total = $ecart * $duree;
        $kgChange = $total / 7700.0; // approximation

        return [
            'duree' => $duree,
            'ecart_par_jour' => $ecart,
            'total_calories_change' => $total,
            'estimated_weight_change_kg' => round($kgChange, 4)
        ];
    }
}
