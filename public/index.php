<?php
/**
 * Front controller: unico punto di ingresso dell'applicazione.
 * Tutte le richieste passano da qui grazie al .htaccess.
 */

declare(strict_types=1);

// In produzione non mostrare gli errori PHP all'utente (restano nei log).
ini_set('display_errors', '0');

// Intestazioni di sicurezza di base.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Cookie di sessione irrobustiti (HttpOnly, SameSite, Secure su HTTPS).
$https = (($_SERVER['HTTPS'] ?? '') === 'on')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => $https,
]);
session_start();

define('APP_PATH', dirname(__DIR__) . '/app');
define('ROOT_PATH', dirname(__DIR__));

// --- Configurazione ---
$configFile = APP_PATH . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configurazione mancante: copia app/config.example.php in app/config.php.');
}
$CONFIG = require $configFile;

// --- Bootstrap ---
require APP_PATH . '/Database.php';
require APP_PATH . '/helpers.php';
require APP_PATH . '/Describe.php';
require APP_PATH . '/models/Category.php';
require APP_PATH . '/models/Link.php';
require APP_PATH . '/models/User.php';
require APP_PATH . '/controllers/PublicController.php';
require APP_PATH . '/controllers/AuthController.php';
require APP_PATH . '/controllers/AdminController.php';

try {
    Database::connect($CONFIG['db']);
} catch (Throwable $ex) {
    http_response_code(500);
    exit('Impossibile connettersi al database. Controlla app/config.php.');
}

/* ------------------------------------------------------------------ *
 *  Router minimale
 * ------------------------------------------------------------------ */

// Path richiesto, ripulito dalla base_url e dalla query string.
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$base = rtrim($CONFIG['site']['base_url'] ?? '', '/');
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
$uri = '/' . trim(rawurldecode($uri), '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/**
 * Definizione delle rotte. Ogni voce: [metodo, pattern regex, callable].
 * I gruppi catturati vengono passati come argomenti.
 */
$routes = [
    ['GET',  '#^/$#',                        fn()      => PublicController::home()],
    ['GET',  '#^/search$#',                  fn()      => PublicController::search()],
    ['GET',  '#^/sitemap\.xml$#',            fn()      => PublicController::sitemap()],
    ['GET',  '#^/suggest$#',                 fn()      => PublicController::suggestForm()],
    ['POST', '#^/suggest$#',                 fn()      => PublicController::suggestSubmit()],
    ['GET',  '#^/fetch-description$#',        fn()      => PublicController::fetchDescription()],
    ['GET',  '#^/(privacy|cookie|contatti)$#', fn($p)   => PublicController::page($p)],

    // Autenticazione
    ['GET',  '#^/login$#',                   fn()      => AuthController::loginForm()],
    ['POST', '#^/login$#',                   fn()      => AuthController::login()],
    ['GET',  '#^/logout$#',                  fn()      => AuthController::logout()],

    // Area amministrativa
    ['GET',  '#^/admin$#',                   fn()      => AdminController::dashboard()],
    ['GET',  '#^/admin/profile$#',           fn()      => AdminController::profile()],
    ['POST', '#^/admin/profile$#',           fn()      => AdminController::profileUpdate()],

    ['GET',  '#^/admin/categories$#',        fn()      => AdminController::categories()],
    ['GET',  '#^/admin/categories/create$#', fn()      => AdminController::categoryForm()],
    ['POST', '#^/admin/categories/create$#', fn()      => AdminController::categoryStore()],
    ['GET',  '#^/admin/categories/(\d+)/edit$#', fn($id)=> AdminController::categoryForm((int)$id)],
    ['POST', '#^/admin/categories/(\d+)/edit$#', fn($id)=> AdminController::categoryUpdate((int)$id)],
    ['POST', '#^/admin/categories/(\d+)/delete$#', fn($id)=> AdminController::categoryDelete((int)$id)],

    ['GET',  '#^/admin/links$#',             fn()      => AdminController::links()],
    ['GET',  '#^/admin/links/create$#',      fn()      => AdminController::linkForm()],
    ['POST', '#^/admin/links/create$#',      fn()      => AdminController::linkStore()],
    ['GET',  '#^/admin/links/(\d+)/edit$#',  fn($id)   => AdminController::linkForm((int)$id)],
    ['POST', '#^/admin/links/(\d+)/edit$#',  fn($id)   => AdminController::linkUpdate((int)$id)],
    ['POST', '#^/admin/links/(\d+)/delete$#',fn($id)   => AdminController::linkDelete((int)$id)],

    ['GET',  '#^/admin/featured$#',          fn()      => AdminController::featured()],
    ['POST', '#^/admin/featured/(\d+)/remove$#', fn($id)=> AdminController::featuredRemove((int)$id)],

    ['GET',  '#^/admin/suggestions$#',       fn()      => AdminController::suggestions()],
    ['POST', '#^/admin/suggestions/(\d+)/approve$#', fn($id)=> AdminController::suggestionApprove((int)$id)],
    ['POST', '#^/admin/suggestions/(\d+)/reject$#',  fn($id)=> AdminController::suggestionReject((int)$id)],

    ['GET',  '#^/admin/users$#',             fn()      => AdminController::users()],
    ['GET',  '#^/admin/users/create$#',      fn()      => AdminController::userForm()],
    ['POST', '#^/admin/users/create$#',      fn()      => AdminController::userStore()],
    ['GET',  '#^/admin/users/(\d+)/edit$#',  fn($id)   => AdminController::userForm((int)$id)],
    ['POST', '#^/admin/users/(\d+)/edit$#',  fn($id)   => AdminController::userUpdate((int)$id)],
    ['POST', '#^/admin/users/(\d+)/delete$#',fn($id)   => AdminController::userDelete((int)$id)],
];

foreach ($routes as [$rMethod, $pattern, $handler]) {
    if ($rMethod === $method && preg_match($pattern, $uri, $m)) {
        array_shift($m);
        $handler(...$m);
        exit;
    }
}

// Nessuna rotta statica: proviamo come percorso di categoria (es. /arte/cinema).
if ($method === 'GET' && $uri !== '/') {
    if (PublicController::category(trim($uri, '/'))) {
        exit;
    }
}

// 404
http_response_code(404);
view('errors/404', ['title' => 'Pagina non trovata']);
