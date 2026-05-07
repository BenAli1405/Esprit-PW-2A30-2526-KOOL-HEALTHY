<div class="header-actions">
    <h2>Catalogue des exercices de référence</h2>
    <div>
        <a href="index.php?action=admin_reference_create" class="btn btn-primary">Ajouter un exercice</a>
    </div>
</div>

<div class="card">
    <p>Ce catalogue sert de base de connaissances pour l'algorithme de recommandation (KNN — distance pondérée sur <strong>6 caractéristiques</strong>).</p>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th title="Poids KNN : 0.8">Intensité</th>
                <th title="Poids KNN : 1.0">Équipement</th>
                <th title="Poids KNN : 1.0">Difficulté</th>
                <th title="Poids KNN : 2.5 ★">Cible musc.</th>
                <th title="Poids KNN : 1.5">Type mvt</th>
                <th title="Poids KNN : 2.0">Groupe prim.</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($references)): ?>
                <tr>
                    <td colspan="9" style="text-align:center;">Aucun exercice de référence trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($references as $ref): ?>
                    <tr>
                        <td><?= htmlspecialchars($ref['id']) ?></td>
                        <td><strong><?= htmlspecialchars($ref['nom']) ?></strong></td>
                        <td><?= number_format($ref['intensite_calorique'], 2) ?></td>
                        <td><?= number_format($ref['equipement'], 2) ?></td>
                        <td><?= number_format($ref['difficulte'], 2) ?></td>
                        <td><?= number_format($ref['cible_musculaire'], 2) ?></td>
                        <td><?= number_format($ref['type_mouvement']  ?? 0.5, 2) ?></td>
                        <td><?= number_format($ref['groupe_primaire'] ?? 0.5, 2) ?></td>
                        <td>
                            <a href="index.php?action=admin_reference_edit&id=<?= $ref['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
                            <a href="index.php?action=admin_reference_delete&id=<?= $ref['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet exercice de référence ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
