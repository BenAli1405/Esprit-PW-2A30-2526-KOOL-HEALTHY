<?php
session_start();
require_once __DIR__ . '/../CONTROLLER/PlanNutritionnelController.php';
require_once __DIR__ . '/../CONTROLLER/RepasController.php';

$planID = (int)($_GET['id'] ?? 0);
if (!$planID) { header('Location: mes-plans.php'); exit; }

$planCtrl  = new PlanNutritionnelController();
$repasCtrl = new RepasController();

$plan  = $planCtrl->obtenirPlan($planID);
if (!$plan) { header('Location: mes-plans.php'); exit; }

$reco  = $planCtrl->recommandation($plan['calories_journalieres']);

// Ajout d'un repas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouterRepas') {
    $repas = new Repas($planID, $_POST['recette'], $_POST['date'], $_POST['type_repas']);
    $repasCtrl->ajouterRepas($repas);
    header("Location: detail-plan.php?id=$planID");
    exit;
}

// Changement de statut
if (isset($_GET['statut']) && isset($_GET['repas_id'])) {
    $repasCtrl->changerStatut((int)$_GET['repas_id'], $_GET['statut']);
    header("Location: detail-plan.php?id=$planID");
    exit;
}

// Suppression d'un repas
if (isset($_GET['supprimer_repas'])) {
    $repasCtrl->supprimerRepas((int)$_GET['supprimer_repas']);
    header("Location: detail-plan.php?id=$planID");
    exit;
}

$repas  = $repasCtrl->listeRepas($planID);
$duree  = (int)((strtotime($plan['date_fin']) - strtotime($plan['date_debut'])) / 86400);
$nbCons = count(array_filter($repas, fn($r) => $r['statut'] === 'consommé'));

// Grouper les repas par date
$repasByDate = [];
foreach ($repas as $r) {
    $repasByDate[$r['date']][] = $r;
}
ksort($repasByDate);

