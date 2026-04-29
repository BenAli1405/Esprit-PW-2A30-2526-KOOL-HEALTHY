<?php
require_once __DIR__ . '/RecetteController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../MODEL/Recette.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../VIEW/fil-recettes.php');
    exit();
}

$controller = new RecetteController();
$authController = new AuthController();
$action = $_GET['action'] ?? 'create';
$utilisateurConnecte = $_SESSION['utilisateur'] ?? null;

if ($authController->estAdmin($utilisateurConnecte)) {
    header('Location: ../VIEW/backoffice.php');
    exit();
}

$auteurConnecte = trim((string) ($utilisateurConnecte['nom'] ?? ''));

if ($auteurConnecte === '') {
    header('Location: ../VIEW/auth.php');
    exit();
}

if ($action === 'delete') {
    $recetteId = (int) ($_POST['id'] ?? 0);
    if ($recetteId <= 0) {
        header('Location: ../VIEW/mes-recettes.php?error=invalid_recipe');
        exit();
    }

    $recetteExistante = $controller->obtenirRecette($recetteId);
    if (!$recetteExistante || (string) ($recetteExistante['auteur'] ?? '') !== $auteurConnecte) {
        header('Location: ../VIEW/mes-recettes.php?error=unauthorized');
        exit();
    }

    $controller->supprimerRecette($recetteId);
    header('Location: ../VIEW/mes-recettes.php?success=recipe_deleted');
    exit();
}

$titre = trim((string) ($_POST['titre'] ?? ''));
$tempsPrep = (int) ($_POST['temps_prep'] ?? 0);
$ingredients = trim((string) ($_POST['ingredients'] ?? ''));
$etapes = trim((string) ($_POST['etapes'] ?? ''));

if ($titre === '' || $ingredients === '' || $tempsPrep < 1) {
    $redirect = $action === 'update' ? '../VIEW/mes-recettes.php?error=invalid_data' : '../VIEW/fil-recettes.php?error=invalid_data';
    header('Location: ' . $redirect);
    exit();
}

$image = null;
if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['image']['tmp_name'];
    $imgType = mime_content_type($imgTmp);
    $imgData = base64_encode((string) file_get_contents($imgTmp));
    $image = 'data:' . $imgType . ';base64,' . $imgData;
}

if ($action === 'update') {
    $recetteId = (int) ($_POST['id'] ?? 0);
    if ($recetteId <= 0) {
        header('Location: ../VIEW/mes-recettes.php?error=invalid_recipe');
        exit();
    }

    $recetteExistante = $controller->obtenirRecette($recetteId);
    if (!$recetteExistante || (string) ($recetteExistante['auteur'] ?? '') !== $auteurConnecte) {
        header('Location: ../VIEW/mes-recettes.php?error=unauthorized');
        exit();
    }

    if ($image === null) {
        $image = $recetteExistante['image'] ?? null;
    }

    $recette = new Recette($titre, $tempsPrep, $ingredients, $etapes, $image, $auteurConnecte);
    $controller->modifierRecette($recette, $recetteId);
    header('Location: ../VIEW/mes-recettes.php?success=recipe_updated');
    exit();
}

$recette = new Recette($titre, $tempsPrep, $ingredients, $etapes, $image, $auteurConnecte);
$result = $controller->ajouterRecette($recette);

if ($result) {
    header('Location: ../VIEW/mes-recettes.php?success=recipe_created');
    exit();
}

header('Location: ../VIEW/fil-recettes.php?error=recipe_create_failed');
exit();
?>
