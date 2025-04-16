<?php
use CryptoTrade\Services\JWTService;


$authUser = JWTService::verifyJWT();

if (!$authUser || isset($authUser['error'])) {
    header("Location: /login");
    exit;
}
?>

<div class="main-content">
    <h2>My Profile</h2>

    <?php include "views/components/user_form.php"; ?>

    <p class="note">Leave the password field empty if you don't want to change it.</p>
</div>