$badgeStatut = ['planifié' => 'badge-planifie', 'consommé' => 'badge-consomme', 'annulé' => 'badge-annule'];
$iconeType   = ['petit-déjeuner' => 'coffee', 'déjeuner' => 'utensils', 'dîner' => 'moon', 'collation' => 'apple-alt'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kool Healthy | <?= htmlspecialchars($plan['nom']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../CSS/plans.css">
</head>
<body>

<nav class="navbar">
  <div class="logo"><i class="fas fa-seedling"></i><h1>Kool Healthy</h1></div>
  <div class="nav-links">
    <a href="mes-plans.php"><i class="fas fa-arrow-left"></i> Mes Plans</a>
    <a href="modifier-plan.php?id=<?= $planID ?>">
      <i class="fas fa-edit"></i> Modifier le plan
    </a>
  </div>
</nav>

<section class="section">
  <!-- En-tête du plan -->
  <div class="detail-header">
    <div style="flex:1;">
      <h1 style="font-size:1.8rem;font-weight:800;color:var(--vert-kool-dark);">
        <?= htmlspecialchars($plan['nom']) ?>
      </h1>
      <p style="color:var(--gris-texte);margin-top:.4rem;">
        <i class="fas fa-calendar-alt" style="color:var(--bleu-tech);"></i>
        <?= date('d/m/Y', strtotime($plan['date_debut'])) ?>
        &nbsp;→&nbsp;
        <?= date('d/m/Y', strtotime($plan['date_fin'])) ?>
        &nbsp;·&nbsp;
        <?= $duree ?> jours
      </p>
    </div>
    <div class="detail-stat">
      <div class="big"><?= number_format($plan['calories_journalieres'], 0, ',', ' ') ?></div>
      <small>kcal/jour</small>
    </div>
    <div class="detail-stat">
      <div class="big" style="color:var(--bleu-tech);"><?= count($repas) ?></div>
      <small>repas planifiés</small>
    </div>
    <div class="detail-stat">
      <div class="big" style="color:var(--vert-kool);"><?= $nbCons ?></div>
      <small>repas consommés</small>
    </div>
  </div>

  <!-- Recommandation -->
  <div class="alert alert-<?= $reco['type'] ?>">
    <i class="fas fa-<?= $reco['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <?= $reco['message'] ?>
  </div>

  <?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i>
      Plan créé avec succès ! Ajoutez vos premiers repas ci-dessous.
    </div>
  <?php endif; ?>

  <!-- Formulaire ajout de repas -->
  <div class="card-panel">
    <div class="panel-header">
      <span class="panel-title"><i class="fas fa-plus" style="color:var(--bleu-tech);"></i> Ajouter un repas</span>
    </div>
    <form method="POST" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end;">
      <input type="hidden" name="action" value="ajouterRepas">
      <div class="form-group" style="flex:2;min-width:180px;">
        <label>Recette / Repas</label>
        <input type="text" name="recette" placeholder="ex : Salade de quinoa" required>
      </div>
      <div class="form-group" style="min-width:140px;">
        <label>Date</label>
        <input type="date" name="date"
               min="<?= $plan['date_debut'] ?>"
               max="<?= $plan['date_fin'] ?>"
               value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-group" style="min-width:160px;">
        <label>Type de repas</label>
        <select name="type_repas">
          <option value="petit-déjeuner">🌅 Petit-déjeuner</option>
          <option value="déjeuner" selected>☀️ Déjeuner</option>
          <option value="dîner">🌙 Dîner</option>
          <option value="collation">🍎 Collation</option>
        </select>
      </div>
      <div class="form-group">
        <button type="submit" class="btn-primary">
          <i class="fas fa-plus"></i> Ajouter
        </button>
      </div>
    </form>
  </div>

  <!-- Calendrier des repas -->
  <div class="card-panel">
    <div class="panel-header">
      <span class="panel-title">
        <i class="fas fa-calendar-week" style="color:var(--vert-kool);"></i>
        Planning des repas
      </span>
      <span style="color:var(--gris-texte);font-size:.82rem;"><?= count($repas) ?> repas au total</span>
    </div>

    <?php if (empty($repas)): ?>
      <div class="empty-state">
        <i class="fas fa-bowl-food"></i>
        <p>Aucun repas ajouté pour l'instant.</p>
      </div>
    <?php else: ?>
      <?php foreach ($repasByDate as $date => $repasJour): ?>
        <div style="margin-bottom:1.5rem;">
          <div style="font-weight:700;color:var(--bleu-tech-dark);margin-bottom:.6rem;padding:.4rem .8rem;background:var(--bleu-tech-light);border-radius:10px;font-size:.88rem;">
            <i class="fas fa-calendar-day"></i>
            <?= date('l d/m/Y', strtotime($date)) ?>
          </div>
          <div class="repas-table-wrap">
            <table class="repas-table">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Recette</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($repasJour as $r): ?>
                <tr>
                  <td>
                    <span class="badge-type">
                      <i class="fas fa-<?= $iconeType[$r['type_repas']] ?? 'utensils' ?>"></i>
                      <?= htmlspecialchars($r['type_repas']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($r['recette']) ?></td>
                  <td><span class="badge <?= $badgeStatut[$r['statut']] ?? '' ?>"><?= $r['statut'] ?></span></td>
                  <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                      <?php if ($r['statut'] !== 'consommé'): ?>
                      <a href="?id=<?= $planID ?>&statut=consommé&repas_id=<?= $r['id'] ?>"
                         class="btn-primary btn-sm" title="Marquer consommé">
                        <i class="fas fa-check"></i>
                      </a>
                      <?php endif; ?>
                      <?php if ($r['statut'] !== 'annulé'): ?>
                      <a href="?id=<?= $planID ?>&statut=annulé&repas_id=<?= $r['id'] ?>"
                         class="btn-secondary btn-sm" title="Annuler">
                        <i class="fas fa-times"></i>
                      </a>
                      <?php endif; ?>
                      <a href="?id=<?= $planID ?>&supprimer_repas=<?= $r['id'] ?>"
                         class="btn-danger btn-sm"
                         onclick="return confirm('Supprimer ce repas ?')" title="Supprimer">
                        <i class="fas fa-trash"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<footer class="footer">
  <p>© 2025 Kool Healthy — Manger mieux, préserver la planète 🌱</p>
</footer>

<script src="../JS/plans.js"></script>
</body>
</html>
