<div class="card-panel">
  <div class="panel-header">
    <h3><i class="fas fa-chart-line icon-tech"></i> Exercices</h3>
    <a class="btn-primary" href="index.php?action=admin_creer_exercice"><i class="fas fa-plus-circle"></i> Ajouter un exercice</a>
  </div>
  <table class="data-table">
    <thead><tr><th>ID</th><th>Exercice</th><th>Session</th><th>Sport</th><th>Séries</th><th>Répétitions</th><th>Repos</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($exercices as $exo): ?>
        <tr>
          <td><?= htmlspecialchars($exo['id_exercice']) ?></td>
          <td><?= htmlspecialchars($exo['nom']) ?></td>
          <td><?= htmlspecialchars($exo['session_date']) ?></td>
          <td><?= htmlspecialchars($exo['type_sport']) ?></td>
          <td><?= htmlspecialchars($exo['series']) ?></td>
          <td><?= htmlspecialchars($exo['repetitions']) ?></td>
          <td><?= htmlspecialchars($exo['repos_secondes']) ?> s</td>
          <td>
            <a class="small-link" href="index.php?action=admin_modifier_exercice&id=<?= $exo['id_exercice'] ?>">Modifier</a> |
            <a class="small-link" href="index.php?action=admin_supprimer_exercice&id=<?= $exo['id_exercice'] ?>" onclick="return confirm('Supprimer cet exercice ?')">Supprimer</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
