<?php

require_once __DIR__ . '/../data_access/UserRepository.php';
require_once __DIR__ . '/../services/auth.php';
require_once __DIR__ . '/../services/jwt_service.php';

class UserController {

    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();

        // Automatically check CSRF token for all POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
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
     * Ensure user is an admin
     */
    private function checkAdmin()
    {
        $user = $this->checkAuthenticated();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["success" => false, "error" => "Access denied"]);
            exit;
        }
    }

    /**
     * Get all users (Admin only)
     */
    public function getAll()
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
     * Register user
     */
    public function register()
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
                    'two_factor_enabled' => $_POST['two_factor_enabled'] ?? true
                ];

                $userId = $this->userRepository->register($data);
                echo json_encode(['success' => true, 'user_id' => $userId]);

            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }

        echo "<script>window.location.href = '/';</script>";
    }

    /**
     * Login user
     */
    public function login()
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
    public function logout()
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
    public function getUserByEmail()
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
    public function verify()
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
    public function update()
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
    public function delete()
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
}
?>
