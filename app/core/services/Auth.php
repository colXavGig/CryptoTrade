<?php
namespace CryptoTrade\Services;
use App\Services\Database;
use CryptoTrade\Services\JWTService;
use PDO;


class Auth {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = Database::getConnection();
    }

    public function login(string $email, string $password): ?string {
        $stmt = $this->db->prepare("SELECT id, email, password_hash, role, balance, two_factor_enabled FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Generate JWT Token
            $jwt = JWTService::generateToken([
                "id" => $user['id'],
                "email" => $user['email'],
                "role" => $user['role'],
                "balance" => $user['balance'],
                "two_factor_enabled" => $user['two_factor_enabled']
            ]);

            // Ensure session is started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Store token in session
            $_SESSION['jwt'] = $jwt;

            return $jwt;
        }

        return null; // Invalid credentials
    }
}
?>
