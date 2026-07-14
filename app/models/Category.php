<?php
/**
 * Modello Categoria: gestione dell'albero gerarchico.
 */
class Category
{
    public static function db(): PDO
    {
        return Database::pdo();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM categories WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findByPath(string $path): ?array
    {
        $st = self::db()->prepare('SELECT * FROM categories WHERE path = ?');
        $st->execute([trim($path, '/')]);
        return $st->fetch() ?: null;
    }

    /** Categorie di primo livello. */
    public static function roots(): array
    {
        $st = self::db()->query(
            'SELECT * FROM categories WHERE parent_id IS NULL
             ORDER BY sort_order, name'
        );
        return $st->fetchAll();
    }

    /** Sottocategorie dirette. */
    public static function children(?int $parentId): array
    {
        if ($parentId === null) {
            return self::roots();
        }
        $st = self::db()->prepare(
            'SELECT * FROM categories WHERE parent_id = ?
             ORDER BY sort_order, name'
        );
        $st->execute([$parentId]);
        return $st->fetchAll();
    }

    /** Tutte le categorie (per elenchi/admin). */
    public static function all(): array
    {
        return self::db()->query(
            'SELECT * FROM categories ORDER BY path'
        )->fetchAll();
    }

    /** Catena di antenati (dalla radice alla categoria stessa). */
    public static function breadcrumb(array $category): array
    {
        $chain = [$category];
        $current = $category;
        while ($current['parent_id'] !== null) {
            $current = self::find((int) $current['parent_id']);
            if (!$current) {
                break;
            }
            array_unshift($chain, $current);
        }
        return $chain;
    }

    /** Conta i link approvati in una categoria. */
    public static function linkCount(int $categoryId): int
    {
        $st = self::db()->prepare(
            "SELECT COUNT(*) FROM links WHERE category_id = ? AND status = 'approved'"
        );
        $st->execute([$categoryId]);
        return (int) $st->fetchColumn();
    }

    /** Calcola lo slug unico e il path completo, poi inserisce. */
    public static function create(string $name, ?int $parentId, ?string $description): int
    {
        $slug = self::uniqueSlug(slugify($name), $parentId);
        $path = self::buildPath($parentId, $slug);
        $st = self::db()->prepare(
            'INSERT INTO categories (parent_id, name, slug, path, description)
             VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([$parentId, $name, $slug, $path, $description]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, string $name, ?int $parentId, ?string $description): void
    {
        $cat = self::find($id);
        if (!$cat) {
            return;
        }
        // Evita di rendere la categoria figlia di sé stessa o di un discendente.
        if ($parentId !== null && self::isDescendant($id, $parentId)) {
            $parentId = $cat['parent_id'] !== null ? (int) $cat['parent_id'] : null;
        }
        $slug = self::uniqueSlug(slugify($name), $parentId, $id);
        $path = self::buildPath($parentId, $slug);

        $st = self::db()->prepare(
            'UPDATE categories SET name = ?, parent_id = ?, slug = ?, path = ?, description = ?
             WHERE id = ?'
        );
        $st->execute([$name, $parentId, $slug, $path, $description, $id]);

        // Aggiorna il path di tutti i discendenti.
        self::rebuildDescendantPaths($id);
    }

    public static function delete(int $id): void
    {
        // ON DELETE CASCADE elimina anche sottocategorie e link.
        $st = self::db()->prepare('DELETE FROM categories WHERE id = ?');
        $st->execute([$id]);
    }

    /* --------------------- helper interni --------------------- */

    private static function buildPath(?int $parentId, string $slug): string
    {
        if ($parentId === null) {
            return $slug;
        }
        $parent = self::find($parentId);
        return $parent ? $parent['path'] . '/' . $slug : $slug;
    }

    private static function uniqueSlug(string $base, ?int $parentId, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;
        while (true) {
            $sql = 'SELECT COUNT(*) FROM categories WHERE slug = ? AND '
                 . ($parentId === null ? 'parent_id IS NULL' : 'parent_id = ?');
            $params = [$slug];
            if ($parentId !== null) {
                $params[] = $parentId;
            }
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

    /** Ricostruisce ricorsivamente i path dei discendenti. */
    private static function rebuildDescendantPaths(int $parentId): void
    {
        foreach (self::children($parentId) as $child) {
            $path = self::buildPath($parentId, $child['slug']);
            $st = self::db()->prepare('UPDATE categories SET path = ? WHERE id = ?');
            $st->execute([$path, $child['id']]);
            self::rebuildDescendantPaths((int) $child['id']);
        }
    }

    /** true se $possibleDescendant è discendente (o uguale) di $id. */
    private static function isDescendant(int $id, int $possibleDescendant): bool
    {
        if ($id === $possibleDescendant) {
            return true;
        }
        $current = self::find($possibleDescendant);
        while ($current && $current['parent_id'] !== null) {
            if ((int) $current['parent_id'] === $id) {
                return true;
            }
            $current = self::find((int) $current['parent_id']);
        }
        return false;
    }
}
