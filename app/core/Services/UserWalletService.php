<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\UserWalletRepository;
use CryptoTrade\Models\UserWallet;
use Exception;

class UserWalletService
{
    private UserWalletRepository $walletRepo;

    public function __construct()
    {
        $this->walletRepo = UserWalletRepository::getInstance();
    }

    /**
     * Get all wallet entries for a user
     * @param int $userId
     * @return array<UserWallet>
     */
    public function getWalletForUser(int $userId): array
    {
        return $this->walletRepo->getByUserId($userId);
    }

    /**
     * Get a specific wallet by user and crypto.
     */
    public function getWalletByUserAndCrypto(int $userId, int $cryptoId): ?UserWallet
    {
        return $this->walletRepo->getByUserAndCrypto($userId, $cryptoId);
    }

    /**
     * Upsert (insert or update) wallet.
     */
    public function saveWallet(UserWallet $wallet): void
    {
        $this->walletRepo->upsert($wallet);
    }

    /**
     * Delete wallet (if needed).
     */
    public function deleteWallet(UserWallet $wallet): void
    {
        $this->walletRepo->deleteUserWallet($wallet);
    }

    /**
     * Get wallet by ID (only if needed).
     */
    public function getWalletById(int $id): UserWallet
    {
        return $this->walletRepo->getUserWalletById($id);
    }
}
