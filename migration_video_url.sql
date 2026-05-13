-- ============================================================
-- Migration : ajout de video_url dans exercice_reference
-- ============================================================

ALTER TABLE exercice_reference
    ADD COLUMN video_url VARCHAR(255) NULL DEFAULT NULL
        COMMENT 'URL YouTube embed (https://www.youtube.com/embed/...) pour la démo vidéo';

-- ============================================================
-- Exemples de vidéos pour les exercices populaires
-- (adaptez les noms selon les données présentes dans votre base)
-- ============================================================

-- Exercices de base (poids du corps)
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/IODxDxX7oi4' WHERE LOWER(nom) = 'push-up';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/IODxDxX7oi4' WHERE LOWER(nom) = 'push up';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/IODxDxX7oi4' WHERE LOWER(nom) LIKE '%push%up%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/YaXPRqUwItQ' WHERE LOWER(nom) = 'squat';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/YaXPRqUwItQ' WHERE LOWER(nom) LIKE '%squat%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/R08gYyypGto' WHERE LOWER(nom) LIKE '%plank%';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/R08gYyypGto' WHERE LOWER(nom) LIKE '%gainage%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/swKBi-hA2do' WHERE LOWER(nom) LIKE '%lunge%';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/swKBi-hA2do' WHERE LOWER(nom) LIKE '%fente%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/ykJmrYK5nSU' WHERE LOWER(nom) LIKE '%deadlift%';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/ykJmrYK5nSU' WHERE LOWER(nom) LIKE '%soulevé%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/eGo4IYlbE5g' WHERE LOWER(nom) LIKE '%pull-up%';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/eGo4IYlbE5g' WHERE LOWER(nom) LIKE '%traction%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/gRVjAtPip0Y' WHERE LOWER(nom) LIKE '%bench press%';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/gRVjAtPip0Y' WHERE LOWER(nom) LIKE '%développé couché%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/1fbU_MkV7NE' WHERE LOWER(nom) LIKE '%overhead press%';
UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/1fbU_MkV7NE' WHERE LOWER(nom) LIKE '%military press%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/rT7DgCr-3pg' WHERE LOWER(nom) LIKE '%burpee%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/jDwoBqPH0jk' WHERE LOWER(nom) LIKE '%mountain climber%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/5jS8niJCLmQ' WHERE LOWER(nom) LIKE '%jumping jack%';

UPDATE exercice_reference SET video_url = 'https://www.youtube.com/embed/SFLTnQkBKJ0' WHERE LOWER(nom) LIKE '%dip%';

-- Vérification : afficher les exercices avec vidéo
SELECT id, nom, video_url FROM exercice_reference WHERE video_url IS NOT NULL ORDER BY nom;
