<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\CryptoCurrencyRepository;
use CryptoTrade\DataAccess\UserRepository;
use CryptoTrade\Models\Transaction;
use CryptoTrade\Models\TransactionType;
use CryptoTrade\Models\UserWallet;
use CryptoTrade\DataAccess\TransactionRepository;
use CryptoTrade\DataAccess\UserWalletRepository;
use CryptoTrade\DataAccess\MarketPriceRepository;
use DateTime;
use Exception;

class TransactionService
{
    private TransactionRepository $transactionRepo;
    private UserRepository $userRepo;
    private UserWalletRepository $walletRepo;
    private MarketPriceRepository $marketRepo;
    private CryptoCurrencyRepository $cryptoRepo;

    public function __construct()
    {
        $this->transactionRepo = TransactionRepository::getInstance();
        $this->userRepo = new UserRepository();
        $this->walletRepo = UserWalletRepository::getInstance();
        $this->marketRepo = MarketPriceRepository::getInstance();
        $this->cryptoRepo = CryptoCurrencyRepository::getInstance();
    }

    public function sellAll(UserWallet $wallet): void
    {
        $price = $this->marketRepo->getLatestByCryptoId($wallet->crypto_id)?->price;
        if (!$price) {
            throw new Exception("Current price unavailable for crypto ID: {$wallet->crypto_id}");
        }

        if ($wallet->balance <= 0) {
            throw new Exception("Insufficient balance to sell.");
        }

        $transaction = new Transaction(
            id: 0,
            user_id: $wallet->user_id,
            crypto_id: $wallet->crypto_id,
            transaction_type: TransactionType::SELL,
            amount: $wallet->balance,
            price: $price,
            created_at: new DateTime()
        );

        $this->transactionRepo->createTransaction($transaction);

        $user = $this->userRepo->get_by_id($wallet->user_id);
        $user->balance += $wallet->balance * $price;
        $this->userRepo->update($user->toArray());

        $wallet->balance = 0;
        $this->walletRepo->upsert($wallet);
    }

    public function sellCrypto(int $userId, int $cryptoId, float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero.");
        }

        $wallet = $this->walletRepo->getByUserAndCrypto($userId, $cryptoId);
        if (!$wallet) {
            throw new Exception("Wallet not found for this cryptocurrency.");
        }

        if ($wallet->balance < $amount) {
            throw new Exception("Insufficient balance in wallet.");
        }

        $price = $this->marketRepo->getLatestByCryptoId($cryptoId)?->price;
        if (!$price) {
            throw new Exception("Latest market price unavailable.");
        }

        $user = $this->userRepo->get_user_by_id($userId);
        $wallet->balance -= $amount;
        $user->balance += $amount * $price;

        $this->walletRepo->upsert($wallet);
        $this->userRepo->update($user->toArray());

        $transaction = new Transaction(
            id: 0,
            user_id: $userId,
            crypto_id: $cryptoId,
            transaction_type: TransactionType::SELL,
            amount: $amount,
            price: $price,
            created_at: new DateTime()
        );

