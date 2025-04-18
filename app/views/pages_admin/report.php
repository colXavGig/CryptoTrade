<?php
use CryptoTrade\Services\ReportService;

$reportService = new ReportService();
$report = $reportService->getAdminReport();
?>

<h2>Admin Report</h2>

<!-- Export Button -->
<form method="POST" action="api/pdf/report">
    <input type="hidden" name="type" value="admin">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <button type="submit">Download PDF Report</button>
</form>

<canvas id="barVolumeByCrypto"></canvas>
<canvas id="lineDailyVolume"></canvas>
<canvas id="pieAlerts"></canvas>

<h3>Platform Overview</h3>
<ul>
    <li>Total Users: <?= $report['totalUsers'] ?></li>
    <li>Active Users Today: <?= $report['activeUsersToday'] ?></li>
    <li>Total Transaction Volume: USD <?= number_format($report['transactionVolume'], 2) ?></li>
    <li>Average Transaction Value: USD <?= number_format($report['avgTransactionValue'], 2) ?></li>
    <li>Failed Logins Today: <?= $report['failedLoginsToday'] ?></li>
</ul>

<h3>Transactions by Crypto</h3>
<ul>
    <?php foreach ($report['transactionsByCrypto'] as $item): ?>
        <li><?= htmlspecialchars($item['symbol']) ?>: USD <?= number_format($item['volume'], 2) ?></li>
    <?php endforeach; ?>
</ul>

<h3>Top Users by Volume</h3>
<ul>
    <?php foreach ($report['topUsers'] as $user): ?>
        <li><?= htmlspecialchars($user['email']) ?> - USD <?= number_format($user['volume'], 2) ?></li>
    <?php endforeach; ?>
</ul>

<h3>Most Traded Cryptos</h3>
<ul>
    <?php foreach ($report['mostTradedCryptos'] as $crypto): ?>
        <li><?= htmlspecialchars($crypto['symbol']) ?> (<?= $crypto['count'] ?> trades)</li>
    <?php endforeach; ?>
</ul>

<h3>Alerts</h3>
<ul>
    <li>Total Alerts: <?= $report['alertSummary']['total_alerts'] ?></li>
    <li>Active Alerts: <?= $report['alertSummary']['active_alerts'] ?></li>
    <li>Triggered Alerts: <?= $report['alertSummary']['triggered_alerts'] ?></li>
</ul>

<h3>Log Action Summary</h3>
<ul>
    <?php foreach ($report['logActionSummary'] as $action => $count): ?>
        <li><?= htmlspecialchars($action) ?>: <?= $count ?></li>
    <?php endforeach; ?>
</ul>

<h3>Recent Logs</h3>
<table border="1" cellpadding="6">
    <thead>
    <tr>
        <th>Date</th>
        <th>Action</th>
        <th>User ID</th>
    </tr>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx1 = document.getElementById('barVolumeByCrypto');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($report['transactionsByCrypto'], 'symbol')) ?>,
            datasets: [{
                label: 'Transaction Volume (USD)',
                data: <?= json_encode(array_column($report['transactionsByCrypto'], 'volume')) ?>,
                borderWidth: 1
            }]
        }
    });

    const ctx2 = document.getElementById('lineDailyVolume');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($report['dailyVolume'], 'date')) ?>,
            datasets: [{
                label: 'Daily Transaction Volume',
                data: <?= json_encode(array_column($report['dailyVolume'], 'volume')) ?>,
                fill: false,
                borderWidth: 2
            }]
        }
    });

    const ctx3 = document.getElementById('pieAlerts');
    new Chart(ctx3, {
        type: 'pie',
        data: {
            labels: ['Active', 'Triggered', 'Others'],
            datasets: [{
                data: [
                    <?= $report['alertSummary']['active_alerts'] ?>,
                    <?= $report['alertSummary']['triggered_alerts'] ?>,
                    <?= max(0, $report['alertSummary']['total_alerts'] - $report['alertSummary']['active_alerts'] - $report['alertSummary']['triggered_alerts']) ?>
                ]
            }]
        }
    });
</script>
