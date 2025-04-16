<?php

use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserWalletService;
use CryptoTrade\Services\MarketPriceService;

// Authenticated user session
$user = JWTService::verifyJWT();
$walletService = new UserWalletService();
$wallets = $walletService->getWalletForUser($user['user_id']);

// Get current crypto prices and metadata
$priceService = new MarketPriceService();
$prices = $priceService->getLatestPrices(); // includes: id, name, symbol, price

// Map for quick lookup
$cryptoMap = [];
foreach ($prices as $crypto) {
    $cryptoMap[$crypto['id']] = $crypto;
}
?>

<div class="container">
    <h2>Your Wallet</h2>

    <?php if (empty($wallets)): ?>
        <p>You don’t own any cryptocurrencies yet.</p>
    <?php else: ?>
        <table class="wallet-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Symbol</th>
                <th>Balance</th>
                <th>Value (USD)</th>
                <th>Sell All</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($wallets as $wallet): ?>
                <?php
                $crypto = $cryptoMap[$wallet->crypto_id] ?? null;
                if (!$crypto) continue;
                $value = $wallet->balance * $crypto['price'];
                ?>
                <tr style="color: yellow;">
                    <td><?= htmlspecialchars($crypto['name']) ?></td>
                    <td><?= htmlspecialchars($crypto['symbol']) ?></td>
                    <td><?= number_format($wallet->balance, 8) ?></td>
                    <td>$<?= number_format($value, 2) ?></td>

                    <td>
                        <form method="POST" action="/?route=api/user/wallet/delete">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="crypto_id" value="<?= $wallet->crypto_id ?>">
                            <button type="submit" onclick="return confirm('Sell all <?= $crypto['symbol'] ?>?')">Sell All</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- TODO: export in a standalone component for reusability -->
<?php
use CryptoTrade\Services\TransactionService;
use CryptoTrade\DataAccess\CryptoCurrencyRepository;

$transactionService = new TransactionService();
$cryptoRepo = CryptoCurrencyRepository::getInstance();

$transactions = $transactionService->getUserTransactions($user['user_id']);
$recentTransactions = array_slice(array_reverse($transactions), 0, 5); // latest 5

$cryptos = [];
foreach ($cryptoRepo->getAllCryptoCurrencies() as $c) {
    $cryptos[$c->id] = $c->symbol;
}
?>

<h3>Recent Transactions</h3>
<?php if (empty($recentTransactions)): ?>
    <p>No recent transactions found.</p>
<?php else: ?>
    <table class="transaction-table">
        <thead>
        <tr>
            <th>Date</th>
            <th>Crypto</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Price (USD)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recentTransactions as $tx): ?>
            <tr style="color: yellow;">
                <td><?= $tx->created_at->format('Y-m-d H:i') ?></td>
                <td><?= htmlspecialchars($cryptos[$tx->crypto_id] ?? 'Unknown') ?></td>
                <td><?= ucfirst($tx->transaction_type->value) ?></td>
                <td><?= number_format($tx->amount, 8) ?></td>
                <td>$<?= number_format($tx->price, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
