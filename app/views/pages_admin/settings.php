<?php
use CryptoTrade\Services\AdminSettingService;

$csrfToken = $_SESSION['csrf_token'] ?? '';
$service = new AdminSettingService();
$settings = $service->getAllSettings();

// Optional: descriptive tooltips for settings
$settingTooltips = [
    'max_transactions_per_day'       => 'Maximum number of transactions a user can make per day.',
    'min_deposit_amount'             => 'Minimum USD amount required for a deposit.',
    '2fa_required'                   => 'Require two-factor authentication for user login.',
    'auto_sell_threshold_percentage' => 'Trigger auto-sell when crypto drops below this percentage.',
    'invitation_required'            => 'Require an invitation token to register.',
    'initial_account_balance_usd'    => 'USD balance assigned to new user accounts.',
    'price_update_interval'          => 'Seconds between crypto price simulation updates.',
    'audit_log_retention_days'       => 'Number of days to retain audit logs.',
    'max_failed_logins'              => 'Number of failed logins before lockout.',
    'stripe_test_mode'               => 'Enable test mode for Stripe payments.'
];
?>

<h2>Admin Settings</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
    <tr>
        <th>Setting Key</th>
        <th>Value</th>
        <th>Update</th>
        <th>Delete</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($settings as $setting): ?>
        <tr>
            <!-- Update Form -->
            <form method="POST" action="api/admin/settings/update">
                <td title="<?= htmlspecialchars($settingTooltips[$setting->setting_key] ?? '') ?>">
                    <?= htmlspecialchars($setting->setting_key) ?>
                </td>
                <td>
                    <?php
                    $val = strtolower($setting->setting_value);
                    $isBool = in_array($val, ['true', 'false'], true);
                    ?>
                    <?php if ($isBool): ?>
                        <label>
                            <input type="radio" name="setting_value" value="true" <?= $val === 'true' ? 'checked' : '' ?>> true
                        </label>
                        <label>
                            <input type="radio" name="setting_value" value="false" <?= $val === 'false' ? 'checked' : '' ?>> false
                        </label>
                    <?php else: ?>
                        <input type="text" name="setting_value" value="<?= htmlspecialchars($setting->setting_value) ?>">
                    <?php endif; ?>
                    <input type="hidden" name="setting_key" value="<?= htmlspecialchars($setting->setting_key) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                </td>
                <td>
                    <button type="submit">Update</button>
                </td>
            </form>

            <!-- Delete Form -->
            <form method="POST" action="api/admin/settings/delete">
                <td>
                    <input type="hidden" name="setting_key" value="<?= htmlspecialchars($setting->setting_key) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <button type="submit" onclick="return confirm('Are you sure you want to delete this setting?')">Delete</button>
                </td>
            </form>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Add New Setting -->
<h3>Add New Setting</h3>
<form method="POST" action="api/admin/settings/update">
    <label>Key:
        <input type="text" name="setting_key" required>
    </label>
    <label>Value:
        <input type="text" name="setting_value" required>
    </label>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <button type="submit">Add</button>
</form>
