-- ============================================================
-- Kool Healthy — Module Gamification
-- Base de données : kool-healthy 
-- ============================================================

-- Défis
CREATE TABLE IF NOT EXISTS `defis` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'nutrition',
  `points` INT NOT NULL DEFAULT 0,
  `date_debut` DATE,
  `date_fin` DATE,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Participations utilisateurs aux défis
CREATE TABLE IF NOT EXISTS `participations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur_id` INT NOT NULL,
  `defi_id` INT NOT NULL,
  `progression` INT NOT NULL DEFAULT 0,
  `termine` TINYINT(1) NOT NULL DEFAULT 0,
  `points_gagnes` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_participation` (`utilisateur_id`, `defi_id`),
  FOREIGN KEY (`defi_id`) REFERENCES `defis`(`id`) ON DELETE CASCADE,
  INDEX `idx_utilisateur` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données exemple — défis
INSERT INTO `defis` (`titre`, `type`, `points`, `date_debut`, `date_fin`) VALUES
  ('Manger 5 fruits/légumes par jour', 'nutrition', 50,  '2025-03-01', '2025-03-31'),
  ('Réduire son empreinte carbone',    'ecologie',  100, '2025-03-01', '2025-04-15'),
  ('Tester 3 recettes végétales',      'recette',   75,  '2025-03-10', '2025-04-10'),
  ('Partager un repas durable',        'social',    30,  '2025-03-15', '2025-03-30');

-- Données exemple — participations
INSERT INTO `participations` (`utilisateur_id`, `defi_id`, `progression`, `termine`, `points_gagnes`) VALUES
  (1, 1, 25, 0, 0),
  (2, 3, 100, 1, 75);
