<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\StripeService;
class CheckoutController
{
    private StripeService $service;

    public function __construct() {
        $this->service = new StripeService();
    }

    public function checkout() : void
    {
        try {
            $payload = $this->service->createCheckoutSession();
        } catch (\Exception $e) {
            echo  json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
        $payload['success'] = true;
        http_response_code(200);
        echo json_encode($payload);
    }

    public function sessionStatus(): void
    {
        try {
            $status = $this->service->getCheckoutSessionStatus();
            http_response_code(200);
            echo json_encode($status);
        } catch (\Exception $e) {
            echo  json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}