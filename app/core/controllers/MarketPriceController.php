<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\MarketPriceService;
use Exception;

class MarketPriceController
{
    private MarketPriceService $service;

    public function __construct()
    {
        $this->service = new MarketPriceService();
    }

    public function getLivePrices(): void
    {
        try {
            $prices = $this->service->getLatestPrices();
            echo json_encode(['success' => true, 'data' => $prices]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getWithPrevious(): void
    {
        try {
            $data = $this->service->getLatestAndPreviousPrices();
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }


    public function getChartData(): void
    {
        try {
            if (!isset($_GET['crypto_id'])) {
                throw new Exception("Missing crypto_id parameter.");
            }

            $cryptoId = (int) $_GET['crypto_id'];
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;

            $history = $this->service->getPriceHistory($cryptoId, $limit);
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
