<?php

require_once '../../app/vendor/autoload.php';

use CryptoTrade\DataAccess\MarketPriceRepository;
use CryptoTrade\DataAccess\CryptoCurrencyRepository;
use CryptoTrade\Models\MarketPrice;

date_default_timezone_set('UTC');

echo "[INIT] Market Price Simulation Daemon started...\n";

$marketPriceRepo = MarketPriceRepository::getInstance();
$cryptoRepo = CryptoCurrencyRepository::getInstance();

while (true) {
    echo "[RUN] Simulating prices...\n";

    $cryptos = $cryptoRepo->getAllCryptoCurrencies();

    foreach ($cryptos as $crypto) {
        $id = $crypto->id;
        $currentPrice = $crypto->current_price;
        $volatility = $crypto->volatility;

        $newPrice = simulatePrice($currentPrice, $volatility);

        $marketPrice = new MarketPrice(
            0,                        // id (auto-increment)
            $id,                      // crypto_id
            $newPrice,                // price
            new DateTime(),            // created_at
            new DateTime()             // updated_at
        );

        try {
            $marketPriceRepo->createMarketPrice($marketPrice);
            echo "[OK] {$crypto->symbol} → $newPrice ({$volatility})\n";
        } catch (Exception $e) {
            echo "[ERROR] Failed to update price for {$crypto->symbol}: " . $e->getMessage() . "\n";
        }
    }

    echo "[WAIT] Sleeping 60 seconds...\n";
    sleep(60);
}

/**
 * Simulate new price based on volatility factor
 */
function simulatePrice(float $price, string $volatility): float
{
    $factor = match ($volatility) {
        'low' => 0.01,
        'medium' => 0.05,
        'high' => 0.15,
        default => 0.03,
    };

    $variation = $price * $factor * (mt_rand(-100, 100) / 100);
    return round(max($price + $variation, 0.00000001), 8); // avoid 0 or negative prices
}
