<?php
/**
 * Funzioni di utilità condivise da tutta l'applicazione.
 */

/** Escape HTML sicuro per l'output. */
function e(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL assoluto rispetto alla base configurata. */
function url(string $path = ''): string
{
    global $CONFIG;
    $base = rtrim($CONFIG['site']['base_url'] ?? '', '/');
    $path = '/' . ltrim($path, '/');
    return $base . ($path === '/' ? '/' : $path);
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
