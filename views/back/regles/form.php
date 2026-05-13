<div class="card-panel">
  <?php $errors = $errors ?? $this->errors ?? []; ?>
  <?php $data = $data ?? []; ?>
  <?php $editing = $editing ?? false; ?>
  <h3><?= $editing ? 'Modifier une règle' : 'Ajouter une règle' ?></h3>
  <?php if (!empty($errors)): ?>
    <div class="alert">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" action="">
    <div class="form-group"><label>Type de repas</label><input type="text" name="type_repas" value="<?= htmlspecialchars($data['type_repas'] ?? '') ?>"></div>
    <div class="form-group"><label>Exercice suggéré</label><input type="text" name="exercice_suggere" value="<?= htmlspecialchars($data['exercice_suggere'] ?? '') ?>"></div>
    <div class="form-group"><label>Séries</label><input type="text" name="series" value="<?= htmlspecialchars($data['series'] ?? '') ?>"></div>
    <div class="form-group"><label>Répétitions</label><input type="text" name="repetitions" value="<?= htmlspecialchars($data['repetitions'] ?? '') ?>"></div>
    <div class="button-row">
      <button class="btn-primary" type="submit"><?= $editing ? 'Enregistrer' : 'Ajouter la règle' ?></button>
      <a class="btn-outline" href="index.php?action=admin_regles">Retour</a>
    </div>
  </form>
</div>
