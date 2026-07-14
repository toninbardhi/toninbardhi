<?php
/**
 * Area riservata: gestione di categorie, link, suggerimenti e utenti.
 */
class AdminController
{
    /* =============================== Dashboard =============================== */

    public static function dashboard(): void
    {
        require_login();
        $stats = [
            'categories'  => count(Category::all()),
            'links'       => (int) Database::pdo()->query("SELECT COUNT(*) FROM links WHERE status='approved'")->fetchColumn(),
            'pending'     => Link::pendingCount(),
            'users'       => count(User::all()),
        ];
        self::render('admin/dashboard', ['title' => 'Pannello', 'stats' => $stats]);
    }

    /* =============================== Categorie =============================== */

    public static function categories(): void
    {
        require_login();
        self::render('admin/categories', [
            'title'      => 'Categorie',
            'categories' => Category::all(),
        ]);
    }

    public static function categoryForm(?int $id = null): void
    {
        require_login();
        $category = $id ? Category::find($id) : null;
        if ($id && !$category) {
            self::notFound();
        }
        self::render('admin/category_form', [
            'title'      => $id ? 'Modifica categoria' : 'Nuova categoria',
            'category'   => $category,
            'categories' => Category::all(),
        ]);
    }

    public static function categoryStore(): void
    {
        require_login();
        csrf_check();
        $name = input('name');
        if ($name === '') {
            flash('error', 'Il nome della categoria è obbligatorio.');
            redirect('/admin/categories/create');
        }
        $parentId = self::nullableInt('parent_id');
        Category::create($name, $parentId, self::nullable('description'));
        flash('success', 'Categoria creata.');
        redirect('/admin/categories');
    }

    public static function categoryUpdate(int $id): void
    {
        require_login();
        csrf_check();
        if (!Category::find($id)) {
            self::notFound();
        }
        $name = input('name');
        if ($name === '') {
            flash('error', 'Il nome della categoria è obbligatorio.');
            redirect("/admin/categories/$id/edit");
        }
        Category::update($id, $name, self::nullableInt('parent_id'), self::nullable('description'));
        flash('success', 'Categoria aggiornata.');
        redirect('/admin/categories');
    }

    public static function categoryDelete(int $id): void
    {
        require_login();
        csrf_check();
        Category::delete($id);
        flash('success', 'Categoria eliminata (con eventuali sottocategorie e link).');
        redirect('/admin/categories');
    }

    /* ================================= Link ================================= */

    public static function links(): void
    {
        require_login();
        $categoryId = self::nullableInt('category_id');
        $links = [];
        if ($categoryId) {
            $links = Link::allByCategory($categoryId);
        }
        self::render('admin/links', [
            'title'      => 'Link',
            'categories' => Category::all(),
            'categoryId' => $categoryId,
            'links'      => $links,
        ]);
    }

    public static function linkForm(?int $id = null): void
    {
        require_login();
        $link = $id ? Link::find($id) : null;
        if ($id && !$link) {
            self::notFound();
        }
        self::render('admin/link_form', [
            'title'      => $id ? 'Modifica link' : 'Nuovo link',
            'link'       => $link,
            'categories' => Category::all(),
        ]);
    }

