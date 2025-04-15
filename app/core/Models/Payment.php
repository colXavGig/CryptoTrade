<?php

namespace CryptoTrade\Models;

use DateTime;

class Payment implements RepoCompatibility
{
    private const FIELD_NAMES = [
        "id",
        "user_id",
        "stripe_transaction_id",
        "amount",
        "status",
        "created_at",
    ];
    public int $id;
    public int $user_id;
    public string $stripe_transaction_id;
    public float $amount;
    public string $status;
    public DateTime $created_at;

    public function __construct(int $id, int $user_id, string $stripe_transaction_id, float $amount, string $status, DateTime $created_at)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->stripe_transaction_id = $stripe_transaction_id;
        $this->amount = $amount;
        $this->status = $status;
        $this->created_at = $created_at;
    }

    public static function fromArray(array $array): self
    {
        assert(is_array($array));
        assert(count($array) === count(self::FIELD_NAMES));
        assert(count($array) === count(array_intersect(array_keys($array), array_keys(self::FIELD_NAMES))));

        return new Payment(
            $array['id'],
            $array['user_id'],
            $array['stripe_transaction_id'],
            $array['amount'],
            $array['status'],
            $array['created_at']
        );
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }

    function toArray(): array
    {
        $list = [];
        foreach (self::FIELD_NAMES as $field) {
            $list[$field] = $this->$field;
        }
        return $list;
    }
}