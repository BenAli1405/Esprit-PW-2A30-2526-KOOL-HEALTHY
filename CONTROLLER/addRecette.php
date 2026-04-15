<?php
require_once __DIR__ . '/RecetteController.php';
require_once __DIR__ . '/../MODEL/Recette.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $titre = $_POST['titre'] ?? '';
    $temps_prep = $_POST['temps_prep'] ?? 0;
    $ingredients = $_POST['ingredients'] ?? '';
    $etapes = $_POST['etapes'] ?? '';
    $auteur = $_SESSION['utilisateur']['nom'] ?? ($_POST['auteur'] ?? 'Moi');

    // Gerer l'image
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_type = mime_content_type($img_tmp);
        $img_data = base64_encode(file_get_contents($img_tmp));
        $image = 'data:' . $img_type . ';base64,' . $img_data;
    }

    // Creer l'objet Recette
    $recette = new Recette($titre, $temps_prep, $ingredients, $etapes, $image, $auteur);

    // Sauvegarder dans la base de donnees
    $controller = new RecetteController();
    $result = $controller->ajouterRecette($recette);

    if ($result) {
        header('Location: ../VIEW/mes-recettes.php?success=1');
        exit();
    } else {
        header('Location: ../VIEW/fil-recettes.php?error=1');
        exit();
    }
}
?>
