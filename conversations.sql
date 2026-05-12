-- Table conversations pour le Coach Nutritionnel (Chatbot)
-- Clé étrangère vers plan(id) avec suppression en cascade

CREATE TABLE IF NOT EXISTS `conversations` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `plan_id` INT NOT NULL,
    `message_utilisateur` TEXT NOT NULL,
    `reponse_chatbot` TEXT NOT NULL,
    `sentiment_detecte` VARCHAR(50) NOT NULL DEFAULT 'neutre',
    `date_creation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_conversations_plan_id` (`plan_id`),
    KEY `idx_conversations_date` (`date_creation`),
    CONSTRAINT `fk_conversations_plan` FOREIGN KEY (`plan_id`)
        REFERENCES `plan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
