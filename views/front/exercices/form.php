<div class="card-panel">
  <?php $errors = $errors ?? ($this->errors ?? []) ?? []; ?>
  <?php $data = $data ?? []; ?>
  <?php $editing = $editing ?? false; ?>
  <?php $entrainement = $entrainement ?? []; ?>
  <?php $returnId = $editing ? ($data['id_entrainement'] ?? '') : ($entrainement['id_entrainement'] ?? ''); ?>
  <?php $returnUrl = $returnId !== '' ? 'index.php?action=voir_exercices&id=' . urlencode($returnId) : 'index.php?action=mes_entrainements'; ?>
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
    <div class="form-group"><label>Nom de l'exercice</label><input type="text" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>"></div>
    <div class="form-group"><label>Séries</label><input type="text" name="series" value="<?= htmlspecialchars($data['series'] ?? '') ?>"></div>
    <div class="form-group"><label>Répétitions</label><input type="text" name="repetitions" value="<?= htmlspecialchars($data['repetitions'] ?? '') ?>"></div>
    <div class="form-group"><label>Repos (secondes)</label><input type="text" name="repos_secondes" value="<?= htmlspecialchars($data['repos_secondes'] ?? '') ?>"></div>
    <div class="form-group"><label>Ordre</label><input type="text" name="ordre" value="<?= htmlspecialchars($data['ordre'] ?? '') ?>"></div>
    <div class="button-row">
      <button class="btn-primary" type="submit">
        <?= $editing ? 'Enregistrer' : 'Ajouter l\'exercice' ?>
      </button>
      <a class="btn-secondary" href="<?= htmlspecialchars($returnUrl) ?>">Retour</a>
    </div>
  </form>
</div>
