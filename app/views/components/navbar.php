<?php
use CryptoTrade\Services\JWTService;

// Initialize user as null
$user = null;

// Attempt to decode JWT if set
if (isset($_SESSION['jwt'])) {
    $decoded = JWTService::getUserFromToken($_SESSION['jwt']);

    if (!isset($decoded['error'])) {
        $user = $decoded;
    } else {
        // Expired or invalid token – clean session and cookie
        unset($_SESSION['jwt']);
        setcookie('jwt', '', time() - 3600, '/');
    }
}
?>

<nav>
    <!-- Hamburger Button -->
    <button id="hamburger-btn" aria-label="Toggle Navigation">☰</button>

    <div id="nav-container">
        <!-- First Row: Public + Shared -->
        <ul class="nav-row">
            <li><a href="home" class="spa-link">Home</a></li>
            <li><a href="about" class="spa-link">About</a></li>
            <li><a href="contact" class="spa-link">Contact</a></li>

            <?php if ($user): ?>
                <li><strong>Welcome <?= htmlspecialchars($user['email']) ?></strong></li>
                <li><a href="#" id="logout-link">Logout</a></li>
            <?php else: ?>
                <li><a href="login" class="spa-link">Login</a></li>
                <li><a href="register" class="spa-link">Register</a></li>
            <?php endif; ?>
        </ul>

        <!-- Second Row: Role-Specific -->
        <?php if ($user): ?>
            <ul class="nav-row secondary">
                <li><a href="profile" class="spa-link">Profile</a></li>

                <?php if ($user['role'] === 'user'): ?>
                    <li><a href="user/dashboard" class="spa-link">Dashboard</a></li>
                    <li><a href="user/wallet" class="spa-link">Wallet</a></li>
                    <li><a href="user/market" class="spa-link">Market</a></li>
                    <li><a href="user/history" class="spa-link">History</a></li>
                    <li><a href="user/alerts" class="spa-link">Alerts</a></li>
                    <li><a href="user/report" class="spa-link">Report</a></li>

                <?php elseif ($user['role'] === 'admin'): ?>
                    <li><a href="admin/dashboard" class="spa-link">Dashboard</a></li>
                    <li><a href="admin/users" class="spa-link">Users</a></li>
                    <li><a href="admin/transactions" class="spa-link">Transactions</a></li>
                    <li><a href="admin/alerts" class="spa-link">Alerts</a></li>
                    <li><a href="admin/cryptos" class="spa-link">Cryptos</a></li>
                    <li><a href="admin/payments" class="spa-link">Payments</a></li>
                    <li><a href="admin/logs" class="spa-link">Logs</a></li>
                    <li><a href="admin/settings" class="spa-link">Settings</a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</nav>

<script>
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const navContainer = document.getElementById('nav-container');

    hamburgerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        navContainer.classList.toggle('active');
    });

    navContainer.querySelectorAll('a.spa-link').forEach(link => {
        link.addEventListener('click', () => {
            navContainer.classList.remove('active');
        });
    });

    document.addEventListener('click', (e) => {
        const isClickInside = navContainer.contains(e.target) || hamburgerBtn.contains(e.target);
        if (!isClickInside && navContainer.classList.contains('active')) {
            navContainer.classList.remove('active');
        }
    });
</script>
