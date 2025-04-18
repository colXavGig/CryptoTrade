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
            <input type="number" id="amount" name="amount" class="form-control" min="0" step="0.001" value="0" oninput="updateTransactionSummary()">
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

<script>
    const wallets = <?= json_encode($wallets_by_crypto) ?>;
    const cryptoMap = <?= json_encode($cryptoMap) ?>;
    const userBalance = <?= isset($user['balance']) ? (float) $user['balance'] : 0 ?>;

    const usdBalanceEl = document.getElementById("usd-balance");
    const unitPriceEl = document.getElementById("unit-price");
    const totalPriceEl = document.getElementById("total-price");
    const cryptoHoldingEl = document.getElementById("crypto-holding");
    const amountInput = document.getElementById("amount");
    const cryptoSelect = document.getElementById("crypto_id");
    const typeSelect = document.getElementById("type");
    const errorSpan = document.getElementById("amount-error");
    const priceInput = document.getElementById("price");

    function formatCurrency(value) {
        return isNaN(value) ? "$0.00" : `$${(+value).toFixed(2)}`;
    }

    function formatCrypto(value, decimals = 6) {
        return isNaN(value) ? "0.000000" : (+value).toFixed(decimals);
    }

    function updateTransactionSummary() {
        const cryptoId = cryptoSelect.value;
        const amount = parseFloat(amountInput.value || 0);
        const price = parseFloat(cryptoMap[cryptoId]?.price || 0);

        const wallet = wallets[cryptoId] || { balance: 0 };
        const holding = parseFloat(wallet.balance || 0);
        const holdingValue = holding * price;
        const totalTransaction = amount * price;

        usdBalanceEl.textContent = formatCurrency(userBalance);
        unitPriceEl.textContent = formatCurrency(price);
        totalPriceEl.textContent = formatCurrency(totalTransaction);
        cryptoHoldingEl.textContent = `${formatCrypto(holding)} ${cryptoMap[cryptoId]?.symbol || ""} (${formatCurrency(holdingValue)})`;

        priceInput.value = price;

        // Validation
        const type = typeSelect.value;
        if (type === 'buy' && totalTransaction > userBalance) {
            errorSpan.textContent = `Not enough USD. You have ${formatCurrency(userBalance)}.`;
            errorSpan.hidden = false;
        } else if (type === 'sell' && amount > holding) {
            errorSpan.textContent = `Not enough crypto. You own ${formatCrypto(holding)} ${cryptoMap[cryptoId]?.symbol}.`;
            errorSpan.hidden = false;
        } else {
            errorSpan.hidden = true;
            errorSpan.textContent = '';
        }
    }

    document.getElementById("form").addEventListener("submit", function (e) {
        e.preventDefault();
        if (!errorSpan.hidden) return;

        fetch(this.action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                csrf_token: document.getElementById("csrf_token").value,
                crypto_id: cryptoSelect.value,
                type: typeSelect.value,
                amount: amountInput.value,
                price: priceInput.value
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success === true) {
                    window.location.reload();
                } else {
                    throw new Error(data.error);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Transaction failed: " + err.message);
            });
    });

    // Init on page load
    document.addEventListener("DOMContentLoaded", updateTransactionSummary);
</script>

