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

        view('public/category', [
            'title'      => $category['name'],
            'category'   => $category,
            'children'   => $children,
            'links'      => $links,
            'crumbs'     => $crumbs,
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
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

    public static function suggestForm(): void
    {
        view('public/suggest', [
            'title'      => 'Suggerisci un sito',
            'categories' => Category::all(),
            'old'        => [],
        ]);
    }

    public static function suggestSubmit(): void
    {
        csrf_check();
        $categoryId = (int) input('category_id');
        $title      = input('title');
        $url        = input('url');
        $description= input('description');
        $email      = input('email');

        $errors = [];
        if ($title === '')                          $errors[] = 'Il titolo è obbligatorio.';
        if (!filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'Inserisci un URL valido (inizia con http:// o https://).';
        if (!Category::find($categoryId))           $errors[] = 'Seleziona una categoria valida.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email inserita non è valida.';
        }

        if ($errors) {
            view('public/suggest', [
                'title'      => 'Suggerisci un sito',
                'categories' => Category::all(),
                'errors'     => $errors,
                'old'        => compact('categoryId', 'title', 'url', 'description', 'email'),
            ]);
            return;
        }

        Link::create([
            'category_id'  => $categoryId,
            'title'        => $title,
            'url'          => $url,
            'description'  => $description !== '' ? $description : null,
            'status'       => 'pending',
            'submitted_by' => $email !== '' ? $email : null,
        ]);

        view('public/suggest_done', ['title' => 'Grazie!']);
    }
}
