<?php
use CryptoTrade\Services\JWTService;
$authUser = JWTService::verifyJWT();
?>
<div class="container">
    <h2>Transaction History</h2>

    <table id="transactions-table" class="wallet-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Type<br>
                <select id="filter-type">
                    <option value="">All</option>
                    <option value="buy">Buy</option>
                    <option value="sell">Sell</option>
                </select>
            </th>
            <th>Crypto<br><input type="number" id="filter-crypto" placeholder="ID"></th>
            <th>Amount<br>
                <input type="number" id="min-amount" placeholder="Min" step="0.00000001"><br>
                <input type="number" id="max-amount" placeholder="Max" step="0.00000001">
            </th>
            <th>Price<br>
                <input type="number" id="min-price" placeholder="Min" step="0.01"><br>
                <input type="number" id="max-price" placeholder="Max" step="0.01">
            </th>
            <th>Total</th>
            <th>Date<br>
                <input type="date" id="start-date"><br>
                <input type="date" id="end-date">
            </th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="pagination-controls">
        <button id="prev-page">« Prev</button>
        <span id="page-info"></span>
        <button id="next-page">Next »</button>
    </div>
</div>
