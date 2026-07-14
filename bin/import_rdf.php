<?php
/**
 * Importatore RDF stile DMOZ / Curlie.
 *
 * Legge i dump RDF dell'Open Directory e popola le tabelle `categories`
 * (e, opzionalmente, `links`). Usa XMLReader in streaming, quindi funziona
 * anche con file da diversi GB senza esaurire la memoria.
 *
 * SOLO da riga di comando (php bin/import_rdf.php ...), per evitare i timeout
 * del web su hosting condiviso.
 *
 * Esempi:
 *   # Tutte le categorie (solo struttura, niente siti)
 *   php bin/import_rdf.php --structure=dump/structure.rdf.u8
 *
 *   # Solo il ramo italiano, che diventa la radice del nostro albero
 *   php bin/import_rdf.php --structure=dump/structure.rdf.u8 \
 *        --branch=Top/World/Italiano --strip=Top/World/Italiano
 *
 *   # Struttura + siti
 *   php bin/import_rdf.php --structure=dump/structure.rdf.u8 \
 *        --content=dump/content.rdf.u8 --with-sites
 *
 * Opzioni:
 *   --structure=FILE   (obbligatorio) percorso di structure.rdf.u8
 *   --content=FILE     percorso di content.rdf.u8 (per i siti)
 *   --with-sites       importa anche i siti (richiede --content)
 *   --branch=PATH      importa solo i topic sotto questo path (default: Top)
 *   --strip=PATH       prefisso da rimuovere per ottenere la radice (default: Top)
 *   --limit=N          ferma dopo N elementi (utile per una prova)
 *   --fresh            svuota categorie e link prima di importare
 *   --help             mostra questo aiuto
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Questo script si esegue solo da riga di comando.\n");
}

$opts = getopt('', [
    'structure:', 'content:', 'with-sites', 'branch::', 'strip::',
    'limit::', 'fresh', 'help',
]);

if (isset($opts['help']) || empty($opts['structure'])) {
    fwrite(STDOUT, file_get_contents(__FILE__, false, null, 0, 1900));
    exit(isset($opts['help']) ? 0 : 1);
}

define('APP_PATH', dirname(__DIR__) . '/app');
$CONFIG = require APP_PATH . '/config.php';
require APP_PATH . '/Database.php';
require APP_PATH . '/helpers.php';   // per slugify()

Database::connect($CONFIG['db']);
$pdo = Database::pdo();

$structureFile = $opts['structure'];
$contentFile   = $opts['content']   ?? null;
$withSites     = isset($opts['with-sites']);
$branch        = rtrim($opts['branch'] ?? 'Top', '/');
$strip         = rtrim($opts['strip']  ?? 'Top', '/');
$limit         = isset($opts['limit']) ? (int) $opts['limit'] : 0;

if (!is_file($structureFile)) {
    exit("File struttura non trovato: $structureFile\n");
}
if ($withSites && (!$contentFile || !is_file($contentFile))) {
    exit("--with-sites richiede --content=FILE valido.\n");
}

/* -------------------------------------------------------------------- */

if (isset($opts['fresh'])) {
    echo "Svuoto categorie e link...\n";
    $pdo->exec('DELETE FROM links');
    $pdo->exec('DELETE FROM categories');
}

/**
 * Trasforma un id DMOZ (es. "Top/Arts/Movies") nel nostro percorso slug,
 * rimuovendo il prefisso $strip. Ritorna [pathSlug, segmenti[]] oppure null
 * se il topic è fuori dal ramo o coincide con la radice.
 */
$toPath = function (string $id) use ($branch, $strip): ?array {
    if ($branch !== '' && $id !== $branch && !str_starts_with($id . '/', $branch . '/')) {
        return null; // fuori dal ramo richiesto
    }
    // Rimuovi il prefisso da "azzerare".
    $rel = $id;
    if ($strip !== '' && ($id === $strip || str_starts_with($id . '/', $strip . '/'))) {
        $rel = ltrim(substr($id, strlen($strip)), '/');
    }
    if ($rel === '') {
        return null; // è la radice: non creiamo una categoria
    }
    $segments = explode('/', $rel);
    $slugs = array_map('slugify', $segments);
    return [implode('/', $slugs), $segments];
};

/* ---------------------- 1) STRUTTURA (categorie) --------------------- */

echo "Importo le categorie da $structureFile ...\n";

$pathToId = [];   // cache path-slug -> id categoria
$countCat = 0;

// Precarica le categorie già presenti (per idempotenza).
foreach ($pdo->query('SELECT id, path FROM categories') as $row) {
    $pathToId[$row['path']] = (int) $row['id'];
}

