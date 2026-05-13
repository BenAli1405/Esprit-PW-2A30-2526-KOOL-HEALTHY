<?php

require_once __DIR__ . '/../models/ExerciceModel.php';
require_once __DIR__ . '/../models/EntrainementModel.php';
require_once __DIR__ . '/../models/KnnModel.php';
require_once __DIR__ . '/../models/PerformanceModel.php';
require_once __DIR__ . '/../services/WorkoutXApiService.php';


class ExerciceController
{
    private $model;
    private $entrainementModel;
    private $knnModel;
    private $performanceModel;
    private $errors = [];

    public function __construct()
    {
        $this->model             = new \ExerciceModel();
        $this->entrainementModel = new \EntrainementModel();
        $this->knnModel          = new \KnnModel();
        $this->performanceModel  = new \PerformanceModel();
    }

    public function index()
    {
        $idEntrainement = (int)($_GET['id'] ?? 0);
        $entrainement = $this->entrainementModel->getById($idEntrainement);

        if (!$entrainement) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $exercices = $this->model->getAllByEntrainement($idEntrainement);
        $layout = 'front';
        $action = 'voir_exercices';
        $pageTitle = 'Kool Healthy | Exercices de la séance';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/list.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function create()
    {
        $idEntrainement = (int)($_GET['id'] ?? 0);
        $entrainement = $this->entrainementModel->getById($idEntrainement);

        if (!$entrainement) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $data = $this->postData();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validate($data)) {
                $data['id_entrainement'] = $idEntrainement;
                $this->model->create($data);
                header('Location: index.php?action=voir_exercices&id=' . $idEntrainement);
                exit;
            }
        }

        $editing = false;
        $layout = 'front';
        $action = 'ajouter_exercice';
        $pageTitle = 'Kool Healthy | Ajouter un exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $exercice = $this->model->getById($id);

        if (!$exercice) {
            header('Location: index.php?action=mes_entrainements');
            exit;
        }

