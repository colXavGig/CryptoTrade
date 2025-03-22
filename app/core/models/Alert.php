<?php

namespace CryptoTrade\Models;

class Alert implements  RepoCompatibility
{
    public int $id;
    public int $user_id;
    public int $crypto_id;
    public float $price_threshold;
    public bool $notified;

    private const FIELD_NAMES = [
        "id",
        "user_id",
        "crypto_id",
        "price_threshold",
        "alert_type",
        "notified",
    ];

    public function __construct(int $id, int $user_id, int $crypto_id, float $price_threshold, string $type, bool $notified)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->crypto_id = $crypto_id;
        $this->price_threshold = $price_threshold;
        $this->type = $type;
        $this->notified = $notified;
    }
    public static function fromArray($array): Alert {
        assert(is_array($array));
        assert(array_key_exists('id', $array));
        assert(array_key_exists('user_id', $array));
        assert(array_key_exists('crypto_id', $array));
        assert(array_key_exists('price_threshold', $array));
        assert(array_key_exists('alert_type', $array));
        assert(array_key_exists('notified', $array));

        return new Alert(
            $array['id'],
            $array['user_id'],
            $array['crypto_id'],
            $array['price_threshold'],
            $array['alert_type'],
            $array['notified']
        );
    }
    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'crypto_id' => $this->crypto_id,
            'price_threshold' => $this->price_threshold,
            'alert_type' => $this->type,
            'notified' => $this->notified
        ];
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }
}