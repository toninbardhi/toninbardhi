-- ============================================================
--  Migrazione: anteprima (thumbnail) e mappa OpenStreetMap
--  Esegui SOLO se hai già installato una versione precedente
--  dello schema (senza queste colonne).
-- ============================================================

ALTER TABLE `links`
  ADD COLUMN `image_url` VARCHAR(2048) NULL AFTER `featured_until`,
  ADD COLUMN `latitude`  DECIMAL(10,7) NULL AFTER `image_url`,
  ADD COLUMN `longitude` DECIMAL(10,7) NULL AFTER `latitude`,
  ADD COLUMN `address`   VARCHAR(255)  NULL AFTER `longitude`;
