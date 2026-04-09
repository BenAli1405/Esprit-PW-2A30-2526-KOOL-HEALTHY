-- Recettes partagees
CREATE TABLE IF NOT EXISTS `recettes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `temps_prep` INT NOT NULL,
  `ingredients` LONGTEXT NOT NULL,
  `etapes` LONGTEXT NOT NULL,
  `image` LONGBLOB,
  `auteur` VARCHAR(100) DEFAULT 'Moi',
  `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `likes_count` INT DEFAULT 0,
  INDEX `idx_auteur` (`auteur`),
  INDEX `idx_date` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favoris des utilisateurs
CREATE TABLE IF NOT EXISTS `favoris` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `recette_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_favori` (`user_id`, `recette_id`),
  FOREIGN KEY (`recette_id`) REFERENCES `recettes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Utilisateurs
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `role` VARCHAR(30) NOT NULL DEFAULT 'utilisateur',
  `poids` FLOAT,
  `taille` FLOAT,
  `imc` FLOAT,
  `objectif` VARCHAR(120),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Profil nutritif (jointure 1-1 avec utilisateurs)
CREATE TABLE IF NOT EXISTS `profil_nutritif` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur` INT NOT NULL,
  `age` INT NOT NULL,
  `allergies` VARCHAR(255),
  `besoins_caloriques` INT NOT NULL,
  UNIQUE KEY `uniq_utilisateur_profil` (`utilisateur`),
  CONSTRAINT `fk_profil_utilisateur`
    FOREIGN KEY (`utilisateur`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Commentaires sur les recettes
CREATE TABLE IF NOT EXISTS `commentaires` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `recette_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `texte` LONGTEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`recette_id`) REFERENCES `recettes`(`id`) ON DELETE CASCADE,
  INDEX `idx_recette` (`recette_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Donnees exemple: une recette
INSERT INTO `recettes` (`titre`, `temps_prep`, `ingredients`, `etapes`, `auteur`)
VALUES (
  'Salade fraiche du soleil',
  15,
  'Laitue, tomates cerise, concombre, carottes, huile olive, citron, sel',
  '1. Laver la laitue et les legumes. 2. Couper les tomates en deux et le concombre en des. 3. Ciseler les carottes. 4. Melanger dans un saladier. 5. Ajouter lhuile olive et le jus de citron. 6. Saler et servir frais.',
  'Yasmine'
);
