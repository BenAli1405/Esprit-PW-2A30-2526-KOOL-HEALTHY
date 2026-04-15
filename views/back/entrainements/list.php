<div class="card-panel">
  <div class="panel-header">
    <h3><i class="fas fa-chart-pie icon-tech"></i> Entraînements</h3>
    <a class="btn-primary" href="index.php?action=admin_creer_entrainement"><i class="fas fa-plus-circle"></i> Nouvelle séance</a>
  </div>
  <table class="data-table">
    <thead><tr><th>ID</th><th>Utilisateur</th><th>Date</th><th>Sport</th><th>Durée</th><th>Calories</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($entrainements as $ent): ?>
        <tr>
          <td><?= htmlspecialchars($ent['id_entrainement']) ?></td>
          <td><?= htmlspecialchars($ent['utilisateur_nom'] ?? 'Utilisateur inconnu') ?></td>
          <td><?= htmlspecialchars($ent['date']) ?></td>
          <td><?= htmlspecialchars($ent['type_sport']) ?></td>
          <td><?= htmlspecialchars($ent['duree_minutes']) ?> min</td>
          <td><?= htmlspecialchars($ent['calories_brulees']) ?></td>
          <td>
            <a class="small-link" href="index.php?action=admin_modifier_entrainement&id=<?= $ent['id_entrainement'] ?>">Modifier</a> |
            <a class="small-link" href="index.php?action=admin_supprimer_entrainement&id=<?= $ent['id_entrainement'] ?>" onclick="return confirm('Supprimer cette séance ?')">Supprimer</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
