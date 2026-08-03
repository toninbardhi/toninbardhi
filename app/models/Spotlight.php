<?php
/**
 * Modello Spotlight: schede di presentazione di prodotti e siti.
 * NON è una recensione: nessun voto/giudizio. Mostra una
 * presentazione + "come lo vede il web" + "come lo vede l'AI".
 * L'inclusione può essere gratuita o a pagamento; il contenuto è
 * sempre dichiarato come assistito da AI.
 */
class Spotlight
{
    public static function db(): PDO
    {
        return Database::pdo();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM spotlights WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $st = self::db()->prepare(
            'SELECT s.*, c.name AS category_name, c.path AS category_path
             FROM spotlights s LEFT JOIN categories c ON c.id = s.category_id
             WHERE s.slug = ?'
        );
        $st->execute([$slug]);
        return $st->fetch() ?: null;
    }

    /** Tutte le schede (admin). */
    public static function all(): array
    {
        return self::db()->query(
            'SELECT s.*, c.name AS category_name FROM spotlights s
             LEFT JOIN categories c ON c.id = s.category_id
             ORDER BY COALESCE(s.published_at, s.created_at) DESC, s.id DESC'
        )->fetchAll();
    }

    /** Schede pubblicate, con paginazione ed eventuale filtro categoria. */
    public static function published(int $limit, int $offset, ?int $categoryId = null): array
    {
        $sql = "SELECT s.*, c.name AS category_name, c.path AS category_path
                FROM spotlights s LEFT JOIN categories c ON c.id = s.category_id
                WHERE s.status = 'published'";
        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND s.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY s.published_at DESC, s.id DESC LIMIT ? OFFSET ?';
        $st = self::db()->prepare($sql);
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_INT);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i++, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function publishedCount(?int $categoryId = null): int
    {
        $sql = "SELECT COUNT(*) FROM spotlights WHERE status = 'published'";
        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND category_id = ?';
            $params[] = $categoryId;
        }
        $st = self::db()->prepare($sql);
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    /** Ultime schede pubblicate (per sidebar/home). */
    public static function recent(int $limit): array
    {
        $st = self::db()->prepare(
            "SELECT id, title, slug, subject_name, published_at FROM spotlights
             WHERE status = 'published' ORDER BY published_at DESC LIMIT ?"
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function create(array $d): int
    {
        $st = self::db()->prepare(
            'INSERT INTO spotlights (title, slug, subject_name, subject_url, category_id,
                                     presentation, web_view, ai_view, cover_image, ai_generated,
                                     is_paid, sponsor_name, status, author, published_at)
             VALUES (:title, :slug, :subject_name, :subject_url, :category_id,
                     :presentation, :web_view, :ai_view, :cover_image, :ai_generated,
                     :is_paid, :sponsor_name, :status, :author, :published_at)'
        );
        $st->execute(self::params($d) + [':author' => $d['author'] ?? null]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $st = self::db()->prepare(
            'UPDATE spotlights SET title = :title, slug = :slug, subject_name = :subject_name,
                    subject_url = :subject_url, category_id = :category_id,
                    presentation = :presentation, web_view = :web_view, ai_view = :ai_view,
                    cover_image = :cover_image, ai_generated = :ai_generated,
                    is_paid = :is_paid, sponsor_name = :sponsor_name,
                    status = :status, published_at = :published_at
             WHERE id = :id'
        );
        $st->execute(self::params($d) + [':id' => $id]);
    }

    /** Parametri comuni a insert/update. */
    private static function params(array $d): array
    {
        return [
            ':title'        => $d['title'],
            ':slug'         => $d['slug'],
            ':subject_name' => $d['subject_name'],
            ':subject_url'  => $d['subject_url'] ?? null,
            ':category_id'  => $d['category_id'] ?? null,
            ':presentation' => $d['presentation'] ?? null,
            ':web_view'     => $d['web_view'] ?? null,
            ':ai_view'      => $d['ai_view'] ?? null,
            ':cover_image'  => $d['cover_image'] ?? null,
            ':ai_generated' => !empty($d['ai_generated']) ? 1 : 0,
            ':is_paid'      => !empty($d['is_paid']) ? 1 : 0,
            ':sponsor_name' => $d['sponsor_name'] ?? null,
            ':status'       => $d['status'] ?? 'draft',
            ':published_at' => $d['published_at'] ?? null,
        ];
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM spotlights WHERE id = ?');
        $st->execute([$id]);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = slugify($title);
        $slug = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT COUNT(*) FROM spotlights WHERE slug = ?';
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
