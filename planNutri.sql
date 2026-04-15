-- schéma pour les tables de plan nutritionnel et repas
CREATE TABLE `plan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nom` VARCHAR(255) NOT NULL,
  `objectif` TEXT,
  `utilisateur_id` INT NOT NULL,
  `duree` VARCHAR(100),
  `preference` VARCHAR(255),
  `allergies` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `repas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `plan_id` INT NOT NULL,
  `recette_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `type_repas` ENUM('petit_dejeuner', 'dejeuner', 'diner', 'collation') NOT NULL DEFAULT 'dejeuner',
  `statut` ENUM('prevu', 'consomme', 'annule') NOT NULL DEFAULT 'prevu',
  `calories_consommees` INT,
  `heure_prevue` TIME,
  `heure_reelle` TIME,
  `notes` TEXT,
  PRIMARY KEY (`id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_recette_id` (`recette_id`),
  CONSTRAINT `fk_repas_plan` FOREIGN KEY (`plan_id`) REFERENCES `plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_repas_recette` FOREIGN KEY (`recette_id`) REFERENCES `Recette` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
