<?php

namespace CryptoTrade\Models;

class User implements RepoCompatibility
{
    public $id;
    public $email;
    public $role; // TODO: handle role with through inheritence
    /* User should be an abstract class with Admin and... Client
     as child ? They have different  behavior and admin doesnt need
    balance/UserWallets */
    public $balance; // TODO: store balance in UserWallets
    public $two_factor_enabled;
    public $created_at;

    private const FIELD_NAMES = [
        "id",
        "email",
        "role",
        "balance",
        "two_factor_enabled",
        "created_at",
    ];

    public function __construct($id, $email, $role, $balance, $two_factor_enabled, $created_at)
    {
        $this->id = $id;
        $this->email = $email;
        $this->role = $role;
        $this->balance = $balance;
        $this->two_factor_enabled = $two_factor_enabled;
        $this->created_at = $created_at;
    }

    public static function fromArray(array $array): User {
        assert(array_key_exists('id', $array));
        assert(array_key_exists('email', $array));
        assert(array_key_exists('role', $array));
        assert(array_key_exists('balance', $array));
        assert(array_key_exists('two_factor_enabled', $array));
        assert(array_key_exists('created_at', $array));

        return new User(
            $array['id'],
            $array['email'],
            $array['role'],
            $array['balance'],
            $array['two_factor_enabled'],
            $array['created_at']
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'balance' => $this->balance,
            'two_factor_enabled' => $this->two_factor_enabled,
            'created_at' => $this->created_at,
        ];
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }
}

