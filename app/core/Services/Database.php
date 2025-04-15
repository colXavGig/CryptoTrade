<?php

namespace CryptoTrade\Services;
// Use environment variables to store sensitive information
require_once __DIR__ . '/../../vendor/autoload.php'; // Load Composer dependencies

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    } // Prevent instantiation

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Load environment variables
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'crypto_db';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $maxAttempts = 5;
            $waitSeconds = 5;

            while ($maxAttempts > 0) {
                try {
                    self::$connection = new PDO($dsn, $username, $password, $options);
                    break; // Success
                } catch (PDOException $e) {
                    $maxAttempts--;

                    if ($maxAttempts === 0) {
                        die("Database connection failed after multiple attempts: " . $e->getMessage());
                    }

                    echo "[DB] Connection failed, retrying in {$waitSeconds}s...\n";
                    sleep($waitSeconds);
                }
            }
        }

        return self::$connection;
    }

}
