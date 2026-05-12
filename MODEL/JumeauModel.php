<?php

include_once __DIR__ . '/Database.php';

class JumeauModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    private function getDailyCaloriesForPlan(int $planId): array
    {
        $stmt = $this->pdo->prepare('SELECT date, SUM(IFNULL(calories_consommees,0)) AS daily_calories FROM repas WHERE plan_id = ? GROUP BY date ORDER BY date');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    private function clamp(float $v, float $min, float $max): float
    {
        if ($v < $min) return $min;
        if ($v > $max) return $max;
        return $v;
    }

    public function getFullStats(array $plan, int $predictionDays = 14): array
    {
        $planId = (int)$plan['id'];
        if ($predictionDays < 1) $predictionDays = 1;
        if ($predictionDays > 90) $predictionDays = 90;

        // Récupère les repas du plan
        $daily = $this->getDailyCaloriesForPlan($planId);

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

        // Profil (fallbacks robustes)
        $poids = isset($plan['poids']) ? (float)$plan['poids'] : 70.0;
        $taille = isset($plan['taille']) ? (float)$plan['taille'] : 175.0;
        $age = isset($plan['age']) ? (int)$plan['age'] : 30;
        $sexe = isset($plan['sexe']) ? strtolower((string)$plan['sexe']) : 'homme';
        $activite = isset($plan['niveau_activite']) ? (string)$plan['niveau_activite'] : 'modérée';

        // BMR Harris-Benedict simplifié
        if ($sexe === 'femme') {
            $bmr = (int)round(447.593 + (9.247 * $poids) + (3.098 * $taille) - (4.330 * $age));
        } else {
            $bmr = (int)round(88.362 + (13.397 * $poids) + (4.799 * $taille) - (5.677 * $age));
        }

        $actLower = strtolower($activite);
        $activityFactor = 1.55;
        if (strpos($actLower, 'séd') !== false || strpos($actLower, 'sed') !== false) $activityFactor = 1.2;
        if (strpos($actLower, 'léger') !== false || strpos($actLower, 'leger') !== false) $activityFactor = 1.375;
        if (strpos($actLower, 'mod') !== false) $activityFactor = 1.55;
        if (strpos($actLower, 'intense') !== false) $activityFactor = 1.725;
        if (strpos($actLower, 'très') !== false || strpos($actLower, 'tres') !== false) $activityFactor = 1.9;

        $besoins = isset($plan['besoins_caloriques']) ? (int)$plan['besoins_caloriques'] : (int)round($bmr * $activityFactor);
        $difference = $average - $besoins; // + surplus
        $dailyDeltaKg = -$difference / 7700.0;

        $energieScore = (int)round($this->clamp(100 - (abs($difference) / 20), 0, 100));
        $energieEmoji = $energieScore >= 70 ? '⚡' : ($energieScore >= 40 ? '🟡' : '🧯');

        $confianceBiometrie = isset($plan['poids']) ? 35 : 15;
        $confianceFrequence = $count >= 7 ? 35 : ($count >= 3 ? 25 : 10);
        $confianceRegularite = $count >= 1 ? 30 : 10;
        $confianceScore = min(100, $confianceBiometrie + $confianceFrequence + $confianceRegularite);

        $ecartMoyen = $difference;
        if ($count < 2) {
            $tendance = ['emoji' => '❓', 'label' => 'Données insuffisantes', 'ecart_moyen' => 0];
        } elseif ($ecartMoyen < -150) {
            $tendance = ['emoji' => '📉', 'label' => 'Déficit calorique', 'ecart_moyen' => (int)round($ecartMoyen)];
        } elseif ($ecartMoyen > 150) {
            $tendance = ['emoji' => '📈', 'label' => 'Surplus calorique', 'ecart_moyen' => (int)round($ecartMoyen)];
        } else {
            $tendance = ['emoji' => '⚖️', 'label' => 'Équilibre global', 'ecart_moyen' => (int)round($ecartMoyen)];
        }

        $predictionsMap = [];
        foreach ([7, 14, 30] as $d) {
            $predictionsMap[$d] = round($poids + ($dailyDeltaKg * $d), 2);
        }

        $forecastBars = [];
        for ($i = 1; $i <= 7; $i++) {
            $forecastBars[] = [
                'jour' => $i,
                'poids_predit' => round($poids + ($dailyDeltaKg * $i), 2),
            ];
        }

        $plateau = [
            'plateau' => false,
            'message' => 'Aucun plateau détecté.',
            'conseil' => 'Continuez avec une bonne régularité.'
        ];
        if ($count >= 10 && abs($difference) < 80) {
            $plateau = [
                'plateau' => true,
                'message' => 'Plateau probable détecté.',
                'conseil' => 'Variez l’intensité sportive et l’apport protéique quelques jours.'
            ];
        }

        $emotions = [
            'emoji' => '🙂',
            'emotion_dominante' => 'neutre',
            'conseil' => 'Votre humeur semble équilibrée, continuez comme ça !'
        ];

        $correctionActif = abs($difference) >= 250;
        $correctionAdjust = (int)round(-$difference * 0.35);
        $correction = [
            'actif' => $correctionActif,
            'ajustement' => $correctionAdjust,
            'message' => 'Ajustement proposé pour compenser la tendance actuelle.',
            'cibles' => [
                ['jour' => 'J+1', 'calories' => $besoins + $correctionAdjust],
                ['jour' => 'J+2', 'calories' => $besoins + $correctionAdjust],
                ['jour' => 'J+3', 'calories' => $besoins + $correctionAdjust],
            ]
        ];

        $conseil = ($difference > 200)
            ? 'Surplus calorique détecté. Réduisez légèrement les portions et augmentez les légumes.'
            : (($difference < -200)
                ? 'Déficit calorique important. Augmentez un peu les apports et hydratez-vous bien.'
                : 'Bon équilibre global détecté. Maintenez cette régularité.');

        // Legacy fields kept for backward compatibility
        $legacyPredictions = [];
        $runningKg = 0.0;
        for ($i = 1; $i <= $predictionDays; $i++) {
            $runningKg += $dailyDeltaKg;
            $legacyPredictions[] = [
                'day' => $i,
                'expected_weight_change_kg' => round($runningKg, 4),
                'expected_calories' => $average
            ];
        }

        return [
            // New fields expected by Assets/jumeau.js
            'bmr' => $bmr,
            'besoins' => $besoins,
            'profil' => [
                'poids' => round($poids, 2),
                'taille' => (int)$taille,
                'age' => $age,
                'sexe' => $sexe,
                'activite' => $activite,
            ],
            'energie' => [
                'emoji' => $energieEmoji,
                'score' => $energieScore,
            ],
            'confiance' => [
                'score' => $confianceScore,
                'details' => [
                    'biometrie' => $confianceBiometrie,
                    'frequence' => $confianceFrequence,
                    'regularite' => $confianceRegularite,
                ]
            ],
            'plateau' => $plateau,
            'emotions' => $emotions,
            'tendance' => $tendance,
            'predictions' => $predictionsMap,
            'forecast' => $forecastBars,
            'correction' => $correction,
            'conseil' => $conseil,

            // Compatibility fields expected by older front-end
            'totalCalories' => $tot,
            'days' => $count,
            'averageCalories' => $average,
            'forecast_total' => $average * $predictionDays,
            'predictions_legacy' => $legacyPredictions,
            'perDay' => $perDay,
        ];
    }

    public function simulerEcart(array $plan, int $ecart): array
    {
        // ecart: calorie difference to apply per day (positive = surplus)
        $duree = isset($plan['duree']) ? (int)$plan['duree'] : 7;
        if ($duree < 1) $duree = 7;

        $planId = (int)$plan['id'];
        $daily = $this->getDailyCaloriesForPlan($planId);
        $originalTotal = 0;
        foreach ($daily as $d) {
            $originalTotal += (int)($d['daily_calories'] ?? 0);
        }

        // If no meals yet, estimate from besoins caloriques / average baseline
        if ($originalTotal <= 0) {
            $base = isset($plan['besoins_caloriques']) ? (int)$plan['besoins_caloriques'] : 2000;
            $originalTotal = $base * $duree;
        }

        // total caloric change over period
        $total = $ecart * $duree;
        $simulatedTotal = $originalTotal + $total;
        $kgChange = $total / 7700.0; // approximation

        return [
            'duree' => $duree,
            'ecart_par_jour' => $ecart,
            'total_calories_change' => $total,
            'estimated_weight_change_kg' => round($kgChange, 4),
            // Compatibility fields expected by front-end
            'originalTotal' => $originalTotal,
            'simulatedTotal' => $simulatedTotal,
            'ecartApplique' => $ecart,
            // Fields expected by Assets/jumeau.js modal
            'message' => 'Simulation appliquée: **' . ($ecart >= 0 ? '+' : '') . $ecart . ' kcal/jour** pendant **' . $duree . ' jours**.',
            'impact' => [
                7 => round(($ecart * 7) / 7700.0, 2),
                14 => round(($ecart * 14) / 7700.0, 2),
                30 => round(($ecart * 30) / 7700.0, 2),
            ]
        ];
    }
}
