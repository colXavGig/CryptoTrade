<?php
use CryptoTrade\Services\{
    JWTService,
    AlertService,
    UserService,
    MarketPriceService
};

$auth = JWTService::verifyJWT();
if ($auth['role'] !== 'admin') {
    echo '<p>Access denied.</p>';
    return;
}

$alertService = new AlertService();
$userService = new UserService();
$priceService = new MarketPriceService();

$alerts = $alertService->getAll();
$users = $userService->getAllUsers(); // user_id → User object

try {
    $prices = $priceService->getLatestPrices(); // crypto_id → array with name, price, etc.
} catch (Exception $e) {
    echo '<p>Error loading prices: ' . htmlspecialchars($e->getMessage()) . '</p>';
    return;
}
?>

<div class="admin-section">
    <h2>All User Alerts</h2>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Crypto</th>
            <th>Threshold</th>
            <th>Type</th>
            <th>Status</th>
            <th>Last Triggered</th>
            <th>Last Price</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($alerts as $a): ?>
            <?php
            $userEmail = isset($users[$a['user_id']]) ? $users[$a['user_id']]->email : "User #{$a['user_id']}";
            $crypto = $prices[$a['crypto_id']] ?? null;
            $cryptoName = $crypto['name'] ?? "Crypto #{$a['crypto_id']}";
            $cryptoValue = isset($crypto['price']) ? number_format((float)$crypto['price'], 2, '.', ',') . ' USD' : '-';
            ?>
            <tr class="alert-row <?= $a['active'] ? 'active' : 'inactive' ?>">
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($userEmail) ?></td>
                <td><?= htmlspecialchars($cryptoName) ?></td>
                <td><?= number_format((float)$a['price_threshold'], 2, '.', ',') ?></td>
                <td><?= ucfirst($a['alert_type']) ?></td>
                <td><?= $a['active'] ? 'Active' : 'Inactive' ?></td>
                <td><?= $a['last_triggered_at'] ?? '-' ?></td>
                <td><?= $cryptoValue ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
