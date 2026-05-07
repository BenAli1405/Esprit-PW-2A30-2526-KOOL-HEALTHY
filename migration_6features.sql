-- ============================================================
-- Migration : ajout de type_mouvement et groupe_primaire
-- Table : exercice_reference
-- Date  : 2026-05-06
-- ============================================================

-- 1. Ajouter les deux nouvelles colonnes (DEFAULT 0.5 pour compatibilité
--    avec les exercices déjà présents qui n'ont pas ces valeurs)
ALTER TABLE exercice_reference
    ADD COLUMN type_mouvement  DECIMAL(3,2) NOT NULL DEFAULT 0.50
        COMMENT '0.1=mobilité/étirement | 0.3=isolation | 0.5=cardio | 0.7=plyométrie | 0.8=compound | 1.0=olympique',
    ADD COLUMN groupe_primaire DECIMAL(3,2) NOT NULL DEFAULT 0.50
        COMMENT '0.15=quadriceps | 0.25=fessiers | 0.40=abdos | 0.55=biceps | 0.65=dorsaux | 0.80=épaules | 0.85=pectoraux';

-- 2. (Facultatif) S'assurer que la colonne nom a bien un index UNIQUE
--    pour que ON DUPLICATE KEY UPDATE fonctionne dans WorkoutXApiService.
--    Si l'index existe déjà, supprimez cette ligne.
-- ALTER TABLE exercice_reference ADD UNIQUE KEY uq_nom (nom);

-- 3. Vérification post-migration
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME   = 'exercice_reference'
ORDER BY ORDINAL_POSITION;
