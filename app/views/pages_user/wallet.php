<?php

use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserWalletService;
use CryptoTrade\Models\UserWallet;

// Authenticated user session
$user = JWTService::verifyJWT();
$walletService = new UserWalletService();
$wallets = $walletService->getWalletForUser($user['user_id']);

?>

<div class="container">
    <h2>Your Wallet</h2>

    <?php if (empty($wallets)): ?>
        <p>You don’t own any cryptocurrencies yet.</p>
    <?php else: ?>
        <table class="wallet-table">
            <thead>
            <tr>
                <th>Crypto ID</th>
                <th>Balance</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($wallets as $wallet): ?>
                <tr>
                    <td><?= htmlspecialchars($wallet->crypto_id) ?></td>
                    <td><?= number_format($wallet->balance, 8) ?></td>

                    <!-- Update Wallet Form -->
                    <td>
                        <form method="POST" action="/?route=api/user/wallet/update" style="display: flex; gap: 4px;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="crypto_id" value="<?= $wallet->crypto_id ?>">
                            <input type="number" step="0.00000001" name="balance" placeholder="New balance" required>
                            <button type="submit">Update</button>
                        </form>
                    </td>

                    <!-- Delete Wallet Form -->
                    <td>
                        <form method="POST" action="/?route=api/user/wallet/delete">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="crypto_id" value="<?= $wallet->crypto_id ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this wallet?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
