<?php
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\UserService;

$authUser = JWTService::verifyJWT();

if (!$authUser || $authUser['role'] !== 'admin') {
    header("Location: /unauthorized");
    exit;
}

$userService = new UserService();
$allUsers = $userService->getAllUsers();
?>

<div class="main-content">
    <h2>User Management Panel</h2>

    <table class="wallet-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>2FA</th>
            <th>Balance</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($allUsers as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u->id) ?></td>
                <td><?= htmlspecialchars($u->email) ?></td>
                <td><?= htmlspecialchars($u->role) ?></td>
                <td><?= $u->two_factor_enabled ? 'Yes' : 'No' ?></td>
                <td><?= number_format($u->balance, 2) ?> USD</td>
                <td>
                    <button class="btn-primary edit-user-btn" data-id="<?= htmlspecialchars($u->id) ?>">
                        Edit
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <!-- Dynamic edit panel -->
    <div id="edit-panel" style="display: none; margin-top: 30px;">
        <h3>Edit User</h3>
        <?php include "views/components/user_form.php"; ?>
    </div>
</div>
