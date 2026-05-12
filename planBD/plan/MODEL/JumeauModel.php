<?php
include_once __DIR__ . '/Database.php';

class JumeauModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Calcule le métabolisme de base (BMR) avec la formule de Harris-Benedict.
     */
    public function calculerBMR(float $poids, float $taille, int $age, string $sexe): float
    {
        if ($sexe === 'femme') {
            return 655.1 + (9.56 * $poids) + (1.85 * $taille) - (4.68 * $age);
        }
        return 66.47 + (13.75 * $poids) + (5.0 * $taille) - (6.75 * $age);
    }

    /**
     * Calcule les besoins caloriques quotidiens selon le niveau d'activité.
     */
    public function calculerBesoins(float $bmr, string $niveau_activite): float
    {
        $facteurs = [
            'sedentaire' => 1.2,
            'leger'      => 1.375,
            'modere'     => 1.55,
            'actif'      => 1.725,
        ];
        $facteur = $facteurs[$niveau_activite] ?? 1.55;
        return round($bmr * $facteur, 1);
    }

    /**
     * Prédit le poids futur en fonction de l'écart calorique et d'un facteur d'apprentissage.
     * 1 kg = 7700 kcal
     */
    public function predirePoids(float $poidsActuel, float $caloriesConsommees, float $caloriesBesoins, int $nbJours, float $facteurApprentissage = 1.0): float
    {
        $ecart = ($caloriesConsommees - $caloriesBesoins) * $nbJours;
        return round($poidsActuel + (($ecart / 7700) * $facteurApprentissage), 2);
    }

    /**
     * Estime le niveau d'énergie sur une échelle 0-100.
     */
    public function estimerEnergie(float $caloriesConsommees, float $caloriesObjectif): array
    {
        if ($caloriesObjectif <= 0) {
            return ['score' => 50, 'label' => 'Neutre', 'emoji' => '😐'];
        }

        $ratio = $caloriesConsommees / $caloriesObjectif;

        if ($ratio >= 0.9 && $ratio <= 1.1) {
            return ['score' => 90, 'label' => 'Excellent', 'emoji' => '⚡'];
        } elseif ($ratio >= 0.75 && $ratio < 0.9) {
            return ['score' => 70, 'label' => 'Bon', 'emoji' => '😊'];
        } elseif ($ratio >= 0.5 && $ratio < 0.75) {
            return ['score' => 50, 'label' => 'Faible', 'emoji' => '😴'];
        } elseif ($ratio < 0.5) {
            return ['score' => 25, 'label' => 'Critique', 'emoji' => '⚠️'];
        } elseif ($ratio > 1.1 && $ratio <= 1.3) {
            return ['score' => 65, 'label' => 'Surplus léger', 'emoji' => '🍔'];
        } else {
            return ['score' => 40, 'label' => 'Surplus excessif', 'emoji' => '🚨'];
        }
    }

    /**
     * Récupère l'historique des calories consommées par jour (depuis table repas).
     */
    public function getHistoriqueCalories(int $planId, int $nbJours = 7): array
    {
        $dateDebut = date('Y-m-d', strtotime("-{$nbJours} days"));
        $stmt = $this->pdo->prepare(
            'SELECT date, SUM(calories_consommees) as total_cal, COUNT(*) as nb_repas
             FROM repas
             WHERE plan_id = ? AND date >= ? AND statut = ?
             GROUP BY date
             ORDER BY date ASC'
        );
        $stmt->execute([$planId, $dateDebut, 'consomme']);
        return $stmt->fetchAll();
    }

    /**
     * Détermine la tendance (perte/maintien/prise) sur les N derniers jours.
     */
    public function getTendance(int $planId, float $caloriesBesoins, int $nbJours = 7): array
    {
        $historique = $this->getHistoriqueCalories($planId, $nbJours);

        if (empty($historique)) {
            return ['tendance' => 'inconnu', 'label' => 'Données insuffisantes', 'emoji' => '❓', 'ecart_moyen' => 0];
        }

        $totalEcart = 0;
        foreach ($historique as $jour) {
            $totalEcart += ((float)$jour['total_cal'] - $caloriesBesoins);
        }
        $ecartMoyen = round($totalEcart / count($historique), 1);

        if ($ecartMoyen < -200) {
            return ['tendance' => 'perte', 'label' => 'Perte de poids', 'emoji' => '📉', 'ecart_moyen' => $ecartMoyen];
        } elseif ($ecartMoyen > 200) {
            return ['tendance' => 'prise', 'label' => 'Prise de poids', 'emoji' => '📈', 'ecart_moyen' => $ecartMoyen];
        }
        return ['tendance' => 'maintien', 'label' => 'Maintien', 'emoji' => '⚖️', 'ecart_moyen' => $ecartMoyen];
    }

    /**
     * Détecte un plateau basé sur les 10 derniers jours.
     */
    public function detecterPlateau(int $planId, float $besoins): array
    {
        $historique = $this->getHistoriqueCalories($planId, 10);
        if (count($historique) < 5) {
            return ['plateau' => false, 'message' => 'Données insuffisantes pour détecter un plateau.'];
        }

        $totalEcart = 0;
        foreach ($historique as $jour) {
            $totalEcart += ((float)$jour['total_cal'] - $besoins);
        }
        
        $variation = $totalEcart / 7700;
        
        if (abs($variation) < 0.2) {
            return [
                'plateau' => true,
                'message' => '⚠️ Plateau détecté : votre variation estimée est de ' . round($variation, 2) . ' kg sur 10 jours.',
                'conseil' => 'Essayez de varier vos apports caloriques (rebond glucidique) ou de changer de routine d\'entraînement.'
            ];
        }

        return ['plateau' => false, 'message' => 'Aucun plateau détecté.'];
    }

    /**
     * Analyse les émotions des 7 derniers repas renseignés.
     */
    public function analyserEmotions(int $planId): array
    {
        $stmt = $this->pdo->prepare('SELECT notes FROM repas WHERE plan_id = ? AND notes IS NOT NULL AND notes != "" ORDER BY date DESC, heure_reelle DESC LIMIT 7');
        $stmt->execute([$planId]);
        $repas = $stmt->fetchAll();

        $emotions = [
            'stress' => 0,
            'fatigue' => 0,
            'coupable' => 0,
            'motivation' => 0
        ];

        foreach ($repas as $r) {
            $notes = mb_strtolower($r['notes'], 'UTF-8');
            if (strpos($notes, 'stress') !== false || strpos($notes, 'anxieux') !== false) $emotions['stress']++;
            if (strpos($notes, 'fatigu') !== false || strpos($notes, 'épuis') !== false) $emotions['fatigue']++;
            if (strpos($notes, 'coupable') !== false || strpos($notes, 'craqu') !== false || strpos($notes, 'honte') !== false) $emotions['coupable']++;
            if (strpos($notes, 'motiv') !== false || strpos($notes, 'super') !== false || strpos($notes, 'bien') !== false) $emotions['motivation']++;
        }

        $maxEmotion = 'neutre';
        $maxCount = 0;
        foreach ($emotions as $emo => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $maxEmotion = $emo;
            }
        }

        $conseil = "Votre humeur semble équilibrée, continuez comme ça !";
        $emoji = "😊";
        if ($maxEmotion === 'stress') {
            $conseil = "Vous semblez stressé(e). Prenez le temps de respirer, et pourquoi pas inclure des aliments riches en magnésium (chocolat noir, amandes).";
            $emoji = "😰";
        } elseif ($maxEmotion === 'fatigue') {
            $conseil = "De la fatigue est détectée. Reposez-vous, hydratez-vous, et assurez-vous de manger assez de glucides complexes.";
            $emoji = "😴";
        } elseif ($maxEmotion === 'coupable') {
            $conseil = "Ne culpabilisez pas pour un écart ! C'est la régularité sur le long terme qui compte. Reprenez vos bonnes habitudes.";
            $emoji = "😔";
        } elseif ($maxEmotion === 'motivation') {
            $conseil = "Super motivation détectée ! Profitez de cette belle énergie pour atteindre vos objectifs.";
            $emoji = "💪";
        }

        return ['emotion_dominante' => $maxEmotion, 'conseil' => $conseil, 'emoji' => $emoji, 'occurrences' => $maxCount];
    }

    /**
     * Calcule le score de confiance (0-100%).
     */
    public function scoreConfiance(int $planId, array $plan): array
    {
        $score = 0;
        
        $biometrieScore = 20;
        if (empty($plan['age']) || $plan['age'] == 30) $biometrieScore -= 5;
        if (empty($plan['poids']) || $plan['poids'] == 70) $biometrieScore -= 5;
        if (empty($plan['taille']) || $plan['taille'] == 170) $biometrieScore -= 5;
        $score += $biometrieScore;

        $historique = $this->getHistoriqueCalories($planId, 7);
        $totalRepas = 0;
        foreach ($historique as $jour) {
            $totalRepas += (int)$jour['nb_repas'];
        }
        $repasScore = min(50, ($totalRepas / 21) * 50);
        $score += $repasScore;

        $stmt = $this->pdo->prepare("SELECT heure_prevue, heure_reelle FROM repas WHERE plan_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([$planId]);
        $repasHoraire = $stmt->fetchAll();
        
        $horaireScore = 0;
        if (count($repasHoraire) > 0) {
            $regularitePoints = 0;
            foreach ($repasHoraire as $r) {
                if (!empty($r['heure_prevue']) && !empty($r['heure_reelle'])) {
                    $t1 = strtotime($r['heure_prevue']);
                    $t2 = strtotime($r['heure_reelle']);
                    $ecartMin = abs($t1 - $t2) / 60;
                    if ($ecartMin <= 30) $regularitePoints += 1;
                    elseif ($ecartMin <= 60) $regularitePoints += 0.5;
                }
            }
            $horaireScore = min(30, ($regularitePoints / count($repasHoraire)) * 30);
        } else {
            $horaireScore = 15;
        }
        $score += $horaireScore;

        $scoreTotal = round($score);
        $niveau = 'Faible';
        if ($scoreTotal >= 80) $niveau = 'Élevé';
        elseif ($scoreTotal >= 50) $niveau = 'Moyen';

        return [
            'score' => $scoreTotal,
            'niveau' => $niveau,
            'details' => [
                'biometrie' => round($biometrieScore),
                'frequence' => round($repasScore),
                'regularite' => round($horaireScore)
            ]
        ];
    }

    /**
     * Génère un plan de correction sur 3 jours si retard détecté.
     */
    public function genererPlanCorrection(array $plan, array $tendance, float $besoins): array
    {
        $objectif = $plan['objectif'] ?? 'maintien';
        $correctionActive = false;
        $ajustement = 0;

        if ($objectif === 'perte-poids' && ($tendance['tendance'] === 'prise' || $tendance['tendance'] === 'maintien')) {
            $correctionActive = true;
            $ajustement = -250;
            $message = "Retard sur la perte de poids. Plan de correction (-250 kcal/j) :";
        } elseif ($objectif === 'prise-muscle' && ($tendance['tendance'] === 'perte' || $tendance['tendance'] === 'maintien')) {
            $correctionActive = true;
            $ajustement = +300;
            $message = "Retard sur la prise de masse. Plan de correction (+300 kcal/j) :";
        }

        if (!$correctionActive) {
            return ['actif' => false, 'message' => "Votre trajectoire est optimale, aucune correction nécessaire !"];
        }

        $jours = [];
        $caloriesCibles = round($besoins + $ajustement);
        for ($i = 1; $i <= 3; $i++) {
            $jours[] = [
                'jour' => "Jour +$i",
                'calories' => $caloriesCibles,
                'repartition' => [
                    'petit_dejeuner' => round($caloriesCibles * 0.25),
                    'dejeuner' => round($caloriesCibles * 0.35),
                    'collation' => round($caloriesCibles * 0.15),
                    'diner' => round($caloriesCibles * 0.25)
                ]
            ];
        }

        return [
            'actif' => true,
            'message' => $message,
            'ajustement' => $ajustement,
            'cibles' => $jours
        ];
    }

    /**
     * Génère les prédictions complètes pour le jumeau numérique.
     */
    public function getFullStats(array $plan): array
    {
        $poids   = (float)($plan['poids'] ?? 70);
        $taille  = (float)($plan['taille'] ?? 170);
        $age     = (int)($plan['age'] ?? 30);
        $sexe    = $plan['sexe'] ?? 'homme';
        $activite = $plan['niveau_activite'] ?? 'modere';
        $planId  = (int)$plan['id'];

        $bmr = $this->calculerBMR($poids, $taille, $age, $sexe);
        $besoins = $this->calculerBesoins($bmr, $activite);

        // Historique 7 jours
        $historique = $this->getHistoriqueCalories($planId, 7);
        $calMoyenne = 0;
        if (!empty($historique)) {
            $total = 0;
            foreach ($historique as $h) { $total += (float)$h['total_cal']; }
            $calMoyenne = round($total / count($historique), 1);
        }

        // Énergie
        $energie = $this->estimerEnergie($calMoyenne, $besoins);

        // Tendance
        $tendance = $this->getTendance($planId, $besoins, 7);

        // Score de confiance
        $confiance = $this->scoreConfiance($planId, $plan);
        
        // Facteur d'apprentissage adaptatif basé sur la confiance
        $facteurApprentissage = 1.0;
        if ($confiance['score'] < 50) {
            $facteurApprentissage = 0.8; // On réduit la prédiction si la confiance est faible (données incertaines)
        }

        // Prédictions poids à 7, 14, 30 jours
        $predictions = [];
        foreach ([7, 14, 30] as $j) {
            $predictions[$j] = $this->predirePoids($poids, $calMoyenne, $besoins, $j, $facteurApprentissage);
        }

        // Forecast journalier sur 7 jours
        $forecast = [];
        for ($i = 1; $i <= 7; $i++) {
            $forecast[] = [
                'jour' => $i,
                'date' => date('Y-m-d', strtotime("+{$i} days")),
                'poids_predit' => $this->predirePoids($poids, $calMoyenne, $besoins, $i, $facteurApprentissage),
            ];
        }

        // Plateau et Émotions
        $plateau = $this->detecterPlateau($planId, $besoins);
        $emotions = $this->analyserEmotions($planId);
        
        // Plan de correction
        $correction = $this->genererPlanCorrection($plan, $tendance, $besoins);

        // Conseil personnalisé
        $conseil = $this->genererConseil($tendance['tendance'], $plan['objectif'] ?? 'maintien', $energie['score']);

        return [
            'bmr'         => round($bmr, 1),
            'besoins'     => $besoins,
            'cal_moyenne' => $calMoyenne,
            'energie'     => $energie,
            'tendance'    => $tendance,
            'predictions' => $predictions,
            'forecast'    => $forecast,
            'historique'  => $historique,
            'plateau'     => $plateau,
            'emotions'    => $emotions,
            'confiance'   => $confiance,
            'correction'  => $correction,
            'conseil'     => $conseil,
            'profil'      => [
                'poids' => $poids, 'taille' => $taille, 'age' => $age,
                'sexe' => $sexe, 'activite' => $activite
            ]
        ];
    }

    /**
     * Simule l'impact d'un écart calorique sur le poids.
     */
    public function simulerEcart(array $plan, int $caloriesEcart): array
    {
        $poids   = (float)($plan['poids'] ?? 70);
        $taille  = (float)($plan['taille'] ?? 170);
        $age     = (int)($plan['age'] ?? 30);
        $sexe    = $plan['sexe'] ?? 'homme';
        $activite = $plan['niveau_activite'] ?? 'modere';

        $bmr = $this->calculerBMR($poids, $taille, $age, $sexe);
        $besoins = $this->calculerBesoins($bmr, $activite);
        $calAvecEcart = $besoins + $caloriesEcart;

        $impact = [];
        foreach ([1, 7, 14, 30] as $j) {
            $impact[$j] = $this->predirePoids($poids, $calAvecEcart, $besoins, $j);
        }

        $diff30 = round($impact[30] - $poids, 2);
        if ($caloriesEcart > 0) {
            $msg = "🍔 Un surplus de {$caloriesEcart} kcal/jour entraînerait **+" . abs($diff30) . " kg** en 30 jours.";
        } else {
            $msg = "🥗 Un déficit de " . abs($caloriesEcart) . " kcal/jour entraînerait **-" . abs($diff30) . " kg** en 30 jours.";
        }

        return ['impact' => $impact, 'message' => $msg, 'ecart' => $caloriesEcart];
    }

    private function genererConseil(string $tendance, string $objectif, int $energie): string
    {
        if ($objectif === 'perte-poids' && $tendance === 'perte') {
            return "✅ Votre jumeau confirme : vous êtes en déficit calorique, la perte de poids est en cours !";
        } elseif ($objectif === 'perte-poids' && $tendance === 'prise') {
            return "⚠️ Attention : votre jumeau détecte un surplus calorique. Réduisez les portions ou augmentez l'activité.";
        } elseif ($objectif === 'prise-muscle' && $tendance === 'prise') {
            return "💪 Parfait ! Le surplus calorique soutient votre prise de masse. Pensez aux protéines !";
        } elseif ($objectif === 'prise-muscle' && $tendance === 'perte') {
            return "⚠️ Votre jumeau détecte un déficit. Augmentez votre apport pour soutenir la prise de muscle.";
        } elseif ($tendance === 'maintien') {
            return "⚖️ Équilibre parfait ! Votre jumeau est stable. Continuez ainsi.";
        } elseif ($energie < 40) {
            return "😴 Énergie faible détectée. Mangez plus régulièrement et hydratez-vous.";
        }
        return "👥 Votre jumeau numérique analyse vos données. Continuez à renseigner vos repas !";
    }
}
