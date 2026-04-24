<?php
require_once __DIR__ . '/../lib/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Inclusion des modèles
include_once __DIR__ . '/../MODEL/PlanModel.php';
include_once __DIR__ . '/../MODEL/RepasModel.php';

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId <= 0) {
    die('Plan non spécifié.');
}

$planModel = new PlanModel();
$repasModel = new RepasModel();

$plan = $planModel->find($planId);
if (!$plan) {
    die('Plan introuvable.');
}

$repas = $repasModel->getByPlanId($planId);

// Mappage des labels
$objectifLabels = [
    'perte-poids' => 'Perte de poids',
    'maintien' => 'Maintien',
    'prise-muscle' => 'Prise de muscle'
];

$typeRepasLabels = [
    'petit_dejeuner' => 'Petit-déjeuner',
    'dejeuner' => 'Déjeuner',
    'diner' => 'Dîner',
    'collation' => 'Collation'
];

$statutLabels = [
    'prevu' => 'Prévu',
    'consomme' => 'Consommé',
    'annule' => 'Annulé'
];

// Remplacer la police par défaut par DejaVu Sans pour les émojis
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Groupement des repas par jour
$repasParJour = [];
if (!empty($repas)) {
    foreach ($repas as $r) {
        $repasParJour[$r['date']][] = $r;
    }
    ksort($repasParJour);

    $typeOrder = ['petit_dejeuner' => 1, 'dejeuner' => 2, 'collation' => 3, 'diner' => 4];
    foreach ($repasParJour as $date => &$repasJour) {
        usort($repasJour, function($a, $b) use ($typeOrder) {
            if (!empty($a['heure_prevue']) && !empty($b['heure_prevue'])) {
                return strcmp($a['heure_prevue'], $b['heure_prevue']);
            }
            $orderA = $typeOrder[$a['type_repas']] ?? 99;
            $orderB = $typeOrder[$b['type_repas']] ?? 99;
            return $orderA <=> $orderB;
        });
    }
}

$caloriesObjectif = $plan['objectif'] === 'perte-poids' ? 1750 : ($plan['objectif'] === 'prise-muscle' ? 2500 : 2000);
$totalRepas = count($repas);

$jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$mois = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Plan Nutritionnel - ' . htmlspecialchars($plan['nom']) . '</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            margin: 0;
            padding: 20px;
            color: #2c3e2f;
            font-size: 13px;
        }
        .header {
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 { margin: 0 0 5px; font-size: 24px; }
        .header p { margin: 0; font-size: 14px; opacity: 0.9; }
        
        .section-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2f8a43;
            margin-bottom: 12px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 5px;
        }
        
        /* Section 1: Infos */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 6px 0; border-bottom: 1px solid #f0f0f0; }
        .info-table td.label { font-weight: bold; color: #4a9b8e; width: 40%; }
        
        /* Section 2: Récapitulatif */
        .recap-container { width: 100%; text-align: center; }
        .recap-box {
            display: inline-block;
            width: 30%;
            background: #f6fbf7;
            border: 1px solid #dcedc8;
            border-radius: 8px;
            padding: 10px;
            margin: 0 1%;
            vertical-align: top;
        }
        .recap-box .val { font-size: 18px; font-weight: bold; color: #2e7d32; display: block; margin-bottom: 3px; }
        .recap-box .lbl { font-size: 11px; color: #555; text-transform: uppercase; }

        /* Section 3: Repas par jour */
        .day-title {
            background: #e8f5e9;
            color: #1b5e20;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 6px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .meal-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .meal-row { border-bottom: 1px solid #eee; }
        .meal-row td { padding: 8px; vertical-align: middle; }
        .meal-row.alt { background: #fafafa; }
        .meal-icon { font-size: 18px; width: 30px; text-align: center; }
        .meal-details { width: 45%; }
        .meal-name { font-weight: bold; color: #333; font-size: 14px; }
        .meal-recipe { color: #666; font-size: 12px; margin-top: 2px; }
        .meal-time { width: 15%; color: #555; text-align: center; }
        .meal-cal { width: 15%; font-weight: bold; text-align: center; color: #e65100; }
        .meal-status { width: 15%; text-align: right; }

        .total-day {
            text-align: right;
            font-weight: bold;
            color: #d84315;
            padding: 8px;
            font-size: 14px;
        }

        /* Section 4: IA */
        .ia-box { font-style: italic; color: #444; background: #fffde7; padding: 12px; border-left: 4px solid #fbc02d; border-radius: 4px; }
        
        .footer { text-align: center; margin-top: 30px; color: #999; font-size: 10px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌿 KOOL HEALTHY</h1>
        <p>Plan Nutritionnel Personnalisé</p>
    </div>

    <div class="section-card">
        <div class="section-title">📋 INFORMATIONS DU PLAN</div>
        <table class="info-table">
            <tr><td class="label">Nom du plan</td><td>' . htmlspecialchars($plan['nom']) . '</td></tr>
            <tr><td class="label">Objectif</td><td>' . ($objectifLabels[$plan['objectif']] ?? 'Maintien') . '</td></tr>
            <tr><td class="label">Durée</td><td>' . htmlspecialchars($plan['duree']) . ' jours</td></tr>
            <tr><td class="label">Préférence</td><td>' . htmlspecialchars($plan['preference'] ?? 'Standard') . '</td></tr>
            <tr><td class="label">Allergies</td><td>' . htmlspecialchars($plan['allergies'] ?? 'Aucune') . '</td></tr>
        </table>
    </div>

    <div class="section-card" style="text-align: center;">
        <div class="section-title" style="text-align: left;">📊 RÉCAPITULATIF</div>
        <div class="recap-container">
            <div class="recap-box">
                <span class="val">' . $caloriesObjectif . '</span>
                <span class="lbl">kcal / jour</span>
            </div>
            <div class="recap-box">
                <span class="val">' . ($objectifLabels[$plan['objectif']] ?? 'Maintien') . '</span>
                <span class="lbl">Objectif</span>
            </div>
            <div class="recap-box">
                <span class="val">' . $totalRepas . '</span>
                <span class="lbl">Repas totaux</span>
            </div>
        </div>
    </div>';

if (!empty($repasParJour)) {
    $html .= '<div class="section-card">';
    $html .= '<div class="section-title">🍽️ REPAS PAR JOUR</div>';
    
    foreach ($repasParJour as $date => $repasJour) {
        $ts = strtotime($date);
        $jourMoisStr = mb_strtoupper($jours[date('w', $ts)] . ' ' . date('d', $ts) . ' ' . $mois[(int)date('m', $ts)] . ' ' . date('Y'), 'UTF-8');
        
        $html .= '<div class="day-title">📅 ' . $jourMoisStr . '</div>';
        $html .= '<table class="meal-table">';
        
        $caloriesJour = 0;
        $alt = false;

        foreach ($repasJour as $r) {
            $icon = '🍽️';
            $nomType = 'Repas';
            if ($r['type_repas'] === 'petit_dejeuner') { $icon = '🍳'; $nomType = 'Petit-déjeuner'; }
            if ($r['type_repas'] === 'dejeuner') { $icon = '🍲'; $nomType = 'Déjeuner'; }
            if ($r['type_repas'] === 'diner') { $icon = '🍝'; $nomType = 'Dîner'; }
            if ($r['type_repas'] === 'collation') { $icon = '🍎'; $nomType = 'Collation'; }
            
            $statusIcon = '→';
            if ($r['statut'] === 'consomme') $statusIcon = '✅';
            if ($r['statut'] === 'annule') $statusIcon = '❌';

            $heure = !empty($r['heure_prevue']) ? substr($r['heure_prevue'], 0, 5) : '--:--';
            $cal = !empty($r['calories_consommees']) ? (int)$r['calories_consommees'] : 0;
            $caloriesJour += $cal;
            
            $bgClass = $alt ? 'alt' : '';
            $alt = !$alt;

            $html .= '<tr class="meal-row ' . $bgClass . '">';
            $html .= '<td class="meal-icon">' . $icon . '</td>';
            $html .= '<td class="meal-details"><div class="meal-name">' . $nomType . '</div><div class="meal-recipe">' . htmlspecialchars($r['nom_recette'] ?? '—') . '</div></td>';
            $html .= '<td class="meal-time">' . $heure . '</td>';
            $html .= '<td class="meal-cal">' . ($cal > 0 ? $cal . ' kcal' : '—') . '</td>';
            $html .= '<td class="meal-status">' . $statusIcon . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '<div class="total-day">➜ Total : ' . $caloriesJour . ' / ' . $caloriesObjectif . ' kcal</div>';
    }
    $html .= '</div>';
} else {
    $html .= '<div class="section-card"><div class="section-title">🍽️ REPAS PAR JOUR</div><p style="text-align:center;color:#666;">Aucun repas planifié.</p></div>';
}

$recommandation = "Pensez à bien vous hydrater tout au long de la journée (1.5L à 2L d'eau). Écoutez vos sensations de satiété.";
if ($plan['objectif'] === 'perte-poids') {
    $recommandation = "Privilégiez les fibres et les protéines pour augmenter la satiété. Évitez les sucres rapides en dehors des périodes d'entraînement.";
} elseif ($plan['objectif'] === 'prise-muscle') {
    $recommandation = "Assurez-vous un bon apport en protéines post-entraînement et ne négligez pas les glucides pour l'énergie.";
}

$html .= '
    <div class="section-card">
        <div class="section-title">💡 RECOMMANDATION IA</div>
        <div class="ia-box">" ' . $recommandation . ' "</div>
    </div>
    
    <div class="footer">
        &copy; Kool Healthy - Plan généré le ' . date('d/m/Y à H:i') . '
    </div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream('plan_nutritionnel_' . $plan['id'] . '_' . date('Y-m-d') . '.pdf', [
    'Attachment' => true
]);
