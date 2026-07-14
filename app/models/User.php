<?php
/**
 * Modello Utente: admin ed editor.
 */
class User
{
    public static function db(): PDO
    {
        return Database::pdo();
    }

    public static function find(int $id): ?array
    {
        $st = self::db()->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $st = self::db()->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([strtolower(trim($email))]);
        return $st->fetch() ?: null;
    }

    public static function all(): array
    {
        return self::db()->query('SELECT * FROM users ORDER BY name')->fetchAll();
    }

    public static function create(string $name, string $email, string $password, string $role): int
    {
        $st = self::db()->prepare(
            'INSERT INTO users (name, email, password_hash, role)
             VALUES (?, ?, ?, ?)'
        );
        $st->execute([
            $name,
            strtolower(trim($email)),
            password_hash($password, PASSWORD_DEFAULT),
            $role,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, string $name, string $email, string $role, ?string $password): void
    {
        if ($password !== null && $password !== '') {
            $st = self::db()->prepare(
                'UPDATE users SET name = ?, email = ?, role = ?, password_hash = ? WHERE id = ?'
            );
            $st->execute([$name, strtolower(trim($email)), $role,
                password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $st = self::db()->prepare(
                'UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?'
            );
            $st->execute([$name, strtolower(trim($email)), $role, $id]);
        }
    }

    public static function delete(int $id): void
    {
        $st = self::db()->prepare('DELETE FROM users WHERE id = ?');
        $st->execute([$id]);
    }

    public static function verify(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if ($user && (int) $user['active'] === 1 && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return null;
    }
}
