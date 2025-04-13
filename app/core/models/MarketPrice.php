<?php

namespace CryptoTrade\Models;

use DateMalformedStringException;
use DateTime;
use Exception;

class MarketPrice implements RepoCompatibility
{
    private const FIELD_NAMES = [
        'id',
        'crypto_id',
        'price',
        'created_at',
        'updated_at'
    ];

    public int $id;
    public int $crypto_id;
    public float $price;
    public DateTime $created_at;
    public DateTime $updated_at;

    public function __construct(
        int $id,
        int $crypto_id,
        float $price,
        DateTime $created_at,
        DateTime $updated_at
    ) {
        $this->id = $id;
        $this->crypto_id = $crypto_id;
        $this->price = $price;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    /**
     * @throws Exception
     */
    public static function fromArray($array): MarketPrice
    {
        try {
            return new MarketPrice(
                (int)$array['id'],
                (int)$array['crypto_id'],
                (float)$array['price'],
                new DateTime($array['created_at']),
                new DateTime($array['updated_at'])
            );
        } catch (DateMalformedStringException $e) {
            throw new Exception("Invalid market price");
        }
    }

    public static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'crypto_id' => $this->crypto_id,
            'price' => $this->price,
            'created_at' => $this->created_at->format(DateTime::ATOM),
            'updated_at' => $this->updated_at->format(DateTime::ATOM),
        ];
    }
}