    public static function linkStore(): void
    {
        require_login();
        csrf_check();
        [$data, $errors] = self::validateLink();
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/admin/links/create');
        }
        Link::create($data);
        flash('success', 'Link creato.');
        redirect('/admin/links?category_id=' . $data['category_id']);
    }

    public static function linkUpdate(int $id): void
    {
        require_login();
        csrf_check();
        if (!Link::find($id)) {
            self::notFound();
        }
        [$data, $errors] = self::validateLink();
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect("/admin/links/$id/edit");
        }
        Link::update($id, $data);
        flash('success', 'Link aggiornato.');
        redirect('/admin/links?category_id=' . $data['category_id']);
    }

    public static function linkDelete(int $id): void
    {
        require_login();
        csrf_check();
        $link = Link::find($id);
        Link::delete($id);
        flash('success', 'Link eliminato.');
        redirect('/admin/links' . ($link ? '?category_id=' . $link['category_id'] : ''));
    }

    /* ============================= Suggerimenti ============================= */

    public static function suggestions(): void
    {
        require_login();
        self::render('admin/suggestions', [
            'title'   => 'Suggerimenti in attesa',
            'pending' => Link::pending(),
        ]);
    }

    public static function suggestionApprove(int $id): void
    {
        require_login();
        csrf_check();
        Link::setStatus($id, 'approved');
        flash('success', 'Suggerimento approvato e pubblicato.');
        redirect('/admin/suggestions');
    }

    public static function suggestionReject(int $id): void
    {
        require_login();
        csrf_check();
        Link::delete($id);
        flash('success', 'Suggerimento rifiutato ed eliminato.');
        redirect('/admin/suggestions');
    }

    /* =============================== Utenti =============================== */

    public static function users(): void
    {
        require_admin();
        self::render('admin/users', [
            'title' => 'Utenti',
            'users' => User::all(),
        ]);
    }

    public static function userForm(?int $id = null): void
    {
        require_admin();
        $user = $id ? User::find($id) : null;
        if ($id && !$user) {
            self::notFound();
        }
        self::render('admin/user_form', [
            'title' => $id ? 'Modifica utente' : 'Nuovo utente',
            'user'  => $user,
        ]);
    }

    public static function userStore(): void
    {
        require_admin();
        csrf_check();
        $name = input('name');
        $email = input('email');
        $password = input('password');
        $role = input('role') === 'admin' ? 'admin' : 'editor';

        $errors = [];
        if ($name === '')                                     $errors[] = 'Il nome è obbligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))       $errors[] = 'Email non valida.';
        if (strlen($password) < 6)                            $errors[] = 'La password deve avere almeno 6 caratteri.';
        if (!$errors && User::findByEmail($email))            $errors[] = 'Esiste già un utente con questa email.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/admin/users/create');
        }
        User::create($name, $email, $password, $role);
        flash('success', 'Utente creato.');
        redirect('/admin/users');
    }

    public static function userUpdate(int $id): void
    {
        require_admin();
        csrf_check();
        $user = User::find($id);
        if (!$user) {
            self::notFound();
        }
        $name = input('name');
        $email = input('email');
        $password = input('password'); // vuota = non cambia
        $role = input('role') === 'admin' ? 'admin' : 'editor';

        $errors = [];
        if ($name === '')                               $errors[] = 'Il nome è obbligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email non valida.';
        if ($password !== '' && strlen($password) < 6)  $errors[] = 'La password deve avere almeno 6 caratteri.';
        $other = User::findByEmail($email);
        if (!$errors && $other && (int) $other['id'] !== $id) {
            $errors[] = 'Email già usata da un altro utente.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect("/admin/users/$id/edit");
        }
        User::update($id, $name, $email, $role, $password !== '' ? $password : null);
        flash('success', 'Utente aggiornato.');
        redirect('/admin/users');
    }

    public static function userDelete(int $id): void
    {
        require_admin();
        csrf_check();
        $me = current_user();
        if ($me && (int) $me['id'] === $id) {
            flash('error', 'Non puoi eliminare il tuo stesso account.');
            redirect('/admin/users');
        }
        User::delete($id);
        flash('success', 'Utente eliminato.');
        redirect('/admin/users');
    }

    /* =============================== Helper =============================== */

    /** Valida i dati di un link dal form. Ritorna [dati, errori]. */
    private static function validateLink(): array
    {
        $categoryId = (int) input('category_id');
        $title = input('title');
        $url = input('url');
        $description = input('description');
        $status = input('status') === 'pending' ? 'pending' : 'approved';

        $errors = [];
        if ($title === '')                          $errors[] = 'Il titolo è obbligatorio.';
        if (!filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'URL non valido.';
        if (!Category::find($categoryId))           $errors[] = 'Categoria non valida.';

        return [[
            'category_id' => $categoryId,
            'title'       => $title,
            'url'         => $url,
            'description' => $description !== '' ? $description : null,
            'status'      => $status,
        ], $errors];
    }

    private static function nullable(string $key): ?string
    {
        $v = input($key);
        return $v !== '' ? $v : null;
    }

    private static function nullableInt(string $key): ?int
    {
        $v = input($key);
        return $v !== '' && ctype_digit($v) ? (int) $v : null;
    }

    private static function notFound(): void
    {
        http_response_code(404);
        exit('Elemento non trovato.');
    }

    /** Rende una view usando il layout dell'area admin. */
    private static function render(string $view, array $data): void
    {
        $data['pending_badge'] = Link::pendingCount();
        view($view, $data, 'admin_layout');
    }
}
