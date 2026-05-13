<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * PerformanceModel — Analyse de la progression d'un exercice
 *
 * Formules de régression linéaire (méthode des moindres carrés) :
 *
 *   Soit n points (x_i, y_i) où :
 *     x_i = jours depuis la première séance
 *     y_i = charge totale = poids * répétitions * séries
 *
 *   Pente  : b = ( n·Σ(x·y) − Σx·Σy ) / ( n·Σ(x²) − (Σx)² )
 *   Origine: a = (Σy − b·Σx) / n
 *   Droite : y = a + b·x
 *
 *   R² (coefficient de détermination) :
 *     SS_res = Σ(y_i − ŷ_i)²
 *     SS_tot = Σ(y_i − ȳ)²
 *     R² = 1 − SS_res / SS_tot
 */
class PerformanceModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new \Database())->getConnection();
    }

    // =========================================================================
    // REQUÊTES DE BASE
    // =========================================================================

    /**
     * Retourne tous les exercices d'un utilisateur (via leurs séances)
     * pour alimenter le <select> de la page progression.
     *
     * @param  int   $idUtilisateur
     * @return array Liste [{id_exercice, nom_exercice, nb_performances}]
     */
    public function getExercicesUtilisateur(int $idUtilisateur): array
    {
        $stmt = $this->pdo->prepare('
            SELECT e.id_exercice,
                   e.nom AS nom_exercice,
                   COUNT(p.id_performance) AS nb_performances
            FROM exercice e
            JOIN entrainement t ON t.id_entrainement = e.id_entrainement
            LEFT JOIN performance_exercice p ON p.id_exercice = e.id_exercice
            WHERE t.id_utilisateur = :uid
            GROUP BY e.id_exercice, e.nom
            HAVING nb_performances > 0
            ORDER BY e.nom ASC
        ');
        $stmt->execute(['uid' => $idUtilisateur]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne l'historique complet des performances pour un exercice,
     * trié par date croissante.
     *
     * @param  int   $idExercice
     * @return array [{id_performance, date, poids, repetitions, series, fatigue,
     *                 commentaire, charge_totale}]
     */
    public function getHistorique(int $idExercice): array
    {
        $stmt = $this->pdo->prepare('
            SELECT id_performance,
                   date,
                   COALESCE(poids, 0)           AS poids,
                   repetitions,
                   series,
                   fatigue,
                   commentaire,
                   ROUND(
                       COALESCE(poids, 0) * repetitions * series,
                       1
                   )                            AS charge_totale
            FROM performance_exercice
            WHERE id_exercice = :id
            ORDER BY date ASC
        ');
        $stmt->execute(['id' => $idExercice]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le nom de l'exercice (pour l'affichage).
     */
    public function getNomExercice(int $idExercice): string
    {
        $stmt = $this->pdo->prepare('SELECT nom FROM exercice WHERE id_exercice = :id');
        $stmt->execute(['id' => $idExercice]);
        return (string)($stmt->fetchColumn() ?: '');
    }

    /**
     * Ajoute une nouvelle performance.
     */
    public function ajouterPerformance(array $data): bool
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO performance_exercice
                (id_exercice, date, poids, repetitions, series, fatigue, commentaire)
            VALUES
                (:id_exercice, :date, :poids, :repetitions, :series, :fatigue, :commentaire)
        ');
        return $stmt->execute([
            ':id_exercice'  => (int)$data['id_exercice'],
            ':date'         => $data['date'],
            ':poids'        => ($data['poids'] !== '' && $data['poids'] !== null)
                               ? (float)$data['poids'] : null,
            ':repetitions'  => (int)$data['repetitions'],
            ':series'       => (int)$data['series'],
            ':fatigue'      => ($data['fatigue'] !== '' && $data['fatigue'] !== null)
                               ? (int)$data['fatigue'] : null,
            ':commentaire'  => $data['commentaire'] ?: null,
        ]);
    }

    /**
     * Supprime une performance par son ID.
     */
    public function supprimerPerformance(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM performance_exercice WHERE id_performance = :id');
        return $stmt->execute(['id' => $id]);
    }

    // =========================================================================
    // ALGORITHME — RÉGRESSION LINÉAIRE (moindres carrés)
    // =========================================================================

    /**
     * Calcule la droite de régression linéaire sur l'historique fourni.
     *
     * @param  array $historique  Résultat de getHistorique()
     * @return array|null         [pente, origine, r2, xMin, xMax, moyenne]
     *                            ou null si moins de 2 points disponibles
     */
    public function calculerRegression(array $historique): ?array
    {
        $n = count($historique);
        if ($n < 2) {
            return null;
        }

        // Convertir les dates en jours depuis la première séance (x=0)
        $dateDebut = new \DateTime($historique[0]['date']);

        $sumX  = 0.0; // Σx
        $sumY  = 0.0; // Σy
        $sumXY = 0.0; // Σ(x·y)
        $sumX2 = 0.0; // Σ(x²)
        $points = [];

        foreach ($historique as $row) {
            $date  = new \DateTime($row['date']);
            $x     = (float)$date->diff($dateDebut)->days; // jours depuis le début
            $y     = (float)$row['charge_totale'];

            $sumX  += $x;
            $sumY  += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;

            $points[] = ['x' => $x, 'y' => $y, 'date' => $row['date']];
        }

        // ── Calcul de la pente (b) et de l'ordonnée à l'origine (a) ──
        $denominateur = $n * $sumX2 - $sumX * $sumX;

        if (abs($denominateur) < 1e-10) {
            // Toutes les séances le même jour : pente indéfinie → 0
            $pente   = 0.0;
            $origine = $sumY / $n;
        } else {
            $pente   = ($n * $sumXY - $sumX * $sumY) / $denominateur;
            $origine = ($sumY - $pente * $sumX) / $n;
        }

        // ── Calcul de R² ──
        $moyenne = $sumY / $n;
        $ssTot   = 0.0; // SS_total = Σ(y − ȳ)²
        $ssRes   = 0.0; // SS_résiduel = Σ(y − ŷ)²

        foreach ($points as $p) {
            $yPredit  = $origine + $pente * $p['x'];
            $ssTot   += ($p['y'] - $moyenne) ** 2;
            $ssRes   += ($p['y'] - $yPredit)  ** 2;
        }

        $r2 = ($ssTot > 1e-10) ? 1.0 - $ssRes / $ssTot : 1.0;

        return [
            'pente'    => round($pente,   4),
            'origine'  => round($origine, 4),
            'r2'       => round(max(0.0, $r2), 4),
            'moyenne'  => round($moyenne, 2),
            'n'        => $n,
            'xMin'     => $points[0]['x'],
            'xMax'     => $points[$n - 1]['x'],
            'points'   => $points, // utile pour Chart.js
        ];
    }

    // =========================================================================
    // PRÉDICTION
    // =========================================================================

    /**
     * Prédit la charge totale dans $joursFuturs jours à partir d'aujourd'hui,
     * en ajoutant ces jours à la date du dernier point connu.
     *
     * @param  int   $joursFuturs  Horizon de prédiction en jours
     * @param  float $pente        Coefficient directeur de la droite
     * @param  float $origine      Ordonnée à l'origine
     * @param  float $xDernierPoint Valeur x du dernier point de l'historique
     * @return float               Charge estimée (peut être négative si déclin)
     */
    public function predireCharge(
        int   $joursFuturs,
        float $pente,
        float $origine,
        float $xDernierPoint
    ): float {
        $xFutur = $xDernierPoint + $joursFuturs;
        return round($origine + $pente * $xFutur, 2);
    }

    /**
     * Estime dans combien de jours l'utilisateur atteindra une charge cible.
     * Retourne null si la pente est nulle ou négative (objectif inaccessible).
     *
     * @param  float $chargeCible
     * @param  float $pente
     * @param  float $origine
     * @param  float $xDernierPoint  Valeur x du dernier point
     * @return int|null              Jours restants depuis le dernier point
     */
    public function joursAvantObjectif(
        float $chargeCible,
        float $pente,
        float $origine,
        float $xDernierPoint
    ): ?int {
        if ($pente <= 0.0) {
            return null; // Pas de progression, objectif inaccessible par la droite
        }

        // y = a + b·x  ⟹  x = (y − a) / b
        $xCible = ($chargeCible - $origine) / $pente;
        $joursRestants = (int)ceil($xCible - $xDernierPoint);

        return $joursRestants > 0 ? $joursRestants : 0;
    }

    // =========================================================================
    // DÉTECTION DE PLATEAU
    // =========================================================================

    /**
     * Détecte si l'utilisateur stagne sur les dernières séances.
     *
     * La méthode calcule la pente sur les $nbDernieres dernières séances
     * (même algorithme que calculerRegression) et la compare à :
     *   seuil absolu = $seuil × moyenne_globale_des_charges
     *
     * Si |pente_récente| < seuil OU si la pente est négative ET forte,
     * on considère qu'il y a un plateau.
     *
     * @param  array $historique         Complet (trié par date ASC)
     * @param  int   $nbDernieres        Nombre de séances récentes à analyser
     * @param  float $seuilRelatif       Fraction de la moyenne (ex: 0.05 = 5 %)
     * @return array  ['plateau' => bool, 'pente_recente' => float,
     *                 'seuil_absolu' => float, 'nb_seances' => int]
     */
    public function detecterPlateau(
        array $historique,
        int   $nbDernieres   = 3,
        float $seuilRelatif  = 0.05
    ): array {
        $n = count($historique);

        // Résultat par défaut
        $result = [
            'plateau'       => false,
            'pente_recente' => 0.0,
            'seuil_absolu'  => 0.0,
            'nb_seances'    => $n,
        ];

        if ($n < 2) {
            return $result; // Pas assez de données
        }

        // Calculer la moyenne globale des charges pour le seuil
        $charges = array_column($historique, 'charge_totale');
        $moyenne = array_sum($charges) / count($charges);
        $seuil   = $seuilRelatif * max($moyenne, 1.0);

        // Prendre les N dernières séances
        $dernieres = array_slice($historique, -$nbDernieres);
        $regRecente = $this->calculerRegression($dernieres);

        if ($regRecente === null) {
            return $result;
        }

        $penteRecente = $regRecente['pente'];
        $result['pente_recente'] = $penteRecente;
        $result['seuil_absolu']  = round($seuil, 2);
        $result['nb_seances']    = count($dernieres);

        // Plateau si la pente est très faible (positive ou négative)
        $result['plateau'] = (abs($penteRecente) < $seuil);

        return $result;
    }

    // =========================================================================
    // CONSEILS
    // =========================================================================

    /**
     * Génère une liste de recommandations textuelles basées sur l'analyse.
     *
     * @param  array      $regression   Résultat de calculerRegression()
     * @param  array      $plateau      Résultat de detecterPlateau()
     * @param  float|null $chargeJ30    Prédiction à 30 jours (ou null)
     * @return array                    Liste de chaînes de caractères
     */
    public function genererConseils(
        array  $regression,
        array  $plateau,
        ?float $chargeJ30 = null
    ): array {
        $conseils = [];
        $pente    = $regression['pente'];
        $r2       = $regression['r2'];

        // Qualité de la régression
        if ($r2 >= 0.8) {
            $conseils[] = "📈 Régularité excellente (R²=" . number_format($r2, 2) . ") : ta progression est très linéaire.";
        } elseif ($r2 >= 0.5) {
            $conseils[] = "📊 Progression correcte (R²=" . number_format($r2, 2) . "), quelques variations mais la tendance est claire.";
        } else {
            $conseils[] = "📉 Progression irrégulière (R²=" . number_format($r2, 2) . ") : essaie de rendre tes séances plus régulières.";
        }

        // Tendance globale
        if ($pente > 1.0) {
            $conseils[] = "🚀 Excellente progression globale (+{$pente} kg·reps·séries / jour en moyenne).";
        } elseif ($pente > 0.1) {
            $conseils[] = "✅ Bonne progression globale. Continue sur cette lancée !";
        } elseif ($pente >= 0.0) {
            $conseils[] = "⚠️ Progression très lente. Pense à augmenter la charge ou le volume.";
        } else {
            $conseils[] = "🔻 Charge totale en baisse. Vérifie ta récupération et ta nutrition.";
        }

        // Plateau récent
        if ($plateau['plateau']) {
            $conseils[] = "🧱 Plateau détecté sur les {$plateau['nb_seances']} dernières séances "
                        . "(pente = " . number_format($plateau['pente_recente'], 2) . " < seuil "
                        . number_format($plateau['seuil_absolu'], 2) . ").";
            $conseils[] = "💡 Stratégies anti-plateau : augmente le poids de 2,5 kg, modifie le tempo, ou ajoute une série.";
        } else {
            $conseils[] = "✅ Pas de plateau sur les dernières séances. Tu progresses bien !";
        }

        // Prédiction à 30 jours
        if ($chargeJ30 !== null && $chargeJ30 > 0) {
            $conseils[] = "🔮 Dans 30 jours : charge totale estimée ≈ " . number_format($chargeJ30, 0) . " kg·reps·séries.";
        }

        return $conseils;
    }
}
