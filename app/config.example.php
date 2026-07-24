<?php
/**
 * Configurazione dell'applicazione.
 *
 * ISTRUZIONI:
 *  1. Copia questo file come  app/config.php
 *  2. Inserisci i dati del database che trovi nel pannello Netsons
 *     (Database MySQL -> nome db, utente, password, host).
 *
 * Il file config.php NON viene versionato (vedi .gitignore) perché
 * contiene le credenziali.
 */

return [
    // --- Database ---
    'db' => [
        'host'    => 'localhost',      // su Netsons di solito 'localhost'
        'name'    => 'nome_database',
        'user'    => 'utente_database',
        'pass'    => 'password_database',
        'charset' => 'utf8mb4',
    ],

    // --- Sito ---
    'site' => [
        'name'     => 'webdirectory.link',
        // URL base dell'installazione. Lascia '' se il sito è nella
        // radice del dominio, altrimenti es. '/directory'
        'base_url' => '',
        // URL assoluto del sito (usato per sitemap, canonical, Open Graph).
        // Senza slash finale.
        'url'      => 'https://webdirectory.link',
    ],

    // Numero di link per pagina nelle categorie e nella ricerca
    'per_page' => 20,

    // --- Google AdSense (banner in fondo alle pagine pubbliche) ---
    // Lascia 'client' vuoto per non mostrare alcun banner.
    'adsense' => [
        'client' => '',            // es. 'ca-pub-1234567890123456'
        'slot'   => '',            // es. '1234567890'
    ],

    // --- Antispam del form "Suggerisci un sito" ---
    'antispam' => [
        'enabled'  => true,        // honeypot + domanda matematica + controllo tempo
        'min_secs' => 3,           // invio più rapido di così = probabile bot
    ],

    // --- Descrizione automatica dal sito ---
    // Inserisci l'URL e recupera titolo/descrizione con un clic.
    'describe' => [
        // GRATIS: legge <title> e la meta description del sito. Nessun costo.
        'meta_enabled' => true,

        // OPZIONALE (a pagamento, ma pochissimo ~0,002 € a sito con Haiku):
        // se metti una chiave API Anthropic, l'AI genera una descrizione
        // in italiano quando quella del sito manca o è troppo scarna.
        'ai_enabled'   => false,
        'ai_api_key'   => '',                 // es. 'sk-ant-...'
        'ai_model'     => 'claude-haiku-4-5', // il modello più economico
    ],
];
