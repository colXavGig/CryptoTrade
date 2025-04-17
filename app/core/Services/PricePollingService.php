<?php

namespace CryptoTrade\Services;


use Exception;

class PricePollingService
{
    private AlertService $alertService;
    private MarketPriceService $marketPriceService;

    public function __construct()
    {
        $this->alertService = new AlertService();
        $this->marketPriceService = new MarketPriceService();
    }

    /**
     * @throws Exception
     */
    public function pollAndTrigger(): void  // TODO: Implement a more efficient polling mechanism
    {
        $latestPrices = $this->marketPriceService->getLatestPrices();

        foreach ($latestPrices as $cryptoData) {
            $cryptoId = $cryptoData['id'];
            $price = $cryptoData['price'];

            $this->alertService->checkAlerts($cryptoId, $price);
        }
    }
}
