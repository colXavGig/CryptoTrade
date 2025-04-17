<?php

namespace CryptoTrade\Services;

use Exception;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
class StripeService
{
    private string $secretKey = "sk_test_51REaJN2eoqplFxF1zCy5QqzzgjYOOaHf1JovynkrrLU0KfvLfUIQ5IuPQckNeMfdFl5mDD8NMnBgi2yDLjNEKYQn00ndcrCGa3";
    private StripeClient $client;

    public function __construct() {
        $this->client = new StripeClient($this->secretKey);
    }

    /**
     * @return array<string,
     * @throws Exception
     */
    public function createCheckoutSession(): array
    {
        try {
            $session = $this->client->checkout->sessions->create([
                'ui_mode' => 'embedded',
                'line_items' => [[
                    'price' => 'price_1REaU72eoqplFxF1F9ywrNSB', // NOTE: id from the stripe dashboard
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'return_url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/user-wallet?session_id={CHECKOUT_SESSION_ID}',
            ]);
        } catch (ApiErrorException $e) {
            throw new Exception("Error Processing Request: " . $e->getMessage());
        }
        if (($secret = $session->client_secret) == null) {
            throw new Exception("Empty secret key was given by stripe client");
        }
        return [
          'clientSecret' => $secret,
        ];
    }

    /**
     * @throws Exception
     */
    public function getCheckoutSessionStatus(): array
    {
        try {
            $jsonstring = file_get_contents("php://input");
            $obj = json_decode($jsonstring);

            $session = $this->client->checkout->sessions->retrieve($obj->session_id);

            return [
                'status' => $session->status,
                'customer_email' => $session->customer_details->email,
                'amount' => floatval($session->amount_total) / 100,
                'session' => $session,
            ];
        } catch (ApiErrorException $e) {
            throw new Exception("Error Processing Request: " . $e->getMessage());
        }

    }
}