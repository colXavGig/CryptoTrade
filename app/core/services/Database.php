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

            // Retrieve database credentials from .env
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'crypto_db';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset"; // Data Source Name
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable error exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays
                PDO::ATTR_EMULATE_PREPARES => false, // Prevent SQL injection
            ];

            try {
                self::$connection = new PDO($dsn, $username, $password, $options); // Create a new PDO instance
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
