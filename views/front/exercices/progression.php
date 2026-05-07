<!-- =======================================================================
     progression.php — Analyse & Optimisation de progression d'exercice
     Variables PHP disponibles :
       $exercicesListe   array  — liste des exercices de l'utilisateur
       $idExercice       int    — exercice sélectionné
       $nomExercice      string — nom de l'exercice
       $historique       array  — performances passées triées par date
       $regression       array|null — {pente, origine, r2, moyenne, points...}
       $plateau          array|null — {plateau, pente_recente, seuil_absolu}
       $conseils         array  — liste de recommandations textuelles
       $chargeJ30        float|null — charge estimée à J+30
       $chargeObjectif   float  — objectif saisi par l'utilisateur
       $joursObjectif    int|null — jours avant d'atteindre l'objectif
       $this->errors     array  — erreurs de validation
======================================================================= -->

<?php
$idExercice       = $idExercice ?? 0;
$nomExercice      = $nomExercice ?? '';
$exercicesListe   = $exercicesListe ?? [];
$historique       = $historique ?? [];
$regression       = $regression ?? null;
$plateau          = $plateau ?? null;
$conseils         = $conseils ?? [];
$chargeJ30        = $chargeJ30 ?? null;
$chargeObjectif   = $chargeObjectif ?? 0.0;
$joursObjectif    = $joursObjectif ?? null;
$poidsCible       = $poidsCible ?? '';
$repetitionsCible = $repetitionsCible ?? '';
$seriesCible      = $seriesCible ?? '';
?>

<div class="header-actions" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h2 style="margin:0;">📈 Analyse de progression</h2>
    <a href="index.php?action=mes_entrainements" class="btn btn-secondary">Retour aux entraînements</a>
</div>

