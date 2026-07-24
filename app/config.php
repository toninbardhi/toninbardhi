<?php
/**
 * Configurazione LOCALE (non versionata).
 * In produzione su Netsons sostituisci i valori 'db' con quelli reali.
 */

return [
    'db' => [
        'driver'  => getenv('DB_DRIVER') ?: 'mysql', // 'mysql' o 'sqlite'
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'name'    => getenv('DB_NAME') ?: 'dircms',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'site' => [
        'name'     => 'webdirectory.link',
        'base_url' => '',
        'url'      => getenv('SITE_URL') ?: 'https://webdirectory.link',
    ],
    'per_page' => 20,

    'adsense' => [
        'client' => getenv('ADSENSE_CLIENT') ?: '',
        'slot'   => getenv('ADSENSE_SLOT') ?: '',
    ],

    'antispam' => [
        'enabled'  => true,
        'min_secs' => 3,
    ],

    // Descrizione automatica dal sito (meta tag = gratis; AI = opzionale)
    'describe' => [
        'meta_enabled' => true,                          // legge titolo/descrizione dal sito
        'ai_enabled'   => (bool) (getenv('ANTHROPIC_API_KEY')), // richiede una chiave
        'ai_api_key'   => getenv('ANTHROPIC_API_KEY') ?: '',
        'ai_model'     => 'claude-haiku-4-5',            // il più economico
    ],
];
