<?php

namespace Project\Library;

/**
 * Database wrapper
 */
class Database
{
    /**
     * Returns database PDO
     */
    public static function getpdo(): object
    {
        try {
            $pdo = new PDO(
                "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . "," . $_ENV['DB_USER'] . "," . $_ENV['DB_PASSWORD']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch ( PDOException $exception ) {
            throw $exception;
        }
    }
}