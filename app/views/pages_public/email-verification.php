
<h2>Email Verification</h2>

<?php if (!empty($_GET['error'])): ?>
    <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form action="api/user/verify-email" method="post">
    <label for="token">Enter the 6-digit token you received by email:</label><br>
    <input type="text" id="token" name="token" required pattern="\d{6}" maxlength="6"><br><br>
    <button type="submit">Verify Email</button>
</form>
