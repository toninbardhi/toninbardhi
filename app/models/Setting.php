<?php
/**
 * Impostazioni del sito (coppie chiave/valore modificabili dal pannello).
 */
class Setting
{
    private static ?array $cache = null;

    /** Carica tutte le impostazioni una volta sola. */
    private static function load(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        self::$cache = [];
        try {
            $rows = Database::pdo()->query('SELECT `key`,`value` FROM settings');
            foreach ($rows as $r) {
                self::$cache[$r['key']] = $r['value'];
            }
        } catch (Throwable $e) {
            // Tabella non ancora presente (vecchia installazione): usa i default.
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $all = self::load();
        $v = $all[$key] ?? null;
        return ($v === null || $v === '') ? $default : $v;
    }

    public static function all(): array
    {
        return self::load();
    }

    /** Salva (upsert) una impostazione, in modo portabile MySQL/SQLite. */
    public static function set(string $key, ?string $value): void
    {
        $pdo = Database::pdo();
        $up = $pdo->prepare('UPDATE settings SET `value` = ? WHERE `key` = ?');
        $up->execute([$value, $key]);
        if ($up->rowCount() === 0) {
            $ins = $pdo->prepare('INSERT INTO settings (`key`,`value`) VALUES (?, ?)');
            $ins->execute([$key, $value]);
        }
        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
    }
}
