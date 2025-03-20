<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Use Composer's autoloader

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JWTService {
    private static $secret_key;
    private static $algorithm;
    private static $expire_time;

    // Load environment variables
    public static function init() {
        if (!self::$secret_key) {
            // Load environment variables
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            // Assign values from .env and set defaults
            self::$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret';
            self::$algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
            self::$expire_time = $_ENV['JWT_EXPIRES_TIME'] ?? 3600;
        }
    }

    // Generate JWT Token
    public static function generateToken($user) {
        self::init(); // Ensure env variables are loaded

        $payload = [
            "iat" => time(), // Token issue time
            "exp" => time() + self::$expire_time, // Expiry time
            "user_id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role'],  // Role from new user table
            "balance" => $user['balance'],  // User balance
            "two_factor_enabled" => $user['two_factor_enabled'], // 2FA flag
        ];

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    // Verify JWT Token and return user data
    public static function verifyJWT() {
        self::init();
        $headers = getallheaders();

        if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            http_response_code(401);
            echo json_encode(["error" => "Missing Authorization header"]);
            exit;
        }

        $authHeader = $headers['Authorization'] ?? $headers['authorization'];
        $token = str_replace("Bearer ", "", $authHeader);

        $decoded = self::getUserFromToken($token);

        if (!$decoded || isset($decoded['error'])) {
            http_response_code(401);
            echo json_encode(["error" => "Invalid or expired token"]);
            exit;
        }

        return [
            "user_id" => $decoded['user_id'],
            "email" => $decoded['email'],
            "role" => $decoded['role'],
            "balance" => $decoded['balance'],
            "two_factor_enabled" => $decoded['two_factor_enabled']
        ];
    }

    // Get user data from token (utility function)
    public static function getUserFromToken($token) {
        self::init(); // Ensure env variables are loaded
        try {
            // Ensure algorithm is valid
            if (!self::$algorithm || !is_string(self::$algorithm)) {
                throw new Exception("JWT Algorithm is invalid or missing.");
            }

            // Decode the token
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));

            return (array) $decoded; // Convert object to array
        } catch (Exception $e) {
            return ["error" => $e->getMessage()]; // Return error
        }
    }
}
?>
