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
  `featured`         TINYINT(1) NOT NULL DEFAULT 0,   -- 1 = in evidenza (a pagamento)
  `featured_position` INT NOT NULL DEFAULT 0,         -- ordine tra gli sponsor (1 = primo)
  `featured_until`   DATE NULL,                       -- scadenza del posizionamento
  `image_url`    VARCHAR(2048) NULL,                  -- anteprima/thumbnail (og:image)
  `latitude`     DECIMAL(10,7) NULL,                  -- posizione (mappa OpenStreetMap)
  `longitude`    DECIMAL(10,7) NULL,
  `address`      VARCHAR(255) NULL,                   -- indirizzo mostrato sotto la mappa
  `sort_order`   INT NOT NULL DEFAULT 0,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_links_category` (`category_id`),
  KEY `idx_links_status` (`status`),
  KEY `idx_links_featured` (`featured`,`featured_position`),
  FULLTEXT KEY `ft_links_search` (`title`,`description`),
  CONSTRAINT `fk_links_category` FOREIGN KEY (`category_id`)
      REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- ------------------------------------------------------------
--  Dati iniziali di esempio
-- ------------------------------------------------------------

-- Utente admin di default.
-- Accesso: admin@webdirectory.link  ·  Password: admin123
-- CAMBIA email/nome/password dopo il primo accesso da "Il mio profilo".
INSERT INTO `users` (`name`,`email`,`password_hash`,`role`) VALUES
  ('Amministratore','admin@webdirectory.link',
   '$2y$12$mgJg4RkAWS1TsoaswMD.lu/ZVoQ2RnAMaUnqOWOSS1jE/UhLyWtK2','admin');

-- ------------------------------------------------------------
--  Categorie: struttura Open Directory / Curlie (in italiano)
--  15 categorie principali con sottocategorie rappresentative.
-- ------------------------------------------------------------
INSERT INTO `categories` (`id`,`parent_id`,`name`,`slug`,`path`,`description`,`sort_order`) VALUES
  -- Livello principale
  (1,  NULL, 'Affari',            'affari',            'affari',            'Aziende, lavoro, finanza ed economia.', 1),
  (2,  NULL, 'Arte',              'arte',              'arte',              'Cinema, musica, letteratura, fotografia e spettacolo.', 2),
  (3,  NULL, 'Computer',          'computer',          'computer',          'Software, hardware, internet e programmazione.', 3),
  (4,  NULL, 'Consultazione',     'consultazione',     'consultazione',     'Biblioteche, dizionari, enciclopedie e istruzione.', 4),
  (5,  NULL, 'Casa',              'casa',              'casa',              'Cucina, giardinaggio, famiglia e fai da te.', 5),
  (6,  NULL, 'Giochi',            'giochi',            'giochi',            'Videogiochi, giochi da tavolo, di ruolo e online.', 6),
  (7,  NULL, 'Notizie',           'notizie',           'notizie',           'Quotidiani, riviste, meteo e attualità.', 7),
  (8,  NULL, 'Ragazzi e giovani', 'ragazzi-e-giovani', 'ragazzi-e-giovani', 'Contenuti per bambini, ragazzi e scuola.', 8),
  (9,  NULL, 'Regionale',         'regionale',         'regionale',         'Siti per area geografica e paese.', 9),
  (10, NULL, 'Salute',            'salute',            'salute',            'Medicina, alimentazione, fitness e benessere.', 10),
  (11, NULL, 'Scienza',           'scienza',           'scienza',           'Fisica, biologia, matematica, astronomia e chimica.', 11),
  (12, NULL, 'Shopping',          'shopping',          'shopping',          'Negozi online e acquisti per categoria.', 12),
  (13, NULL, 'Società',           'societa',           'societa',           'Politica, religione, storia e filosofia.', 13),
  (14, NULL, 'Sport',             'sport',             'sport',             'Discipline sportive, squadre e competizioni.', 14),
  (15, NULL, 'Tempo libero',      'tempo-libero',      'tempo-libero',      'Viaggi, hobby, cucina, umorismo e outdoor.', 15),

  -- Affari (1)
  (16, 1, 'Lavoro',          'lavoro',          'affari/lavoro',          NULL, 1),
  (17, 1, 'Finanza',         'finanza',         'affari/finanza',         NULL, 2),
  (18, 1, 'Marketing',       'marketing',       'affari/marketing',       NULL, 3),
  (19, 1, 'Piccole imprese', 'piccole-imprese', 'affari/piccole-imprese', NULL, 4),

  -- Arte (2)
  (20, 2, 'Cinema',      'cinema',      'arte/cinema',      NULL, 1),
  (21, 2, 'Musica',      'musica',      'arte/musica',      NULL, 2),
  (22, 2, 'Letteratura', 'letteratura', 'arte/letteratura', NULL, 3),
  (23, 2, 'Fotografia',  'fotografia',  'arte/fotografia',  NULL, 4),
  (24, 2, 'Televisione', 'televisione', 'arte/televisione', NULL, 5),

  -- Computer (3)
  (25, 3, 'Software',       'software',       'computer/software',       NULL, 1),
  (26, 3, 'Hardware',       'hardware',       'computer/hardware',       NULL, 2),
  (27, 3, 'Internet',       'internet',       'computer/internet',       NULL, 3),
  (28, 3, 'Programmazione', 'programmazione', 'computer/programmazione', NULL, 4),
  (29, 3, 'Sicurezza',      'sicurezza',      'computer/sicurezza',      NULL, 5),

  -- Consultazione (4)
  (30, 4, 'Biblioteche',  'biblioteche',  'consultazione/biblioteche',  NULL, 1),
  (31, 4, 'Dizionari',    'dizionari',    'consultazione/dizionari',    NULL, 2),
  (32, 4, 'Enciclopedie', 'enciclopedie', 'consultazione/enciclopedie', NULL, 3),
  (33, 4, 'Istruzione',   'istruzione',   'consultazione/istruzione',   NULL, 4),

  -- Casa (5)
  (34, 5, 'Cucina',      'cucina',      'casa/cucina',      NULL, 1),
  (35, 5, 'Giardinaggio','giardinaggio','casa/giardinaggio',NULL, 2),
  (36, 5, 'Famiglia',    'famiglia',    'casa/famiglia',    NULL, 3),
  (37, 5, 'Fai da te',   'fai-da-te',   'casa/fai-da-te',   NULL, 4),

  -- Giochi (6)
  (38, 6, 'Videogiochi',     'videogiochi',     'giochi/videogiochi',     NULL, 1),
  (39, 6, 'Giochi da tavolo','giochi-da-tavolo','giochi/giochi-da-tavolo',NULL, 2),
  (40, 6, 'Giochi di ruolo', 'giochi-di-ruolo', 'giochi/giochi-di-ruolo', NULL, 3),
  (41, 6, 'Giochi online',   'giochi-online',   'giochi/giochi-online',   NULL, 4),

  -- Notizie (7)
  (42, 7, 'Quotidiani', 'quotidiani', 'notizie/quotidiani', NULL, 1),
  (43, 7, 'Riviste',    'riviste',    'notizie/riviste',    NULL, 2),
  (44, 7, 'Meteo',      'meteo',      'notizie/meteo',      NULL, 3),
  (45, 7, 'Attualità',  'attualita',  'notizie/attualita',  NULL, 4),

  -- Ragazzi e giovani (8)
  (46, 8, 'Scuola',       'scuola',       'ragazzi-e-giovani/scuola',       NULL, 1),
  (47, 8, 'Divertimento', 'divertimento', 'ragazzi-e-giovani/divertimento', NULL, 2),
  (48, 8, 'Fumetti',      'fumetti',      'ragazzi-e-giovani/fumetti',      NULL, 3),

  -- Regionale (9)
  (49, 9, 'Italia',  'italia',  'regionale/italia',  NULL, 1),
  (50, 9, 'Europa',  'europa',  'regionale/europa',  NULL, 2),
  (51, 9, 'America', 'america', 'regionale/america', NULL, 3),
  (52, 9, 'Asia',    'asia',    'regionale/asia',    NULL, 4),

  -- Salute (10)
  (53, 10, 'Medicina',      'medicina',      'salute/medicina',      NULL, 1),
  (54, 10, 'Alimentazione', 'alimentazione', 'salute/alimentazione', NULL, 2),
  (55, 10, 'Fitness',       'fitness',       'salute/fitness',       NULL, 3),
  (56, 10, 'Benessere',     'benessere',     'salute/benessere',     NULL, 4),

  -- Scienza (11)
  (57, 11, 'Fisica',     'fisica',     'scienza/fisica',     NULL, 1),
  (58, 11, 'Biologia',   'biologia',   'scienza/biologia',   NULL, 2),
  (59, 11, 'Matematica', 'matematica', 'scienza/matematica', NULL, 3),
  (60, 11, 'Astronomia', 'astronomia', 'scienza/astronomia', NULL, 4),
  (61, 11, 'Chimica',    'chimica',    'scienza/chimica',    NULL, 5),

  -- Shopping (12)
  (62, 12, 'Abbigliamento', 'abbigliamento', 'shopping/abbigliamento', NULL, 1),
  (63, 12, 'Elettronica',   'elettronica',   'shopping/elettronica',   NULL, 2),
  (64, 12, 'Libri',         'libri',         'shopping/libri',         NULL, 3),
  (65, 12, 'Casa',          'casa',          'shopping/casa',          NULL, 4),

  -- Società (13)
  (66, 13, 'Politica',  'politica',  'societa/politica',  NULL, 1),
  (67, 13, 'Religione', 'religione', 'societa/religione', NULL, 2),
  (68, 13, 'Storia',    'storia',    'societa/storia',    NULL, 3),
  (69, 13, 'Filosofia', 'filosofia', 'societa/filosofia', NULL, 4),

  -- Sport (14)
  (70, 14, 'Calcio',   'calcio',   'sport/calcio',   NULL, 1),
  (71, 14, 'Tennis',   'tennis',   'sport/tennis',   NULL, 2),
  (72, 14, 'Basket',   'basket',   'sport/basket',   NULL, 3),
  (73, 14, 'Ciclismo', 'ciclismo', 'sport/ciclismo', NULL, 4),
  (74, 14, 'Motori',   'motori',   'sport/motori',   NULL, 5),

  -- Tempo libero (15)
  (75, 15, 'Viaggi',   'viaggi',   'tempo-libero/viaggi',   NULL, 1),
  (76, 15, 'Cucina',   'cucina',   'tempo-libero/cucina',   NULL, 2),
  (77, 15, 'Hobby',    'hobby',    'tempo-libero/hobby',    NULL, 3),
  (78, 15, 'Umorismo', 'umorismo', 'tempo-libero/umorismo', NULL, 4),
  (79, 15, 'Outdoor',  'outdoor',  'tempo-libero/outdoor',  NULL, 5);

-- ------------------------------------------------------------
--  Categorie moderne aggiuntive: Intelligenza Artificiale e Criptovalute
--  (non presenti nell'albero storico DMOZ/Curlie).
-- ------------------------------------------------------------
INSERT INTO `categories` (`id`,`parent_id`,`name`,`slug`,`path`,`description`,`sort_order`) VALUES
  -- Intelligenza artificiale (sotto Computer = 3)
  (80, 3,  'Intelligenza artificiale', 'intelligenza-artificiale', 'computer/intelligenza-artificiale', 'IA, machine learning e sistemi intelligenti.', 6),
  (81, 80, 'Machine learning',            'machine-learning',            'computer/intelligenza-artificiale/machine-learning',            NULL, 1),
  (82, 80, 'IA generativa',               'ia-generativa',               'computer/intelligenza-artificiale/ia-generativa',               NULL, 2),
  (83, 80, 'Elaborazione del linguaggio', 'nlp',                         'computer/intelligenza-artificiale/nlp',                         NULL, 3),
  (84, 80, 'Visione artificiale',         'visione-artificiale',         'computer/intelligenza-artificiale/visione-artificiale',         NULL, 4),
  (85, 80, 'Robotica',                    'robotica',                    'computer/intelligenza-artificiale/robotica',                    NULL, 5),
  (86, 80, 'Chatbot e assistenti',        'chatbot-e-assistenti',        'computer/intelligenza-artificiale/chatbot-e-assistenti',        NULL, 6),
  (87, 80, 'Etica e sicurezza IA',        'etica-e-sicurezza-ia',        'computer/intelligenza-artificiale/etica-e-sicurezza-ia',        NULL, 7),

  -- Criptovalute (sotto Affari = 1)
  (88, 1,  'Criptovalute',            'criptovalute',        'affari/criptovalute', 'Bitcoin, blockchain, DeFi e finanza digitale.', 5),
  (89, 88, 'Bitcoin',                 'bitcoin',             'affari/criptovalute/bitcoin',              NULL, 1),
  (90, 88, 'Ethereum',                'ethereum',            'affari/criptovalute/ethereum',             NULL, 2),
  (91, 88, 'Blockchain',              'blockchain',          'affari/criptovalute/blockchain',           NULL, 3),
  (92, 88, 'DeFi',                    'defi',                'affari/criptovalute/defi',                 NULL, 4),
  (93, 88, 'NFT',                     'nft',                 'affari/criptovalute/nft',                  NULL, 5),
  (94, 88, 'Exchange e wallet',       'exchange-e-wallet',   'affari/criptovalute/exchange-e-wallet',    NULL, 6),
  (95, 88, 'Mining',                  'mining',              'affari/criptovalute/mining',               NULL, 7);

-- Alcuni link di esempio (puoi eliminarli e inserire i tuoi).
INSERT INTO `links` (`category_id`,`title`,`url`,`description`,`status`,`featured`,`featured_position`,`featured_until`) VALUES
  (28, 'Corso PHP Sponsor', 'https://esempio.it', 'Esempio di sito in evidenza (a pagamento).', 'approved', 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)),
  (28, 'PHP.net', 'https://www.php.net', 'Sito ufficiale del linguaggio PHP con documentazione completa.', 'approved', 0, 0, NULL),
  (28, 'MDN Web Docs', 'https://developer.mozilla.org', 'Documentazione per sviluppatori web di Mozilla.', 'approved', 0, 0, NULL),
  (20, 'IMDb', 'https://www.imdb.com', 'Il più grande database di film e serie TV.', 'approved', 0, 0, NULL),
  -- Intelligenza artificiale
  (82, 'Anthropic (Claude)', 'https://www.anthropic.com', 'Ricerca sull''IA e assistente Claude.', 'approved', 0, 0, NULL),
  (82, 'OpenAI', 'https://openai.com', 'Laboratorio di ricerca sull''intelligenza artificiale.', 'approved', 0, 0, NULL),
  (81, 'Hugging Face', 'https://huggingface.co', 'Piattaforma di modelli e dataset per il machine learning.', 'approved', 0, 0, NULL),
  -- Criptovalute
  (89, 'Bitcoin.org', 'https://bitcoin.org', 'Sito informativo ufficiale su Bitcoin.', 'approved', 0, 0, NULL),
  (90, 'Ethereum.org', 'https://ethereum.org', 'Sito ufficiale della community Ethereum.', 'approved', 0, 0, NULL);
