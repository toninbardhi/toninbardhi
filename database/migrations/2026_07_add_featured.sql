-- ============================================================
--  Migrazione: posizionamento "in evidenza" (sponsorizzato)
--  Esegui SOLO se hai già installato con una versione precedente
--  dello schema (senza le colonne featured*).
-- ============================================================

ALTER TABLE `links`
  ADD COLUMN `featured`          TINYINT(1) NOT NULL DEFAULT 0 AFTER `submitted_by`,
  ADD COLUMN `featured_position` INT NOT NULL DEFAULT 0 AFTER `featured`,
  ADD COLUMN `featured_until`    DATE NULL AFTER `featured_position`,
  ADD KEY `idx_links_featured` (`featured`,`featured_position`);
