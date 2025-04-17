<?php

use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserWalletService;
use CryptoTrade\Services\MarketPriceService;

// Authenticated user session
/** @var array<string,mixed> $user */
$user = JWTService::verifyJWT(); // TODO: user->balance always shows 5000, find out why
$walletService = new UserWalletService();
$wallets = $walletService->getWalletForUser($user['user_id']);
$wallets_by_crypto = [];
foreach ($wallets as $wallet) {
    $wallets_by_crypto[$wallet->crypto_id] = $wallet;
}

// Get current crypto prices and metadata
$priceService = new MarketPriceService();
try {
    $prices = $priceService->getLatestPrices();
} catch (Exception $e) {
    print_r($e);
    die();
} // includes: id, name, symbol, price

// Map for quick lookup
$cryptoMap = [];
foreach ($prices as $crypto) {
    $cryptoMap[$crypto['id']] = $crypto;
}
?>

<script>
    let cryptoMap = <?= json_encode($cryptoMap) ?>;
    let wallets = <?= json_encode($wallets_by_crypto) ?>;

    function getWalletBalance(crypto_id) {
        return wallets[crypto_id].balance;
    }
    function validateAmountSelling(crypto_id, amount) {
        return amount <= getWalletBalance(crypto_id);
    }

    /**
     * Verifiy the is evnough real money balance to purchase the crypto
     * @param crypto_id
     * @param amount
     * @returns {boolean} true if there is enough
     */
    function validateAmountBuying(crypto_id, amount) {

        return amount * cryptoMap[crypto_id].price <= <?= $user['balance'] ?>;
    }

    function updatePrice() {
        const crypto = document.getElementById('crypto_id').value;
        const price = document.getElementById('price');
        fetch('/api/prices/live')
            .then(res => res.json())
            .then( () => {
                price.value = cryptoMap[crypto].price;
            })
            .catch(err => console.error(err))
    }
</script>

<div class="container">
    <h2>Cryptocurrency market</h2>
    <h3>Make a transaction</h3>
    <form id="form" method="post" action="/api/user/transactions/add">
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label for="crypto_id">Crypto currency</label>
            <select name="crypto_id" id="crypto_id" onchange="updatePrice()">
                <?php foreach ($cryptoMap as $crypto) : ?>
                    <option value="<?= $crypto['id'] ?>"><?= $crypto['symbol'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" id="type">
                <option value="buy">Buy</option>
                <option value="sell">Sell</option>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" min="0" step="0.001" value="0"/>
            <span id="amount-error" style="color: red" hidden></span>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" name="price" id="price" min="0" value="<?= $cryptoMap[1]['price']?>" readonly/>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<!-- NOTE: what should we do with that @Alex -->
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

<script>
    const form = document.getElementById('form');
    const price = document.getElementById('price');
    const error_span = document.getElementById('amount-error');
    const amount = document.getElementById('amount');

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const type = document.getElementById('type').value;
        const crypto_id = document.getElementById('crypto_id').value;
        const amount = document.getElementById('amount').value;

        if (type === 'buy') {
            if (!validateAmountBuying(crypto_id, amount)) {
                document.getElementById('amount-error').innerText = 'Not enough money, you have only: ' + <?= $user['balance'] ?>; // FIXME: always show 5000
                document.getElementById('amount-error').hidden = false;
                return;
            }
        } else if (type === 'sell') {
            if (!validateAmountSelling(crypto_id, amount)) {
                document.getElementById('amount-error').innerText = 'Not enough crypto, you have only: ' + getWalletBalance(crypto_id);
                document.getElementById('amount-error').hidden = false;
                return;
            }
        }

        fetch('/api/user/transactions/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                csrf_token: document.getElementById('csrf_token').value,
                crypto_id: document.getElementById('crypto_id').value,
                type: document.getElementById('type').value,
                amount: document.getElementById('amount').value,
                price: document.getElementById('price').value,
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success === true) {
                    window.location.reload();
                    return;
                }
                throw new Error(data.error);
            })
            .catch(err => console.error(err))
    })

    amount.addEventListener('input', () => {
        if (error_span.hidden) return;
        const amount = document.getElementById('amount').value;
        const type = document.getElementById('type').value;
        const crypto_id = document.getElementById('crypto_id').value;
        if ((type === 'buy' && validateAmountBuying(crypto_id, amount)) || (type === 'sell' && validateAmountSelling(crypto_id, amount))) {
            console.log('valid');
            error_span.hidden = true;
            error_span.innerText = '';
            return;
        }
        console.log('invalid');
        console.log(crypto_id, amount, type);
    })


</script>
