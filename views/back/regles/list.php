<div class="card-panel">
  <div class="panel-header">
    <h3><i class="fas fa-brain"></i> Règles IA</h3>
    <a class="btn-primary" href="index.php?action=admin_creer_regle"><i class="fas fa-plus-circle"></i> Nouvelle règle</a>
  </div>
  <table class="data-table">
    <thead><tr><th>ID</th><th>Type de repas</th><th>Exercice</th><th>Séries</th><th>Répétitions</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($regles as $regle): ?>
        <tr>
          <td><?= htmlspecialchars($regle['id']) ?></td>
          <td><?= htmlspecialchars($regle['type_repas']) ?></td>
          <td><?= htmlspecialchars($regle['exercice_suggere']) ?></td>
          <td><?= htmlspecialchars($regle['series']) ?></td>
          <td><?= htmlspecialchars($regle['repetitions']) ?></td>
          <td>
            <a class="small-link" href="index.php?action=admin_modifier_regle&id=<?= $regle['id'] ?>">Modifier</a> |
            <a class="small-link" href="index.php?action=admin_supprimer_regle&id=<?= $regle['id'] ?>" onclick="return confirm('Supprimer cette règle ?')">Supprimer</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
