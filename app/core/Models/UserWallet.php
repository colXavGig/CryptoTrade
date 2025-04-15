<?php

namespace CryptoTrade\Models;

class UserWallet implements RepoCompatibility
{
    private const FIELD_NAMES = [
        "id",
        "user_id",
        "crypto_id",
        "balance",
    ];
    public int $id;
    public int $user_id;
    public int $crypto_id;
    public float $balance;

    public function __construct(
        int $id, int $user_id, int $crypto_id, float $balance
    )
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->crypto_id = $crypto_id;
        $this->balance = $balance;
    }

    public static function fromArray($array): UserWallet
    {
        return new UserWallet(
            $array['id'],
            $array['user_id'],
            $array['crypto_id'],
            $array['balance']
        );
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'crypto_id' => $this->crypto_id,
            'balance' => $this->balance
        ];
    }
}