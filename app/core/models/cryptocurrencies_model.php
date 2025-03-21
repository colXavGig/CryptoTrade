<?php
require_once './model.php';

class CryptoCurrency extends model {
    public function __construct()
    {
        parent::__construct();
        $this->table = "cryptocurrencies";
        $this->columns = [
            "id",
            "name",
            "symbol",
            "sign",
            "initial_price",
            "current_price",
            "volatility",
            "created_at",
        ];
        
    }
}