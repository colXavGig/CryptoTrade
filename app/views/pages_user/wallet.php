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
                <tr>
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
