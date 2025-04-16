<?php


require_once '../../app/vendor/autoload.php';

use CryptoTrade\Services\PricePollingService;

date_default_timezone_set('UTC');

echo "[INIT] Price Polling Daemon started...\n";

$poller = new PricePollingService();

while (true) {
    echo "[RUN] Checking alerts...\n";

    try {
        $poller->pollAndTrigger();
        echo "[OK] Alert check cycle complete.\n";
    } catch (Exception $e) {
        echo "[ERROR] " . $e->getMessage() . "\n";
    }

    echo "[WAIT] Sleeping 60 seconds...\n";
    sleep(60);
}
