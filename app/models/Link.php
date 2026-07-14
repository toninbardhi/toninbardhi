<?php
/**
 * Modello Link: i siti elencati dentro le categorie.
 */
class Link
{
    public static function db(): PDO
    {
        return Database::pdo();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM links WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    /** Link approvati di una categoria, con paginazione. */
    public static function approvedByCategory(int $categoryId, int $limit, int $offset): array
    {
        $st = self::db()->prepare(
            "SELECT * FROM links
             WHERE category_id = ? AND status = 'approved'
             ORDER BY sort_order, title
             LIMIT ? OFFSET ?"
        );
        $st->bindValue(1, $categoryId, PDO::PARAM_INT);
        $st->bindValue(2, $limit, PDO::PARAM_INT);
        $st->bindValue(3, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function countApproved(int $categoryId): int
    {
        $st = self::db()->prepare(
            "SELECT COUNT(*) FROM links WHERE category_id = ? AND status = 'approved'"
        );
        $st->execute([$categoryId]);
        return (int) $st->fetchColumn();
    }

    /** Tutti i link di una categoria (per l'admin, ogni stato). */
    public static function allByCategory(int $categoryId): array
    {
        $st = self::db()->prepare(
            'SELECT * FROM links WHERE category_id = ? ORDER BY status DESC, sort_order, title'
        );
        $st->execute([$categoryId]);
        return $st->fetchAll();
    }

    /** Suggerimenti in attesa di approvazione. */
    public static function pending(): array
    {
        return self::db()->query(
            "SELECT l.*, c.name AS category_name, c.path AS category_path
             FROM links l
             JOIN categories c ON c.id = l.category_id
             WHERE l.status = 'pending'
             ORDER BY l.created_at ASC"
        )->fetchAll();
    }

    public static function pendingCount(): int
    {
        return (int) self::db()
            ->query("SELECT COUNT(*) FROM links WHERE status = 'pending'")
            ->fetchColumn();
    }

    public static function create(array $d): int
    {
        $st = self::db()->prepare(
            'INSERT INTO links (category_id, title, url, description, status, submitted_by)
             VALUES (:category_id, :title, :url, :description, :status, :submitted_by)'
        );
        $st->execute([
            ':category_id' => $d['category_id'],
            ':title'       => $d['title'],
            ':url'         => $d['url'],
            ':description' => $d['description'] ?? null,
            ':status'      => $d['status'] ?? 'approved',
            ':submitted_by'=> $d['submitted_by'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $st = self::db()->prepare(
            'UPDATE links SET category_id = :category_id, title = :title, url = :url,
                    description = :description, status = :status
             WHERE id = :id'
        );
        $st->execute([
            ':category_id' => $d['category_id'],
            ':title'       => $d['title'],
            ':url'         => $d['url'],
            ':description' => $d['description'] ?? null,
            ':status'      => $d['status'] ?? 'approved',
            ':id'          => $id,
        ]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $st = self::db()->prepare('UPDATE links SET status = ? WHERE id = ?');
        $st->execute([$status, $id]);
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM links WHERE id = ?');
        $st->execute([$id]);
    }

    /**
     * Ricerca full-text sui link approvati.
     * Usa MATCH...AGAINST se possibile, con fallback su LIKE.
     */
    public static function search(string $q, int $limit, int $offset): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $like = '%' . $q . '%';
        $st = self::db()->prepare(
            "SELECT l.*, c.name AS category_name, c.path AS category_path
             FROM links l
             JOIN categories c ON c.id = l.category_id
             WHERE l.status = 'approved'
               AND (l.title LIKE :like OR l.description LIKE :like OR c.name LIKE :like)
             ORDER BY l.title
             LIMIT :lim OFFSET :off"
        );
        $st->bindValue(':like', $like);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function searchCount(string $q): int
    {
        $q = trim($q);
        if ($q === '') {
            return 0;
        }
        $like = '%' . $q . '%';
        $st = self::db()->prepare(
            "SELECT COUNT(*)
             FROM links l
             JOIN categories c ON c.id = l.category_id
             WHERE l.status = 'approved'
               AND (l.title LIKE :like OR l.description LIKE :like OR c.name LIKE :like)"
        );
        $st->bindValue(':like', $like);
        $st->execute();
        return (int) $st->fetchColumn();
    }
}
