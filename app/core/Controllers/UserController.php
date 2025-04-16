<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Models\EmailTokenType;
use CryptoTrade\Models\User;
use CryptoTrade\Services\CSRFService;
use CryptoTrade\Services\EmailTokenService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserService;
use Exception;
use JetBrains\PhpStorm\NoReturn;

class UserController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
        }
    }

    private function checkAuthenticated()
    {
        $user = JWTService::verifyJWT();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["success" => false, "error" => "Unauthorized"]);
            exit;
        }
        return $user;
    }

    private function checkAdmin(): void
    {
        $user = $this->checkAuthenticated();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "error" => "Access denied"]);
            exit;
        }
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
                    throw new Exception("Email, password, and confirmation are required.");
                }
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    throw new Exception("Passwords do not match.");
                }

                $user = new User(
                    null,
                    trim($_POST['email']),
                    $_POST['role'] ?? 'user',
                    0.00,
                    $_POST['two_factor_enabled'] ?? false,
                    null
                );

                $token = EmailTokenService::generateToken($user->email, EmailTokenType::EMAIL_CONFIRMATION);
                EmailTokenService::sendToken($user->email, $token, EmailTokenType::EMAIL_CONFIRMATION);

                $userId = $this->userService->registerUser($user, $_POST['password']);
                echo json_encode(['success' => true, 'user_id' => $userId]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        echo "<script>window.location.href = '/email-verification';</script>";
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email']) || empty($_POST['password'])) {
                    throw new Exception("Email and password are required.");
                }

                $jwt = $this->userService->loginUser(trim($_POST['email']), $_POST['password']);
                $decoded = JWTService::getUserFromToken($jwt);

                echo json_encode([
                    'success' => true,
                    'token' => $jwt,
                    'role' => $decoded['role'],
                    'balance' => $decoded['balance'],
                    'two_factor_enabled' => $decoded['two_factor_enabled']
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        echo "<script>window.location.href = '/';</script>";
    }

    #[NoReturn]
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        echo json_encode(['success' => true, 'message' => "Logged out successfully"]);
        exit();
    }

    public function getAll(): void
    {
        $this->checkAdmin();

        try {
            $users = $this->userService->getAllUsers();
            echo json_encode(['success' => true, 'users' => array_map(fn($u) => $u->toArray(), $users)]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getUserByEmail(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
            try {
                $user = $this->userService->getUserByEmail(trim($_GET['email']));
                if ($user) {
                    echo json_encode(['success' => true, 'user' => $user->toArray()]);
                } else {
                    throw new Exception("User not found.");
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    //get by id
    public function getUserById(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            try {
                $user = $this->userService->getUserById(trim($_GET['id']));
                if ($user) {
                    echo json_encode(['success' => true, 'user' => $user->toArray()]);
                } else {
                    throw new Exception("User not found.");
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function update(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['id']) || empty($_POST['email'])) {
                    throw new Exception("User ID and email are required.");
                }

                $user = new User(
                    $_POST['id'],
                    trim($_POST['email']),
                    $_POST['role'] ?? 'user',
                    $_POST['balance'] ?? 0.00,
                    $_POST['two_factor_enabled'] ?? false,
                    null
                );

                $success = $this->userService->updateUser($user, $_POST['password'] ?? null);
                echo json_encode(['success' => $success]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function delete(): void
    {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['id'])) {
                    throw new Exception("User ID is required.");
                }

                $success = $this->userService->deleteUser(trim($_POST['id']));
                echo json_encode(['success' => $success]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function verify(): void
    {
        try {
            $decoded = JWTService::verifyJWT();
            echo json_encode([
                'success' => true,
                'user_id' => $decoded['user_id'],
                'email' => $decoded['email'],
                'role' => $decoded['role'],
                'balance' => $decoded['balance'],
                'two_factor_enabled' => $decoded['two_factor_enabled']
            ]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => "Unauthorized"]);
        }
    }

    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email'])) {
                    throw new Exception("Email is required.");
                }

                $user = $this->userService->getUserByEmail(trim($_POST['email']));
                if (!$user) {
                    throw new Exception("User not found.");
                }

                $token = EmailTokenService::generateToken($user->id, EmailTokenType::PASSWORD_RESET);
                EmailTokenService::sendToken($user->email, $token, EmailTokenType::PASSWORD_RESET);
                EmailTokenService::resetPassword($token);

                echo json_encode(['success' => true, 'message' => "Password reset token sent to email."]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function resendVerificationEmail(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email'])) {
                    throw new Exception("Email is required.");
                }

                $user = $this->userService->getUserByEmail(trim($_POST['email']));
                if (!$user) {
                    throw new Exception("User not found.");
                }

                EmailTokenService::resendVerificationToken($user->email, EmailTokenType::EMAIL_CONFIRMATION);
                echo json_encode(['success' => true, 'message' => "Email token resent to email."]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function confirmEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['token'])) {
                    throw new Exception("Token is required.");
                }

                EmailTokenService::confirmEmail(trim($_POST['token']));
                echo json_encode(['success' => true, 'message' => "Email confirmed successfully"]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
}