<?php if (!empty($this->errors)): ?>
    <div class="error-msg" style="margin-bottom:1rem;">
        <ul><?php foreach ($this->errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SÉLECTEUR D'EXERCICE
════════════════════════════════════════════════════════ -->
<div class="card" style="margin-bottom:1.5rem;">
    <form method="GET" action="index.php" class="form" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;">
        <input type="hidden" name="action" value="progression">

        <div class="form-group" style="flex:1;min-width:220px;margin:0;">
            <label for="id_select">Exercice à analyser</label>
            <select id="id_select" name="id" onchange="this.form.submit()">
                <?php if (empty($exercicesListe)): ?>
                    <option value="0">— Aucun exercice avec historique —</option>
                <?php else: ?>
                    <?php foreach ($exercicesListe as $ex): ?>
                        <option value="<?= $ex['id_exercice'] ?>"
                            <?= ($idExercice === (int)$ex['id_exercice']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ex['nom_exercice']) ?>
                            (<?= $ex['nb_performances'] ?> séance<?= $ex['nb_performances'] > 1 ? 's' : '' ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div style="flex:0 0 auto;">
            <button type="submit" class="btn btn-secondary" style="height:fit-content;">Mettre à jour</button>
        </div>
    </form>
</div>

<?php if (empty($exercicesListe)): ?>
    <div class="card">
        <p>🏋️ Aucun exercice avec historique de performance trouvé. Commencez par enregistrer une séance !</p>
    </div>
<?php elseif ($idExercice > 0): ?>

<!-- ═══════════════════════════════════════════════════════
     GRAPHIQUE CHART.JS
════════════════════════════════════════════════════════ -->
<?php if (!empty($historique)): ?>
    <div class="card" style="margin-bottom:1.5rem;">
        <h3 style="margin-bottom:1rem;">
            📊 Évolution de la charge — <em><?= htmlspecialchars($nomExercice) ?></em>
        </h3>

        <?php if (count($historique) < 2): ?>
            <p style="color:#888;">Enregistrez au moins 2 séances pour afficher le graphique et les analyses.</p>
        <?php else: ?>
            <div style="position:relative;height:340px;">
                <canvas id="progressionChart"></canvas>
            </div>
            <!-- Explication de l'unité sous le graphique -->
            <p style="font-size:.82em;color:#999;margin-top:.7rem;line-height:1.5;">
                ℹ️ <strong>Charge totale = poids&nbsp;(kg) &times; répétitions &times; séries.</strong>
                Par exemple, 60&nbsp;kg &times; 10&nbsp;rép &times; 3&nbsp;séries = <strong>1800&nbsp;kg·reps·séries</strong>.
                Un objectif de 6000 correspond à&nbsp;100&nbsp;kg &times; 10&nbsp;rép &times; 6&nbsp;séries (ou toute combinaison équivalente).
                La droite en pointillés (bleue) est la <em>droite de régression linéaire</em> prolongée de 30 jours.
            </p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="card">
        <h4 style="color:var(--vert-kool-dark);margin-bottom:.8rem;">🎯 Objectif</h4>
        <form method="GET" action="index.php" class="form" style="display:grid;gap:1rem;">
            <input type="hidden" name="action" value="progression">
            <input type="hidden" name="id" value="<?= $idExercice ?>">

            <div class="form-group" style="margin:0;">
                <label for="poids_cible">Poids cible (kg)</label>
                <input type="text" id="poids_cible" name="poids_cible"
                       placeholder="ex: 80"
                       value="<?= htmlspecialchars($poidsCible) ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label for="repetitions_cible">Répétitions cibles</label>
                <input type="text" id="repetitions_cible" name="repetitions_cible"
                       placeholder="ex: 8"
                       value="<?= htmlspecialchars($repetitionsCible) ?>">
            </div>
            <div class="form-group" style="margin:0;">
                <label for="series_cible">Séries cibles</label>
                <input type="text" id="series_cible" name="series_cible"
                       placeholder="ex: 4"
                       value="<?= htmlspecialchars($seriesCible) ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Mettre à jour l'objectif</button>
        </form>

        <div style="margin-top:1rem;padding:.9rem;background:#f8f9fc;border-radius:12px;">
            <?php if ($chargeObjectif > 0): ?>
                <p style="margin:0 0 .6rem 0;color:#222;font-size:1rem;">
                    Objectif cible : <strong><?= number_format($chargeObjectif, 0) ?> kg·reps·séries</strong>
                </p>
                <p style="margin:0;color:#666;font-size:.92rem;">
                    (<?= number_format((float)$poidsCible, 1) ?> kg × <?= (int)$repetitionsCible ?> rép × <?= (int)$seriesCible ?> séries)
                </p>
                <?php if ($joursObjectif === null): ?>
                    <p style="margin:.8rem 0 0 0;color:#c0392b;font-size:.92rem;">
                        La tendance actuelle est stable ou négative : la date estimée ne peut pas être calculée.
                    </p>
                <?php elseif ($joursObjectif <= 0): ?>
                    <p style="margin:.8rem 0 0 0;color:var(--vert-kool-dark);font-size:.92rem;">
                        🎉 Objectif déjà atteint selon la droite de progression actuelle.
                    </p>
                <?php else: ?>
                    <?php $dateObjectif = (new DateTime())->modify("+{$joursObjectif} days")->format('d/m/Y'); ?>
                    <p style="margin:.8rem 0 0 0;font-size:.92rem;color:#222;">
                        Estimation : <strong><?= $joursObjectif ?> jour<?= $joursObjectif > 1 ? 's' : '' ?></strong>
                        (<strong><?= $dateObjectif ?></strong>) selon la pente actuelle.
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p style="margin:0;color:#888;font-size:.92rem;">Renseignez un objectif en poids, répétitions et séries pour afficher la charge cible.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h4 style="color:var(--vert-kool-dark);margin-bottom:.8rem;">🔢 Indicateurs</h4>
        <?php if ($regression): ?>
            <table style="width:100%;font-size:.9rem;border-collapse:collapse;">
                <tr>
                    <td style="padding:.3rem .5rem .3rem 0;color:#666;" title="Variation moyenne de la charge par jour.">Pente</td>
                    <td style="font-weight:700;"><?= ($regression['pente'] >= 0 ? '+' : '') . number_format($regression['pente'], 3) ?>&nbsp;kg&middot;reps&middot;séries</td>
                </tr>
                <tr>
                    <td style="padding:.3rem .5rem .3rem 0;color:#666;" title="Qualité de la droite de régression : plus proche de 1, meilleure est la linéarité.">Fiabilité (R²)</td>
                    <td style="font-weight:700;"><?= number_format($regression['r2'] * 100, 1) ?>&nbsp;%</td>
                </tr>
                <tr>
                    <td style="padding:.3rem .5rem .3rem 0;color:#666;" title="Moyenne des charges observées sur l'historique.">Charge moyenne</td>
                    <td style="font-weight:700;"><?= number_format($regression['moyenne'], 1) ?>&nbsp;kg&middot;reps&middot;séries</td>
                </tr>
                <?php if ($chargeJ30 !== null): ?>
                <tr>
                    <td style="padding:.3rem .5rem .3rem 0;color:#666;" title="Estimation de la charge dans 30 jours selon la tendance actuelle.">Prédiction J+30</td>
                    <td style="font-weight:700;color:var(--bleu-tech-dark);">≈&nbsp;<?= number_format(max(0, $chargeJ30), 0) ?>&nbsp;kg&middot;reps&middot;séries</td>
                </tr>
                <?php endif; ?>
            </table>
            <?php if ($plateau): ?>
                <div style="margin-top:1rem;padding:.8rem;background:#f5fdf9;border-radius:12px;border:1px solid #daf5e8;">
                    <strong style="display:block;margin-bottom:.4rem;">
                        <?= ($plateau['plateau']) ? '🧱 Plateau récent' : '✅ Progression active' ?>
                    </strong>
                    <p style="margin:0;font-size:.9rem;color:#555;">
                        Pente récente : <strong><?= number_format($plateau['pente_recente'], 3) ?></strong><br>
                        Seuil de stagnation : <strong><?= number_format($plateau['seuil_absolu'], 2) ?></strong><br>
                        Sur les <strong><?= $plateau['nb_seances'] ?></strong> dernières séances.
                    </p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="margin:0;color:#888;font-size:.92rem;">Enregistrez au moins 2 séances pour afficher les indicateurs de progression.</p>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($conseils)): ?>
<div class="card" style="margin-bottom:1.5rem;">
    <h4 style="color:var(--vert-kool-dark);margin-bottom:.8rem;">💡 Recommandations personnalisées</h4>
    <ul style="list-style:none;padding:0;display:grid;gap:.6rem;">
        <?php foreach ($conseils as $conseil): ?>
            <li style="padding:.6rem .8rem;background:var(--gris-clair);border-radius:10px;font-size:.92rem;">
                <?= htmlspecialchars($conseil) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     HISTORIQUE TABLEAU
════════════════════════════════════════════════════════ -->
<div class="card" style="margin-bottom:1.5rem;">
    <h4 style="margin-bottom:.8rem;">📋 Historique des séances</h4>
    <?php if (empty($historique)): ?>
        <p style="color:#888;">Aucune performance enregistrée pour cet exercice.</p>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table" style="font-size:.88rem;min-width:720px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Poids (kg)</th>
                        <th>Rép.</th>
                        <th>Séries</th>
                        <th title="Charge totale = poids × répétitions × séries">Charge totale &#9432;</th>
                        <th>Fatigue</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_reverse($historique) as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= number_format((float)$row['poids'], 1) ?></td>
                            <td><?= $row['repetitions'] ?></td>
                            <td><?= $row['series'] ?></td>
                            <td><strong><?= number_format((float)$row['charge_totale'], 1) ?></strong></td>
                            <td><?= $row['fatigue'] !== null ? $row['fatigue'] . '/10' : '—' ?></td>
                            <td style="font-size:.8rem;color:#888;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?= htmlspecialchars($row['commentaire'] ?? '') ?>
                            </td>
                            <td>
                                <form method="POST" action="index.php?action=progression" style="display:inline;">
                                    <input type="hidden" name="sous_action"    value="supprimer">
                                    <input type="hidden" name="id_performance" value="<?= $row['id_performance'] ?>">
                                    <input type="hidden" name="id_exercice"    value="<?= $idExercice ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Supprimer cette séance ?');">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<details style="margin-bottom:2rem;border:1px solid #d9d9d9;border-radius:14px;background:#fff;">
    <summary style="cursor:pointer;padding:1rem 1.2rem;font-weight:700;font-size:1rem;color:#333;">
        ➕ Ajouter une performance
    </summary>
    <div style="padding:1.2rem;">
        <form method="POST" action="index.php?action=progression" class="form" style="display:grid;gap:1rem;">
            <input type="hidden" name="sous_action" value="ajouter">
            <input type="hidden" name="id_exercice" value="<?= $idExercice ?>">

            <div class="form-group">
                <label for="f_date">Date</label>
                <input type="text" id="f_date" name="date"
                       placeholder="AAAA-MM-JJ"
                       value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label for="f_poids">Poids (kg)</label>
                <input type="text" id="f_poids" name="poids" placeholder="ex: 10.5">
            </div>
            <div class="form-group">
                <label for="f_reps">Répétitions</label>
                <input type="text" id="f_reps" name="repetitions" placeholder="ex: 12">
            </div>
            <div class="form-group">
                <label for="f_series">Séries</label>
                <input type="text" id="f_series" name="series" placeholder="ex: 4">
            </div>
            <div class="form-group">
                <label for="f_fatigue">Fatigue (1-10, optionnel)</label>
                <input type="text" id="f_fatigue" name="fatigue" placeholder="ex: 7">
            </div>
            <div class="form-group">
                <label for="f_commentaire">Commentaire</label>
                <input type="text" id="f_commentaire" name="commentaire" placeholder="Optionnel">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Enregistrer</button>
        </form>
    </div>
</details>

<?php endif; /* $idExercice */ ?>

<!-- =======================================================================
     CHART.JS — Chargé uniquement si on a des données
======================================================================= -->
<?php if (!empty($historique) && $regression && count($historique) >= 2): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // ── Données PHP → JS ──────────────────────────────────────────────────
    const points = <?= json_encode(
        array_map(fn($p) => ['x' => $p['x'], 'y' => (float)$p['y']], $regression['points'])
    , JSON_THROW_ON_ERROR) ?>;

    const labels = <?= json_encode(array_column($historique, 'date'), JSON_THROW_ON_ERROR) ?>;

    const pente  = <?= $regression['pente'] ?>;
    const origine = <?= $regression['origine'] ?>;

    // ── Droite de régression (2 points suffisent) ──────────────────────────
    const xMin = <?= $regression['xMin'] ?>;
    const xMax = <?= $regression['xMax'] + 30 ?>; // Prolonger 30 jours

    const regrPoints = [
        { x: labels[0],                              y: Math.max(0, origine + pente * xMin) },
        { x: '<?= (new DateTime($historique[count($historique)-1]['date']))->modify('+30 days')->format('Y-m-d') ?>',
          y: Math.max(0, origine + pente * xMax) }
    ];

    // ── Charges réelles pour les labels ────────────────────────────────────
    const chargesReelles = <?= json_encode(array_map(
        fn($r) => (float)$r['charge_totale'], $historique
    ), JSON_THROW_ON_ERROR) ?>;

    // ── Construction du graphique ──────────────────────────────────────────
    const ctx = document.getElementById('progressionChart');
    if (!ctx) return;

    new window.Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Charge réelle (kg·reps·séries)',
                    data: chargesReelles,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76,175,80,0.12)',
                    pointBackgroundColor: '#388E3C',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.25,
                    order: 1,
                },
                {
                    label: 'Droite de régression',
                    data: [regrPoints[0].y, ...Array(labels.length - 2).fill(null), regrPoints[1].y],
                    borderColor: '#29B6F6',
                    borderDash: [6, 4],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0,
                    order: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    labels: { font: { family: 'Inter, sans-serif', size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y !== null ? ctx.parsed.y.toFixed(1) : '—')
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 11 }, maxTicksLimit: 8 }
                },
                y: {
                    beginAtZero: false,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { size: 11 } },
                    title: { display: true, text: 'Charge totale (kg·reps·séries)', font: { size: 11 } }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>
