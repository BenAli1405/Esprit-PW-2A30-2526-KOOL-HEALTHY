<div class="section-card">

  <!-- En-tête -->
  <div class="section-header">
    <div>
      <h2><i class="fas fa-book icon-highlight"></i> Catalogue KNN — Exercices de référence</h2>
      <p class="section-note">
        Base de connaissances pour l'algorithme de recommandation.
        Distance pondérée sur <strong>6 caractéristiques</strong>.
      </p>
    </div>
    <a class="btn-primary" href="/integweb/sport/index.php?action=admin_reference_create">
      <i class="fas fa-plus-circle"></i> Ajouter un exercice
    </a>
  </div>

  <!-- Légende poids KNN -->
  <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
    <span style="background:var(--vert-kool-light);color:var(--vert-kool-dark);padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;">
      <i class="fas fa-star"></i> Cible musc. ×2.5
    </span>
    <span style="background:var(--bleu-tech-light);color:var(--bleu-tech-dark);padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;">
      <i class="fas fa-layer-group"></i> Groupe prim. ×2.0
    </span>
    <span style="background:#fff3e0;color:#e65100;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;">
      <i class="fas fa-arrows-alt-v"></i> Type mvt ×1.5
    </span>
    <span style="background:#fce4ec;color:#c62828;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;">
      <i class="fas fa-tools"></i> Équipement ×1.0 &nbsp;·&nbsp; <i class="fas fa-signal"></i> Difficulté ×1.0
    </span>
    <span style="background:#f5f5f5;color:#616161;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:600;">
      <i class="fas fa-fire"></i> Intensité ×0.8
    </span>
  </div>

  <!-- Tableau -->
  <?php if (empty($references)): ?>
    <div class="empty-table-row">
      <i class="fas fa-book-open" style="font-size:2.5rem;color:#e0e0e0;margin-bottom:12px;display:block;"></i>
      Aucun exercice de référence trouvé.
    </div>
  <?php else: ?>
    <p style="color:#616161;font-size:0.85rem;margin-bottom:14px;">
      <i class="fas fa-database"></i> <strong><?= count($references) ?></strong> exercice(s) dans le catalogue
    </p>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th><i class="fas fa-tag"></i> Nom</th>
            <th title="Poids KNN : 0.8"><i class="fas fa-fire"></i> Intensité</th>
            <th title="Poids KNN : 1.0"><i class="fas fa-tools"></i> Équipement</th>
            <th title="Poids KNN : 1.0"><i class="fas fa-signal"></i> Difficulté</th>
            <th title="Poids KNN : 2.5 ★"><i class="fas fa-star"></i> Cible musc.</th>
            <th title="Poids KNN : 1.5"><i class="fas fa-arrows-alt-v"></i> Type mvt</th>
            <th title="Poids KNN : 2.0"><i class="fas fa-layer-group"></i> Groupe prim.</th>
            <th style="text-align:center;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($references as $i => $ref): ?>
            <tr>
              <td style="color:#616161;font-size:0.78rem;"><?= $i + 1 ?></td>
              <td style="font-weight:700;color:#243A33;"><?= htmlspecialchars($ref['nom']) ?></td>
              <td>
                <span style="background:#fff3e0;color:#e65100;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                  <?= number_format($ref['intensite_calorique'], 2) ?>
                </span>
              </td>
              <td>
                <span style="background:#e1f5fe;color:#0288d1;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                  <?= number_format($ref['equipement'], 2) ?>
                </span>
              </td>
              <td>
                <span style="background:#fce4ec;color:#c62828;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                  <?= number_format($ref['difficulte'], 2) ?>
                </span>
              </td>
              <td>
                <span style="background:#e8f5e9;color:#388e3c;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;">
                  ★ <?= number_format($ref['cible_musculaire'], 2) ?>
                </span>
              </td>
              <td style="font-weight:600;color:#616161;"><?= number_format($ref['type_mouvement'] ?? 0.5, 2) ?></td>
              <td>
                <span style="background:#e1f5fe;color:#0288d1;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700;">
                  <?= number_format($ref['groupe_primaire'] ?? 0.5, 2) ?>
                </span>
              </td>
              <td class="action-icons">
                <a href="/integweb/sport/index.php?action=admin_reference_edit&id=<?= $ref['id'] ?>"
                   title="Modifier" class="edit-icon" style="margin:0 4px;">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="/integweb/sport/index.php?action=admin_reference_delete&id=<?= $ref['id'] ?>"
                   title="Supprimer" class="delete-icon" style="margin:0 4px;"
                   onclick="return confirm('Supprimer cet exercice de référence ?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      Total : <strong><?= count($references) ?></strong> exercice(s) de référence
    </div>
  <?php endif; ?>
</div>
