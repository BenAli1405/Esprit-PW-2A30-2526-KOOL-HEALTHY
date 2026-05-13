<div class="section-card">

  <!-- En-tête -->
  <div class="section-header">
    <div>
      <h2><i class="fas fa-chart-bar icon-highlight"></i> Gestion des Entraînements</h2>
      <p class="section-note">Liste de toutes les séances enregistrées par les utilisateurs.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="btn-outline" href="/integweb/sport/index.php?action=statistiques_entrainements">
        <i class="fas fa-chart-line"></i> Statistiques
      </a>
      <a class="btn-primary" href="/integweb/sport/index.php?action=ajouter_entrainement">
        <i class="fas fa-plus"></i> Nouvelle séance
      </a>
    </div>
  </div>

  <!-- Filtre -->
  <form method="GET" action="/integweb/sport/index.php" style="margin-bottom:20px;">
    <input type="hidden" name="action" value="admin_entrainements">
    <div class="search-container">
      <label class="search-label"><i class="fas fa-filter"></i> Filtrer :</label>
      <select name="type_sport" class="search-select">
        <option value="">— Tous les sports —</option>
        <?php foreach ($sportTypes ?? [] as $sport): ?>
          <option value="<?= htmlspecialchars($sport) ?>"
            <?= ($_GET['type_sport'] ?? '') === $sport ? 'selected' : '' ?>>
            <?= htmlspecialchars($sport) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Filtrer</button>
      <a href="/integweb/sport/index.php?action=admin_entrainements" class="btn-outline">
        <i class="fas fa-times"></i> Réinitialiser
      </a>
      <span class="search-results-info">
        <?= count($entrainements ?? []) ?> séance(s) trouvée(s)
      </span>
    </div>
  </form>

  <!-- Tableau -->
  <?php if (empty($entrainements)): ?>
    <div class="empty-table-row">
      <i class="fas fa-running" style="font-size:2.5rem;color:var(--gris-moyen);margin-bottom:12px;display:block;"></i>
      Aucune séance trouvée.
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th><i class="fas fa-calendar-alt"></i> Date</th>
            <th><i class="fas fa-running"></i> Sport</th>
            <th><i class="fas fa-user"></i> Utilisateur</th>
            <th><i class="fas fa-clock"></i> Durée</th>
            <th><i class="fas fa-fire"></i> Calories</th>
            <th><i class="fas fa-dumbbell"></i> Exercices</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($entrainements as $i => $seance): ?>
            <tr>
              <td style="color:var(--gris-texte);font-size:0.78rem;"><?= $i + 1 ?></td>
              <td style="font-weight:600;"><?= htmlspecialchars(formatDateFR($seance['date'])) ?></td>
              <td>
                <span style="background:var(--bleu-tech-light);color:var(--bleu-tech-dark);padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                  <?= htmlspecialchars($seance['type_sport']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($seance['user_nom'] ?? $seance['utilisateur_id'] ?? '—') ?></td>
              <td><i class="fas fa-clock" style="color:var(--gris-texte);margin-right:4px;"></i><?= htmlspecialchars($seance['duree_minutes']) ?> min</td>
              <td>
                <span style="color:var(--danger);font-weight:700;">
                  <i class="fas fa-fire"></i> <?= htmlspecialchars($seance['calories_brulees']) ?>
                </span>
              </td>
              <td>
                <span class="status-active">
                  <i class="fas fa-dumbbell"></i> <?= $seance['nb_exercices'] ?>
                </span>
              </td>
              <td class="action-icons">
                <a href="/integweb/sport/index.php?action=voir_exercices&id=<?= $seance['id_entrainement'] ?>"
                   title="Voir exercices" style="color:var(--vert-kool);margin:0 4px;">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="/integweb/sport/index.php?action=modifier_entrainement&id=<?= $seance['id_entrainement'] ?>"
                   title="Modifier" class="edit-icon" style="margin:0 4px;">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="/integweb/sport/index.php?action=supprimer_entrainement&id=<?= $seance['id_entrainement'] ?>"
                   title="Supprimer" class="delete-icon" style="margin:0 4px;"
                   onclick="return confirm('Supprimer cette séance ?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      Total : <strong><?= count($entrainements) ?></strong> séance(s)
    </div>
  <?php endif; ?>
</div>
