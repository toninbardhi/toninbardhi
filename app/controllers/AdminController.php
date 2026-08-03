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

    /* ============================= In evidenza ============================= */

    public static function featured(): void
    {
        require_login();
        self::render('admin/featured', [
            'title'    => 'Link in evidenza',
            'featured' => Link::allFeatured(),
        ]);
    }

    public static function featuredRemove(int $id): void
    {
        require_login();
        csrf_check();
        Link::unfeature($id);
        flash('success', 'Evidenza rimossa: il link è tornato normale.');
        redirect('/admin/featured');
    }

    /* =============================== Profilo =============================== */

    public static function profile(): void
    {
        require_login();
        self::render('admin/profile', [
            'title' => 'Il mio profilo',
            'user'  => User::find((int) current_user()['id']),
        ]);
    }

    public static function profileUpdate(): void
    {
        require_login();
        csrf_check();
        $id = (int) current_user()['id'];
        $user = User::find($id);

        $name    = input('name');
        $email   = input('email');
        $current = input('current_password');
        $new     = input('new_password');
        $confirm = input('confirm_password');

        $errors = [];
        if ($name === '')                                     $errors[] = 'Il nome è obbligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))       $errors[] = 'Email non valida.';
        $other = User::findByEmail($email);
        if ($other && (int) $other['id'] !== $id)             $errors[] = 'Email già usata da un altro utente.';

        // La password attuale è sempre richiesta per confermare le modifiche.
        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'La password attuale non è corretta.';
        }
        // Cambio password opzionale.
        $newPassword = null;
        if ($new !== '' || $confirm !== '') {
            if (strlen($new) < 6)      $errors[] = 'La nuova password deve avere almeno 6 caratteri.';
            if ($new !== $confirm)     $errors[] = 'Le due nuove password non coincidono.';
            $newPassword = $new;
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/admin/profile');
        }

        User::updateProfile($id, $name, $email, $newPassword);
        // Aggiorna la sessione con i nuovi dati.
        $_SESSION['user']['name']  = $name;
        $_SESSION['user']['email'] = strtolower(trim($email));
        flash('success', 'Profilo aggiornato' . ($newPassword ? ' e password modificata.' : '.'));
        redirect('/admin/profile');
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

    /* ============================= Impostazioni ============================= */

    public static function settings(): void
    {
        require_admin();
        self::render('admin/settings', [
            'title' => 'Impostazioni',
            's'     => Setting::all(),
        ]);
    }

    public static function settingsUpdate(): void
    {
        require_admin();
        csrf_check();
        foreach (['site_name', 'home_title', 'home_subtitle', 'footer_text', 'contact_email', 'owner'] as $k) {
            Setting::set($k, input($k));
        }
        flash('success', 'Impostazioni salvate.');
        redirect('/admin/settings');
    }

    /* ================================= Blog ================================= */

    public static function posts(): void
    {
        require_login();
        self::render('admin/posts', ['title' => 'Blog', 'posts' => Post::all()]);
    }

    public static function postForm(?int $id = null): void
    {
        require_login();
        $post = $id ? Post::find($id) : null;
        if ($id && !$post) {
            self::notFound();
        }
        self::render('admin/post_form', [
            'title'      => $id ? 'Modifica articolo' : 'Nuovo articolo',
            'post'       => $post,
            'categories' => Category::all(),
        ]);
    }

    public static function postStore(): void
    {
        require_login();
        csrf_check();
        [$data, $errors] = self::validatePost();
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('/admin/posts/create');
        }
        $data['slug'] = Post::uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title']);
        $data['author'] = current_user()['name'] ?? null;
        Post::create($data);
        flash('success', 'Articolo creato.');
        redirect('/admin/posts');
    }

    public static function postUpdate(int $id): void
    {
        require_login();
        csrf_check();
        if (!Post::find($id)) {
            self::notFound();
        }
        [$data, $errors] = self::validatePost();
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect("/admin/posts/$id/edit");
        }
        $data['slug'] = Post::uniqueSlug($data['slug'] !== '' ? $data['slug'] : $data['title'], $id);
        Post::update($id, $data);
        flash('success', 'Articolo aggiornato.');
        redirect('/admin/posts');
    }

    public static function postDelete(int $id): void
    {
        require_login();
        csrf_check();
        Post::delete($id);
        flash('success', 'Articolo eliminato.');
        redirect('/admin/posts');
    }

    /** Valida un articolo. Ritorna [dati, errori]. */
    private static function validatePost(): array
    {
        $title   = input('title');
        $slug    = input('slug');
        $excerpt = input('excerpt');
        $body    = $_POST['body'] ?? '';           // HTML consentito (autore fidato)
        $body    = is_string($body) ? trim($body) : '';
        $status  = input('status') === 'published' ? 'published' : 'draft';
        $cover   = input('cover_image');
        $tags    = input('tags');
        $catId   = self::nullableInt('category_id');

        $errors = [];
        if ($title === '') {
            $errors[] = 'Il titolo è obbligatorio.';
        }
        if ($cover !== '' && !filter_var($cover, FILTER_VALIDATE_URL)) {
            $errors[] = 'L\'URL dell\'immagine di copertina non è valido.';
        }
        if ($catId !== null && !Category::find($catId)) {
            $errors[] = 'Categoria collegata non valida.';
            $catId = null;
        }

        // Normalizza i tag: "a, b ,c" -> "a, b, c" (max 10, senza duplicati).
        $tagList = array_slice(
            array_values(array_unique(array_filter(
                array_map('trim', explode(',', $tags)),
                fn($t) => $t !== ''
            ))),
            0, 10
        );
        $tagsClean = $tagList ? implode(', ', $tagList) : null;

        // Data di pubblicazione: ora se pubblicato e non impostata.
        $published = null;
        if ($status === 'published') {
            $when = input('published_at');
            $published = ($when !== '' && strtotime($when))
                ? date('Y-m-d H:i:s', strtotime($when))
                : date('Y-m-d H:i:s');
        }

        return [[
            'title'        => $title,
            'slug'         => $slug,
            'excerpt'      => $excerpt !== '' ? $excerpt : null,
            'body'         => $body !== '' ? $body : null,
            'cover_image'  => $cover !== '' ? $cover : null,
            'tags'         => $tagsClean,
            'category_id'  => $catId,
            'status'       => $status,
            'published_at' => $published,
        ], $errors];
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

        // Campi "in evidenza" (sponsorizzazione a pagamento).
        $featured = input('featured') === '1';
        $position = (int) input('featured_position');
        $until    = input('featured_until');

        // Anteprima e mappa.
        $imageUrl = input('image_url');
        $address  = input('address');
        $latRaw   = input('latitude');
        $lngRaw   = input('longitude');

        $errors = [];
        if ($title === '')                          $errors[] = 'Il titolo è obbligatorio.';
        if (!filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'URL non valido.';
        if (!Category::find($categoryId))           $errors[] = 'Categoria non valida.';
        if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'URL dell\'immagine non valido.';
        }

        // Coordinate: entrambe o nessuna, con range valido.
        $lat = $lng = null;
        if ($latRaw !== '' || $lngRaw !== '') {
            if (!is_numeric($latRaw) || !is_numeric($lngRaw)) {
                $errors[] = 'Latitudine e longitudine devono essere numeri.';
            } elseif ($latRaw < -90 || $latRaw > 90 || $lngRaw < -180 || $lngRaw > 180) {
                $errors[] = 'Coordinate fuori intervallo (lat -90..90, lng -180..180).';
            } else {
                $lat = round((float) $latRaw, 7);
                $lng = round((float) $lngRaw, 7);
            }
        }

        if ($featured) {
            if ($position < 1 || $position > Link::FEATURED_SLOTS) {
                $position = Link::FEATURED_SLOTS; // in coda tra gli sponsor
            }
            if ($until === '' || !self::isValidDate($until)) {
                // Default: un anno da oggi.
                $until = date('Y-m-d', strtotime('+1 year'));
            } elseif ($until < date('Y-m-d')) {
                $errors[] = 'La data di scadenza dell\'evidenza è nel passato.';
            }
        } else {
            $position = 0;
            $until = null;
        }

        return [[
            'category_id'       => $categoryId,
            'title'             => $title,
            'url'               => $url,
            'description'       => $description !== '' ? $description : null,
            'status'            => $status,
            'featured'          => $featured ? 1 : 0,
            'featured_position' => $position,
            'featured_until'    => $until,
            'image_url'         => $imageUrl !== '' ? $imageUrl : null,
            'latitude'          => $lat,
            'longitude'         => $lng,
            'address'           => $address !== '' ? $address : null,
        ], $errors];
    }

    private static function isValidDate(string $d): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $d);
        return $dt !== false && $dt->format('Y-m-d') === $d;
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
