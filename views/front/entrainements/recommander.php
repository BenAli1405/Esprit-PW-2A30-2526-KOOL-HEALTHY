<div class="card-panel">
  <h3>Recommander des exercices</h3>
  <p>Choisissez le type de repas que vous venez de prendre pour obtenir des suggestions adaptées.</p>
  <?php if (!empty($this->errors ?? [])): ?>
    <div class="alert">
      <ul>
        <?php foreach ($this->errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" action="">
    <div class="form-group">
      <label>Type de repas</label>
      <select name="type_repas">
        <option value="">Sélectionnez</option>
        <option value="Léger" <?= ($selectedType ?? '') === 'Léger' ? 'selected' : '' ?>>Léger</option>
        <option value="Équilibré" <?= ($selectedType ?? '') === 'Équilibré' ? 'selected' : '' ?>>Équilibré</option>
        <option value="Riche" <?= ($selectedType ?? '') === 'Riche' ? 'selected' : '' ?>>Riche</option>
      </select>
    </div>
    <div class="button-row">
      <button class="btn-primary" type="submit">Obtenir des recommandations</button>
      <a class="btn-secondary" href="index.php?action=mes_entrainements">Retour</a>
    </div>
  </form>

  <?php if (!empty($suggestions)): ?>
    <div class="card-panel card-panel--spaced">
      <h4>Suggestions pour « <?= htmlspecialchars($selectedType) ?> »</h4>
      <table class="data-table">
        <thead><tr><th>Exercice</th><th>Séries</th><th>Répétitions</th></tr></thead>
        <tbody>
          <?php foreach ($suggestions as $suggestion): ?>
            <tr>
              <td><?= htmlspecialchars($suggestion['exercice_suggere']) ?></td>
              <td><?= htmlspecialchars($suggestion['series']) ?></td>
              <td><?= htmlspecialchars($suggestion['repetitions']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
