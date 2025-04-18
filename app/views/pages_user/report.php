
<?php
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\ReportService;

// Get user from JWT token
$jwtService = new JWTService();
$jwt = $_SESSION['jwt'] ?? null;

if (!$jwt) {
    header('Location: /login.php');
    exit;
}

$user = $jwtService::getUserFromToken($jwt);
$reportService = new ReportService();
$report = $reportService->getUserReport((int) $user['user_id']);
$summary = $report['summary'];
?>

<h2>User Report</h2>
<!-- Export Button -->
<form method="POST" action="api/pdf/report">
    <input type="hidden" name="type" value="user">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <button type="submit">Download PDF Report</button>
</form>

<h3>Wallet Summary</h3>
<p>Total Wallet Value (USD): <strong><?= number_format($summary['wallet']['total_value_usd'], 2) ?></strong></p>
<ul>
    <?php foreach ($summary['wallet']['per_crypto'] as $crypto): ?>
        <li>
            <?= htmlspecialchars($crypto['symbol'] ?? 'N/A') ?>:
            <?= htmlspecialchars($crypto['balance'] ?? '0') ?>
            (USD <?= number_format($crypto['value_usd'] ?? 0, 2) ?>)
        </li>
    <?php endforeach; ?>
</ul>
<h3>Transaction Summary</h3>
<ul>
    <li>Total Transactions: <?= htmlspecialchars($summary['transactions']['total_transactions']) ?></li>
    <li>Buy Orders: <?= htmlspecialchars($summary['transactions']['buy_count']) ?></li>
    <li>Sell Orders: <?= htmlspecialchars($summary['transactions']['sell_count']) ?></li>
    <li>Total Volume: USD <?= number_format($summary['transactions']['total_volume'], 2) ?></li>
    <li>Average Transaction Value: USD <?= number_format($summary['transactions']['avg_transaction_value'], 2) ?></li>
</ul>

<h3>ROI</h3>
<p>Return on Investment: <strong><?= number_format($summary['roi'], 2) ?>%</strong></p>

<h3>Top Traded Crypto</h3>
<p><strong><?= htmlspecialchars($summary['top_crypto']) ?></strong></p>

<h3>Alerts</h3>
<ul>
    <li>Total Alerts: <?= htmlspecialchars($summary['alerts']['total_alerts']) ?></li>
    <li>Active Alerts: <?= htmlspecialchars($summary['alerts']['active_alerts']) ?></li>
</ul>

<h3>Recent Activity</h3>
<table border="1" cellpadding="6">
    <thead>
    <tr>
        <th>Date</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($report['logs'] as $log): ?>
        <tr>
            <td><?= $log->created_at instanceof DateTime ? $log->created_at->format('Y-m-d H:i:s') : htmlspecialchars($log->created_at) ?></td>
            <td><?= htmlspecialchars($log->action) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>