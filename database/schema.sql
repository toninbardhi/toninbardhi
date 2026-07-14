-- ============================================================
--  DirCMS - Schema del database (stile DMOZ / Open Directory)
--  Compatibile con MySQL / MariaDB (hosting Netsons)
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ------------------------------------------------------------
--  Utenti (admin ed editor)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  `active`        TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Categorie (albero gerarchico, come le rubriche DMOZ)
--  parent_id NULL = categoria di primo livello
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id`   INT UNSIGNED NULL,
  `name`        VARCHAR(160) NOT NULL,
  `slug`        VARCHAR(180) NOT NULL,
  `path`        VARCHAR(2000) NOT NULL DEFAULT '',  -- percorso slug completo es. arts/movies
  `description` TEXT NULL,
  `sort_order`  INT NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categories_parent` (`parent_id`),
  KEY `idx_categories_path` (`path`(191)),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`)
      REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Link / siti (le "listing" di DMOZ)
--  status: approved = pubblicato, pending = suggerimento da approvare
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  INT UNSIGNED NOT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `url`          VARCHAR(2048) NOT NULL,
  `description`  TEXT NULL,
  `status`       ENUM('approved','pending') NOT NULL DEFAULT 'approved',
  `submitted_by` VARCHAR(190) NULL,   -- email di chi suggerisce (form pubblico)
  `sort_order`   INT NOT NULL DEFAULT 0,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_links_category` (`category_id`),
  KEY `idx_links_status` (`status`),
  FULLTEXT KEY `ft_links_search` (`title`,`description`),
  CONSTRAINT `fk_links_category` FOREIGN KEY (`category_id`)
      REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- ------------------------------------------------------------
--  Dati iniziali di esempio
-- ------------------------------------------------------------

-- Utente admin di default.
-- Password: admin123  (CAMBIALA dopo il primo accesso!)
INSERT INTO `users` (`name`,`email`,`password_hash`,`role`) VALUES
  ('Amministratore','admin@example.com',
   '$2y$12$mgJg4RkAWS1TsoaswMD.lu/ZVoQ2RnAMaUnqOWOSS1jE/UhLyWtK2','admin');

-- Categorie di esempio
INSERT INTO `categories` (`id`,`parent_id`,`name`,`slug`,`path`,`description`,`sort_order`) VALUES
  (1, NULL, 'Arte',        'arte',        'arte',            'Musica, cinema, letteratura e arti visive.', 1),
  (2, NULL, 'Informatica', 'informatica', 'informatica',     'Software, hardware, internet e programmazione.', 2),
  (3, NULL, 'Scienza',     'scienza',     'scienza',         'Fisica, biologia, matematica e ricerca.', 3),
  (4, 1,    'Cinema',      'cinema',      'arte/cinema',     'Film, registi, recensioni.', 1),
  (5, 2,    'Programmazione','programmazione','informatica/programmazione','Linguaggi e sviluppo software.', 1);

INSERT INTO `links` (`category_id`,`title`,`url`,`description`,`status`) VALUES
  (5, 'PHP.net', 'https://www.php.net', 'Sito ufficiale del linguaggio PHP con documentazione completa.', 'approved'),
  (5, 'MDN Web Docs', 'https://developer.mozilla.org', 'Documentazione per sviluppatori web di Mozilla.', 'approved'),
  (4, 'IMDb', 'https://www.imdb.com', 'Il più grande database di film e serie TV.', 'approved');
