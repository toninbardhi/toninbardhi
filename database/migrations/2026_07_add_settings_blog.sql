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

-- ------------------------------------------------------------
--  Blog: copertina, tag e collegamento a una categoria.
--  (Se hai già la tabella `posts` senza queste colonne.)
-- ------------------------------------------------------------
ALTER TABLE `posts`
  ADD COLUMN `cover_image` VARCHAR(500) NULL AFTER `body`,
  ADD COLUMN `tags`        VARCHAR(255) NULL AFTER `cover_image`,
  ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `tags`,
  ADD KEY `idx_posts_category` (`category_id`),
  ADD CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE SET NULL;

-- ------------------------------------------------------------
--  Blog: articoli sponsorizzati (a pagamento, gestiti a mano).
--  L'articolo lo scrive l'admin; lo sponsor paga offline.
-- ------------------------------------------------------------
ALTER TABLE `posts`
  ADD COLUMN `is_sponsored`    TINYINT(1) NOT NULL DEFAULT 0 AFTER `category_id`,
  ADD COLUMN `sponsor_name`    VARCHAR(160) NULL AFTER `is_sponsored`,
  ADD COLUMN `sponsor_url`     VARCHAR(500) NULL AFTER `sponsor_name`,
  ADD COLUMN `sponsor_price`   DECIMAL(8,2) NULL AFTER `sponsor_url`,
  ADD COLUMN `sponsored_until` DATE NULL AFTER `sponsor_price`;

-- Prezzo di listino pubblico per un articolo sponsorizzato ("tot ad articolo").
INSERT IGNORE INTO `settings` (`key`,`value`) VALUES
  ('sponsor_price', ''),
  ('sponsor_info',  'Vuoi pubblicare un articolo sponsorizzato sul nostro blog? Scrivici.');
