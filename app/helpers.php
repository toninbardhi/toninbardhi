<?php
/**
 * Funzioni di utilità condivise da tutta l'applicazione.
 */

/** Escape HTML sicuro per l'output. */
function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Valore di un'impostazione del sito (modificabile dal pannello). */
function setting(string $key, string $default = ''): string
{
    return Setting::get($key, $default);
}

/** URL (relativo alla root del dominio) rispetto alla base configurata. */
function url(string $path = ''): string
{
    global $CONFIG;
    $base = rtrim($CONFIG['site']['base_url'] ?? '', '/');
    $path = '/' . ltrim($path, '/');
    return $base . ($path === '/' ? '/' : $path);
}

/**
 * URL assoluto (con dominio) per sitemap, canonical e Open Graph.
 * Usa site.url se configurato, altrimenti lo deduce dalla richiesta.
 */
function abs_url(string $path = ''): string
{
    global $CONFIG;
    $root = rtrim($CONFIG['site']['url'] ?? '', '/');
    if ($root === '') {
        $scheme = (($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $root = $scheme . '://' . $host;
    }
    return $root . url($path);
}

/** Redirect e stop. */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Genera uno slug URL-safe da una stringa. */
function slugify(string $text): string
{
    $text = trim($text);
    // Traslitterazione base degli accenti se disponibile
    if (function_exists('iconv')) {
        $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($conv !== false) {
            $text = $conv;
        }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item';
}

/* ------------------------------------------------------------------ *
 *  CSRF
 * ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $stored = $_SESSION['csrf'] ?? '';
    $sent = $_POST['_csrf'] ?? '';
    if ($stored === '' || !is_string($sent) || !hash_equals($stored, $sent)) {
        http_response_code(419);
        exit('Sessione scaduta o token non valido. Torna indietro e riprova.');
    }
}

/* ------------------------------------------------------------------ *
 *  Autenticazione
 * ------------------------------------------------------------------ */

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $u = current_user();
    return $u !== null && ($u['role'] ?? '') === 'admin';
}

/** Richiede il login; reindirizza altrimenti. */
function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash']['error'] = 'Devi effettuare l\'accesso.';
        redirect('/login');
    }
}

/** Richiede il ruolo admin. */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Accesso riservato agli amministratori.');
    }
}

/* ------------------------------------------------------------------ *
 *  Messaggi flash
 * ------------------------------------------------------------------ */

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][$type] = $msg;
}

function take_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ------------------------------------------------------------------ *
 *  Antispam (form pubblici)
 * ------------------------------------------------------------------ */

/**
 * Prepara una nuova sfida antispam e restituisce la domanda matematica
 * da mostrare (es. "3 + 5"). Salva la risposta e l'ora in sessione.
 */
function antispam_challenge(): string
{
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['captcha_sum'] = $a + $b;
    $_SESSION['form_time']   = time();
    return "$a + $b";
}

/**
 * Controlla i dati antispam del POST. Ritorna un array di errori.
 * Un errore speciale '__spam__' indica un bot da ignorare silenziosamente.
 *
 * @param array $cfg configurazione 'antispam'
 */
function antispam_errors(array $cfg): array
{
    if (empty($cfg['enabled'])) {
        return [];
    }
    $errors = [];

    // 1) Honeypot: campo nascosto che gli umani non compilano.
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        return ['__spam__'];
    }

    // 2) Tempo minimo di compilazione.
    $t = (int) ($_SESSION['form_time'] ?? 0);
    $min = (int) ($cfg['min_secs'] ?? 3);
    if ($t > 0 && (time() - $t) < $min) {
        $errors[] = 'Invio troppo rapido: attendi un istante e riprova.';
    }

    // 3) Domanda matematica.
    $answer = $_POST['captcha'] ?? '';
    $expected = $_SESSION['captcha_sum'] ?? null;
    if ($expected === null || !ctype_digit((string) $answer) || (int) $answer !== (int) $expected) {
        $errors[] = 'Rispondi correttamente alla domanda di verifica.';
    }

    return $errors;
}

/* ------------------------------------------------------------------ *
 *  Anteprime (thumbnail) e mappe
 * ------------------------------------------------------------------ */

/**
 * URL dell'anteprima per un link: usa image_url se presente,
 * altrimenti la favicon del dominio (servizio gratuito DuckDuckGo).
 */
function thumb_url(array $link): string
{
    if (!empty($link['image_url'])) {
        return $link['image_url'];
    }
    $host = parse_url($link['url'] ?? '', PHP_URL_HOST);
    if (!$host) {
        return '';
    }
    return 'https://icons.duckduckgo.com/ip3/' . rawurlencode($host) . '.ico';
}

/** true se il link ha coordinate valide per la mappa. */
function has_map(array $link): bool
{
    return isset($link['latitude'], $link['longitude'])
        && $link['latitude'] !== null && $link['longitude'] !== null
        && is_numeric($link['latitude']) && is_numeric($link['longitude']);
}

/** URL dell'iframe OpenStreetMap centrato sulle coordinate. */
function osm_embed_url(float $lat, float $lng, float $delta = 0.01): string
{
    $bbox = sprintf('%F,%F,%F,%F', $lng - $delta, $lat - $delta, $lng + $delta, $lat + $delta);
    return 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($bbox)
         . '&layer=mapnik&marker=' . rawurlencode(sprintf('%F,%F', $lat, $lng));
}

/** URL della mappa OpenStreetMap a schermo intero. */
function osm_full_url(float $lat, float $lng): string
{
    return sprintf('https://www.openstreetmap.org/?mlat=%F&mlon=%F#map=16/%F/%F', $lat, $lng, $lat, $lng);
}

/* ------------------------------------------------------------------ *
 *  Rendering delle view
 * ------------------------------------------------------------------ */

/**
 * Renderizza una view dentro il layout principale.
 *
 * @param string $view  nome file dentro app/views (senza .php)
 * @param array  $data  variabili estratte nella view
 * @param string $layout layout da usare (default 'layout')
 */
function view(string $view, array $data = [], string $layout = 'layout'): void
{
    global $CONFIG;                 // reso disponibile alle view e ai layout
    extract($data, EXTR_SKIP);
    ob_start();
    require APP_PATH . '/views/' . $view . '.php';
    $content = ob_get_clean();
    require APP_PATH . '/views/partials/' . $layout . '.php';
}

/** Restituisce il valore POST/GET trimmato. */
function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

/**
 * Genera le <option> per un menu di categorie, indentate secondo la
 * profondità del path. $flat è l'elenco completo (Category::all()),
 * già ordinato per path.
 */
function category_options(array $flat, $selected = null, ?int $excludeId = null): string
{
    $html = '';
    foreach ($flat as $c) {
        if ($excludeId !== null && (int) $c['id'] === $excludeId) {
            continue; // evita di scegliere sé stessa come genitore
        }
        $depth = $c['path'] === '' ? 0 : substr_count($c['path'], '/');
        $indent = str_repeat('— ', $depth);
        $sel = ((string) $selected === (string) $c['id']) ? ' selected' : '';
        $html .= '<option value="' . (int) $c['id'] . '"' . $sel . '>'
               . $indent . e($c['name']) . '</option>';
    }
    return $html;
}
