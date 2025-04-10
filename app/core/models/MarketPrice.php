<?php

namespace CryptoTrade\Models;

use DateTime;

class MarketPrice implements RepoCompatibility
{
    private const FIELD_NAMES = [
        'id',
        'crypto_id',
        'price',
        'created_at'
    ];
    public int $id;
    public int $crypto_id;
    public float $price;
    public DateTime $created_at;

    public function __construct(int $id, int $crypto_id, float $price, DateTime $created_at)
    {
        $this->id = $id;
        $this->crypto_id = $crypto_id;
        $this->price = $price;
        $this->created_at = $created_at;
    }

    public static function fromArray($array): MarketPrice
    {
        return new MarketPrice(
            $array['id'],
            $array['crypto_id'],
            $array['price'],
            $array['created_at']
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
            'crypto_id' => $this->crypto_id,
            'price' => $this->price,
            'created_at' => $this->created_at
        ];
    }
}