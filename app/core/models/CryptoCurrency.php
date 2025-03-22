<?php

namespace CryptoTrade\Models;

class CryptoCurrency implements RepoCompatibility
{
    public $id;
    public $name;
    public $sign;
    public $symbol;
    public $intitial_price;
    public $current_price;
    public $volatibility;
    public $created_at;

    private const  FIELD_NAMES = [
        'id',
        'name',
        'sign',
        'symbol',
        'intitial_price',
        'current_price',
        'volatibility',
        'created_at'
    ];

    public function __construct($id, $name, $sign, $symbol, $intitial_price, $current_price, $volatibility, $created_at)
    {
        $this->id = $id;
        $this->name = $name;
        $this->sign = $sign;
        $this->symbol = $symbol;
        $this->intitial_price = $intitial_price;
        $this->current_price = $current_price;
        $this->volatibility = $volatibility;
        $this->created_at = $created_at;
    }

    public static function fromArray($array): CryptoCurrency {
        assert(is_array($array));
        assert(array_key_exists('id', $array));
        assert(array_key_exists('name', $array));
        assert(array_key_exists('sign', $array));
        assert(array_key_exists('symbol', $array));
        assert(array_key_exists('intitial_price', $array));
        assert(array_key_exists('current_price', $array));
        assert(array_key_exists('volatibility', $array));
        assert(array_key_exists('created_at', $array));


        return new CryptoCurrency(
            $array['id'],
            $array['name'],
            $array['sign'],
            $array['symbol'],
            $array['intitial_price'],
            $array['current_price'],
            $array['volatibility'],
            $array['created_at']
        );
    }
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sign' => $this->sign,
            'symbol' => $this->symbol,
            'intitial_price' => $this->intitial_price,
            'current_price' => $this->current_price,
            'volatibility' => $this->volatibility,
            'created_at' => $this->created_at,
        ];
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }
}