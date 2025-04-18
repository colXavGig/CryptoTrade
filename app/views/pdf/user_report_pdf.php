<?php
    // views/pdf/user_report_pdf.php
    use CryptoTrade\Services\ReportService;
    use CryptoTrade\Services\JWTService;

    // Get user from JWT token
    $jwtService = new JWTService();
    $jwt = $_SESSION['jwt'] ?? null;
    $user = $jwt ? $jwtService::getUserFromToken($jwt) : null;

    $reportService = new ReportService();
    $report = $reportService->getUserReport($user['id'] ?? 0);
    ?>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2, h3 { margin-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
    </style>

    <h2>User Report for <?= htmlspecialchars($user['email'] ?? 'Unknown') ?></h2>

    <h3>Wallet Summary</h3>
    <p>Total Wallet Value (USD): <strong><?= number_format($report['summary']['wallet']['total_value_usd'] ?? 0, 2) ?></strong></p>
    <table>
        <thead>
        <tr>
            <th>Crypto</th>
            <th>Balance</th>
            <th>Value (USD)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($report['summary']['wallet']['per_crypto'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['symbol'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($item['balance'] ?? '0') ?></td>
                <td><?= number_format($item['value_usd'] ?? 0, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Transaction Summary</h3>
    <ul>
        <li>Total Transactions: <?= htmlspecialchars($report['summary']['transactions']['total_transactions'] ?? '0') ?></li>
        <li>Buy Orders: <?= htmlspecialchars($report['summary']['transactions']['buy_count'] ?? '0') ?></li>
        <li>Sell Orders: <?= htmlspecialchars($report['summary']['transactions']['sell_count'] ?? '0') ?></li>
        <li>Total Volume: USD <?= number_format($report['summary']['transactions']['total_volume'] ?? 0, 2) ?></li>
        <li>Average Transaction Value: USD <?= number_format($report['summary']['transactions']['avg_transaction_value'] ?? 0, 2) ?></li>
    </ul>

    <h3>ROI</h3>
    <p><strong><?= number_format($report['summary']['roi'] ?? 0, 2) ?>%</strong></p>

    <h3>Top Traded Crypto</h3>
    <p><strong><?= htmlspecialchars($report['summary']['top_crypto'] ?? 'N/A') ?></strong></p>

    <h3>Alerts</h3>
    <ul>
        <li>Total Alerts: <?= htmlspecialchars($report['summary']['alerts']['total_alerts'] ?? '0') ?></li>
        <li>Active Alerts: <?= htmlspecialchars($report['summary']['alerts']['active_alerts'] ?? '0') ?></li>
    </ul>

    <h3>Recent Activity</h3>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($report['logs'] as $log): ?>
            <tr>
                <td><?= $log->created_at instanceof DateTime ? $log->created_at->format('Y-m-d H:i:s') : htmlspecialchars($log->created_at ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($log->action ?? 'N/A') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>