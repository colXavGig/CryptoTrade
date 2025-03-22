<?php
namespace CryptoTrade\DataAccess;

use CryptoTrade\DataAccess\Repository;
use CryptoTrade\Models\CryptoCurrency;

class CryptoCurrencyRepository extends Repository {
    protected function __construct()
    {
        parent::__construct();
        $this->table = "cryptocurrencies";
        $this->columns = CryptoCurrency::getFieldNames();
    }

    public  function getAllCryptoCurrencies() : array {
        $list = parent::get_all();
         for ($i = 0; $i < count($list); $i++) {
            $list[$i] = CryptoCurrency::fromArray($list[$i]);
         }
         return $list;
    }
    public function getCryptoCurrencyById($id): CryptoCurrency {
        return CryptoCurrency::fromArray(parent::get_by_id($id));
    }

    public  function createCryptoCurrency(CryptoCurrency $cryptoCurrency) {
        parent::insert($cryptoCurrency->toArray());
    }

    public  function updateCryptoCurrency(CryptoCurrency $cryptoCurrency) {
        parent::update($cryptoCurrency->toArray());
    }

    public  function deleteCryptoCurrency(CryptoCurrency $cryptoCurrency) {
        parent::delete($cryptoCurrency->id);
    }
}