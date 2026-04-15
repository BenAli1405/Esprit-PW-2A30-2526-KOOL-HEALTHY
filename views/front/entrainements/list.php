<div class="card-panel">
  <div class="section-header">
    <h3 class="no-margin">Mes séances</h3>
    <a class="btn-primary" href="index.php?action=ajouter_entrainement"><i class="fas fa-plus"></i> Ajouter une séance</a>
  </div>
  <?php if (empty($entrainements)): ?>
    <p>Aucune séance trouvée. Créez votre première séance.</p>
  <?php else: ?>
    <table class="data-table">
      <thead>
        <tr><th>Date</th><th>Sport</th><th>Durée</th><th>Calories</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($entrainements as $seance): ?>
          <tr>
            <td><?= htmlspecialchars(formatDateFR($seance['date'])) ?></td>
            <td><?= htmlspecialchars($seance['type_sport']) ?></td>
            <td><?= htmlspecialchars($seance['duree_minutes']) ?> min</td>
            <td><?= htmlspecialchars($seance['calories_brulees']) ?></td>
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
