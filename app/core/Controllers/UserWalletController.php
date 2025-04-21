<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Models\UserWallet;
use CryptoTrade\Services\CSRFService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserWalletService;
use CryptoTrade\Services\TransactionService;

use Exception;

class UserWalletController
{
    private UserWalletService $walletService;

    public function __construct()
    {
        $this->walletService = new UserWalletService();
    }

    public function getWallet(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed', 'status' => 405]);
            return;
        }

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $targetUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $authUser['user_id'];

            if ($authUser['role'] !== 'admin' && $targetUserId !== $authUser['user_id']) {
                throw new Exception("Permission denied.");
            }

            $wallets = $this->walletService->getWalletForUser($targetUserId);

            echo json_encode([
                'success' => true,
                'wallet' => array_map(fn($w) => $w->toArray(), $wallets),
                'status' => 200
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'status' => 401]);
        }
    }

    public function updateWallet(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed', 'status' => 405]);
            return;
        }

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            if (!isset($_POST['crypto_id'], $_POST['balance'])) {
                throw new Exception("Missing crypto_id or balance.");
            }

            $targetUserId = $_POST['user_id'] ?? $authUser['user_id'];

            if ($authUser['role'] !== 'admin' && $targetUserId != $authUser['user_id']) {
                throw new Exception("Permission denied.");
            }

            $wallet = new UserWallet(
                id: 0,
                user_id: (int)$targetUserId,
                crypto_id: (int)$_POST['crypto_id'],
                balance: (float)$_POST['balance']
            );

            $this->walletService->saveWallet($wallet);

            echo json_encode(['success' => true, 'message' => 'Wallet updated', 'status' => 200]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'status' => 400]);
        }
    }

    public function deleteWallet(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed', 'status' => 405]);
            return;
        }

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            if (!isset($_POST['crypto_id'])) {
                throw new Exception("Missing crypto_id.");
            }

            $targetUserId = $_POST['user_id'] ?? $authUser['user_id'];

            if ($authUser['role'] !== 'admin' && $targetUserId != $authUser['user_id']) {
                throw new Exception("Permission denied.");
            }

            $walletData = $this->walletService->getWalletByUserAndCrypto(
                (int)$targetUserId,
                (int)$_POST['crypto_id']
            );

            if (!$walletData) {
                throw new Exception("Wallet not found.");
            }

            // Ensure $wallet is an instance of UserWallet
            $wallet = is_array($walletData) ? UserWallet::fromArray($walletData) : $walletData;

            // SELL ALL: convert crypto to USD and log transaction
            $transactionService = new TransactionService();
            $transactionService->sellAll($wallet); // handles price lookup, transaction, and balance update

            echo json_encode(['success' => true, 'message' => 'Crypto sold and wallet deleted', 'status' => 200]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'status' => 400]);
        }
    }


}
