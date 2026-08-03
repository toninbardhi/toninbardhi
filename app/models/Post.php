<?php
/**
 * Modello Post: articoli del blog.
 */
class Post
{
    public static function db(): PDO
    {
        return Database::pdo();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM posts WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $st = self::db()->prepare(
            'SELECT p.*, c.name AS category_name, c.path AS category_path
             FROM posts p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ?'
        );
        $st->execute([$slug]);
        return $st->fetch() ?: null;
    }

    /** Tutti gli articoli (admin). */
    public static function all(): array
    {
        return self::db()->query(
            'SELECT * FROM posts ORDER BY COALESCE(published_at, created_at) DESC, id DESC'
        )->fetchAll();
    }

    /**
     * Articoli pubblicati (pubblico), con paginazione.
     * Se $tag è indicato, filtra per quel tag.
     */
    public static function published(int $limit, int $offset, ?string $tag = null): array
    {
        $sql = "SELECT p.*, c.name AS category_name, c.path AS category_path
                FROM posts p LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.status = 'published'";
        $params = [];
        if ($tag !== null && $tag !== '') {
            // Cerca il tag come parola intera nella lista separata da virgole.
            $sql .= " AND (',' || REPLACE(LOWER(p.tags), ', ', ',') || ',') LIKE ?";
            $params[] = '%,' . strtolower(trim($tag)) . ',%';
        }
        $sql .= ' ORDER BY p.published_at DESC, p.id DESC LIMIT ? OFFSET ?';
        $st = self::db()->prepare(self::concatSql($sql));
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i++, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function publishedCount(?string $tag = null): int
    {
        $sql = "SELECT COUNT(*) FROM posts p WHERE p.status = 'published'";
        $params = [];
        if ($tag !== null && $tag !== '') {
            $sql .= " AND (',' || REPLACE(LOWER(p.tags), ', ', ',') || ',') LIKE ?";
            $params[] = '%,' . strtolower(trim($tag)) . ',%';
        }
        $st = self::db()->prepare(self::concatSql($sql));
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    /**
     * Adatta l'operatore di concatenazione al driver:
     * MySQL non supporta `||`, usa CONCAT(). SQLite usa `||`.
     */
    private static function concatSql(string $sql): string
    {
        if (Database::driver() === 'mysql') {
            // Sostituisce l'espressione `(',' || REPLACE(...) || ',')` con CONCAT().
            $sql = str_replace(
                "(',' || REPLACE(LOWER(p.tags), ', ', ',') || ',')",
                "CONCAT(',', REPLACE(LOWER(p.tags), ', ', ','), ',')",
                $sql
            );
        }
        return $sql;
    }

    /** Elenco dei tag distinti (con conteggio) fra gli articoli pubblicati. */
    public static function tagCloud(): array
    {
        $rows = self::db()->query(
            "SELECT tags FROM posts WHERE status = 'published' AND tags IS NOT NULL AND tags <> ''"
        )->fetchAll();
        $counts = [];
        foreach ($rows as $r) {
            foreach (self::splitTags($r['tags']) as $t) {
                $key = mb_strtolower($t);
                if (!isset($counts[$key])) {
                    $counts[$key] = ['tag' => $t, 'count' => 0];
                }
                $counts[$key]['count']++;
            }
        }
        uasort($counts, fn($a, $b) => $b['count'] <=> $a['count']);
        return array_values($counts);
    }

    /** Spezza una stringa di tag separati da virgola in un array pulito. */
    public static function splitTags(?string $tags): array
    {
        if ($tags === null || trim($tags) === '') {
            return [];
        }
        $parts = array_map('trim', explode(',', $tags));
        return array_values(array_filter($parts, fn($t) => $t !== ''));
    }

    /** Ultimi articoli pubblicati (per la sidebar/home). */
    public static function recent(int $limit): array
    {
        $st = self::db()->prepare(
            "SELECT id, title, slug, published_at FROM posts
             WHERE status = 'published' ORDER BY published_at DESC LIMIT ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function create(array $d): int
    {
        $st = self::db()->prepare(
            'INSERT INTO posts (title, slug, excerpt, body, cover_image, tags, category_id, status, author, published_at)
             VALUES (:title, :slug, :excerpt, :body, :cover_image, :tags, :category_id, :status, :author, :published_at)'
        );
        $st->execute([
            ':title'        => $d['title'],
            ':slug'         => $d['slug'],
            ':excerpt'      => $d['excerpt'] ?? null,
            ':body'         => $d['body'] ?? null,
            ':cover_image'  => $d['cover_image'] ?? null,
            ':tags'         => $d['tags'] ?? null,
            ':category_id'  => $d['category_id'] ?? null,
            ':status'       => $d['status'] ?? 'draft',
            ':author'       => $d['author'] ?? null,
            ':published_at' => $d['published_at'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $st = self::db()->prepare(
            'UPDATE posts SET title = :title, slug = :slug, excerpt = :excerpt,
                    body = :body, cover_image = :cover_image, tags = :tags,
                    category_id = :category_id, status = :status, published_at = :published_at
             WHERE id = :id'
        );
        $st->execute([
            ':title'        => $d['title'],
            ':slug'         => $d['slug'],
            ':excerpt'      => $d['excerpt'] ?? null,
            ':body'         => $d['body'] ?? null,
            ':cover_image'  => $d['cover_image'] ?? null,
            ':tags'         => $d['tags'] ?? null,
            ':category_id'  => $d['category_id'] ?? null,
            ':status'       => $d['status'] ?? 'draft',
            ':published_at' => $d['published_at'] ?? null,
            ':id'           => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM posts WHERE id = ?');
        $st->execute([$id]);
    }

    /** Slug unico da un titolo. */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = slugify($title);
        $slug = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT COUNT(*) FROM posts WHERE slug = ?';
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            $st = self::db()->prepare($sql);
            $st->execute($params);
            if ((int) $st->fetchColumn() === 0) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }
}
