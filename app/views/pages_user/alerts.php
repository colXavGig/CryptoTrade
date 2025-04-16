<div class="main-content">
    <h2>My Alerts</h2>

    <div id="alert-form">
        <h3>Add New Alert</h3>
        <form id="new-alert-form">
            <label for="crypto_id">Crypto:</label>
            <select id="crypto_id" name="crypto_id" required></select>

            <label for="alert_type">Type:</label>
            <select id="alert_type" name="alert_type">
                <option value="higher">Higher than</option>
                <option value="lower">Lower than</option>
            </select>

            <label for="price_threshold">Price Threshold:</label>
            <input type="number" step="0.00000001" name="price_threshold" required>

            <button type="submit">Add Alert</button>
        </form>
    </div>

    <div id="alert-list-section">
        <h3>Your Alerts</h3>
        <table id="alerts-table">
            <thead>
            <tr>
                <th>Crypto</th>
                <th>Type</th>
                <th>Threshold</th>
                <th>Status</th>
                <th>Last Triggered</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody id="alerts-body"></tbody>
        </table>
    </div>
</div>
