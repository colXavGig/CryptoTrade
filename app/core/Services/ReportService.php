<?php

namespace CryptoTrade\Services;

use Dompdf\Dompdf;

class ReportService
{
    private TransactionService $txService;
    private UserWalletService $walletService;
    private LogService $logService;
    private AlertService $alertService;
    private NotificationService $notificationService;
    private UserService $userService;
    private MarketPriceService $priceService;

    public function __construct()
    {
        $this->txService = new TransactionService();
        $this->walletService = new UserWalletService();
        $this->logService = new LogService();
        $this->alertService = new AlertService();
        $this->notificationService = new NotificationService();
        $this->userService = new UserService();
        $this->priceService = new MarketPriceService();
    }

    public function getUserReport(int $userId): array
    {
        $wallets = $this->walletService->getWalletForUser($userId);
        $transactions = $this->txService->getTransactionsByUserId($userId);
        $alerts = $this->alertService->getByUserId($userId);
        $logs = $this->logService->getRecentLogsForUser($userId);

        return [
            'wallets' => $wallets,
            'transactions' => $transactions,
            'alerts' => $alerts,
            'logs' => $logs,
            'summary' => [
                'wallet' => $this->calculateUserWalletSummary($wallets),
                'transactions' => $this->calculateUserTransactionSummary($transactions),
                'roi' => $this->calculateUserROI($transactions, $wallets),
                'top_crypto' => $this->getMostTradedCrypto($transactions),
                'alerts' => $this->calculateAlertStats($alerts),
            ]
        ];
    }

    public function getAdminReport(): array
    {
        return [
            'totalUsers' => $this->userService->countUsers(),
            'transactionVolume' => $this->txService->getTotalVolume(),
            'transactionsByCrypto' => $this->txService->getVolumeByAllCryptos(),
            'topUsers' => $this->txService->getTopUsersByVolume(),
            'dailyVolume' => $this->txService->getDailyTransactionVolume(),
            'mostTradedCryptos' => $this->txService->getMostTradedCryptos(),
            'avgTransactionValue' => $this->txService->getAverageTransactionValue(),
            'activeUsersToday' => $this->logService->getActiveUsersToday(),
            'alerts' => $this->alertService->getAll(),
            'alertSummary' => $this->calculateGlobalAlertStats(),
            'logActionSummary' => $this->logService->getSummaryByAction(),
            'failedLoginsToday' => $this->logService->countFailedLoginsToday(),
            'recentLogs' => $this->logService->getAllLogsRecent(),
        ];
    }

    public function generatePDF(string $html): void
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("report.pdf", ["Attachment" => true]);
    }

    private function calculateUserWalletSummary(array $wallets): array
    {
        $total = 0;
        $breakdown = [];
        $latestPrices = $this->priceService->getLatestPrices();

        foreach ($wallets as $wallet) {
            $price = $latestPrices[$wallet->crypto_id]['price'] ?? 0;
            $value = $wallet->balance * $price;
            $total += $value;
            $breakdown[] = [
                'crypto_id' => $wallet->crypto_id,
                'balance' => $wallet->balance,
                'value_usd' => $value
            ];
        }

        return [
            'total_value_usd' => $total,
            'per_crypto' => $breakdown
        ];
    }

    private function calculateUserTransactionSummary(array $transactions): array
    {
        $totalVolume = 0;
        $buyCount = 0;
        $sellCount = 0;

        foreach ($transactions as $tx) {
            $value = $tx->amount * $tx->price;
            $totalVolume += $value;
            if ($tx->transaction_type === 'buy') {
                $buyCount++;
            } elseif ($tx->transaction_type === 'sell') {
                $sellCount++;
            }
        }

        $count = count($transactions);

        return [
            'total_transactions' => $count,
            'buy_count' => $buyCount,
            'sell_count' => $sellCount,
            'avg_transaction_value' => $count > 0 ? $totalVolume / $count : 0,
            'total_volume' => $totalVolume
        ];
    }

    private function calculateUserROI(array $transactions, array $wallets): float
    {
        $spent = 0;
        $gained = 0;
        $walletValue = 0;

        foreach ($transactions as $tx) {
            $value = $tx->amount * $tx->price;
            if ($tx->transaction_type === 'buy') {
                $spent += $value;
            } elseif ($tx->transaction_type === 'sell') {
                $gained += $value;
            }
        }

        $latestPrices = $this->priceService->getLatestPrices();

        foreach ($wallets as $wallet) {
            $price = $latestPrices[$wallet->crypto_id]['price'] ?? 0;
            $walletValue += $wallet->balance * $price;
        }

        return $spent > 0 ? (($walletValue + $gained - $spent) / $spent) * 100 : 0;
    }

    private function getMostTradedCrypto(array $transactions): string
    {
        $counts = [];
        foreach ($transactions as $tx) {
            $counts[$tx->crypto_id] = ($counts[$tx->crypto_id] ?? 0) + 1;
        }
        arsort($counts);
        return key($counts) ?: '';
    }

    private function calculateGlobalAlertStats(): array
    {
        $alerts = $this->alertService->getAll();
        $triggered = array_filter($alerts, fn($a) => $a->last_triggered_at !== null);

        return [
            'total_alerts' => count($alerts),
            'triggered_alerts' => count($triggered),
            'active_alerts' => count(array_filter($alerts, fn($a) => $a->active))
        ];
    }

    private function calculateAlertStats(array $alerts): array
    {
        return [
            'total_alerts' => count($alerts),
            'active_alerts' => count(array_filter($alerts, fn($a) => $a->active))
        ];
    }
}