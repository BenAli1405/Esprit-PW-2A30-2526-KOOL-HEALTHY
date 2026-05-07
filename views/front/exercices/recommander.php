<div class="header-actions" style="display:flex;justify-content:space-between;align-items:center;">
    <h2 style="margin:0;">Recommandation d'exercices (KNN)</h2>
    <a href="index.php?action=mes_entrainements" class="btn btn-secondary">Retour aux entraînements</a>
</div>

<!-- ================================================================
     MODALE VIDÉO YOUTUBE
     ================================================================ -->
<div id="video-modal" class="video-modal" role="dialog" aria-modal="true" aria-label="Vidéo de démonstration">
    <!-- Overlay cliquable pour fermer -->
    <div id="video-modal-overlay" class="video-modal__overlay"></div>

    <div class="video-modal__box">
        <!-- Bouton fermeture -->
        <button id="video-modal-close" class="video-modal__close" aria-label="Fermer la vidéo">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        <div class="video-modal__title" id="video-modal-title">Démonstration</div>

        <!-- Conteneur responsive 16/9 -->
        <div class="video-modal__responsive">
            <iframe id="video-iframe"
                    src=""
                    frameborder="0"
                    allow="autoplay; encrypted-media; picture-in-picture"
                    allowfullscreen
                    title="Vidéo de démonstration de l'exercice">
            </iframe>
        </div>
    </div>
</div>

<!-- ================================================================
     CARTE FORMULAIRE
     ================================================================ -->
<div class="card">
    <p>Sélectionnez un exercice de référence pour découvrir des mouvements similaires. L'algorithme utilise une
        <strong>distance pondérée sur 6 caractéristiques</strong> avec priorité sur la cible musculaire et le groupe
        primaire.</p>
    <p style="font-size:0.88em;color:var(--text-muted,#888);">
        💡 Si l'exercice n'est pas dans la liste, tapez son nom : le système le cherchera automatiquement dans la
        bibliothèque WorkoutX.
    </p>

    <?php if (!empty($this->errors)): ?>
        <div class="error-msg">
            <ul>
                <?php foreach ($this->errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($apiImported)): ?>
        <div style="background:#e8f5e9;border-left:4px solid #4caf50;padding:.75rem 1rem;border-radius:4px;margin-bottom:1rem;color:#2e7d32;">
            ✅ <strong><?= htmlspecialchars($selectedExerciceNom) ?></strong> a été récupéré via l'API WorkoutX, normalisé
            sur 6 dimensions et ajouté à la base de référence.
        </div>
    <?php endif; ?>

    <!-- Formulaire strict sans validation HTML5 -->
    <form action="index.php?action=recommander" method="POST" class="form">
        <div class="form-group">
            <label for="exercice_nom">Saisissez un nom d'exercice :</label>
            <input type="text" name="exercice_nom" id="exercice_nom" placeholder="ex: squat, push-up, boxe…"
                value="<?= htmlspecialchars($selectedExerciceNom ?? '') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="id_exercice">Ou choisissez dans la liste de référence :</label>
            <select name="id_exercice" id="id_exercice">
                <option value="0">-- Sélectionner un exercice --</option>
                <?php foreach ($exercices as $ex): ?>
                    <option value="<?= $ex['id'] ?>" <?= ($selectedExerciceId === (int) $ex['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ex['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Trouver des exercices similaires</button>
    </form>
</div>

<!-- ================================================================
     RÉSULTATS KNN
     ================================================================ -->
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($this->errors)): ?>
    <h3 style="margin:1.5rem 0 0.5rem;">Résultats de la recommandation</h3>

    <?php if (empty($similarExercises)): ?>
        <div class="card">
            <p>Aucun exercice similaire trouvé.</p>
        </div>
    <?php else: ?>
        <div style="font-size:0.82em;color:#888;margin-bottom:.5rem;">
            Distance pondérée : cible musculaire ×2.5 · groupe primaire ×2.0 · type mouvement ×1.5 · difficulté ×1.0 ·
            équipement ×1.0 · intensité ×0.8
        </div>
        <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Exercice Recommandé</th>
                    <th>Distance <small>(0=identique)</small></th>
                    <th>Cible musc.</th>
                    <th>Groupe prim.</th>
                    <th>Type mvt</th>
                    <th>Difficulté</th>
                    <th>Équipement</th>
                    <th>Intensité</th>
                    <th>Démonstration</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($similarExercises as $result): ?>
                    <?php
                        $videoUrl  = trim($result['video_url'] ?? '');
                        $hasVideo  = $videoUrl !== '';
                        $nomExo    = htmlspecialchars($result['nom']);
                    ?>
                    <tr>
                        <td><strong><?= $nomExo ?></strong></td>
                        <td><?= number_format($result['distance'], 4) ?></td>
                        <td><?= number_format($result['cible_musculaire'], 2) ?></td>
                        <td><?= number_format($result['groupe_primaire'] ?? 0.5, 2) ?></td>
                        <td><?= number_format($result['type_mouvement'] ?? 0.5, 2) ?></td>
                        <td><?= number_format($result['difficulte'], 2) ?></td>
                        <td><?= number_format($result['equipement'], 2) ?></td>
                        <td><?= number_format($result['intensite_calorique'], 2) ?></td>
                        <td>
                            <?php if ($hasVideo): ?>
                                <button
                                    class="btn-video"
                                    data-video-url="<?= htmlspecialchars($videoUrl) ?>"
                                    data-exo-nom="<?= $nomExo ?>"
                                    title="Voir la démonstration de <?= $nomExo ?>"
                                    aria-label="Voir la vidéo de <?= $nomExo ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                         fill="currentColor" style="vertical-align:middle;margin-right:4px;">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                    Voir la démo
                                </button>
                            <?php else: ?>
                                <span class="btn-video--disabled" title="Aucune vidéo disponible pour cet exercice">
                                    — Pas de vidéo
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div><!-- /overflow-x:auto -->
    <?php endif; ?>
<?php endif; ?>

<!-- ================================================================
     STYLES MODALE  (scoped à cette page)
     ================================================================ -->
<style>
/* ----- Modale container ----- */
.video-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9000;
    align-items: center;
    justify-content: center;
}
.video-modal.is-open {
    display: flex;
}

