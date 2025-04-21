<?php

namespace CryptoTrade\Services;

require_once __DIR__ . '/../../vendor/autoload.php';

use CryptoTrade\DataAccess\UserRepository;
use Dotenv\Dotenv;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{
    private static string $secret_key;
    private static string $algorithm;
    private static int $expire_time;

    public static function generateToken(array|object $user): string
    {
        self::init();

        if (!self::getField($user, 'id') || !self::getField($user, 'email') || !self::getField($user, 'role')) {
            throw new Exception("Missing required user info for token generation.");
        }

        $payload = [
            "iat" => time(),
            "exp" => time() + self::$expire_time,
            "user_id" => self::getField($user, 'id'), // kept for compatibility
            "email" => self::getField($user, 'email'),
            "role" => self::getField($user, 'role'),
            "balance" => self::getField($user, 'balance', 0),
            "two_factor_enabled" => self::getField($user, 'two_factor_enabled', false),
            "iss" => "CryptoTrade",
            "aud" => "CryptoTradeApp"
        ];

        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function init(): void
    {
        if (!isset(self::$secret_key)) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            self::$secret_key = $_ENV['JWT_SECRET'] ?? 'default_secret';
            self::$algorithm = $_ENV['JWT_ALGORITHM'] ?? 'HS256';
            self::$expire_time = (int) ($_ENV['JWT_EXPIRES_TIME'] ?? 3600);
        }
    }

    /**
     * @return array<string, mixed>
     */
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
            throw new Exception("Missing JWT token", 401);
        }

        $userData = self::getUserFromToken($token);

        if (isset($userData['error'])) {
            throw new Exception($userData['error'], 401);
        }

        return $userData;
    }

    public static function getUserFromToken(string $token): array
    {
        self::init();

        try {
            if (!self::$algorithm || !is_string(self::$algorithm)) {
                throw new Exception("JWT Algorithm is invalid or missing.");
            }

            $decoded = JWT::decode($token, new Key(self::$secret_key, self::$algorithm));

            $userRepo = new UserRepository();
            $user = $userRepo->get_by_id($decoded->user_id);

            if (!$user) {
                throw new Exception("User not found.");
            }

            return [
                "id" => self::getField($user, 'id'),
                "user_id" => self::getField($user, 'id'),
                "email" => self::getField($user, 'email'),
                "role" => self::getField($user, 'role'),
                "balance" => self::getField($user, 'balance'),
                "two_factor_enabled" => self::getField($user, 'two_factor_enabled')
            ];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    /**
     * Safe getter for both array and object access
     */
    private static function getField(array|object|null $source, string $field, mixed $default = null): mixed
    {
        if (is_array($source) && array_key_exists($field, $source)) {
            return $source[$field];
        }

        if (is_object($source) && isset($source->$field)) {
            return $source->$field;
        }

        return $default;
    }
}
