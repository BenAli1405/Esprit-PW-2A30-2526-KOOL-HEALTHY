-- ================================================================
-- Kool Healthy – Module 5 : Plan Nutritionnel
-- Script SQL d'initialisation (compatible MySQL / MariaDB)
-- ================================================================

CREATE DATABASE IF NOT EXISTS `kool_healthy_db`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `kool_healthy_db`;

-- ── Table utilisateurs (partagée avec les autres modules) ───────
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `nom`          VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) UNIQUE NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `role`         ENUM('utilisateur','admin') NOT NULL DEFAULT 'utilisateur',
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table plans_nutritionnels ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `plans_nutritionnels` (
  `planID`                 INT AUTO_INCREMENT PRIMARY KEY,
  `nom`                    VARCHAR(255)   NOT NULL,
  `calories_journalieres`  FLOAT          NOT NULL DEFAULT 2000,
  `utilisateur_id`         INT            NOT NULL,   -- #utilisateur_id (FK)
  `date_debut`             DATE           NOT NULL,
  `date_fin`               DATE           NOT NULL,
  `statistiques`           FLOAT          DEFAULT 0,  -- % progression
  `created_at`             DATETIME       DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_utilisateur` (`utilisateur_id`),
  CONSTRAINT `fk_plan_utilisateur`
    FOREIGN KEY (`utilisateur_id`)
    REFERENCES `utilisateurs`(`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Table repas ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `repas` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `planID`     INT          NOT NULL,   -- #PlanID (FK)
  `recette`    VARCHAR(255) NOT NULL,   -- #recette (référence recette)
  `date`       DATE         NOT NULL,
  `type_repas` ENUM('petit-déjeuner','déjeuner','dîner','collation')
               NOT NULL DEFAULT 'déjeuner',
  `statut`     ENUM('planifié','consommé','annulé')
               NOT NULL DEFAULT 'planifié',
  INDEX `idx_plan` (`planID`),
  CONSTRAINT `fk_repas_plan`
    FOREIGN KEY (`planID`)
    REFERENCES `plans_nutritionnels`(`planID`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Données de démonstration ────────────────────────────────────
INSERT IGNORE INTO `utilisateurs` (`nom`, `email`, `mot_de_passe`, `role`) VALUES
  ('Alice Dupont',   'alice@koolhealthy.com',   SHA2('motdepasse1', 256), 'utilisateur'),
  ('Bob Martin',     'bob@koolhealthy.com',     SHA2('motdepasse2', 256), 'utilisateur'),
  ('Dr. Emma Green', 'admin@koolhealthy.com',   SHA2('admin2025',   256), 'admin');

INSERT INTO `plans_nutritionnels`
  (`nom`, `calories_journalieres`, `utilisateur_id`, `date_debut`, `date_fin`, `statistiques`)
VALUES
  ('Plan équilibré été 2025', 2000, 1, '2025-06-01', '2025-08-31', 65),
  ('Plan perte de poids',     1600, 1, '2025-09-01', '2025-11-30', 20),
  ('Plan sportif intensif',   2800, 2, '2025-07-01', '2025-09-30', 80);

INSERT INTO `repas` (`planID`, `recette`, `date`, `type_repas`, `statut`) VALUES
  (1, 'Porridge avoine & fruits rouges',   '2025-06-01', 'petit-déjeuner', 'consommé'),
  (1, 'Buddha Bowl quinoa & légumes',      '2025-06-01', 'déjeuner',       'consommé'),
  (1, 'Soupe de lentilles corail',         '2025-06-01', 'dîner',          'planifié'),
  (1, 'Smoothie banane & amandes',         '2025-06-02', 'petit-déjeuner', 'planifié'),
  (1, 'Wrap avocat & poulet',              '2025-06-02', 'déjeuner',       'planifié'),
  (2, 'Yaourt grec & graines de chia',     '2025-09-01', 'petit-déjeuner', 'consommé'),
  (2, 'Salade niçoise légère',             '2025-09-01', 'déjeuner',       'consommé'),
  (3, 'Omelette aux épinards (4 œufs)',    '2025-07-01', 'petit-déjeuner', 'consommé'),
  (3, 'Riz brun & saumon grillé',          '2025-07-01', 'déjeuner',       'consommé'),
  (3, 'Pâtes complètes bolognaise légère', '2025-07-01', 'dîner',          'consommé');