/* ----- Overlay flouté ----- */
.video-modal__overlay {
    position: absolute;
    inset: 0;
    background: rgba(20, 36, 26, 0.72);
    backdrop-filter: blur(4px);
    cursor: pointer;
}

/* ----- Boîte centrale ----- */
.video-modal__box {
    position: relative;
    z-index: 1;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.35);
    width: min(860px, 94vw);
    padding: 1.5rem 1.5rem 1rem;
    animation: modalIn .22s ease;
}
@keyframes modalIn {
    from { opacity:0; transform:scale(.94) translateY(12px); }
    to   { opacity:1; transform:scale(1)  translateY(0); }
}

/* ----- Titre ----- */
.video-modal__title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--vert-kool-dark, #388E3C);
    margin-bottom: .9rem;
    padding-right: 2.5rem; /* Espace pour le X */
}

/* ----- Iframe 16:9 ----- */
.video-modal__responsive {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    background: #000;
}
.video-modal__responsive iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: none;
}

/* ----- Bouton fermeture ----- */
.video-modal__close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--gris-clair, #F5F5F5);
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #444;
    transition: background .15s, color .15s;
}
.video-modal__close:hover {
    background: var(--vert-kool, #4CAF50);
    color: #fff;
}

/* ----- Bouton "Voir la démo" ----- */
.btn-video {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #FF0000, #cc0000);
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: transform .15s, box-shadow .15s;
    font-family: inherit;
}
.btn-video:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(204,0,0,.35);
}

/* ----- Bouton grisé (pas de vidéo) ----- */
.btn-video--disabled {
    display: inline-block;
    color: #bbb;
    font-size: 0.8rem;
    white-space: nowrap;
}

/* ----- Responsive modale ----- */
@media (max-width: 560px) {
    .video-modal__box {
        padding: 1rem;
        border-radius: 14px;
    }
    .video-modal__title {
        font-size: .95rem;
    }
}
</style>

<!-- Script chargé après le DOM -->
<script src="public/js/video_modal.js"></script>
<script>
// Mettre à jour le titre de la modale avec le nom de l'exercice
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-video');
    if (!btn) return;
    const title = document.getElementById('video-modal-title');
    if (title) {
        title.textContent = '▶ ' + (btn.dataset.exoNom || 'Démonstration');
    }
});
</script>