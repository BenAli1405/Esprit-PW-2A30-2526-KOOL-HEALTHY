<div class="card-panel">
  <div class="section-header">
    <h3>Exercices de la séance du <?= htmlspecialchars(formatDateFR($entrainement['date'])) ?></h3>
    <div class="button-row">
      <a class="btn-primary" href="index.php?action=ajouter_exercice&id=<?= $entrainement['id_entrainement'] ?>"><i class="fas fa-plus"></i> Ajouter un exercice</a>
      <a class="btn-secondary" href="index.php?action=mes_entrainements">Retour</a>
    </div>
  </div>
  <?php if (empty($exercices)): ?>
    <p>Aucun exercice pour cette séance. Commencez par ajouter un exercice.</p>
  <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Ordre</th><th>Exercice</th><th>Séries</th><th>Répétitions</th><th>Repos</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($exercices as $exercice): ?>
          <tr>
            <td><?= htmlspecialchars($exercice['ordre']) ?></td>
            <td><?= htmlspecialchars($exercice['nom']) ?></td>
            <td><?= htmlspecialchars($exercice['series']) ?></td>
            <td><?= htmlspecialchars($exercice['repetitions']) ?></td>
            <td><?= htmlspecialchars($exercice['repos_secondes']) ?> s</td>
            <td>
              <a class="small-link" href="index.php?action=modifier_exercice&id=<?= $exercice['id_exercice'] ?>">Modifier</a> |
              <a class="small-link" href="index.php?action=supprimer_exercice&id=<?= $exercice['id_exercice'] ?>" onclick="return confirm('Supprimer cet exercice ?')">Supprimer</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
