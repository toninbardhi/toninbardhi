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
        'name'     => 'Web Directory',
        // URL base dell'installazione. Lascia '' se il sito è nella
        // radice del dominio, altrimenti es. '/directory'
        'base_url' => '',
        // URL assoluto del sito (usato per sitemap, canonical, Open Graph).
        // Senza slash finale.
        'url'      => 'https://webdirectory.link',
    ],

    // Numero di link per pagina nelle categorie e nella ricerca
    'per_page' => 20,
];
