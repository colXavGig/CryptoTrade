<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\MarketPrice;

class MarketPriceRepository extends Repository
{
    private static ?self $instance = null;

    protected function __construct()
    {
        $this->table = 'market_prices';
        $this->columns = MarketPrice::getFieldNames();
        parent::__construct();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getMarketPriceById($id): MarketPrice
    {
        return MarketPrice::fromArray(parent::get_by_id($id));
    }

    public function createMarketPrice(MarketPrice $marketPrice): void
    {
        parent::insert($marketPrice->toArray());
    }

    public function updateMarketPrice(MarketPrice $marketPrice): void
    {
        parent::update($marketPrice->toArray());
    }

    public function deleteMarketPrice(MarketPrice $marketPrice): void
    {
        parent::delete($marketPrice->id);
    }

    public function getAllMarketPrices(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = MarketPrice::fromArray($list[$i]);
        }
        return $list;
    }

}