<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\MarketPrice;

class MarketPriceRepository extends Repository
{
    protected function __construct()
    {
        $this->table = 'market_prices';
        $this->columns = MarketPrice::getFieldNames();
        parent::__construct();
    }

    public function getMarketPriceById($id): MarketPrice
    {
        return MarketPrice::fromArray(parent::get_by_id($id));
    }

    public function createMarketPrice(MarketPrice $marketPrice)
    {
        parent::insert($marketPrice->toArray());
    }

    public function updateMarketPrice(MarketPrice $marketPrice)
    {
        parent::update($marketPrice->toArray());
    }

    public function deleteMarketPrice(MarketPrice $marketPrice)
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