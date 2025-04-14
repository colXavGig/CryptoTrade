<?php

namespace CryptoTrade\Models;

use DateTime;
use CryptoTrade\Models\EmailTokenType;

class EmailToken implements RepoCompatibility
{
    // define email token for two-factor authentication
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

    static function fromArray(array $array): RepoCompatibility
    {
        return new self($array['user_id'], $array['token'], $array['type'], $array['expires_at']);
    }

    static function getFieldNames(): array
    {
        return ['user_id', 'token', 'type', 'expires_at'];
    }

    function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'token' => $this->token,
            'type' => $this->type,
            'expires_at' => $this->expires_at
        ];
    }
}

