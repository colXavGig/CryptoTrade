<?php

namespace CryptoTrade\DataAccess;

class EmailTokenRepository extends Repository
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'email_tokens';
        $this->columns = ['id', 'user_id', 'token', 'type', 'created_at'];
    }

    // Get email token by token
    public function get_by_token($token)
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE token = :token');
        $query->execute(['token' => $token]);
        return $query->fetch();
    }

    // Get email token by user ID
    public function get_by_user_id($user_id)
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
        $query->execute(['user_id' => $user_id]);
        return $query->fetch();
    }

    // Delete email token by token
    public function delete_by_token($token): void
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE token = :token');
        $query->execute(['token' => $token]);
    }

    // Delete email token by user ID
    public function delete_by_user_id($user_id): void
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE user_id = :user_id');
        $query->execute(['user_id' => $user_id]);
    }

    // Delete expired email tokens
    public function delete_expired_tokens(): void
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE expires_at < NOW()');
        $query->execute();
    }
}