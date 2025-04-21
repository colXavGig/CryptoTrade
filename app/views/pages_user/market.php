<?php

use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserWalletService;
use CryptoTrade\Services\MarketPriceService;
use CryptoTrade\Services\TransactionService;
use CryptoTrade\DataAccess\CryptoCurrencyRepository;

// Authenticated user session
/** @var array<string,mixed> $user */
$user = JWTService::verifyJWT();

$walletService = new UserWalletService();
$wallets = $walletService->getWalletForUser($user['user_id']);

// Reorganize wallets for easier access by crypto_id
$wallets_by_crypto = [];
foreach ($wallets as $wallet) {
    $wallets_by_crypto[$wallet->crypto_id] = $wallet;
}

$priceService = new MarketPriceService();
$prices = $priceService->getLatestPrices();

$cryptoMap = [];
foreach ($prices as $crypto) {
    $cryptoMap[$crypto['id']] = $crypto;
}

$cryptoRepo = CryptoCurrencyRepository::getInstance();
$cryptos = [];
foreach ($cryptoRepo->getAllCryptoCurrencies() as $c) {
    $cryptos[$c->id] = $c->symbol;
}
?>

<div class="container">
    <h2>Cryptocurrency Market</h2>
    <form id="form" method="post" action="/api/user/transactions/add">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="form-group">
            <label for="crypto_id">Select Cryptocurrency</label>
            <select id="crypto_id" name="crypto_id" class="form-control" onchange="updateTransactionSummary()">
                <?php foreach ($cryptoMap as $crypto): ?>
                    <option value="<?= $crypto['id'] ?>"><?= $crypto['symbol'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="type">Transaction Type</label>
            <select id="type" name="type" class="form-control" onchange="updateTransactionSummary()">
                <option value="buy">Buy</option>
                <option value="sell">Sell</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" class="form-control" min="0" step="0.001" value="0">
            <span id="amount-error" class="text-danger" hidden></span>
        </div>

        <table class="table table-sm mt-4" id="summary-table">
            <tr><th>Your Balance (USD)</th><td id="usd-balance"></td></tr>
            <tr><th>You Own</th><td id="crypto-holding"></td></tr>
            <tr><th>Unit Price</th><td id="unit-price"></td></tr>
            <tr><th>Total Transaction</th><td id="total-price"></td></tr>
        </table>

        <input type="hidden" name="price" id="price">

        <button type="submit" class="btn btn-success w-100">Submit Transaction</button>
    </form>
</div>
<div id="transaction-data"
     data-wallets='<?= json_encode($wallets_by_crypto) ?>'
     data-cryptomap='<?= json_encode($cryptoMap) ?>'
     data-balance='<?= isset($user['balance']) ? (float)$user['balance'] : 0 ?>'>
</div>


