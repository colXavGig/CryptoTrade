<?php
namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\User;
use InvalidArgumentException;

class UserRepository extends Repository
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->columns = User::getFieldNames();
    }

    public function get_by_email(string $email): ?array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format.");
        }

        return $this->get_by(['email' => $email]);
    }


    public function get_all_users(): array
    {
        return $this->get_all();
    }

    public function get_user_by_id(int $id): User
    {
        return User::fromArray($this->get_by_id($id));
    }
}
