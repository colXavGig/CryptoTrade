<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\EmailToken;
use DateTime;

class EmailTokenRepository extends Repository
{
    private static ?EmailTokenRepository $instance = null;

    protected function __construct()
    {
        $this->table = 'email_tokens';
        $this->columns = EmailToken::getFieldNames(); // We assume you define this in the model
        parent::__construct();
    }

    // Singleton access method
    public static function getInstance(): EmailTokenRepository
    {
        if (self::$instance === null) {
            self::$instance = new EmailTokenRepository();
        }
        return self::$instance;
    }

    public function getTokenById(int $id): ?EmailToken
    {
        $row = parent::get_by_id($id);
        return $row ? EmailToken::fromArray($row) : null;
    }

    public function getTokenByToken(string $token): ?EmailToken
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE token = :token');
        $query->execute(['token' => $token]);
        $result = $query->fetch();
        return $result ? EmailToken::fromArray($result) : null;
    }

    public function getTokenByUserId(string $user_id): ?EmailToken
    {
        $query = $this->db->prepare('SELECT * FROM ' . $this->table . ' WHERE user_id = :user_id');
        $query->execute(['user_id' => $user_id]);
        $result = $query->fetch();
        return $result ? EmailToken::fromArray($result) : null;
    }

    public function createToken(EmailToken $token): void
    {
        parent::insert($token->toArray());
    }

    public function updateToken(EmailToken $token): void
    {
        parent::update($token->toArray());
    }

    public function deleteByToken(string $token): void
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE token = :token');
        $query->execute(['token' => $token]);
    }

    public function deleteByUserId(string $user_id): void
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE user_id = :user_id');
        $query->execute(['user_id' => $user_id]);
    }

    public function deleteExpiredTokens(): void
    {
        $query = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE expires_at < NOW()');
        $query->execute();
    }
}
