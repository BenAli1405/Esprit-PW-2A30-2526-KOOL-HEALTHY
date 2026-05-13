<div class="card-panel">
  <?php $errors = $errors ?? $this->errors ?? []; ?>
  <?php $data = $data ?? []; ?>
  <?php $users = $users ?? []; ?>
  <?php $editing = $editing ?? false; ?>
  <h3><?= $editing ? 'Modifier une séance' : 'Ajouter une séance' ?></h3>
  <?php if (!empty($errors)): ?>
    <div class="alert">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form id="entrainement-form" method="post" action="">
    <div id="entrainement-errors" class="alert hidden"></div>
    <div class="form-group">
      <label>Utilisateur</label>
      <select name="id_utilisateur">
        <option value="">Sélectionnez</option>
        <?php foreach ($users as $user): ?>
          <option value="<?= $user['id_utilisateur'] ?>" <?= (isset($data['id_utilisateur']) && $data['id_utilisateur'] == $user['id_utilisateur']) ? 'selected' : '' ?>><?= htmlspecialchars($user['nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Date</label><input type="text" name="date" value="<?= htmlspecialchars($data['date'] ?? '') ?>" placeholder="YYYY-MM-DD"></div>
    <div class="form-group"><label>Durée (minutes)</label><input type="text" name="duree_minutes" value="<?= htmlspecialchars($data['duree_minutes'] ?? '') ?>"></div>
    <div class="form-group"><label>Type de sport</label><input type="text" name="type_sport" value="<?= htmlspecialchars($data['type_sport'] ?? '') ?>"></div>
    <div class="form-group"><label>Calories brûlées</label><input type="text" name="calories_brulees" value="<?= htmlspecialchars($data['calories_brulees'] ?? '') ?>"></div>
    <div class="form-group"><label>Commentaire</label><textarea name="commentaire"><?= htmlspecialchars($data['commentaire'] ?? '') ?></textarea></div>
    <div class="button-row">
      <button class="btn-primary" type="submit"><?= $editing ? 'Enregistrer' : 'Créer la séance' ?></button>
      <a class="btn-outline" href="index.php?action=admin_entrainements">Retour</a>
    </div>
  </form>
</div>
