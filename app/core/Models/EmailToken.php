<?php

namespace CryptoTrade\Models;

use DateTime;

class EmailToken implements RepoCompatibility
{
    private string $user_id;
    private int $token;
    private EmailTokenType $type;
    private DateTime $expires_at;

    public function __construct(string $user_id, int $token, EmailTokenType $type, DateTime $expires_at)
    {
        $this->user_id = $user_id;
        $this->token = $token;
        $this->type = $type;
        $this->expires_at = $expires_at;
    }

    public static function fromArray(array $array): RepoCompatibility
    {
        try {
            return new self(
                $array['user_id'],
                (int)$array['token'],
                EmailTokenType::from((string)$array['type']),
                new DateTime($array['expires_at'])
            );
        } catch (\DateMalformedStringException $e) {
            throw new \Exception("Invalid date format: " . $e->getMessage());
        }
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'token' => $this->token,
            'type' => $this->type->value,
            'expires_at' => $this->expires_at->format('Y-m-d H:i:s')
        ];
    }


    public static function getFieldNames(): array
    {
        return ['user_id', 'token', 'type', 'expires_at'];
    }

    // Getters
    public function getUserId(): string { return $this->user_id; }
    public function getToken(): int { return $this->token; }
    public function getType(): EmailTokenType { return $this->type; }
    public function getExpiresAt(): DateTime { return $this->expires_at; }
}
