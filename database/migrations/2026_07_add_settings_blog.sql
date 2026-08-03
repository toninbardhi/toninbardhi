-- ============================================================
--  Migrazione: impostazioni personalizzabili + blog
--  Esegui SOLO se hai già installato una versione precedente.
-- ============================================================

CREATE TABLE IF NOT EXISTS `settings` (
  `key`   VARCHAR(64) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`key`,`value`) VALUES
  ('site_name',     'webdirectory.link'),
  ('home_title',    'webdirectory.link'),
  ('home_subtitle', 'Sfoglia le categorie della directory o usa la ricerca in alto.'),
  ('footer_text',   'directory web in stile Open Directory.'),
  ('contact_email', 'info@webdirectory.link'),
  ('owner',         '');

CREATE TABLE IF NOT EXISTS `posts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(200) NOT NULL,
  `slug`         VARCHAR(220) NOT NULL,
  `excerpt`      VARCHAR(500) NULL,
  `body`         MEDIUMTEXT NULL,
  `status`       ENUM('draft','published') NOT NULL DEFAULT 'draft',
  `author`       VARCHAR(120) NULL,
  `published_at` DATETIME NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_posts_slug` (`slug`),
  KEY `idx_posts_status` (`status`,`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
