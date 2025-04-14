<?php
use CryptoTrade\Services\JWTService;
$authUser = JWTService::verifyJWT();
?>

<div class="container">
    <h2>Transaction History</h2>

    <!-- Filter Controls -->
    <form id="filter-form" class="filter-section">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">


        <label for="type">Type:</label>
        <select name="type" id="type">
            <option value="">All</option>
            <option value="buy">Buy</option>
            <option value="sell">Sell</option>
        </select>

        <label for="crypto_id">Crypto ID:</label>
        <input type="number" name="crypto_id" id="crypto_id" placeholder="e.g., 1">

        <label for="start">Start Date:</label>
        <input type="date" name="start" id="start">

        <label for="end">End Date:</label>
        <input type="date" name="end" id="end">

        <label for="min_price">Min Price:</label>
        <input type="number" step="0.01" name="min_price" id="min_price">

        <label for="max_price">Max Price:</label>
        <input type="number" step="0.01" name="max_price" id="max_price">

        <label for="min_amount">Min Amount:</label>
        <input type="number" step="0.00000001" name="min_amount" id="min_amount">

        <label for="max_amount">Max Amount:</label>
        <input type="number" step="0.00000001" name="max_amount" id="max_amount">

        <button type="submit">Apply Filters</button>
        <button type="button" id="reset-filters">Reset</button>
    </form>

    <!-- Table Display -->
    <table id="transactions-table" class="wallet-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Crypto</th>
            <th>Amount</th>
            <th>Price</th>
            <th>Total Value</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById('filter-form');
        const tableBody = document.querySelector("#transactions-table tbody");

        function fetchAndDisplay(endpoint, postData = {}) {
            postData.csrf_token = CSRF_TOKEN;
            fetch(`/?route=${endpoint}`, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams(postData)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.transactions) {
                        tableBody.innerHTML = data.transactions.map(tx => `
                    <tr>
                        <td>${tx.id}</td>
                        <td>${tx.transaction_type}</td>
                        <td>${tx.crypto_id}</td>
                        <td>${parseFloat(tx.amount).toFixed(8)}</td>
                        <td>${parseFloat(tx.price).toFixed(8)}</td>
                        <td>${(tx.amount * tx.price).toFixed(2)}</td>
                        <td>${new Date(tx.created_at.date).toLocaleString()}</td>
                    </tr>
                `).join('');
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="7">No transactions found.</td></tr>`;
                    }
                });
        }

        // Load all transactions on initial load
        fetchAndDisplay('api/user/transactions');

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const fd = new FormData(form);
            const filters = Object.fromEntries(fd.entries());

            // Use specific endpoints for optimized filtering
            if (filters.type) return fetchAndDisplay('api/user/transactions/getByType', { type: filters.type });
            if (filters.crypto_id) return fetchAndDisplay('api/user/transactions/getByCryptoId', { crypto_id: filters.crypto_id });
            if (filters.start && filters.end) return fetchAndDisplay('api/user/transactions/getByDateRange', { start: filters.start, end: filters.end });
            if (filters.min_price && filters.max_price) return fetchAndDisplay('api/user/transactions/getByPriceRange', { min: filters.min_price, max: filters.max_price });
            if (filters.min_amount && filters.max_amount) return fetchAndDisplay('api/user/transactions/getByAmountRange', { min: filters.min_amount, max: filters.max_amount });

            // Fallback
            fetchAndDisplay('api/user/transactions');
        });

        document.getElementById("reset-filters").addEventListener("click", () => {
            form.reset();
            fetchAndDisplay('api/user/transactions');
        });
    });

</script>