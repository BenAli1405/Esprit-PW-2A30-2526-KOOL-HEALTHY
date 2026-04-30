<div class="card-panel">
  <div class="section-header">
    <h3 class="no-margin">Mes séances</h3>
    <div>
      <a class="btn-primary" href="index.php?action=statistiques"><i class="fas fa-chart-bar"></i> Statistiques</a>
      <a class="btn-primary" href="index.php?action=ajouter_entrainement"><i class="fas fa-plus"></i> Ajouter une séance</a>
    </div>
  </div>

  <!-- Filtre par type de sport -->
  <div style="margin: 20px 0; padding: 15px; background-color: #f5f5f5; border-radius: 4px;">
    <form method="GET" action="index.php">
      <input type="hidden" name="action" value="mes_entrainements">
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <label for="search">Rechercher :</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Mot-clé dans sport ou commentaire" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; flex: 1; min-width: 200px;">
        <label for="typeSport">Filtrer par type de sport :</label>
        <select id="typeSport" name="type_sport" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
          <option value="">-- Tous les sports --</option>
          <?php $sportTypes = $sportTypes ?? []; ?>
          <?php foreach ($sportTypes as $sport): ?>
            <option value="<?php echo htmlspecialchars($sport); ?>" 
              <?php echo ($_GET['type_sport'] ?? '') === $sport ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($sport); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Rechercher</button>
        <a href="index.php?action=mes_entrainements" class="btn-outline">Réinitialiser</a>
      </div>
    </form>
  </div>

  <?php if (empty($entrainements)): ?>
    <p>Aucune séance trouvée. Créez votre première séance.</p>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr><th>Date</th><th>Sport</th><th>Durée</th><th>Calories</th><th>Exercices</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($entrainements as $seance): ?>
          <tr>
            <td><?= htmlspecialchars(formatDateFR($seance['date'])) ?></td>
            <td><?= htmlspecialchars($seance['type_sport']) ?></td>
            <td><?= htmlspecialchars($seance['duree_minutes']) ?> min</td>
            <td><?= htmlspecialchars($seance['calories_brulees']) ?></td>
            <td>
              <span style="background-color: #dff0d8; padding: 4px 8px; border-radius: 4px; font-weight: bold;">
                <?= $seance['nb_exercices'] ?> exercice(s)
              </span>
            </td>
            <td>
              <a class="small-link" href="index.php?action=voir_exercices&id=<?= $seance['id_entrainement'] ?>">Exercices</a> |
              <a class="small-link" href="index.php?action=modifier_entrainement&id=<?= $seance['id_entrainement'] ?>">Modifier</a> |
              <a class="small-link" href="index.php?action=supprimer_entrainement&id=<?= $seance['id_entrainement'] ?>" onclick="return confirm('Supprimer cette séance ?')">Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
