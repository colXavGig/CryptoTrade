<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\TransactionService;
use CryptoTrade\Services\UserWalletService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\CSRFService;
use Exception;
use DateTime;

class TransactionController
{
    private TransactionService $transactionService;
    private UserWalletService $walletService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
        $this->walletService = new UserWalletService();
    }

    public function addTransaction()
    {
        $this->requirePost();

        if (isset($_POST['type']) && $_POST['type'] === 'buy') {
            $this->buyCrypto();
        } else if (isset($_POST['type']) && $_POST['type'] === 'sell') {
            $this->sellCrypto();
        }
    }

    public function sellAll(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $cryptoId = $_POST['crypto_id'] ?? null;
            if (!$cryptoId) throw new Exception("Missing crypto_id.");

            $targetUserId = $_POST['user_id'] ?? $authUser['user_id'];
            $this->enforceAccess($authUser, $targetUserId);

            $wallet = $this->walletService->getWalletByUserAndCrypto((int)$targetUserId, (int)$cryptoId);
            if (!$wallet) throw new Exception("Wallet not found.");

            $this->transactionService->sellAll($wallet);
            echo json_encode(['success' => true, 'message' => 'All balance sold successfully']);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function sellCrypto(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $cryptoId = $_POST['crypto_id'] ?? null;
            $amount = $_POST['amount'] ?? null;
            if (!$cryptoId || !$amount) throw new Exception("Missing crypto_id or amount.");

            $targetUserId = $_POST['user_id'] ?? $authUser['user_id'];
            $this->enforceAccess($authUser, $targetUserId);

            $this->transactionService->sellCrypto((int)$targetUserId, (int)$cryptoId, floatval($amount));
            echo json_encode(['success' => true, 'message' => "Sold {$amount} successfully."]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function buyCrypto(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $cryptoId = $_POST['crypto_id'] ?? null;
            $amount = $_POST['amount'] ?? null;
            if (!$cryptoId || !$amount) throw new Exception("Missing crypto_id or amount.");

            $targetUserId = $_POST['user_id'] ?? $authUser['user_id'];
            $this->enforceAccess($authUser, $targetUserId);

            $this->transactionService->buyCrypto((int)$targetUserId, (int)$cryptoId, (float)$amount);
            echo json_encode(['success' => true, 'message' => "Bought {$amount} successfully."]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getMyTransactions(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();
            $transactions = $this->transactionService->getUserTransactions($authUser['user_id']);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionById(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $transactionId = $_POST['transaction_id'] ?? null;
            if (!$transactionId) throw new Exception("Missing transaction_id.");

            $transaction = $this->transactionService->getTransactionById((int)$transactionId);
            echo json_encode(['success' => true, 'transaction' => $transaction?->toArray()]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getAllTransactions(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $transactions = $this->transactionService->getAllTransactions();
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByUserId(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $userId = $_POST['user_id'] ?? null;
            if (!$userId) throw new Exception("Missing user_id.");

            $transactions = $this->transactionService->getTransactionsByUserId((int)$userId);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByCryptoId(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $cryptoId = $_POST['crypto_id'] ?? null;
            if (!$cryptoId) throw new Exception("Missing crypto_id.");

            $transactions = $this->transactionService->getTransactionsByCryptoId((int)$cryptoId);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByType(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $type = $_POST['type'] ?? null;
            if (!$type) throw new Exception("Missing type.");

            $transactions = $this->transactionService->getTransactionsByType($type);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByDate(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $date = $_POST['date'] ?? null;
            if (!$date) throw new Exception("Missing date.");

            $dt = new DateTime($date);
            $transactions = $this->transactionService->getTransactionsByDate($dt);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByDateRange(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $start = $_POST['start'] ?? null;
            $end = $_POST['end'] ?? null;
            if (!$start || !$end) throw new Exception("Missing date range.");

            $transactions = $this->transactionService->getTransactionsByDateRange(new DateTime($start), new DateTime($end));
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByPriceRange(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $min = $_POST['min'] ?? null;
            $max = $_POST['max'] ?? null;
            if (!is_numeric($min) || !is_numeric($max)) throw new Exception("Invalid price range.");

            $transactions = $this->transactionService->getTransactionsByPriceRange((float)$min, (float)$max);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getTransactionsByAmountRange(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $min = $_POST['min'] ?? null;
            $max = $_POST['max'] ?? null;
            if (!is_numeric($min) || !is_numeric($max)) throw new Exception("Invalid amount range.");

            $transactions = $this->transactionService->getTransactionsByAmountRange((float)$min, (float)$max);
            echo json_encode(['success' => true, 'transactions' => array_map(fn($t) => $t->toArray(), $transactions)]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
    }

    private function enforceAccess(array $authUser, int $targetUserId): void
    {
        if ($authUser['role'] !== 'admin' && $targetUserId != $authUser['user_id']) {
            throw new Exception("Permission denied.");
        }
    }

    private function sendError(Exception $e): void
    {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
