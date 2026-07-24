<?php
/**
 * Parte pubblica: home, navigazione categorie, ricerca, suggerimenti.
 */
class PublicController
{
    public static function home(): void
    {
        $roots = Category::roots();
        view('public/home', [
            'title' => null,          // usa il nome del sito
            'roots' => $roots,
        ]);
    }

    /**
     * Mostra una categoria dato il suo percorso slug (es. "arte/cinema").
     * Ritorna false se la categoria non esiste (il router mostrerà 404).
     */
    public static function category(string $path): bool
    {
        $category = Category::findByPath($path);
        if (!$category) {
            return false;
        }

        global $CONFIG;
        $perPage = (int) ($CONFIG['per_page'] ?? 20);
        $page = max(1, (int) input('page', '1'));
        $offset = ($page - 1) * $perPage;

        $children = Category::children((int) $category['id']);
        $total    = Link::countApproved((int) $category['id']);
        $links    = Link::approvedByCategory((int) $category['id'], $perPage, $offset);
        $crumbs   = Category::breadcrumb($category);

        $trail = implode(' › ', array_map(fn($c) => $c['name'], $crumbs));
        $meta = $category['description'] ?: sprintf(
            'Siti della categoria %s: %d risorse selezionate nella directory.',
            $trail, $total
        );

        view('public/category', [
            'title'           => $category['name'],
            'metaDescription' => $meta,
            'category'        => $category,
            'children'        => $children,
            'links'           => $links,
            'crumbs'          => $crumbs,
            'page'            => $page,
            'perPage'         => $perPage,
            'total'           => $total,
        ]);
        return true;
    }

    public static function search(): void
    {
        global $CONFIG;
        $q = input('q');
        $perPage = (int) ($CONFIG['per_page'] ?? 20);
        $page = max(1, (int) input('page', '1'));
        $offset = ($page - 1) * $perPage;

        $results = $q !== '' ? Link::search($q, $perPage, $offset) : [];
        $total   = $q !== '' ? Link::searchCount($q) : 0;

        view('public/search', [
            'title'   => 'Ricerca',
            'q'       => $q,
            'results' => $results,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
        ]);
    }

    /** Sitemap XML con home, tutte le categorie e i link approvati. */
    public static function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $entry = static function (string $loc, ?string $lastmod = null, string $priority = '0.5'): void {
            echo "  <url>\n    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
            if ($lastmod) {
                echo "    <lastmod>" . htmlspecialchars(substr($lastmod, 0, 10), ENT_XML1) . "</lastmod>\n";
            }
            echo "    <priority>{$priority}</priority>\n  </url>\n";
        };

        $entry(abs_url('/'), null, '1.0');

        foreach (Category::all() as $c) {
            $entry(abs_url($c['path']), $c['created_at'] ?? null, '0.7');
        }

        echo '</urlset>' . "\n";
    }

    /**
     * Endpoint JSON: dato un URL, restituisce titolo e descrizione.
     * La lettura dei meta tag è gratuita; l'AI (se configurata) viene usata
     * solo per gli utenti autenticati, per non esporre l'API a costi pubblici.
     */
    public static function fetchDescription(): void
    {
        global $CONFIG;
        header('Content-Type: application/json; charset=utf-8');

        $cfg = $CONFIG['describe'] ?? [];
        $url = input('url');

        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL) || !Describe::safeUrl($url)) {
            echo json_encode(['ok' => false, 'error' => 'URL non valido o non consentito.']);
            return;
        }

        $title = '';
        $description = '';
        $image = '';
        if (!empty($cfg['meta_enabled'])) {
            $meta = Describe::meta($url);
            $title = $meta['title'];
            $description = $meta['description'];
            $image = $meta['image'];
        }

        // AI solo se abilitata e richiedente autenticato (controllo costi).
        $usedAi = false;
        if (($description === '' || mb_strlen($description) < 40)
            && is_logged_in() && !empty($cfg['ai_enabled'])) {
            $ai = Describe::ai($cfg, $title !== '' ? $title : $url, $url, $description);
            if ($ai !== '') {
                $description = $ai;
                $usedAi = true;
            }
        }

        echo json_encode([
            'ok'          => true,
            'title'       => $title,
            'description' => $description,
            'image'       => $image,
            'used_ai'     => $usedAi,
        ]);
    }

    public static function suggestForm(): void
    {
        view('public/suggest', [
            'title'      => 'Suggerisci un sito',
            'categories' => Category::all(),
            'captcha'    => antispam_challenge(),
            'old'        => [],
        ]);
    }

    public static function suggestSubmit(): void
    {
        global $CONFIG;
        csrf_check();

        // Antispam: honeypot, tempo minimo, domanda matematica.
        $spam = antispam_errors($CONFIG['antispam'] ?? []);
        if (in_array('__spam__', $spam, true)) {
            // Bot rilevato dall'honeypot: fingiamo successo senza salvare nulla.
            view('public/suggest_done', ['title' => 'Grazie!']);
            return;
        }

        // Fino a 3 categorie: la prima è obbligatoria, le altre facoltative.
        $catInputs = [
            (int) input('category_id_1'),
            (int) input('category_id_2'),
            (int) input('category_id_3'),
        ];
        // Tieni solo gli ID validi e distinti.
        $categoryIds = [];
        foreach ($catInputs as $cid) {
            if ($cid > 0 && !in_array($cid, $categoryIds, true) && Category::find($cid)) {
                $categoryIds[] = $cid;
            }
        }

        $title      = input('title');
        $url        = input('url');
        $description= input('description');
        $email      = input('email');

        $errors = $spam;
        if ($title === '')                          $errors[] = 'Il titolo è obbligatorio.';
        if (!filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'Inserisci un URL valido (inizia con http:// o https://).';
        if (!$categoryIds)                          $errors[] = 'Seleziona almeno una categoria valida.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email inserita non è valida.';
        }

        if ($errors) {
            view('public/suggest', [
                'title'      => 'Suggerisci un sito',
                'categories' => Category::all(),
                'errors'     => $errors,
                'captcha'    => antispam_challenge(), // nuova sfida
                'old'        => [
                    'cats'        => $catInputs,
                    'title'       => $title,
                    'url'         => $url,
                    'description' => $description,
                    'email'       => $email,
                ],
            ]);
            return;
        }

        // Crea una proposta (pending) per ciascuna categoria scelta.
        foreach ($categoryIds as $cid) {
            Link::create([
                'category_id'  => $cid,
                'title'        => $title,
                'url'          => $url,
                'description'  => $description !== '' ? $description : null,
                'status'       => 'pending',
                'submitted_by' => $email !== '' ? $email : null,
            ]);
        }

        view('public/suggest_done', ['title' => 'Grazie!']);
    }
}
