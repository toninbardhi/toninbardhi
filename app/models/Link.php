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

    /** Numero massimo di posizioni "in evidenza" mostrate in cima. */
    public const FEATURED_SLOTS = 5;

    /**
     * Azzera lo stato "in evidenza" dei link la cui scadenza è passata.
     * Chiamata pigra (lazy): la data odierna è calcolata in PHP così da
     * restare compatibile sia con MySQL sia con SQLite.
     */
    public static function expireFeatured(): void
    {
        $st = self::db()->prepare(
            "UPDATE links SET featured = 0
             WHERE featured = 1 AND featured_until IS NOT NULL AND featured_until < ?"
        );
        $st->execute([date('Y-m-d')]);
    }

    /**
     * Link approvati di una categoria, con paginazione.
     * Gli sponsor attivi (featured = 1) vengono mostrati per primi,
     * ordinati per posizione.
     */
    public static function approvedByCategory(int $categoryId, int $limit, int $offset): array
    {
        self::expireFeatured();
        $st = self::db()->prepare(
            "SELECT * FROM links
             WHERE category_id = ? AND status = 'approved'
             ORDER BY featured DESC, featured_position ASC, sort_order, title
             LIMIT ? OFFSET ?"
        );
        $st->bindValue(1, $categoryId, PDO::PARAM_INT);
        $st->bindValue(2, $limit, PDO::PARAM_INT);
        $st->bindValue(3, $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    /** Sponsor attivi in una categoria (per il pannello admin). */
    public static function featuredByCategory(int $categoryId): array
    {
        self::expireFeatured();
        $st = self::db()->prepare(
            "SELECT * FROM links
             WHERE category_id = ? AND featured = 1
             ORDER BY featured_position ASC, title"
        );
        $st->execute([$categoryId]);
        return $st->fetchAll();
    }

    /** Tutti gli sponsor attivi (elenco globale). */
    public static function allFeatured(): array
    {
        self::expireFeatured();
        return self::db()->query(
            "SELECT l.*, c.name AS category_name, c.path AS category_path
             FROM links l JOIN categories c ON c.id = l.category_id
             WHERE l.featured = 1
             ORDER BY c.path, l.featured_position, l.title"
        )->fetchAll();
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
        self::expireFeatured();
        $st = self::db()->prepare(
            'SELECT * FROM links WHERE category_id = ?
             ORDER BY status DESC, featured DESC, featured_position ASC, sort_order, title'
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
            'INSERT INTO links
                (category_id, title, url, description, status, submitted_by,
                 featured, featured_position, featured_until)
             VALUES
                (:category_id, :title, :url, :description, :status, :submitted_by,
                 :featured, :featured_position, :featured_until)'
        );
        $st->execute([
            ':category_id'       => $d['category_id'],
            ':title'             => $d['title'],
            ':url'               => $d['url'],
            ':description'       => $d['description'] ?? null,
            ':status'            => $d['status'] ?? 'approved',
            ':submitted_by'      => $d['submitted_by'] ?? null,
            ':featured'          => !empty($d['featured']) ? 1 : 0,
            ':featured_position' => (int) ($d['featured_position'] ?? 0),
            ':featured_until'    => $d['featured_until'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $st = self::db()->prepare(
            'UPDATE links SET category_id = :category_id, title = :title, url = :url,
                    description = :description, status = :status,
                    featured = :featured, featured_position = :featured_position,
                    featured_until = :featured_until
             WHERE id = :id'
        );
        $st->execute([
            ':category_id'       => $d['category_id'],
            ':title'             => $d['title'],
            ':url'               => $d['url'],
            ':description'       => $d['description'] ?? null,
            ':status'            => $d['status'] ?? 'approved',
            ':featured'          => !empty($d['featured']) ? 1 : 0,
            ':featured_position' => (int) ($d['featured_position'] ?? 0),
            ':featured_until'    => $d['featured_until'] ?? null,
            ':id'                => $id,
        ]);
    }

    /** Rimuove rapidamente l'evidenza da un link. */
    public static function unfeature(int $id): void
    {
        $st = self::db()->prepare(
            'UPDATE links SET featured = 0, featured_position = 0, featured_until = NULL WHERE id = ?'
        );
        $st->execute([$id]);
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
        self::expireFeatured();
        $like = '%' . $q . '%';
        $st = self::db()->prepare(
            "SELECT l.*, c.name AS category_name, c.path AS category_path
             FROM links l
             JOIN categories c ON c.id = l.category_id
             WHERE l.status = 'approved'
               AND (l.title LIKE :like OR l.description LIKE :like OR c.name LIKE :like)
             ORDER BY l.featured DESC, l.featured_position ASC, l.title
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
