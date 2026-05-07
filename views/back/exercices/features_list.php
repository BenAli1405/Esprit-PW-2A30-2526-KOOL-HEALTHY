<?php
/**
 * Vue : Gestion des caractéristiques d'exercices (Admin)
 * 
 * Variables disponibles :
 * - $exercices : Tous les exercices avec leurs features
 */
?>

<div class="container mt-5">
    <h1>Gestion des Caractéristiques d'Exercices</h1>
    <p>Gérez les features (intensité, équipement, difficulté, cible musculaire) pour l'algorithme KNN.</p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Exercice</th>
                    <th>Intensité</th>
                    <th>Équipement</th>
                    <th>Difficulté</th>
                    <th>Cible Musculaire</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($exercices as $exercice): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($exercice['nom']); ?></strong>
                            <?php if (!empty($exercice['session_date'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($exercice['session_date']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($exercice['id_feature']): ?>
                                <span class="badge bg-info"><?php echo $exercice['intensite_calorique']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non renseigné</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($exercice['id_feature']): ?>
                                <span class="badge bg-info"><?php echo $exercice['equipement']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non renseigné</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($exercice['id_feature']): ?>
                                <span class="badge bg-info"><?php echo $exercice['difficulte']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non renseigné</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($exercice['id_feature']): ?>
                                <span class="badge bg-info"><?php echo $exercice['cible_musculaire']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non renseigné</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?action=admin_edit_feature&id=<?php echo $exercice['id_exercice']; ?>" 
                               class="btn btn-sm btn-primary">Modifier</a>
                            <a href="index.php?action=admin_import_feature&id=<?php echo $exercice['id_exercice']; ?>" 
                               class="btn btn-sm btn-success">Importer API</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="index.php?action=admin_exercices" class="btn btn-secondary">Retour</a>
    </div>

    <div class="alert alert-info mt-4">
        <strong>À savoir :</strong>
        <ul class="mb-0">
            <li>Chaque valeur doit être entre 0 et 1</li>
            <li>0 = minimum (faible intensité, pas d'équipement, très facile, cible mineure)</li>
            <li>1 = maximum (haute intensité, équipement lourd, très difficile, cible majeure)</li>
            <li>Le bouton "Importer API" récupère les données de WorkoutX et normalise les features automatiquement</li>
        </ul>
    </div>
</div>

<style>
    .badge {
        font-size: 0.9rem;
        padding: 5px 10px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
