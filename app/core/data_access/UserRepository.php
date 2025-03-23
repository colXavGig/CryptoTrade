<?php
namespace CryptoTrade\DataAccess;
use CryptoTrade\Models\User;
use CryptoTrade\Services\Auth;
use InvalidArgumentException;

class UserRepository extends Repository {
    
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users'; // Updated to match crypto_db schema
        $this->columns = User::getFieldNames(); // ['id', 'email', 'password_hash', 'role', 'balance', 'two_factor_enabled', 'created_at'];
    }

    // Get user by email
    public function get_by_email($email)
    {
        if (!$this->is_valid_email($email)) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE email = :email');
        $query->execute(['email' => $email]);
        return $query->fetch();
    }

    // Get user by ID
    public function get_by_id($id)
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    // User login
    public function login($email, $password): ?string
    {
        if (!$this->is_valid_email($email)) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        $auth = new Auth($this->db);
        return $auth->login($email, $password);
    }

    // Register a new user
    public function register($data): bool|string
    {
        if (!$this->is_valid_email($data['email'])) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        if (!$this->is_valid_password($data['password_hash'])) {
            throw new InvalidArgumentException("Password must be at least 8 characters long.");
        }

        // Hash the password before inserting
        $data['password_hash'] = password_hash($data['password_hash'], PASSWORD_DEFAULT);
        
        // Ensure role is valid
        if (!isset($data['role']) || !in_array($data['role'], ['admin', 'user'])) {
            $data['role'] = 'user'; // Default role
        }

        // Default balance and two-factor status
        $data['balance'] = isset($data['balance']) ? $data['balance'] : 0.00;
        $data['two_factor_enabled'] = isset($data['two_factor_enabled']) ? $data['two_factor_enabled'] : true; // TODO: Implement two-factor authentication

        return parent::insert($data);
    }

    // Update user details
    public function update($data): bool
    {
        if (!isset($data['id'])) {
            throw new InvalidArgumentException("Missing 'id' for update.");
        }

        if (isset($data['password_hash'])) {
            if (!$this->is_valid_password($data['password_hash'])) {
                throw new InvalidArgumentException("Password must be at least 8 characters long.");
            }

            $data['password_hash'] = password_hash($data['password_hash'], PASSWORD_DEFAULT);
        }

        return parent::update($data);
    }

    // Delete user
//    public function delete($id)
//    {
//        return parent::delete($id);
//    }

    // Get all users
//    public function get_all(): array
//    {
//        return parent::get_all();
//    }

    // Get user balance
    public function get_balance($user_id)
    {
        $query = $this->db->prepare('SELECT balance FROM ' . $this->table . ' WHERE id = :user_id');
        $query->execute(['user_id' => $user_id]);
        return $query->fetchColumn();
    }

    // Update user balance
    public function update_balance($user_id, $new_balance): bool
    {
        $query = $this->db->prepare('UPDATE ' . $this->table . ' SET balance = :balance WHERE id = :user_id');
        return $query->execute(['balance' => $new_balance, 'user_id' => $user_id]);
    }

    // Enable/Disable Two-Factor Authentication
    public function set_two_factor($user_id, $enabled): bool
    {
        $query = $this->db->prepare('UPDATE ' . $this->table . ' SET two_factor_enabled = :enabled WHERE id = :user_id');
        return $query->execute(['enabled' => $enabled, 'user_id' => $user_id]);
    }

    // Custom validation functions
    private function is_valid_email($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function is_valid_password($password): bool
    {
        return strlen($password) >= 8;
    }
}
?>
