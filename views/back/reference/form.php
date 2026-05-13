<div class="header-actions">
    <h2><?= $editing ? "Modifier l'exercice de référence" : 'Ajouter un exercice de référence' ?></h2>
    <a href="index.php?action=admin_reference_list" class="btn btn-secondary">Retour à la liste</a>
</div>

<div class="card">
    <?php if (!empty($this->errors)): ?>
        <div class="error-msg">
            <ul>
                <?php foreach ($this->errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Formulaire strict sans validation HTML5 -->
    <form action="" method="POST" class="form">

        <div class="form-group">
            <label for="nom">Nom de l'exercice</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>">
        </div>

        <p><strong>Caractéristiques (toutes les valeurs doivent être comprises entre 0 et 1)</strong></p>

        <div class="form-group">
            <label for="intensite_calorique">Intensité calorique (ex : 0.8)</label>
            <input type="text" id="intensite_calorique" name="intensite_calorique"
                   value="<?= htmlspecialchars($data['intensite_calorique'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="equipement">Équipement (0.0=aucun · 0.1=poids du corps · 0.5=haltères · 0.8=barre · 1.0=machine)</label>
            <input type="text" id="equipement" name="equipement"
                   value="<?= htmlspecialchars($data['equipement'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="difficulte">Difficulté (0.2=débutant · 0.6=intermédiaire · 1.0=avancé)</label>
            <input type="text" id="difficulte" name="difficulte"
                   value="<?= htmlspecialchars($data['difficulte'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="cible_musculaire">Cible musculaire — groupe large (0.2=jambes · 0.5=abdos · 0.7=dos/bras · 0.8=poitrine · 0.9=épaules)</label>
            <input type="text" id="cible_musculaire" name="cible_musculaire"
                   value="<?= htmlspecialchars($data['cible_musculaire'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="type_mouvement">Type de mouvement (0.1=mobilité · 0.3=isolation · 0.5=cardio · 0.7=plyométrie · 0.8=compound · 1.0=olympique)</label>
            <input type="text" id="type_mouvement" name="type_mouvement"
                   value="<?= htmlspecialchars($data['type_mouvement'] ?? '0.5') ?>">
        </div>

        <div class="form-group">
            <label for="groupe_primaire">Groupe primaire — muscle principal (0.15=quadriceps · 0.40=abdos · 0.55=biceps · 0.65=dorsaux · 0.80=épaules · 0.85=pectoraux)</label>
            <input type="text" id="groupe_primaire" name="groupe_primaire"
                   value="<?= htmlspecialchars($data['groupe_primaire'] ?? '0.5') ?>">
        </div>

        <button type="submit" class="btn btn-primary"><?= $editing ? 'Mettre à jour' : 'Ajouter' ?></button>
    </form>
</div>
