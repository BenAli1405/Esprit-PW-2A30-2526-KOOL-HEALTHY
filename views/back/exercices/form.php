<div class="card-panel">
  <?php $errors = $errors ?? $this->errors ?? []; ?>
  <?php $data = $data ?? []; ?>
  <?php $entrainements = $entrainements ?? []; ?>
  <?php $editing = $editing ?? false; ?>
  <h3><?= $editing ? 'Modifier un exercice' : 'Ajouter un exercice' ?></h3>
  <?php if (!empty($errors)): ?>
    <div class="alert">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form id="exercice-form" method="post" action="">
    <div id="exercice-errors" class="alert hidden"></div>
    <div class="form-group">
      <label>Séance</label>
      <select name="id_entrainement">
        <option value="">Sélectionnez</option>
        <?php foreach ($entrainements as $ent): ?>
          <option value="<?= $ent['id_entrainement'] ?>" <?= (isset($data['id_entrainement']) && $data['id_entrainement'] == $ent['id_entrainement']) ? 'selected' : '' ?>>Séance #<?= $ent['id_entrainement'] ?> - <?= htmlspecialchars($ent['date']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Nom de l'exercice</label><input type="text" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>"></div>
    <div class="form-group"><label>Séries</label><input type="text" name="series" value="<?= htmlspecialchars($data['series'] ?? '') ?>"></div>
    <div class="form-group"><label>Répétitions</label><input type="text" name="repetitions" value="<?= htmlspecialchars($data['repetitions'] ?? '') ?>"></div>
    <div class="form-group"><label>Repos (secondes)</label><input type="text" name="repos_secondes" value="<?= htmlspecialchars($data['repos_secondes'] ?? '') ?>"></div>
    <div class="form-group"><label>Ordre</label><input type="text" name="ordre" value="<?= htmlspecialchars($data['ordre'] ?? '') ?>"></div>
    <div class="button-row">
      <button class="btn-primary" type="submit">
        <?= $editing ? 'Enregistrer' : 'Ajouter l\'exercice' ?>
      </button>
      <a class="btn-outline" href="index.php?action=admin_exercices">Retour</a>
    </div>
  </form>
</div>
     
