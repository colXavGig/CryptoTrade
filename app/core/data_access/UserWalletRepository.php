<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\UserWallet;

class UserWalletRepository extends Repository
{
        protected function __construct() {
            $this->table = "user_wallets";
            $this->columns = UserWallet::getFieldNames();
            parent::__construct();
        }

    public function getUserWalletById($id): UserWallet {
            return UserWallet::fromArray(parent::get_by_id($id));
    }

    public function createUserWallet(UserWallet $userWallet) {
            parent::insert($userWallet->toArray());
    }

    public function updateUserWallet(UserWallet $userWallet) {
            parent::update($userWallet->toArray());
    }
    public function deleteUserWallet(UserWallet $userWallet) {
            parent::delete($userWallet->id);
    }

    public function getAllUserWallets() : array {
            $list = parent::get_all();
            for ($i = 0; $i < count($list); $i++) {
                $list[$i] = UserWallet::fromArray($list[$i]);
            }
            return $list;
    }



}