$insCat = $pdo->prepare(
    'INSERT INTO categories (parent_id, name, slug, path, description) VALUES (?, ?, ?, ?, ?)'
);

/** Crea (se serve) una categoria dato il suo path slug e il nome. */
$ensureCategory = function (string $pathSlug, string $name, ?string $desc)
        use (&$pathToId, $insCat): int {
    if (isset($pathToId[$pathSlug])) {
        return $pathToId[$pathSlug];
    }
    $parentId = null;
    $pos = strrpos($pathSlug, '/');
    $slug = $pathSlug;
    if ($pos !== false) {
        $parentPath = substr($pathSlug, 0, $pos);
        $slug = substr($pathSlug, $pos + 1);
        // Il genitore dovrebbe già esistere (i dump elencano prima i padri);
        // se manca, lo creiamo con un nome ricavato dallo slug.
        if (!isset($pathToId[$parentPath])) {
            $parentName = ucfirst(str_replace('-', ' ', basename($parentPath)));
            $parentId = ($GLOBALS['ensureCategory'])($parentPath, $parentName, null);
        } else {
            $parentId = $pathToId[$parentPath];
        }
    }
    $insCat->execute([$parentId, $name, $slug, $pathSlug, $desc]);
    $id = (int) $GLOBALS['pdo']->lastInsertId();
    $pathToId[$pathSlug] = $id;
    return $id;
};
$GLOBALS['ensureCategory'] = $ensureCategory;
$GLOBALS['pdo'] = $pdo;

$domDoc = new DOMDocument();
$reader = new XMLReader();
$reader->open($structureFile);
$pdo->beginTransaction();

while ($reader->read()) {
    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'Topic') {
        continue;
    }
    $id = $reader->getAttribute('r:id') ?? $reader->getAttribute('id');
    if (!$id) {
        continue;
    }
    $mapped = $toPath($id);
    if ($mapped === null) {
        continue;
    }
    [$pathSlug, $segments] = $mapped;

    // Leggi titolo/descrizione dai figli del Topic (namespace-agnostico).
    $title = (string) end($segments);
    $desc = null;
    $node = $reader->expand($domDoc);
    if ($node instanceof DOMElement) {
        foreach ($node->childNodes as $c) {
            if ($c->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            if ($c->localName === 'Title' && trim($c->textContent) !== '') {
                $title = trim($c->textContent);
            } elseif ($c->localName === 'Description' && trim($c->textContent) !== '') {
                $desc = trim($c->textContent);
            }
        }
    }

    $ensureCategory($pathSlug, $title, $desc);
    $countCat++;

    if ($countCat % 1000 === 0) {
        $pdo->commit();
        $pdo->beginTransaction();
        echo "  ... $countCat categorie\r";
    }
    if ($limit && $countCat >= $limit) {
        break;
    }
}
$pdo->commit();
$reader->close();
echo "\nCategorie importate: $countCat\n";

/* ------------------------- 2) SITI (opzionale) ----------------------- */

if ($withSites) {
    echo "Importo i siti da $contentFile ...\n";
    $insLink = $pdo->prepare(
        "INSERT INTO links (category_id, title, url, description, status)
         VALUES (?, ?, ?, ?, 'approved')"
    );

    $reader = new XMLReader();
    $reader->open($contentFile);
    $pdo->beginTransaction();
    $countLink = 0;

    while ($reader->read()) {
        if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'ExternalPage') {
            continue;
        }
        $url = $reader->getAttribute('about');
        $node = $reader->expand($domDoc);
        if (!$url || !($node instanceof DOMElement)) {
            continue;
        }
        $topic = '';
        $title = $url;
        $desc = null;
        foreach ($node->childNodes as $c) {
            if ($c->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            switch ($c->localName) {
                case 'topic':       $topic = trim($c->textContent); break;
                case 'Title':       if (trim($c->textContent) !== '') $title = trim($c->textContent); break;
                case 'Description':  $desc = trim($c->textContent); break;
            }
        }
        if ($topic === '') {
            continue;
        }
        $mapped = $toPath($topic);
        if ($mapped === null || !isset($pathToId[$mapped[0]])) {
            continue; // categoria non importata / fuori ramo
        }
        $catId = $pathToId[$mapped[0]];

        $insLink->execute([$catId, mb_substr($title, 0, 200), $url, $desc !== '' ? $desc : null]);
        $countLink++;

        if ($countLink % 1000 === 0) {
            $pdo->commit();
            $pdo->beginTransaction();
            echo "  ... $countLink siti\r";
        }
        if ($limit && $countLink >= $limit) {
            break;
        }
    }
    $pdo->commit();
    $reader->close();
    echo "\nSiti importati: $countLink\n";
}

echo "Fatto.\n";
