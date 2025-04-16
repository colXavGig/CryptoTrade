<?php
use CryptoTrade\Services\JWTService;
$user = JWTService::verifyJWT();
$isAdmin = $user['role'] === 'admin';
?>

<div class="user-form">
    <form id="user-form">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <input type="hidden" name="id" id="user-id">

        <label>Email:</label>
        <input type="email" name="email" id="user-email" required>

        <label>Password:</label>
        <input type="password" name="password" id="user-password" placeholder="Leave blank to keep current">

        <?php if ($isAdmin): ?>
            <label>Role:</label>
            <select name="role" id="user-role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <label>Balance (USD):</label>
            <input type="number" step="0.01" name="balance" id="user-balance" min="0">



            <label>2FA:</label>
            <select name="two_factor_enabled" id="user-2fa">
                <option value="false">Disabled</option>
                <option value="true">Enabled</option>
            </select>
        <?php else: ?>
            <input type="hidden" name="role" value="user">
            <input type="hidden" name="two_factor_enabled" value="<?= $user['two_factor_enabled'] ? 'true' : 'false' ?>">
        <?php endif; ?>

        <button type="submit" class="btn-primary">Save</button>
        <?php if ($isAdmin): ?>
            <button type="button" id="delete-user-btn" class="btn-danger">Delete</button>
        <?php endif; ?>
    </form>
</div>