        $entrainement = $this->entrainementModel->getById($exercice['id_entrainement']);
        $data = $exercice;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->postData();
            if ($this->validate($data)) {
                $this->model->update($id, $data);
                header('Location: index.php?action=voir_exercices&id=' . $exercice['id_entrainement']);
                exit;
            }
        }

        $editing = true;
        $layout = 'front';
        $action = 'modifier_exercice';
        $pageTitle = 'Kool Healthy | Modifier un exercice';
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/form.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        $exercice = $this->model->getById($id);

        if ($exercice) {
            $this->model->delete($id);
            header('Location: index.php?action=voir_exercices&id=' . $exercice['id_entrainement']);
            exit;
        }

        header('Location: index.php?action=mes_entrainements');
        exit;
    }

    /**
     * Recommandation KNN - Affiche les exercices de référence similaires.
     * Si l'exercice tapé n'existe pas en base, appelle l'API WorkoutX pour
     * l'importer automatiquement avant de lancer le calcul KNN.
     */
    public function recommanderKnn()
    {
        $similarExercises   = [];
        $exercices          = $this->knnModel->getAllReferenceExercises();
        $selectedExerciceId = null;
        $selectedExerciceNom = '';
        $apiImported        = false; // true si l'exercice vient d'être importé via API
        $this->errors       = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedExerciceNom = trim($_POST['exercice_nom'] ?? '');
            $selectedExerciceId  = (int)($_POST['id_exercice'] ?? 0);

            // --- Priorité au champ texte libre ---
            if ($selectedExerciceNom !== '') {
                $exercice = $this->knnModel->getExerciceByNom($selectedExerciceNom);

                if ($exercice) {
                    // Trouvé directement en base
                    $selectedExerciceId = (int)$exercice['id'];
                } else {
                    // Pas trouvé → tentative d'import via l'API WorkoutX
                    try {
                        $pdo     = (new \Database())->getConnection();
                        $service = new \WorkoutXApiService(null, $pdo);
                        $newId   = $service->fetchAndInsertExercise($selectedExerciceNom);

                        if ($newId !== null && $newId > 0) {
                            $selectedExerciceId = $newId;
                            $apiImported        = true;
                            // Rafraîchir la liste déroulante avec le nouvel exercice
                            $exercices = $this->knnModel->getAllReferenceExercises();
                        } else {
                            $this->errors[] = "L'exercice \"" . htmlspecialchars($selectedExerciceNom)
                                . "\" est introuvable dans la base et dans l'API WorkoutX. "
                                . "Vérifiez le nom ou ajoutez-le manuellement.";
                            $selectedExerciceId = 0;
                        }
                    } catch (\Exception $e) {
                        $this->errors[] = "Exercice non trouvé en base. Tentative API échouée : "
                            . $e->getMessage();
                        $selectedExerciceId = 0;
                    }
                }
            }

            // --- Validation minimale ---
            if ($selectedExerciceId === 0 && empty($this->errors)) {
                $this->errors[] = 'Veuillez sélectionner un exercice ou saisir un nom.';
            } elseif ($selectedExerciceId !== 0) {
                try {
                    $similarExercises = $this->knnModel->getSimilarExercises($selectedExerciceId, 5);
                } catch (Exception $e) {
                    $this->errors[] = 'Erreur lors du calcul KNN : ' . $e->getMessage();
                }
            }
        }

        $layout    = 'front';
        $action    = 'recommander_knn';
        $pageTitle = "Kool Healthy | Recommandation d'Exercices (KNN)";
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/recommander.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    // =========================================================================
    // PROGRESSION & ANALYSE
    // =========================================================================

    /**
     * Page d'analyse de la progression d'un exercice.
     *
     * Actions POST :
     *   - sous-action 'ajouter'    → enregistre une nouvelle performance
     *   - sous-action 'supprimer'  → supprime une performance par son ID
     *   (défaut)                   → charge + analyse + affichage
     */
    public function progression()
    {
        $this->errors = [];

        // ── ID utilisateur (simplifié : on prend l'utilisateur 1 si pas de session) ──
        // Adaptez selon votre système d'authentification.
        $idUtilisateur = (int)($_SESSION['id_utilisateur'] ?? 1);

        // ── Sous-actions POST ──────────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sousAction = $_POST['sous_action'] ?? '';

            // -- Ajouter une performance --
            if ($sousAction === 'ajouter') {
                $idExo = (int)($_POST['id_exercice'] ?? 0);
                $data  = [
                    'id_exercice'  => $idExo,
                    'date'         => trim($_POST['date']        ?? date('Y-m-d')),
                    'poids'        => $_POST['poids']            ?? null,
                    'repetitions'  => trim($_POST['repetitions'] ?? ''),
                    'series'       => trim($_POST['series']      ?? ''),
                    'fatigue'      => $_POST['fatigue']          ?? null,
                    'commentaire'  => trim($_POST['commentaire'] ?? ''),
                ];

                // Validation manuelle (pas de HTML5)
                if ($idExo <= 0) {
                    $this->errors[] = "Sélectionnez un exercice valide.";
                }
                if ($data['date'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
                    $this->errors[] = "La date est invalide (format AAAA-MM-JJ requis).";
                }
                if ($data['repetitions'] === '' || !ctype_digit($data['repetitions']) || (int)$data['repetitions'] <= 0) {
                    $this->errors[] = "Le nombre de répétitions doit être un entier positif.";
                }
                if ($data['series'] === '' || !ctype_digit($data['series']) || (int)$data['series'] <= 0) {
                    $this->errors[] = "Le nombre de séries doit être un entier positif.";
                }
                if ($data['poids'] !== null && $data['poids'] !== '' && !is_numeric($data['poids'])) {
                    $this->errors[] = "Le poids doit être un nombre (ex: 10.5).";
                }

                if (empty($this->errors)) {
                    $this->performanceModel->ajouterPerformance($data);
                    header('Location: index.php?action=progression&id=' . $idExo);
                    exit;
                }
            }

            // -- Supprimer une performance --
            if ($sousAction === 'supprimer') {
                $idPerf = (int)($_POST['id_performance'] ?? 0);
                $idExo  = (int)($_POST['id_exercice']   ?? 0);
                if ($idPerf > 0) {
                    $this->performanceModel->supprimerPerformance($idPerf);
                }
                header('Location: index.php?action=progression&id=' . $idExo);
                exit;
            }
        }

        // ── Exercice sélectionné (GET ?id= ou POST id_exercice) ───────────────
        $idExercice = (int)($_GET['id'] ?? $_POST['id_exercice'] ?? 0);

        // Liste des exercices ayant au moins une performance (pour le <select>)
        $exercicesListe = $this->performanceModel->getExercicesUtilisateur($idUtilisateur);

        // Si aucun ID sélectionné, prendre le premier de la liste
        if ($idExercice <= 0 && !empty($exercicesListe)) {
            $idExercice = (int)$exercicesListe[0]['id_exercice'];
        }

        // ── Données d'analyse ─────────────────────────────────────────────────
        $historique      = [];
        $nomExercice     = '';
        $regression      = null;
        $plateau         = null;
        $conseils        = [];
        $chargeJ30       = null;
        $joursObjectif   = null;

        $poidsCible       = trim((string)($_GET['poids_cible'] ?? $_POST['poids_cible'] ?? ''));
        $repetitionsCible = trim((string)($_GET['repetitions_cible'] ?? $_POST['repetitions_cible'] ?? ''));
        $seriesCible      = trim((string)($_GET['series_cible'] ?? $_POST['series_cible'] ?? ''));
        $chargeObjectif   = 0.0;

        if ($poidsCible !== '' && is_numeric($poidsCible)
            && $repetitionsCible !== '' && ctype_digit($repetitionsCible) && (int)$repetitionsCible > 0
            && $seriesCible !== '' && ctype_digit($seriesCible) && (int)$seriesCible > 0) {
            $chargeObjectif = round((float)$poidsCible * (int)$repetitionsCible * (int)$seriesCible, 1);
        }

        if ($idExercice > 0) {
            $historique  = $this->performanceModel->getHistorique($idExercice);
            $nomExercice = $this->performanceModel->getNomExercice($idExercice);

            if (count($historique) >= 2) {
                $regression = $this->performanceModel->calculerRegression($historique);

                if ($regression) {
                    $xDernier  = (float)$regression['xMax'];

                    // Prédiction dans 30 jours
                    $chargeJ30 = $this->performanceModel->predireCharge(
                        30,
                        $regression['pente'],
                        $regression['origine'],
                        $xDernier
                    );

                    // Objectif utilisateur
                    if ($chargeObjectif > 0) {
                        $joursObjectif = $this->performanceModel->joursAvantObjectif(
                            $chargeObjectif,
                            $regression['pente'],
                            $regression['origine'],
                            $xDernier
                        );
                    }

                    // Détection plateau
                    $plateau = $this->performanceModel->detecterPlateau($historique);

                    // Conseils
                    $conseils = $this->performanceModel->genererConseils(
                        $regression,
                        $plateau,
                        $chargeJ30
                    );
                }
            }
        }

        $layout    = 'front';
        $action    = 'progression';
        $pageTitle = "Kool Healthy | Analyse de progression";
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/front/exercices/progression.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    private function postData(): array
    {
        return [
            'nom' => trim($_POST['nom'] ?? ''),
            'series' => trim($_POST['series'] ?? ''),
            'repetitions' => trim($_POST['repetitions'] ?? ''),
            'repos_secondes' => trim($_POST['repos_secondes'] ?? ''),
            'ordre' => trim($_POST['ordre'] ?? ''),
        ];
    }

    private function validate(array $data): bool
    {
        $this->errors = [];

        if ($data['nom'] === '') {
            $this->errors[] = 'Le nom de l\'exercice est requis.';
        }

        if ($data['series'] === '' || !ctype_digit($data['series']) || (int)$data['series'] <= 0) {
            $this->errors[] = 'Le nombre de séries doit être un entier positif.';
        }

        if ($data['repetitions'] === '' || !ctype_digit($data['repetitions']) || (int)$data['repetitions'] <= 0) {
            $this->errors[] = 'Le nombre de répétitions doit être un entier positif.';
        }

        if ($data['repos_secondes'] === '' || !ctype_digit($data['repos_secondes']) || (int)$data['repos_secondes'] < 0) {
            $this->errors[] = 'Le repos doit être un nombre de secondes valide.';
        }

        if ($data['ordre'] === '' || !ctype_digit($data['ordre']) || (int)$data['ordre'] <= 0) {
            $this->errors[] = 'L\'ordre de l\'exercice doit être un entier positif.';
        }

        return empty($this->errors);
    }
}
