<?php

namespace Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function all(string $order = 'id DESC'): array
    {
        $stmt = self::db()->query('SELECT * FROM ' . static::$table . ' ORDER BY ' . $order);
        return $stmt->fetchAll();
    }

    public static function find($id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findBy(string $column, $value): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM ' . static::$table . " WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function where(string $column, $value, string $order = 'id DESC'): array
    {
        $stmt = self::db()->prepare('SELECT * FROM ' . static::$table . " WHERE {$column} = ? ORDER BY {$order}");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);
        return (int) self::db()->lastInsertId();
    }

    public static function update($id, array $data): bool
    {
        $set = implode(',', array_map(fn($c) => "{$c} = :{$c}", array_keys($data)));
        $sql = 'UPDATE ' . static::$table . " SET {$set} WHERE " . static::$primaryKey . ' = :__id';
        $data['__id'] = $id;
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($data);
    }

    public static function delete($id): bool
    {
        $stmt = self::db()->prepare('DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?');
        return $stmt->execute([$id]);
    }

    public static function count(string $where = '1', array $params = []): int
    {
        $stmt = self::db()->prepare('SELECT COUNT(*) c FROM ' . static::$table . " WHERE {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetch()['c'];
    }

    public static function raw(string $sql, array $params = []): array
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function rawOne(string $sql, array $params = []): ?array
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function rawExecute(string $sql, array $params = []): bool
    {
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function tableExists(): bool
    {
        try {
            $stmt = self::db()->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
            $stmt->execute([static::$table]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public static function columnExists(string $column): bool
    {
        try {
            $stmt = self::db()->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
            $stmt->execute([static::$table, $column]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public static function filterColumns(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (self::columnExists($k)) $out[$k] = $v;
        }
        return $out;
    }
}
