-- ============================================================
-- Migration : table performance_exercice
-- Module : Progression & Optimisation
-- ============================================================

CREATE TABLE IF NOT EXISTS performance_exercice (
    id_performance  INT           AUTO_INCREMENT PRIMARY KEY,
    id_exercice     INT           NOT NULL COMMENT 'FK vers exercice.id_exercice',
    date            DATE          NOT NULL,
    poids           DECIMAL(5,2)  NULL     DEFAULT NULL COMMENT 'Charge en kg (NULL = poids du corps)',
    repetitions     INT           NOT NULL,
    series          INT           NOT NULL,
    fatigue         TINYINT       NULL     DEFAULT NULL COMMENT 'Ressenti 1-10 (optionnel)',
    commentaire     TEXT          NULL,
    FOREIGN KEY (id_exercice) REFERENCES exercice(id_exercice) ON DELETE CASCADE,
    INDEX idx_exercice_date (id_exercice, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historique des performances par exercice';

-- ============================================================
-- Données exemples (exercice id=1 = Étirements, id=2 = Fentes,
--                   id=3 = Pompes, id=4 = Gainage)
-- Adaptez les id_exercice à vos valeurs réelles.
-- ============================================================

-- Pompes (id_exercice = 3) sur 8 semaines, progression réaliste
INSERT INTO performance_exercice (id_exercice, date, poids, repetitions, series, fatigue, commentaire) VALUES
(3, '2026-03-01', 0.00, 10, 3, 6, 'Séance de départ'),
(3, '2026-03-08', 0.00, 11, 3, 5, 'Légère progression'),
(3, '2026-03-15', 5.00, 10, 3, 7, 'Gilet lesté +5 kg'),
(3, '2026-03-22', 5.00, 12, 3, 6, NULL),
(3, '2026-03-29', 5.00, 12, 4, 5, '1 série supplémentaire'),
(3, '2026-04-05', 10.00, 10, 4, 7, 'Augmentation charge'),
(3, '2026-04-12', 10.00, 11, 4, 6, NULL),
(3, '2026-04-19', 10.00, 11, 4, 6, 'Plateau potentiel'),
(3, '2026-04-26', 10.00, 12, 4, 5, NULL),

-- Fentes (id_exercice = 2) sur 6 semaines
(2, '2026-03-03', 0.00, 12, 3, 5, 'Sans haltères'),
(2, '2026-03-10', 5.00, 10, 3, 6, '+5 kg haltères'),
(2, '2026-03-17', 5.00, 12, 3, 5, NULL),
(2, '2026-03-24', 8.00, 10, 3, 7, '+8 kg'),
(2, '2026-03-31', 8.00, 12, 3, 6, NULL),
(2, '2026-04-07', 10.00, 10, 4, 6, '10 kg + 4 séries');

-- Vérification
SELECT e.nom AS exercice, p.date, p.poids, p.repetitions, p.series,
       ROUND(p.poids * p.repetitions * p.series, 1) AS charge_totale
FROM performance_exercice p
JOIN exercice e ON e.id_exercice = p.id_exercice
ORDER BY p.id_exercice, p.date;
