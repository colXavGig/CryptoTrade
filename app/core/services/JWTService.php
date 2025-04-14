<?php
namespace CryptoTrade\Services;
require_once __DIR__ . '/../../vendor/autoload.php'; // Use Composer's autoloader

use Dotenv\Dotenv;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private static $secret_key;
    private static $algorithm;
    private static $expire_time;

    // Load environment variables

    public static function generateToken($user)
    {
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

    // Generate JWT Token

    public static function init()
    {
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

    // Verify JWT Token and return user data

    public static function verifyJWT()
    {
        self::init();
        $headers = getallheaders();

        $token = null;

        // Check for Bearer token
        if (isset($headers['Authorization']) || isset($headers['authorization'])) {
            $authHeader = $headers['Authorization'] ?? $headers['authorization'];
            $token = str_starts_with($authHeader, "Bearer ")
                ? substr($authHeader, 7)
                : $authHeader;
        }
        // Fallbacks using 'jwt' key
        elseif (!empty($_POST['jwt'])) {
            $token = $_POST['jwt'];
        } elseif (!empty($_SESSION['jwt'])) {
            $token = $_SESSION['jwt'];
        } elseif (!empty($_COOKIE['jwt'])) {
            $token = $_COOKIE['jwt'];
        }


        if (!$token) {
            echo json_encode([
                "success" => false,
                "error" => "Missing JWT token",
                "status" => 401
            ]);
            exit;
        }

        $decoded = self::getUserFromToken($token);

        if (!$decoded || isset($decoded['error'])) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid or expired token",
                "status" => 401
            ]);
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
    public static function getUserFromToken($token): array
    {
        self::init(); // Ensure env variables are loaded
        try {
            // Ensure algorithm is valid
            if (!self::$algorithm || !is_string(self::$algorithm)) {
                throw new Exception("JWT Algorithm is invalid or missing.");
            }

            // Decode the token
            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));

            return (array)$decoded; // Convert object to array
        } catch (Exception $e) {
            return ["error" => $e->getMessage()]; // Return error
        }
    }
}

?>
