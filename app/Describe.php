<?php
/**
 * Recupero automatico di titolo e descrizione da un URL.
 *
 *  - meta():  legge <title> e la meta/OG description del sito (GRATIS)
 *  - ai():    genera una descrizione in italiano con Claude (opzionale, a pagamento)
 *
 * Include una protezione anti-SSRF: rifiuta indirizzi locali/privati.
 */
class Describe
{
    /** Controlla che l'URL sia http/https e non punti a una rete privata. */
    public static function safeUrl(string $url): bool
    {
        $p = parse_url($url);
        if (!$p || empty($p['scheme']) || empty($p['host'])) {
            return false;
        }
        if (!in_array(strtolower($p['scheme']), ['http', 'https'], true)) {
            return false;
        }
        $host = $p['host'];
        // Risolvi l'host e verifica che non sia un IP privato/riservato/loopback.
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false; // 10.x, 192.168.x, 127.x, ecc.
        }
        return true;
    }

    /** Scarica la pagina (con limiti di tempo e dimensione). */
    private static function fetch(string $url): ?string
    {
        if (!self::safeUrl($url)) {
            return null;
        }
        $protos = defined('CURLPROTO_HTTP') ? (CURLPROTO_HTTP | CURLPROTO_HTTPS) : null;
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT      => 'WebDirectoryBot/1.0 (+https://webdirectory.link)',
            CURLOPT_SSL_VERIFYPEER => true,
            // Scarica al massimo ~400 KB: interrompe il download oltre quella soglia.
            CURLOPT_BUFFERSIZE     => 16384,
            CURLOPT_NOPROGRESS     => false,
            CURLOPT_PROGRESSFUNCTION => function ($ch, $dltotal, $dlnow) {
                return $dlnow > 400000 ? 1 : 0;
            },
        ];
        // Consenti solo http/https (blocca file://, gopher://, ecc.).
        if ($protos !== null) {
            $opts[CURLOPT_PROTOCOLS]       = $protos;
            $opts[CURLOPT_REDIR_PROTOCOLS] = $protos;
        }
        curl_setopt_array($ch, $opts);
        $html = curl_exec($ch);
        // Anti-SSRF via redirect: se l'URL finale punta a una rete privata, scarta.
        $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if (is_string($effective) && $effective !== '' && !self::safeUrl($effective)) {
            return null;
        }
        return is_string($html) && $html !== '' ? $html : null;
    }

    /**
     * Estrae titolo e descrizione dai meta tag del sito.
     * Ritorna ['title' => ..., 'description' => ...] (valori eventualmente vuoti).
     */
    public static function meta(string $url): array
    {
        $out = ['title' => '', 'description' => '', 'image' => ''];
        $html = self::fetch($url);
        if ($html === null) {
            return $out;
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $xp = new DOMXPath($doc);

        // Titolo: og:title, poi <title>
        $title = self::firstContent($xp, [
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content',
            '//title',
        ]);
        // Descrizione: meta description, poi og:description
        $desc = self::firstContent($xp, [
            '//meta[@name="description"]/@content',
            '//meta[@property="og:description"]/@content',
            '//meta[@name="twitter:description"]/@content',
        ]);

        // Immagine di anteprima (og:image / twitter:image).
        $image = self::firstContent($xp, [
            '//meta[@property="og:image"]/@content',
            '//meta[@name="twitter:image"]/@content',
            '//meta[@property="og:image:url"]/@content',
        ]);
        // Risolvi eventuali URL relativi rispetto alla pagina.
        if ($image !== '' && !preg_match('#^https?://#i', $image)) {
            $base = parse_url($url);
            if (!empty($base['scheme']) && !empty($base['host'])) {
                $root = $base['scheme'] . '://' . $base['host'];
                $image = $image[0] === '/' ? $root . $image : $root . '/' . ltrim($image, '/');
            }
        }

        $out['title'] = self::clean($title, 200);
        $out['description'] = self::clean($desc, 1000);
        $out['image'] = filter_var($image, FILTER_VALIDATE_URL) ? $image : '';
        return $out;
    }

    /**
     * Genera una descrizione in italiano con Claude a partire da titolo/URL/testo.
     * Ritorna la descrizione oppure '' se l'AI non è configurata o fallisce.
     */
    public static function ai(array $cfg, string $title, string $url, string $hint = ''): string
    {
        if (empty($cfg['ai_enabled']) || empty($cfg['ai_api_key'])) {
            return '';
        }
        $prompt = "Scrivi una descrizione in italiano, chiara e neutra, di 1-2 frasi "
                . "(massimo 250 caratteri) per questa voce di una directory di siti web. "
                . "Niente virgolette, niente frasi promozionali, solo di cosa si occupa il sito.\n\n"
                . "Titolo: {$title}\nURL: {$url}\n"
                . ($hint !== '' ? "Info dal sito: {$hint}\n" : '');

        $payload = json_encode([
            'model'      => $cfg['ai_model'] ?? 'claude-haiku-4-5',
            'max_tokens' => 300,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'content-type: application/json',
                'x-api-key: ' . $cfg['ai_api_key'],
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res === false || $code !== 200) {
            return '';
        }
        $data = json_decode($res, true);
        $text = $data['content'][0]['text'] ?? '';
        return self::clean($text, 1000);
    }

    /**
     * Genera una bozza di "Spotlight" per un prodotto/sito.
     * Restituisce tre sezioni in italiano (HTML semplice, solo <p>):
     *   - presentation: cos'è (presentazione neutra)
     *   - web_view:     "come lo vede il web" (percezione generale)
     *   - ai_view:      "come lo vede l'AI" (descrizione sintetica)
     * Sempre descrittivo e neutro: niente voti, niente giudizi, nessun
     * dato inventato o affermazione negativa su persone/aziende reali.
     * Ritorna [] se l'AI non è configurata o fallisce: in tal caso
     * l'admin compila a mano.
     */
    public static function spotlight(array $cfg, string $name, string $url, string $hint = ''): array
    {
        if (empty($cfg['ai_enabled']) || empty($cfg['ai_api_key'])) {
            return [];
        }
        $prompt = "Sei un redattore che prepara una scheda di presentazione (non una recensione) "
                . "per una directory di siti web, in italiano.\n"
                . "Prepara TRE sezioni brevi, neutre e descrittive su questo soggetto. "
                . "Regole ferree: nessun voto o giudizio; niente toni promozionali; "
                . "NON inventare dati, numeri, premi o fatti; non scrivere nulla di negativo o "
                . "diffamatorio; se non hai informazioni, resta generico. Ogni sezione 2-4 frasi.\n\n"
                . "Soggetto: {$name}\nURL: {$url}\n"
                . ($hint !== '' ? "Info dal sito: {$hint}\n" : '')
                . "\nRispondi SOLO con un oggetto JSON valido con queste chiavi (testo semplice, senza HTML):\n"
                . '{"presentation":"cos\'è, in modo neutro","web_view":"come viene percepito/presentato sul web in generale","ai_view":"come un\'AI lo descriverebbe sinteticamente"}';

        $payload = json_encode([
            'model'      => $cfg['ai_model'] ?? 'claude-haiku-4-5',
            'max_tokens' => 900,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'content-type: application/json',
                'x-api-key: ' . $cfg['ai_api_key'],
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS     => $payload,
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($res === false || $code !== 200) {
            return [];
        }
        $data = json_decode($res, true);
        $text = $data['content'][0]['text'] ?? '';
        // Estrai il primo blocco JSON dalla risposta.
        if (!preg_match('/\{.*\}/s', $text, $m)) {
            return [];
        }
        $parsed = json_decode($m[0], true);
        if (!is_array($parsed)) {
            return [];
        }
        $toHtml = static function (string $s): string {
            $s = self::clean($s, 1500);
            return $s !== '' ? '<p>' . htmlspecialchars($s, ENT_QUOTES, 'UTF-8') . '</p>' : '';
        };
        return [
            'presentation' => $toHtml((string) ($parsed['presentation'] ?? '')),
            'web_view'     => $toHtml((string) ($parsed['web_view'] ?? '')),
            'ai_view'      => $toHtml((string) ($parsed['ai_view'] ?? '')),
        ];
    }

    /* ------------------------------------------------------------ */

    private static function firstContent(DOMXPath $xp, array $queries): string
    {
        foreach ($queries as $q) {
            $nodes = $xp->query($q);
            if ($nodes && $nodes->length > 0) {
                $val = trim($nodes->item(0)->nodeValue ?? '');
                if ($val !== '') {
                    return $val;
                }
            }
        }
        return '';
    }

    private static function clean(string $s, int $max): string
    {
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = trim(preg_replace('/\s+/u', ' ', $s));
        if (mb_strlen($s) > $max) {
            $s = mb_substr($s, 0, $max - 1) . '…';
        }
        return $s;
    }
}
