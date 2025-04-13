<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\MarketPrice;
use Exception;

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

    public function getLatestByCryptoId($id): ?MarketPrice
    {
        $query = "SELECT * FROM {$this->table} WHERE crypto_id = :crypto_id ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':crypto_id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? MarketPrice::fromArray($result) : null;
    }

    public function getRecentPricesByCryptoId(int $cryptoId, int $limit): array
    {
        $query = "SELECT * FROM {$this->table} WHERE crypto_id = :crypto_id ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':crypto_id', $cryptoId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => MarketPrice::fromArray($row), $rows);
    }


}