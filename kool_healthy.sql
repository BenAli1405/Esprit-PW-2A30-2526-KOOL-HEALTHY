-- ========== KOOL HEALTHY DATABASE ==========
-- SQL Database Schema for MVC Application
-- Created: April 14, 2026

-- Create Database
CREATE DATABASE IF NOT EXISTS web;
USE web;

-- ========== TABLE: INGREDIENTS ==========
CREATE TABLE IF NOT EXISTS ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL UNIQUE,
    calories VARCHAR(50),
    eco_score ENUM('A+', 'A', 'B', 'C', 'D', 'E') DEFAULT 'A',
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    est_actif BOOLEAN DEFAULT TRUE,
    INDEX idx_eco_score (eco_score),
    INDEX idx_nom (nom)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== TABLE: RECETTES (Recipes) ==========
CREATE TABLE IF NOT EXISTS recettes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(150) NOT NULL,
    instruction TEXT NOT NULL,
    temps_preparation INT,
    difficulte ENUM('Facile', 'Moyen', 'Difficile') DEFAULT 'Facile',
    eco_score ENUM('A+', 'A', 'B', 'C', 'D', 'E') DEFAULT 'A',
    nombre_portions INT DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    est_actif BOOLEAN DEFAULT TRUE,
    INDEX idx_difficulte (difficulte),
    INDEX idx_eco_score (eco_score),
    INDEX idx_titre (titre)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== TABLE: RECETTE_INGREDIENT (Junction Table) ==========
