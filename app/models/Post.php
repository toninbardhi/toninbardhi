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
        $st = self::db()->prepare('SELECT * FROM posts WHERE slug = ?');
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

    /** Articoli pubblicati (pubblico), con paginazione. */
    public static function published(int $limit, int $offset): array
    {
        $st = self::db()->prepare(
            "SELECT * FROM posts WHERE status = 'published'
             ORDER BY published_at DESC, id DESC
             LIMIT ? OFFSET ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function publishedCount(): int
    {
        return (int) self::db()
            ->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")
            ->fetchColumn();
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
            'INSERT INTO posts (title, slug, excerpt, body, status, author, published_at)
             VALUES (:title, :slug, :excerpt, :body, :status, :author, :published_at)'
        );
        $st->execute([
            ':title'        => $d['title'],
            ':slug'         => $d['slug'],
            ':excerpt'      => $d['excerpt'] ?? null,
            ':body'         => $d['body'] ?? null,
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
                    body = :body, status = :status, published_at = :published_at
             WHERE id = :id'
        );
        $st->execute([
            ':title'        => $d['title'],
            ':slug'         => $d['slug'],
            ':excerpt'      => $d['excerpt'] ?? null,
            ':body'         => $d['body'] ?? null,
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
