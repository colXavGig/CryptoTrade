<?php

namespace CryptoTrade\Services;

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private static $secret_key;
    private static $algorithm;
    private static $expire_time;

    public static function generateToken($user): string
    {
        self::init();
        //TODO: solve the id not set issue

//        if (!isset($user['id'], $user['email'], $user['role'])) {
//            throw new Exception("Missing required user info for token generation.");
//        }

        $payload = [
            "iat" => time(),
            "exp" => time() + self::$expire_time,
            "user_id" => $user['id'],
            "email" => $user['email'],
            "role" => $user['role'],
            "balance" => $user['balance'],
            "two_factor_enabled" => $user['two_factor_enabled'],
            "iss" => "CryptoTrade",
            "aud" => "CryptoTradeApp"
        ];

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function init(): void
    {
        if (!self::$secret_key) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            self::$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret';
            self::$algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
            self::$expire_time = (int) ($_ENV['JWT_EXPIRES_TIME'] ?? 3600);
        }
    }

    public static function verifyJWT(): array
    {
        self::init();
        $headers = getallheaders();

        $token = null;

        if (isset($headers['Authorization']) || isset($headers['authorization'])) {
            $authHeader = $headers['Authorization'] ?? $headers['authorization'];
            $token = str_starts_with($authHeader, "Bearer ")
                ? substr($authHeader, 7)
                : $authHeader;
        } elseif (!empty($_POST['jwt'])) {
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

    public static function getUserFromToken($token): array
    {
        self::init();

        try {
            if (!self::$algorithm || !is_string(self::$algorithm)) {
                throw new Exception("JWT Algorithm is invalid or missing.");
            }

            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
