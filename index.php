<?php
/**
 * Punto di ingresso alla RADICE del sito.
 *
 * Il vero front controller è in public/index.php: questo file lo richiama,
 * così l'applicazione funziona anche quando la document root del dominio è
 * la cartella del progetto (tipico su hosting condiviso come Netsons).
 *
 * Le URL "pulite" (es. /arte/cinema) sono gestite dal file .htaccess nella
 * radice, che inoltra le richieste a public/index.php.
 */
require __DIR__ . '/public/index.php';