        $this->transactionRepo->createTransaction($transaction);
    }

    public function buyCrypto(int $userId, int $cryptoId, float $amount): void
    {
        $price = $this->marketRepo->getLatestByCryptoId($cryptoId)?->price;
        if (!$price) {
            throw new Exception("Current price unavailable for crypto ID: {$cryptoId}");
        }

        $user = $this->userRepo->get_user_by_id($userId);
        $cost = $amount * $price;

        if ($user->balance < $cost) {
            throw new Exception("Insufficient USD balance.");
        }

        $wallet = $this->walletRepo->getByUserAndCrypto($userId, $cryptoId) ?? new UserWallet(0, $userId, $cryptoId, 0);
        $wallet->balance += $amount;
        $this->walletRepo->upsert($wallet);

        $transaction = new Transaction(
            id: 0,
            user_id: $userId,
            crypto_id: $cryptoId,
            transaction_type: TransactionType::BUY,
            amount: $amount,
            price: $price,
            created_at: new DateTime()
        );

        $this->transactionRepo->createTransaction($transaction);

        $user->balance -= $cost;
        $this->userRepo->update($user->toArray());
    }

    public function getUserTransactions(int $userId): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->user_id === $userId
        );
    }

    public function getTransactionById(int $transactionId): ?Transaction
    {
        return $this->transactionRepo->getTransactionById($transactionId);
    }

    public function getAllTransactions(): array
    {
        return $this->transactionRepo->getAllTransactions();
    }

    public function getTransactionsByUserId(int $userId): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->user_id === $userId
        );
    }

    public function getTransactionsByCryptoId(int $cryptoId): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->crypto_id === $cryptoId
        );
    }

    public function getTransactionsByType(string $type): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->transaction_type === $type
        );
    }

    public function getTransactionsByDate(DateTime $date): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->created_at->format('Y-m-d') === $date->format('Y-m-d')
        );
    }

    public function getTransactionsByDateRange(DateTime $start, DateTime $end): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->created_at >= $start && $t->created_at <= $end
        );
    }

    public function getTransactionsByPriceRange(float $min, float $max): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->price >= $min && $t->price <= $max
        );
    }

    public function getTransactionsByAmountRange(float $min, float $max): array
    {
        return array_filter(
            $this->transactionRepo->getAllTransactions(),
            fn($t) => $t->amount >= $min && $t->amount <= $max
        );
    }

    public function getTotalVolume(): float|int
    {
        $transactions = $this->getAllTransactions();
        $totalVolume = 0;

        foreach ($transactions as $tx) {
            $totalVolume += $tx->amount * $tx->price;
        }
        return $totalVolume;
    }

    public function getVolumeByCrypto(int $cryptoId): float|int
    {
        $transactions = $this->getTransactionsByCryptoId($cryptoId);
        $totalVolume = 0;

        foreach ($transactions as $tx) {
            $totalVolume += $tx->amount * $tx->price;
        }
        return $totalVolume;
    }
    public function getVolumeByAllCryptos(): array
    {
        $cryptoRepo = \CryptoTrade\DataAccess\CryptoCurrencyRepository::getInstance();
        $cryptos = $cryptoRepo->getAllCryptoCurrencies();
        $result = [];

        foreach ($cryptos as $crypto) {
            $result[] = [
                'crypto_id' => $crypto->id,
                'symbol' => $crypto->symbol,
                'volume' => $this->getVolumeByCrypto($crypto->id)
            ];
        }

        return $result;
    }


    public function getTopUsersByVolume(): array
    {
        $transactions = $this->getAllTransactions();
        $volumes = [];

        foreach ($transactions as $tx) {
            $volumes[$tx->user_id] = ($volumes[$tx->user_id] ?? 0) + ($tx->amount * $tx->price);
        }

        arsort($volumes);
        $result = [];

        foreach (array_slice($volumes, 0, 5, true) as $userId => $volume) {
            $user = $this->userRepo->get_user_by_id($userId);
            $result[] = ['user_id' => $userId, 'email' => $user->email, 'volume' => $volume];
        }

        return $result;
    }

    public function getDailyTransactionVolume(): array
    {
        $transactions = $this->getAllTransactions();
        $dailyVolume = [];

        foreach ($transactions as $tx) {
            $date = $tx->created_at->format('Y-m-d');
            $dailyVolume[$date] = ($dailyVolume[$date] ?? 0) + ($tx->amount * $tx->price);
        }

        ksort($dailyVolume);

        $result = [];
        foreach ($dailyVolume as $date => $volume) {
            $result[] = ['date' => $date, 'volume' => $volume];
        }

        return $result;
    }

    public function getMostTradedCryptos(): array
    {
        $transactions = $this->getAllTransactions();
        $counts = [];

        foreach ($transactions as $tx) {
            $counts[$tx->crypto_id] = ($counts[$tx->crypto_id] ?? 0) + 1;
        }

        arsort($counts);

        $result = [];
        foreach ($counts as $cryptoId => $count) {
            $crypto = $this->cryptoRepo->getCryptoCurrencyById($cryptoId);
            $result[] = ['crypto_id' => $cryptoId, 'symbol' => $crypto->symbol, 'count' => $count];
        }

        return $result;
    }

    public function getAverageTransactionValue(): float
    {
        $transactions = $this->getAllTransactions();
        $count = count($transactions);

        if ($count === 0) {
            return 0;
        }

        $totalValue = 0;
        foreach ($transactions as $tx) {
            $totalValue += $tx->amount * $tx->price;
        }

        return $totalValue / $count;
    }
}
