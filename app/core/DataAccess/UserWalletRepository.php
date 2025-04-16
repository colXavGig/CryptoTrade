<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\UserWallet;

class UserWalletRepository extends Repository
{
    private static ?UserWalletRepository $instance = null;
    protected function __construct()
    {
        $this->table = "user_wallets";
        $this->columns = UserWallet::getFieldNames();
        parent::__construct();
    }

    public static function getInstance(): UserWalletRepository
    {
        if (self::$instance === null) {
            self::$instance = new UserWalletRepository();
        }
        return self::$instance;
    }

    public function getUserWalletById($id): UserWallet
    {
        return UserWallet::fromArray(parent::get_by_id($id));
    }


    // this function checks if the wallet already exists for the user and crypto pair then chose to update or create
    public function upsert(UserWallet $wallet): void
    {
        $existing = $this->getByUserAndCrypto($wallet->user_id, $wallet->crypto_id);
        if ($existing) {
            $wallet->id = $existing->id;
            $this->updateUserWallet($wallet);
        } else {
            $this->createUserWallet($wallet);
        }
    }

    protected function createUserWallet(UserWallet $userWallet)
    {
        parent::insert($userWallet->toArray());
    }

    protected function updateUserWallet(UserWallet $userWallet)
    {
        parent::update($userWallet->toArray());
    }

    public function deleteUserWallet(UserWallet $userWallet)
    {
        parent::delete($userWallet->id);
    }

    public function getAllUserWallets(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = UserWallet::fromArray($list[$i]);
        }
        return $list;
    }

    /**
     * get all user wallets of user
     * @param int $userId
     * @return array<UserWallet>
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($r) => UserWallet::fromArray($r), $rows);
    }

    public function getByUserAndCrypto(int $userId, int $cryptoId): ?UserWallet
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = :user_id AND crypto_id = :crypto_id");
        $stmt->execute(['user_id' => $userId, 'crypto_id' => $cryptoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? UserWallet::fromArray($row) : null;
    }




}