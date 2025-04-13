<?php

namespace CryptoTrade\Models;

use DateTime;

class CryptoCurrency implements RepoCompatibility
{
    private const FIELD_NAMES = [
        'id',
        'name',
        'sign',
        'symbol',
        'initial_price',
        'current_price',
        'volatility',
        'created_at'
    ];

    public int $id;
    public string $name;
    public string $sign;
    public string $symbol;
    public float $initial_price;
    public float $current_price;
    public string $volatility;
    public DateTime $created_at;

    public function __construct(
        int $id,
        string $name,
        string $sign,
        string $symbol,
        float $initial_price,
        float $current_price,
        string $volatility,
        DateTime $created_at
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->sign = $sign;
        $this->symbol = $symbol;
        $this->initial_price = $initial_price;
        $this->current_price = $current_price;
        $this->volatility = $volatility;
        $this->created_at = $created_at;
    }

    public static function fromArray($array): CryptoCurrency
    {
        assert(is_array($array));
        assert(array_key_exists('id', $array));
        assert(array_key_exists('name', $array));
        assert(array_key_exists('sign', $array));
        assert(array_key_exists('symbol', $array));
        assert(array_key_exists('initial_price', $array));
        assert(array_key_exists('current_price', $array));
        assert(array_key_exists('volatility', $array));
        assert(array_key_exists('created_at', $array));

        return new CryptoCurrency(
            (int) $array['id'],
            $array['name'],
            $array['sign'],
            $array['symbol'],
            (float) $array['initial_price'],
            (float) $array['current_price'],
            $array['volatility'],
            new DateTime($array['created_at'])
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
            'name' => $this->name,
            'sign' => $this->sign,
            'symbol' => $this->symbol,
            'initial_price' => $this->initial_price,
            'current_price' => $this->current_price,
            'volatility' => $this->volatility,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
