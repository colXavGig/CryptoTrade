<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\MarketPriceRepository;
use CryptoTrade\DataAccess\CryptoCurrencyRepository;
use CryptoTrade\Models\MarketPrice;
use DateTime;

class MarketPriceService
{
    private MarketPriceRepository $priceRepo;
    private CryptoCurrencyRepository $cryptoRepo;

    public function __construct()
    {
        $this->priceRepo = MarketPriceRepository::getInstance();
        $this->cryptoRepo = CryptoCurrencyRepository::getInstance();
    }

    /**
     * @throws \Exception
     */
    public function getLatestPrices(): array
    {
        $cryptos = $this->cryptoRepo->getAllCryptoCurrencies();
        $latestPrices = [];

        foreach ($cryptos as $crypto) {
            $price = $this->priceRepo->getLatestByCryptoId($crypto->id);
            $latestPrices[] = [
                'id' => $crypto->id,
                'name' => $crypto->name,
                'symbol' => $crypto->symbol,
                'sign' => $crypto->sign,
                'volatility' => $crypto->volatility,
                'price' => $price?->price ?? $crypto->current_price,
                'updated_at' => $price?->updated_at?->format('Y-m-d H:i:s') ?? null
            ];
        }

        return $latestPrices;
    }

    /**
     * @throws \Exception
     */
    public function getPriceHistory(int $cryptoId, int $limit = 50): array
    {
        $history = $this->priceRepo->getRecentPricesByCryptoId($cryptoId, $limit);
        return array_map(fn($price) => $price->toArray(), $history);
    }


    /**
     * @throws \Exception
     */
    public function getLatestPriceFor(int $cryptoId): ?MarketPrice
    {
        return $this->priceRepo->getLatestByCryptoId($cryptoId);
    }
}
