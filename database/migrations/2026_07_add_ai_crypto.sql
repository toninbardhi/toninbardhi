-- ============================================================
--  Migrazione: categorie Intelligenza Artificiale e Criptovalute
--  Esegui SOLO se hai già importato lo schema base (categorie 1-79)
--  e vuoi aggiungere questi rami senza reimportare tutto.
--  Da phpMyAdmin: seleziona il database e importa/incolla questo file.
-- ============================================================

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

-- Qualche link di esempio (facoltativi: puoi eliminarli).
INSERT INTO `links` (`category_id`,`title`,`url`,`description`,`status`) VALUES
  (82, 'Anthropic (Claude)', 'https://www.anthropic.com', 'Ricerca sull''IA e assistente Claude.', 'approved'),
  (82, 'OpenAI', 'https://openai.com', 'Laboratorio di ricerca sull''intelligenza artificiale.', 'approved'),
  (81, 'Hugging Face', 'https://huggingface.co', 'Piattaforma di modelli e dataset per il machine learning.', 'approved'),
  (89, 'Bitcoin.org', 'https://bitcoin.org', 'Sito informativo ufficiale su Bitcoin.', 'approved'),
  (90, 'Ethereum.org', 'https://ethereum.org', 'Sito ufficiale della community Ethereum.', 'approved');
