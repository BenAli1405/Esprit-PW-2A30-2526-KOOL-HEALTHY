<div class="section-card">

  <!-- En-tête -->
  <div class="section-header">
    <div>
      <h2><i class="fas fa-dumbbell icon-highlight"></i> Gestion des Exercices</h2>
      <p class="section-note">Tous les exercices rattachés aux séances d'entraînement.</p>
    </div>
    <a class="btn-primary" href="/integweb/sport/index.php?action=admin_creer_exercice">
      <i class="fas fa-plus-circle"></i> Nouvel exercice
    </a>
  </div>

  <!-- Tableau -->
  <?php if (empty($exercices)): ?>
    <div class="empty-table-row">
      <i class="fas fa-dumbbell" style="font-size:2.5rem;color:var(--gris-moyen);margin-bottom:12px;display:block;"></i>
      Aucun exercice trouvé.
    </div>
  <?php else: ?>
    <!-- Compteur -->
    <p style="color:var(--gris-texte);font-size:0.85rem;margin-bottom:14px;">
      <i class="fas fa-list"></i> <strong><?= count($exercices) ?></strong> exercice(s) au total
    </p>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th><i class="fas fa-calendar-alt"></i> Séance</th>
            <th><i class="fas fa-sort-numeric-up"></i> Ordre</th>
            <th><i class="fas fa-tag"></i> Nom</th>
            <th><i class="fas fa-layer-group"></i> Séries</th>
            <th><i class="fas fa-redo"></i> Répétitions</th>
            <th><i class="fas fa-hourglass-half"></i> Repos</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exercices as $i => $ex): ?>
            <tr>
              <td style="color:var(--gris-texte);font-size:0.78rem;"><?= $i + 1 ?></td>
              <td>
                <span style="font-weight:600;"><?= htmlspecialchars($ex['session_date'] ?? '—') ?></span>
                <?php if (!empty($ex['type_sport'])): ?>
                  <br><span style="background:var(--bleu-tech-light);color:var(--bleu-tech-dark);padding:2px 8px;border-radius:20px;font-size:0.72rem;font-weight:600;">
                    <?= htmlspecialchars($ex['type_sport']) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <span style="background:var(--gris-clair);border:1px solid var(--gris-moyen);padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;">
                  <?= htmlspecialchars($ex['ordre']) ?>
                </span>
              </td>
              <td style="font-weight:600;color:#243A33;"><?= htmlspecialchars($ex['nom']) ?></td>
              <td>
                <span class="status-active"><?= htmlspecialchars($ex['series']) ?> séries</span>
              </td>
              <td style="font-weight:600;color:var(--vert-kool-dark);">
                × <?= htmlspecialchars($ex['repetitions']) ?>
              </td>
              <td>
                <span style="color:var(--gris-texte);">
                  <i class="fas fa-pause-circle"></i> <?= htmlspecialchars($ex['repos_secondes']) ?>s
                </span>
              </td>
              <td class="action-icons">
                <a href="/integweb/sport/index.php?action=admin_modifier_exercice&id=<?= $ex['id_exercice'] ?>"
                   title="Modifier" class="edit-icon" style="margin:0 4px;">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="/integweb/sport/index.php?action=admin_supprimer_exercice&id=<?= $ex['id_exercice'] ?>"
                   title="Supprimer" class="delete-icon" style="margin:0 4px;"
                   onclick="return confirm('Supprimer cet exercice ?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      Total : <strong><?= count($exercices) ?></strong> exercice(s)
    </div>
  <?php endif; ?>
</div>
