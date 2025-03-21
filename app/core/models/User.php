<?php

namespace models;

class User
{
    public $id;
    public $email;
    public $role;
    public $balance;
    public $two_factor_enabled;
    public $created_at;

    public function __construct($id, $email, $role, $balance, $two_factor_enabled, $created_at)
    {
        $this->id = $id;
        $this->email = $email;
        $this->role = $role;
        $this->balance = $balance;
        $this->two_factor_enabled = $two_factor_enabled;
        $this->created_at = $created_at;
    }
}

