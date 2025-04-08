<?php
// /core/Database.php
namespace Core;

use PDO;
use PDOException;

class Database
{
    private static $pdo;

    /**
     * Connect to the database
     *
     * @return PDO
     * @throws PDOException If connection fails
     */
    public static function connect()
    {
        if (!self::$pdo) {
            try {
                $config = require __DIR__ . '/../config/database.php';
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                self::$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                // Log error and rethrow
                error_log("Database connection failed: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$pdo;
    }
}
