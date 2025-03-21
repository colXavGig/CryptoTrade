<?php

namespace CryptoTrade\Models;

use DateTime;

class Transaction implements RepoCompatibility
{
    public int $id;
    public int $user_id;
    public int $crypto_id;
    public int $transaction_type;
    public float $amount;
    public float $price;
    public DateTime $created_at;

    private const FIELD_NAMES = [
        'id',
        'user_id',
        'crypto_id',
        'transaction_type',
        'amount',
        'price',
        'created_at'
    ];

    public function __construct(
        int $id, int $user_id, int $crypto_id, int $transaction_type, float $amount, float $price, DateTime $created_at
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->crypto_id = $crypto_id;
        $this->transaction_type = $transaction_type;
        $this->amount = $amount;
        $this->price = $price;
        $this->created_at = $created_at;
    }

    /**
     * @throws \DateMalformedStringException
     */
    public static function fromArray($array): Transaction
    {
        return new Transaction(
            $array['id'],
            $array['user_id'],
            $array['crypto_id'],
            $array['transaction_type'],
            $array['amount'],
            $array['price'],
            new DateTime($array['created_at'])
        );
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'crypto_id' => $this->crypto_id,
            'transaction_type' => $this->transaction_type,
            'amount' => $this->amount,
            'price' => $this->price,
            'created_at' => $this->created_at,
        ];
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }
}