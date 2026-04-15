<div class="card-panel">
  <h3><?= $editing ? 'Modifier une séance' : 'Ajouter une séance' ?></h3>
  <?php if (!empty($this->errors ?? [])): ?>
    <div class="alert">
      <ul>
        <?php foreach ($this->errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form id="entrainement-form" method="post" action="">
    <div id="entrainement-errors" class="alert hidden"></div>
    <div class="form-group"><label>Date</label><input type="text" name="date" value="<?= htmlspecialchars($data['date'] ?? '') ?>" placeholder="YYYY-MM-DD"></div>
    <div class="form-group"><label>Durée (minutes)</label><input type="text" name="duree_minutes" value="<?= htmlspecialchars($data['duree_minutes'] ?? '') ?>"></div>
    <div class="form-group"><label>Type de sport</label><input type="text" name="type_sport" value="<?= htmlspecialchars($data['type_sport'] ?? '') ?>"></div>
    <div class="form-group"><label>Calories brûlées</label><input type="text" name="calories_brulees" value="<?= htmlspecialchars($data['calories_brulees'] ?? '') ?>"></div>
    <div class="form-group"><label>Commentaire</label><textarea name="commentaire"><?= htmlspecialchars($data['commentaire'] ?? '') ?></textarea></div>
    <div class="button-row">
      <button class="btn-primary" type="submit"><?= $editing ? 'Enregistrer' : 'Créer la séance' ?></button>
      <a class="btn-secondary" href="index.php?action=mes_entrainements">Retour</a>
    </div>
  </form>
</div>
