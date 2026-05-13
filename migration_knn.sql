-- Migration : Ajout de la table exercice_feature pour le système KNN
-- Exécuter ce script dans PhpMyAdmin après database.sql

USE kool_healthy;

-- Créer la table exercice_feature
CREATE TABLE IF NOT EXISTS exercice_feature (
    id_feature INT AUTO_INCREMENT PRIMARY KEY,
    id_exercice INT NOT NULL UNIQUE,
    intensite_calorique DECIMAL(3,2) CHECK (intensite_calorique BETWEEN 0 AND 1),
    equipement DECIMAL(3,2) CHECK (equipement BETWEEN 0 AND 1),
    difficulte DECIMAL(3,2) CHECK (difficulte BETWEEN 0 AND 1),
    cible_musculaire DECIMAL(3,2) CHECK (cible_musculaire BETWEEN 0 AND 1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_exercice) REFERENCES exercice(id_exercice) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insérer des données exemple pour les exercices existants
INSERT INTO exercice_feature (id_exercice, intensite_calorique, equipement, difficulte, cible_musculaire) VALUES
-- Exercice 1: Étirements dynamiques (très facile, pas d'équipement, faible intensité)
(1, 0.2, 0.1, 0.1, 0.3),
-- Exercice 2: Fentes avant (moyen, poids du corps, difficulté moyenne)
(2, 0.6, 0.2, 0.5, 0.7),
-- Exercice 3: Pompes (élevé, poids du corps, difficulté moyenne-haute)
(3, 0.7, 0.1, 0.6, 0.8),
-- Exercice 4: Gainage (moyen-élevé, poids du corps, difficulté moyenne)
(4, 0.65, 0.1, 0.5, 0.6);
