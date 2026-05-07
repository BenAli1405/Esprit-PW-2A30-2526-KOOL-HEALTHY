<?php
/**
 * Vue : Formulaire de modification des caractéristiques d'exercices
 * 
 * Variables disponibles :
 * - $exercice : Données de l'exercice
 * - $feature : Données des features (peut être vide)
 * - $editing : Booléen indiquant si c'est une modification
 * - $errors : Erreurs de validation
 */
?>

<div class="container mt-5">
    <h1><?php echo $editing ? 'Modifier les caractéristiques' : 'Ajouter les caractéristiques'; ?> : <?php echo htmlspecialchars($exercice['nom']); ?></h1>

    <!-- Affichage des erreurs -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <!-- Intensité Calorique -->
                    <div class="col-md-6 mb-3">
                        <label for="intensite_calorique" class="form-label">Intensité Calorique <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="intensite_calorique" 
                            name="intensite_calorique" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($feature['intensite_calorique'] ?? ''); ?>"
                            placeholder="0.00"
                        >
                        <small class="form-text text-muted">
                            Entre 0 (très faible) et 1 (très intense). Ex: 0.75
                        </small>
                    </div>

                    <!-- Équipement -->
                    <div class="col-md-6 mb-3">
                        <label for="equipement" class="form-label">Équipement <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="equipement" 
                            name="equipement" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($feature['equipement'] ?? ''); ?>"
                            placeholder="0.00"
                        >
                        <small class="form-text text-muted">
                            Entre 0 (poids du corps) et 1 (équipement lourd). Ex: 0.50
                        </small>
                    </div>

                    <!-- Difficulté -->
                    <div class="col-md-6 mb-3">
                        <label for="difficulte" class="form-label">Difficulté <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="difficulte" 
                            name="difficulte" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($feature['difficulte'] ?? ''); ?>"
                            placeholder="0.00"
                        >
                        <small class="form-text text-muted">
                            Entre 0 (très facile) et 1 (très difficile). Ex: 0.60
                        </small>
                    </div>

                    <!-- Cible Musculaire -->
                    <div class="col-md-6 mb-3">
                        <label for="cible_musculaire" class="form-label">Cible Musculaire <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            id="cible_musculaire" 
                            name="cible_musculaire" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($feature['cible_musculaire'] ?? ''); ?>"
                            placeholder="0.00"
                        >
                        <small class="form-text text-muted">
                            Entre 0 (stabilisateurs) et 1 (grands groupes). Ex: 0.80
                        </small>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <strong>Guide de normalisation :</strong>
                    <ul class="mb-0">
                        <li><strong>Intensité :</strong> 0.1-0.3 (faible), 0.4-0.6 (modéré), 0.7-1.0 (haute)</li>
                        <li><strong>Équipement :</strong> 0.1 (corps), 0.3-0.5 (léger), 0.6-0.8 (moyen), 0.9+ (lourd)</li>
                        <li><strong>Difficulté :</strong> 0.0-0.3 (débutant), 0.3-0.6 (intermédiaire), 0.6-1.0 (avancé)</li>
                        <li><strong>Cible :</strong> 0.3-0.5 (petit groupe), 0.6-0.8 (groupe moyen), 0.8-1.0 (grand groupe)</li>
                    </ul>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $editing ? 'Mettre à jour' : 'Ajouter'; ?>
                    </button>
                    <a href="index.php?action=admin_features" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .text-danger {
        color: #dc3545;
    }
</style>

<script>
    // Validation simple côté client (JS pur, pas HTML5)
    document.querySelector('form').addEventListener('submit', function(e) {
        const fields = ['intensite_calorique', 'equipement', 'difficulte', 'cible_musculaire'];
        let hasError = false;

        fields.forEach(field => {
            const input = document.getElementById(field);
            const value = parseFloat(input.value);

            if (isNaN(value) || value < 0 || value > 1) {
                input.classList.add('is-invalid');
                hasError = true;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Erreur : Chaque valeur doit être un nombre entre 0 et 1.');
        }
    });
</script>