-- Many-to-many relationship between recipes and ingredients
CREATE TABLE IF NOT EXISTS recette_ingredient (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recette_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantite DECIMAL(10, 2) NOT NULL,
    unite VARCHAR(20) DEFAULT 'g',
    ordre INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recette_ingredient_recette FOREIGN KEY (recette_id) REFERENCES recettes(id) ON DELETE CASCADE,
    CONSTRAINT fk_recette_ingredient_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_recette_ingredient (recette_id, ingredient_id),
    INDEX idx_recette_id (recette_id),
    INDEX idx_ingredient_id (ingredient_id)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== TABLE: AVIS (Reviews) ==========
CREATE TABLE IF NOT EXISTS avis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recette_id INT NOT NULL,
    utilisateur_nom VARCHAR(100) NOT NULL,
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    commentaire TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    est_modere BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_avis_recette FOREIGN KEY (recette_id) REFERENCES recettes(id) ON DELETE CASCADE,
    INDEX idx_recette_id (recette_id),
    INDEX idx_note (note),
    INDEX idx_date_creation (date_creation)
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== INSERT: SAMPLE DATA ==========

-- Insert Ingredients
INSERT INTO ingredients (nom, calories, eco_score, description) VALUES
('Pois chiches', '139kcal/100g', 'A', 'Légumineuse riche en protéines'),
('Quinoa', '120kcal/100g', 'A', 'Pseudo-céréale complète'),
('Kale', '49kcal/100g', 'A+', 'Chou frisé très nutritif'),
('Lentilles vertes', '116kcal/100g', 'A', 'Légumineuse avec fibres'),
('Tofu fermier', '76kcal/100g', 'A', 'Protéine végétale'),
('Avocat', '160kcal/100g', 'B', 'Fruit riche en gras sains'),
('Pomme locale', '52kcal', 'A', 'Fruit local et saisonnier'),
('Patate douce', '86kcal/100g', 'A', 'Légume sucré et nutritif'),
('Brocoli', '34kcal/100g', 'A', 'Crucifère riche en vitamines'),
('Riz complet', '111kcal/100g', 'A', 'Céréale complète');

-- Insert Recipes
INSERT INTO recettes (titre, instruction, temps_preparation, difficulte, eco_score, nombre_portions) VALUES
('Buddha Bowl pois chiches & quinoa', 'Mélanger pois chiches rôtis, quinoa cuit, kale frais. Ajouter sauce tahini et citron.', 25, 'Facile', 'A', 2),
('Soupe de lentilles corail', 'Cuire les lentilles avec carottes, oignon, lait de coco. Mixer et servir chaud.', 30, 'Facile', 'A', 4),
('Tartine avocat & graines', 'Écraser l\'avocat sur pain complet toasté, ajouter graines, citron, sel et poivre.', 10, 'Facile', 'A+', 1),
('Tofu mariné aux épices', 'Mariner le tofu dans sauce soja, gingembre, ail. Cuire à la poêle 10 min.', 20, 'Moyen', 'A', 2),
('Patates douces rôties au romarin', 'Couper les patates en cubes, mélanger avec huile et romarin. Enfourner 25 min à 200°C.', 35, 'Facile', 'A', 3),
('Salade kale & pois chiches', 'Mélanger kale massé, pois chiches rôtis, vinaigrette maison.', 15, 'Facile', 'A', 2),
('Curry de lentilles vertes', 'Cuire lentilles avec oignons, épices, tomate. Servir avec riz complet.', 40, 'Moyen', 'A', 4),
('Bol de quinoa aux brocolis', 'Cuire quinoa, brocoli vapeur, ajouter amandes et vinaigrette citron.', 20, 'Facile', 'A', 2);

-- Insert Recipe-Ingredient links
INSERT INTO recette_ingredient (recette_id, ingredient_id, quantite, unite, ordre) VALUES
-- Buddha Bowl
(1, 1, 150, 'g', 1),
(1, 2, 100, 'g', 2),
(1, 3, 50, 'g', 3),
-- Soupe lentilles
(2, 4, 200, 'g', 1),
-- Tartine avocat
(3, 6, 100, 'g', 1),
-- Tofu mariné
(4, 5, 200, 'g', 1),
-- Patates douces rôties
(5, 8, 500, 'g', 1),
-- Salade kale
(6, 3, 100, 'g', 1),
(6, 1, 100, 'g', 2),
-- Curry lentilles
(7, 4, 250, 'g', 1),
(7, 10, 200, 'g', 2),
-- Bol quinoa
(8, 2, 150, 'g', 1),
(8, 9, 200, 'g', 2);

-- Insert Reviews
INSERT INTO avis (recette_id, utilisateur_nom, note, commentaire) VALUES
(1, 'Sophie Martin', 5, 'Délicieux et rassasiant !'),
(1, 'Thomas Leroy', 4, 'Très bon, facile à préparer.'),
(2, 'Marie Dubois', 5, 'Soupe réconfortante et healthy !'),
(4, 'Sophie Martin', 4, 'Très bon, épices parfaites.'),
(6, 'Julien Rousseau', 5, 'Recette simple mais savoureuse'),
(7, 'Marie Dubois', 4, 'Saveurs exotiques agréables'),
(8, 'Sophie Martin', 5, 'Avec tous les atouts santé');

-- ========== VIEWS (Optional - for easier querying) ==========

-- View: Recipes with ingredient count
CREATE OR REPLACE VIEW v_recettes_details AS
SELECT 
    r.id,
    r.titre,
    r.temps_preparation,
    r.difficulte,
    r.eco_score,
    r.nombre_portions,
    COUNT(DISTINCT ri.ingredient_id) as nombre_ingredients,
    COUNT(DISTINCT a.id) as nombre_avis,
    AVG(a.note) as note_moyenne
FROM recettes r
LEFT JOIN recette_ingredient ri ON r.id = ri.recette_id
LEFT JOIN avis a ON r.id = a.recette_id
WHERE r.est_actif = TRUE
GROUP BY r.id;

-- View: Ingredients with usage count
CREATE OR REPLACE VIEW v_ingredients_stats AS
SELECT 
    i.id,
    i.nom,
    i.calories,
    i.eco_score,
    COUNT(DISTINCT ri.recette_id) as nombre_utilisations
FROM ingredients i
LEFT JOIN recette_ingredient ri ON i.id = ri.ingredient_id
GROUP BY i.id;

-- View: Top recipes by rating
CREATE OR REPLACE VIEW v_top_recettes AS
SELECT 
    r.id,
    r.titre,
    AVG(a.note) as note_moyenne,
    COUNT(a.id) as nombre_avis,
    r.eco_score
FROM recettes r
LEFT JOIN avis a ON r.id = a.recette_id
WHERE r.est_actif = TRUE
GROUP BY r.id
ORDER BY note_moyenne DESC;

-- ========== INDEXES FOR PERFORMANCE ==========

-- Additional performance indexes
CREATE INDEX idx_avis_recette_date ON avis(recette_id, date_creation);
CREATE INDEX idx_ingredients_eco_score ON ingredients(eco_score);

-- ========== QUERY EXAMPLES ==========
/*

-- Get all recipes with their ingredients
SELECT 
    r.titre,
    GROUP_CONCAT(i.nom SEPARATOR ', ') as ingredients,
    r.temps_preparation,
    r.difficulte
FROM recettes r
LEFT JOIN recette_ingredient ri ON r.id = ri.recette_id
LEFT JOIN ingredients i ON ri.ingredient_id = i.id
GROUP BY r.id;

-- Get recipes by eco-score
SELECT * FROM recettes WHERE eco_score = 'A' AND est_actif = TRUE;

-- Get reviews for a recipe
SELECT 
    a.utilisateur_nom,
    a.commentaire,
    a.note,
    r.titre as recette,
    DATE_FORMAT(a.date_creation, '%d/%m/%Y') as date_avis
FROM avis a
JOIN recettes r ON a.recette_id = r.id
ORDER BY a.date_creation DESC;

-- Get top ingredients
SELECT nom, nombre_utilisations FROM v_ingredients_stats ORDER BY nombre_utilisations DESC LIMIT 5;

*/
