<div class="card-panel">
  <div class="panel-header">
    <h3><i class="fas fa-dumbbell icon-tech"></i> Exercices</h3>
    <a class="btn-primary" href="index.php?action=admin_creer_exercice"><i class="fas fa-plus-circle"></i> Nouvel exercice</a>
  </div>
  <table class="data-table">
    <thead><tr><th>ID</th><th>Séance</th><th>Ordre</th><th>Exercice</th><th>Séries</th><th>Répétitions</th><th>Repos</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($exercices as $ex): ?>
        <tr>
          <td><?= htmlspecialchars($ex['id_exercice']) ?></td>
          <td><?= htmlspecialchars($ex['session_date'] ?? '') ?> - <?= htmlspecialchars($ex['type_sport'] ?? '') ?></td>
          <td><?= htmlspecialchars($ex['ordre']) ?></td>
          <td><?= htmlspecialchars($ex['nom']) ?></td>
          <td><?= htmlspecialchars($ex['series']) ?></td>
          <td><?= htmlspecialchars($ex['repetitions']) ?></td>
          <td><?= htmlspecialchars($ex['repos_secondes']) ?> s</td>
          <td>
            <a class="small-link" href="index.php?action=admin_modifier_exercice&id=<?= $ex['id_exercice'] ?>">Modifier</a> |
            <a class="small-link" href="index.php?action=admin_supprimer_exercice&id=<?= $ex['id_exercice'] ?>" onclick="return confirm('Supprimer cet exercice ?')">Supprimer</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>