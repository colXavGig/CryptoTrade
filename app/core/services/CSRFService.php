<?php

class CSRFService {
    // Generate and store CSRF token in session
    public static function generateToken(): string {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a random token
        }

        return $_SESSION['csrf_token'];
    }

    // Verify CSRF token in a request (utility function)
    public static function verifyToken($token): void {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }
}
?>
