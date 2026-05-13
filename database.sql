-- Base de données pour le module Entraînement & Exercice
CREATE DATABASE IF NOT EXISTS kool_healthy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kool_healthy;

CREATE TABLE IF NOT EXISTS utilisateur (
  id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS entrainement (
  id_entrainement INT AUTO_INCREMENT PRIMARY KEY,
  id_utilisateur INT NOT NULL,
  date DATE NOT NULL,
  duree_minutes INT NOT NULL,
  type_sport VARCHAR(100) NOT NULL,
  calories_brulees INT NOT NULL,
  commentaire TEXT,
  FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exercice (
  id_exercice INT AUTO_INCREMENT PRIMARY KEY,
  id_entrainement INT NOT NULL,
  nom VARCHAR(150) NOT NULL,
  series INT NOT NULL,
  repetitions INT NOT NULL,
  repos_secondes INT NOT NULL,
  ordre INT NOT NULL,
  FOREIGN KEY (id_entrainement) REFERENCES entrainement(id_entrainement) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recommandation_regle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type_repas VARCHAR(50) NOT NULL,
  exercice_suggere VARCHAR(150) NOT NULL,
  series INT NOT NULL,
  repetitions INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO utilisateur (nom, email) VALUES
('Alice Dupont', 'alice@example.com'),
('Marc Petit', 'marc@example.com');

INSERT INTO entrainement (id_utilisateur, date, duree_minutes, type_sport, calories_brulees, commentaire) VALUES
(1, '2026-04-01', 45, 'Course à pied', 420, 'Séance en endurance légère.'),
(1, '2026-04-03', 30, 'Renforcement musculaire', 320, 'Circuit full body.'),
(2, '2026-04-02', 60, 'Cyclisme', 510, 'Sortie vélo en extérieur.');

INSERT INTO exercice (id_entrainement, nom, series, repetitions, repos_secondes, ordre) VALUES
(1, 'Étirements dynamiques', 2, 10, 30, 1),
(1, 'Fentes avant', 3, 12, 45, 2),
(2, 'Pompes', 4, 10, 60, 1),
(2, 'Gainage', 3, 40, 30, 2);

INSERT INTO recommandation_regle (type_repas, exercice_suggere, series, repetitions) VALUES
('Léger', 'Étirements doux', 2, 12),
('Léger', 'Marche active', 1, 20),
('Équilibré', 'Squats', 3, 15),
('Équilibré', 'Planche', 3, 30),
('Riche', 'Burpees', 4, 10),
('Riche', 'Sauts sur place', 4, 20);
