export default function initTransactionForm() {
    const dataEl = document.getElementById("transaction-data");
    let wallets = {};
    let cryptoMap = {};
    let userBalance = 0;

    if (dataEl) {
        wallets = JSON.parse(dataEl.dataset.wallets || '{}');
        cryptoMap = JSON.parse(dataEl.dataset.cryptomap || '{}');
        userBalance = parseFloat(dataEl.dataset.balance || 0);
    }


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
        const cryptoId = cryptoSelect?.value;
        const amount = parseFloat(amountInput?.value || 0);
        const price = parseFloat(cryptoMap[cryptoId]?.price || 0);
        const wallet = wallets[cryptoId] || { balance: 0 };
        const holding = parseFloat(wallet.balance || 0);
        const holdingValue = holding * price;
        const totalTransaction = amount * price;

        if (usdBalanceEl) usdBalanceEl.textContent = formatCurrency(userBalance);
        if (unitPriceEl) unitPriceEl.textContent = formatCurrency(price);
        if (totalPriceEl) totalPriceEl.textContent = formatCurrency(totalTransaction);
        if (cryptoHoldingEl) cryptoHoldingEl.textContent = `${formatCrypto(holding)} ${cryptoMap[cryptoId]?.symbol || ""} (${formatCurrency(holdingValue)})`;

        if (priceInput) priceInput.value = price;

        if (!errorSpan) return;

        const type = typeSelect?.value;
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

    const form = document.getElementById("form");
    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (!errorSpan.hidden) return;

            fetch(form.action, {
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

        updateTransactionSummary(); // Call immediately when form is initialized

        cryptoSelect?.addEventListener("change", updateTransactionSummary);
        typeSelect?.addEventListener("change", updateTransactionSummary);
        amountInput?.addEventListener("input", updateTransactionSummary);
    }
}
