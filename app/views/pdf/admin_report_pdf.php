<?php
// views/pdf/admin_report_pdf.php
use CryptoTrade\Services\ReportService;

$reportService = new ReportService();
$report = $reportService->getAdminReport();
?>
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    h2, h3 { margin-bottom: 5px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #444; padding: 6px; text-align: left; }
</style>

<h2>Admin Report</h2>

<h3>Platform Overview</h3>
<ul>
    <li>Total Users: <?= $report['totalUsers'] ?></li>
    <li>Active Users Today: <?= $report['activeUsersToday'] ?></li>
    <li>Total Transaction Volume: USD <?= number_format($report['transactionVolume'], 2) ?></li>
    <li>Average Transaction Value: USD <?= number_format($report['avgTransactionValue'], 2) ?></li>
    <li>Failed Logins Today: <?= $report['failedLoginsToday'] ?></li>
</ul>

<h3>Transactions by Crypto</h3>
<table>
    <thead>
    <tr><th>Crypto</th><th>Volume (USD)</th></tr>
    </thead>
    <tbody>
    <?php foreach ($report['transactionsByCrypto'] as $item): ?>
        <tr>
            <td><?= $item['symbol'] ?></td>
            <td><?= number_format($item['volume'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Top Users by Volume</h3>
<table>
    <thead>
    <tr><th>Email</th><th>Volume (USD)</th></tr>
    </thead>
    <tbody>
    <?php foreach ($report['topUsers'] as $user): ?>
        <tr>
            <td><?= $user['email'] ?></td>
            <td><?= number_format($user['volume'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Most Traded Cryptos</h3>
<table>
    <thead>
    <tr><th>Crypto</th><th>Trade Count</th></tr>
    </thead>
    <tbody>
    <?php foreach ($report['mostTradedCryptos'] as $crypto): ?>
        <tr>
            <td><?= $crypto['symbol'] ?></td>
            <td><?= $crypto['count'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Alerts</h3>
<ul>
    <li>Total Alerts: <?= $report['alertSummary']['total_alerts'] ?></li>
    <li>Active Alerts: <?= $report['alertSummary']['active_alerts'] ?></li>
    <li>Triggered Alerts: <?= $report['alertSummary']['triggered_alerts'] ?></li>
</ul>

<h3>Log Summary</h3>
<table>
    <thead>
    <tr><th>Action</th><th>Count</th></tr>
    </thead>
    <tbody>
    <?php foreach ($report['recentLogs'] as $log): ?>
        <tr>
            <td><?= $log->created_at instanceof DateTime ? $log->created_at->format('Y-m-d H:i:s') : htmlspecialchars($log->created_at) ?></td>
            <td><?= htmlspecialchars($log->action) ?></td>
            <td><?= htmlspecialchars($log->user_id) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Recent Logs</h3>
<table>
    <thead>
    <tr><th>Date</th><th>Action</th><th>User ID</th></tr>
    </thead>
    <tbody>
    <?php foreach ($report['recentLogs'] as $log): ?>
        <tr>
            <td><?= $log->created_at instanceof DateTime ? $log->created_at->format('Y-m-d H:i:s') : htmlspecialchars($log->created_at) ?></td>
            <td><?= htmlspecialchars($log->action) ?></td>
            <td><?= htmlspecialchars($log->user_id) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>