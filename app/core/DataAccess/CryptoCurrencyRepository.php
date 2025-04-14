<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\CryptoCurrency;

class CryptoCurrencyRepository extends Repository
{
    private static ?self $instance = null;
    protected function __construct()
    {
        parent::__construct();
        $this->table = "cryptocurrencies";
        $this->columns = CryptoCurrency::getFieldNames();
    }
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getAllCryptoCurrencies(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = CryptoCurrency::fromArray($list[$i]);
        }
        return $list;
    }

    public function getCryptoCurrencyById($id): CryptoCurrency
    {
        return CryptoCurrency::fromArray(parent::get_by_id($id));
    }

    public function createCryptoCurrency(CryptoCurrency $cryptoCurrency)
    {
        parent::insert($cryptoCurrency->toArray());
    }

    public function updateCryptoCurrency(CryptoCurrency $cryptoCurrency)
    {
        parent::update($cryptoCurrency->toArray());
    }

    public function deleteCryptoCurrency(CryptoCurrency $cryptoCurrency)
    {
        parent::delete($cryptoCurrency->id);
    }
}