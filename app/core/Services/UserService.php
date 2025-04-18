<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\UserRepository;
use CryptoTrade\Models\User;
use InvalidArgumentException;
use Exception;


class UserService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function getUserByEmail(string $email): ?User
    {
        $row = $this->userRepository->get_by_email($email);
        return $row ? User::fromArray($row) : null;
    }

    public function getUserById(string $id): ?User
    {
        $row = $this->userRepository->get_by_id($id);
        return $row ? User::fromArray($row) : null;
    }

    public function getAllUsers(): array
    {
        return array_map(fn($row) => User::fromArray($row), $this->userRepository->get_all_users());
    }

    public function registerUser(User $user, string $rawPassword): bool|string
    {
        if (strlen($rawPassword) < 8) {
            throw new InvalidArgumentException("Password must be at least 8 characters.");
        }

        $data = $user->toArray();
        $data['password_hash'] = password_hash($rawPassword, PASSWORD_DEFAULT);

        return $this->userRepository->insert($data);
    }


    public function loginUser(string $email, string $password): string
    {
        $userData = $this->userRepository->get_by_email($email);



        if (!$userData) {
            throw new Exception("User not found.");
        }

        if (!password_verify($password, $userData['password_hash'])) {
            throw new Exception("Invalid credentials.");
        }

        // Generate JWT
        $token = JWTService::generateToken($userData);

        // Start session and store token
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['jwt'] = $token;
        setcookie('jwt', $token, time() + 3600*24, "/");

        return $token;
    }


    public function updateUser(User $user, ?string $newPassword = null): bool
    {
        $data = $user->toArray();

        if ($newPassword !== null) {
            if (strlen($newPassword) < 8) {
                throw new InvalidArgumentException("Password must be at least 8 characters.");
            }
            $data['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        } else {
            unset($data['password_hash']); // just to be safe
        }

        return $this->userRepository->update($data);
    }


    public function deleteUser(string $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function changeUserRole(User $user, string $newRole): bool
    {
        if (!in_array($newRole, ['admin', 'user'])) {
            throw new InvalidArgumentException("Invalid role.");
        }
        $user->role = $newRole;
        return $this->updateUser($user);
    }

    public function toggleTwoFactor(User $user, bool $enabled): bool
    {
        $user->two_factor_enabled = $enabled;
        return $this->updateUser($user);
    }

    public function countUsers()
    {
        return $this->userRepository->countUsers();
    }
}
