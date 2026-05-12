<?php
$restaurants = [
    ["nom" => "Dar El Jeld", "adresse" => "5-10 Rue Dar El Jeld, Tunis Médina", "cuisine" => "Tunisien gastronomique", "prix_moyen" => 100, "note" => 4.6, "eco_score" => "B", "vegetarien" => false, "telephone" => "+21671560916"],
    ["nom" => "Le Golfe", "adresse" => "Gammarth, Tunis", "cuisine" => "Méditerranéen / Fruits de mer", "prix_moyen" => 50, "note" => 4.4, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21671274939"],
    ["nom" => "The Cliff", "adresse" => "Gammarth, Tunis", "cuisine" => "International / Vue mer", "prix_moyen" => 65, "note" => 4.5, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21624600000"],
    ["nom" => "Café des Nattes", "adresse" => "Sidi Bou Saïd", "cuisine" => "Café / Tunisien traditionnel", "prix_moyen" => 15, "note" => 4.3, "eco_score" => "C", "vegetarien" => true, "telephone" => "+21671749661"],
    ["nom" => "El Ali", "adresse" => "Avenue Habib Bourguiba, Tunis", "cuisine" => "Tunisien rapide / Sandwichs", "prix_moyen" => 15, "note" => 4.1, "eco_score" => "C", "vegetarien" => false, "telephone" => "+21671321927"],
    ["nom" => "La Trattoria Italiana", "adresse" => "La Marsa, Tunis", "cuisine" => "Italienne", "prix_moyen" => 40, "note" => 4.4, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21671747514"],
    ["nom" => "Sushishop Tunis", "adresse" => "Lac 2, Tunis", "cuisine" => "Japonais / Sushi", "prix_moyen" => 50, "note" => 4.5, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21671861001"],
    ["nom" => "Ben's Burgers", "adresse" => "Centre Urbain Nord, Tunis", "cuisine" => "Fast-food / Burgers", "prix_moyen" => 20, "note" => 4.2, "eco_score" => "C", "vegetarien" => true, "telephone" => "+21629123456"],
    ["nom" => "Veggy Delight", "adresse" => "Mutuelleville, Tunis", "cuisine" => "Végétarien / Vegan", "prix_moyen" => 22, "note" => 4.3, "eco_score" => "A", "vegetarien" => true, "telephone" => "+21650111222"],
    ["nom" => "Le Grand Café", "adresse" => "La Marsa", "cuisine" => "Café / Brunch", "prix_moyen" => 20, "note" => 4.2, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21671743000"],
    ["nom" => "Dar Slah", "adresse" => "Rue Sidi Ben Arous, La Médina", "cuisine" => "Tunisien authentique", "prix_moyen" => 27, "note" => 4.4, "eco_score" => "C", "vegetarien" => false, "telephone" => "+21671261026"],
    ["nom" => "Restaurant Al Mutawassit", "adresse" => "Carthage Hannibal", "cuisine" => "Méditerranéen", "prix_moyen" => 50, "note" => 4.3, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21671731215"],
    ["nom" => "La Villa Didon", "adresse" => "Byrsa, Carthage", "cuisine" => "Tunisien / Méditerranéen", "prix_moyen" => 80, "note" => 4.7, "eco_score" => "B", "vegetarien" => true, "telephone" => "+21671733433"],
    ["nom" => "L'Entracte", "adresse" => "La Marsa", "cuisine" => "Brasserie / International", "prix_moyen" => 32, "note" => 4.2, "eco_score" => "C", "vegetarien" => true, "telephone" => "+21671742654"],
    ["nom" => "Jar Ammar", "adresse" => "La Médina, Tunis", "cuisine" => "Tunisien traditionnel", "prix_moyen" => 27, "note" => 4.2, "eco_score" => "C", "vegetarien" => false, "telephone" => "+21622334455"]
];

// Récupération des poids dynamiques (0 à 100) depuis l'URL
$w_prix = isset($_GET['p_prix']) ? (int)$_GET['p_prix'] : 50;
$w_eco = isset($_GET['p_eco']) ? (int)$_GET['p_eco'] : 50;
$w_plaisir = isset($_GET['p_plaisir']) ? (int)$_GET['p_plaisir'] : 50;
$w_temps = isset($_GET['p_temps']) ? (int)$_GET['p_temps'] : 50; // Utilisé comme un proxy d'accessibilité rapide / fast-food vs gastro
$is_vege_required = isset($_GET['vegetarien']) && $_GET['vegetarien'] == '1';

// Normaliser les poids pour que leur somme soit 100%
$total_w = $w_prix + $w_eco + $w_plaisir + $w_temps;
if ($total_w === 0) {
    $w_prix = $w_eco = $w_plaisir = $w_temps = 25;
    $total_w = 100;
}

// Mapping Eco Score
$eco_map = ['A' => 100, 'B' => 75, 'C' => 50, 'D' => 25, 'E' => 0];

// Fonction pour calculer le pourcentage de match
function calculateMatch($resto, $w_prix, $w_eco, $w_plaisir, $w_temps, $total_w, $eco_map) {
    // Score Prix (Moins c'est cher, plus le score est élevé. Ex: 15 TND = 100, 120 TND = 0)
    $score_prix = max(0, 100 - (($resto['prix_moyen'] - 15) / 105 * 100));
    
    // Score Eco
    $score_eco = $eco_map[$resto['eco_score']] ?? 0;

    // Score Note (Plaisir)
    $score_note = ($resto['note'] / 5) * 100;

    // Score Temps/Type (Si le user veut de la rapidité, on favorise les prix bas/fast-food qui sont souvent plus rapides)
    // C'est une approximation vu qu'on n'a pas le temps exact pour les restos.
    $score_temps = max(0, 100 - (($resto['prix_moyen'] - 15) / 105 * 100));

    // Moyenne pondérée
    $match = ($score_prix * $w_prix + $score_eco * $w_eco + $score_note * $w_plaisir + $score_temps * $w_temps) / $total_w;
    
    return round($match);
}

// Calculer le match pour tous
foreach ($restaurants as &$resto) {
    $resto['match'] = calculateMatch($resto, $w_prix, $w_eco, $w_plaisir, $w_temps, $total_w, $eco_map);
}

// Filtrage strict : UNIQUEMENT pour le végétarien
$filtered = array_filter($restaurants, function($r) use ($is_vege_required) {
    if ($is_vege_required && !$r['vegetarien']) return false;
    return true;
});

// Tri par match dynamique
usort($filtered, function($a, $b) {
    return $b['match'] <=> $a['match'];
});

// Résultats finaux
$results = $filtered;
$is_fallback = false;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants Recommandés - Kool Healthy</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/restaurants.css">
</head>
<body>
    <header class="header">
        <div class="logo">🍽️ Kool Restaurants</div>
        <p class="subtitle">Vos recommandations en Tunisie basées sur vos critères d'orchestrateur.</p>
        <div class="criteria-tags">
            <span class="tag">Poids Budget : <?= round(($w_prix / $total_w) * 100) ?>%</span>
            <span class="tag">Poids Éco : <?= round(($w_eco / $total_w) * 100) ?>%</span>
            <?= $is_vege_required ? '<span class="tag">Végé-friendly</span>' : '' ?>
        </div>
    </header>

    <main class="container">
        <?php if ($is_fallback): ?>
            <div class="alert-warning">
                ⚠️ Aucun restaurant ne correspond exactement à tous vos critères. Voici les 3 meilleures alternatives !
            </div>
        <?php endif; ?>

        <div class="restaurant-list">
            <?php foreach ($results as $resto): ?>
                <div class="card">
                    <div class="card-header">
                        <div class="match-badge"><?= $resto['match'] ?>% Match</div>
                        <h2 class="resto-name"><?= htmlspecialchars($resto['nom']) ?></h2>
                        <div class="resto-cuisine"><?= htmlspecialchars($resto['cuisine']) ?></div>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label">Prix moyen :</span>
                            <span class="info-value price"><?= $resto['prix_moyen'] ?> TND</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Éco-Score :</span>
                            <span class="info-value eco-<?= strtolower($resto['eco_score']) ?>"><?= $resto['eco_score'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Avis Google :</span>
                            <span class="info-value star">⭐ <?= $resto['note'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Adresse :</span>
                            <span class="info-value addr"><?= htmlspecialchars($resto['adresse']) ?></span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($resto['nom'] . ' ' . $resto['adresse']) ?>" target="_blank" class="btn btn-map">📍 Voir sur Maps</a>
                        <a href="tel:<?= urlencode($resto['telephone']) ?>" class="btn btn-call">📞 Appeler</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
