<?php
namespace CryptoTrade\Models;
class Alert implements RepoCompatibility
{
    private const FIELD_NAMES = [
        "id",
        "user_id",
        "crypto_id",
        "price_threshold",
        "alert_type",
        "active",
        "created_at",
        "last_triggered_at"
    ];

    public int $id;
    public int $user_id;
    public int $crypto_id;
    public float $price_threshold;
    public AlertType $type;
    public bool $active;
    public string $created_at;
    public ?string $last_triggered_at;

    public function __construct(
        int $id,
        int $user_id,
        int $crypto_id,
        float $price_threshold,
        AlertType $type,
        bool $active,
        string $created_at,
        ?string $last_triggered_at
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->crypto_id = $crypto_id;
        $this->price_threshold = $price_threshold;
        $this->type = $type;
        $this->active = $active;
        $this->created_at = $created_at;
        $this->last_triggered_at = $last_triggered_at;
    }

    public static function fromArray(array $array): Alert
    {
        return new Alert(
            $array['id'],
            $array['user_id'],
            $array['crypto_id'],
            $array['price_threshold'],
            AlertType::from($array['alert_type']),
            (bool) $array['active'],
            $array['created_at'],
            $array['last_triggered_at'] ?? null
        );
    }

    public static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'crypto_id' => $this->crypto_id,
            'price_threshold' => $this->price_threshold,
            'alert_type' => $this->type->value,
            'active' => $this->active,
            'created_at' => $this->created_at,
            'last_triggered_at' => $this->last_triggered_at
        ];
    }
}
