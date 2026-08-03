-- ============================================================
--  Migrazione: sezione "Spotlight" (presentazioni assistite da AI).
--  Esegui SOLO se hai già installato una versione precedente.
--  NON è una recensione: nessun voto/giudizio. Mostra una
--  presentazione + "come lo vede il web" + "come lo vede l'AI".
--  Disclosure "assistito da AI" obbligatoria (EU AI Act).
-- ============================================================

CREATE TABLE IF NOT EXISTS `spotlights` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(200) NOT NULL,
  `slug`          VARCHAR(220) NOT NULL,
  `subject_name`  VARCHAR(200) NOT NULL,
  `subject_url`   VARCHAR(500) NULL,
  `category_id`   INT UNSIGNED NULL,
  `presentation`  MEDIUMTEXT NULL,
  `web_view`      MEDIUMTEXT NULL,
  `ai_view`       MEDIUMTEXT NULL,
  `cover_image`   VARCHAR(500) NULL,
  `ai_generated`  TINYINT(1) NOT NULL DEFAULT 1,
  `is_paid`       TINYINT(1) NOT NULL DEFAULT 0,
  `sponsor_name`  VARCHAR(160) NULL,
  `status`        ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `author`        VARCHAR(120) NULL,
  `published_at`  DATETIME NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_spotlights_slug` (`slug`),
  KEY `idx_spotlights_status` (`status`,`published_at`),
  KEY `idx_spotlights_category` (`category_id`),
  CONSTRAINT `fk_spotlights_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
