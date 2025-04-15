<?php
namespace CryptoTrade\Controllers;
use CryptoTrade\DataAccess\UserRepository;
use CryptoTrade\Models\EmailTokenType;
use CryptoTrade\Services\CSRFService;
use CryptoTrade\Services\Database;
use CryptoTrade\Services\EmailTokenService;
use CryptoTrade\Services\JWTService;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use CryptoTrade\Services\Auth;

require_once __DIR__ . '/../DataAccess/UserRepository.php';
require_once __DIR__ . '/../Services/Auth.php';
require_once __DIR__ . '/../Services/JWTService.php';

class UserController
{

    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();

        // Automatically check CSRF token for all POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
        }
    }

    /**
     * Get all users (Admin only)
     */
    public function getAll(): void
    {
        $this->checkAdmin(); // Only admin can access

        try {
            $users = $this->userRepository->get_all();
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Ensure user is an admin
     */
    private function checkAdmin(): void
    {
        $user = $this->checkAuthenticated();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "error" => "Access denied"]);
            exit;
        }
    }

    /**
     * Ensure user is authenticated
     */
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

    /**
     * Register user
     */
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

                $data = [
                    'email' => trim($_POST['email']),
                    'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'role' => $_POST['role'] ?? 'user',
                    'balance' => 0.00,
                    'two_factor_enabled' => $_POST['two_factor_enabled'] ?? false
                ];

                // Generate token for email confirmation
                $token = EmailTokenService::generateToken($data['email'], EmailTokenType::EMAIL_CONFIRMATION);
                EmailTokenService::sendToken($data['email'], $token, EmailTokenType::EMAIL_CONFIRMATION);

                $userId = $this->userRepository->register($data);
                echo json_encode(['success' => true, 'user_id' => $userId]);

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        echo "<script>window.location.href = '/email-verification';</script>";
    }

    /**
     * Verify email token
     */
    public function confirmEmail(): void
    {
        // call updateTwoFactorAuthentication from EmailTokenService
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['token'])) {
                    throw new Exception("Token is required.");
                }
                $token = trim($_POST['token']);
                EmailTokenService::confirmEmail($token);

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            echo json_encode(['success' => true, 'message' => "Email confirmed successfully"]);
        }
    }

    /**
     * Login user
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email']) || empty($_POST['password'])) {
                    throw new Exception("Email and password are required.");
                }

                $auth = new Auth(Database::getConnection());
                $jwt = $auth->login(trim($_POST['email']), $_POST['password']);

                if ($jwt) {
                    $decoded = JWTService::getUserFromToken($jwt);
                    echo json_encode([
                        'success' => true,
                        'token' => $jwt,
                        'role' => $decoded['role'],
                        'balance' => $decoded['balance'],
                        'two_factor_enabled' => $decoded['two_factor_enabled']
                    ]);
                } else {
                    throw new Exception("Invalid credentials.");
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        // redirect to home page JS
        echo "<script>window.location.href = '/';</script>";


    }

    /**
     * Logout user
     */
    #[NoReturn] public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();

        echo json_encode([
            'success' => true,
            'message' => "Logged out successfully"
        ]);
        exit();
    }

    /**
     * Get user by email (Authenticated users only)
     */
    public function getUserByEmail(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
            try {
                $email = trim($_GET['email']);
                $user = $this->userRepository->get_by_email($email);

                if ($user) {
                    echo json_encode(['success' => true, 'user' => $user]);
                } else {
                    throw new Exception("User not found.");
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Verify JWT and return user info
     */
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
            http_response_code(401);
            echo json_encode(["success" => false, "error" => "Unauthorized"]);
        }
    }

    /**
     * Update user (Authenticated users only)
     */
    public function update(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['id']) || empty($_POST['email'])) {
                    throw new Exception("User ID and email are required.");
                }

                $data = [
                    'id' => trim($_POST['id']),
                    'email' => trim($_POST['email']),
                    'password_hash' => isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null,
                    'role' => $_POST['role'] ?? 'user',
                    'balance' => $_POST['balance'] ?? 0.00,
                    'two_factor_enabled' => $_POST['two_factor_enabled'] ?? false
                ];

                $userId = $this->userRepository->update($data);
                echo json_encode(['success' => true, 'user_id' => $userId]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Delete user (Admin only)
     */
    public function delete(): void
    {
        $this->checkAdmin(); // Only admin can delete users

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['id'])) {
                    throw new Exception("User ID is required.");
                }

                $id = trim($_POST['id']);
                $userId = $this->userRepository->delete($id);
                echo json_encode(['success' => true, 'user_id' => $userId]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Reset password (calling EmailTokenService)
     */
    public function resetPassword(): void
    {
        // generate token for password reset
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email'])) {
                    throw new Exception("Email is required.");
                }

                $email = trim($_POST['email']);
                $user = $this->userRepository->get_by_email($email);

                if (!$user) {
                    throw new Exception("User not found.");
                }

                // TODO: Check if email service works before sending token and changing password


                $token = EmailTokenService::generateToken($user['id'], EmailTokenType::PASSWORD_RESET);
                EmailTokenService::sendToken($email, $token, EmailTokenType::PASSWORD_RESET);
                EmailTokenService::resetPassword($token);

                echo json_encode(['success' => true, 'message' => "Password reset token sent to email."]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }

    }

    /**
     * Resend email token (for email validation only.)
     */
    public function resendVerificationEmail(): void
    {
        $this->checkAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (empty($_POST['email'])) {
                    throw new Exception("Email is required.");
                }

                $email = trim($_POST['email']);
                $user = $this->userRepository->get_by_email($email);

                if (!$user) {
                    throw new Exception("User not found.");
                }

                EmailTokenService::resendVerificationToken($email, EmailTokenType::EMAIL_CONFIRMATION);
                echo json_encode(['success' => true, 'message' => "Email token resent to email."]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }
}

?>
