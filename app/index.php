<?php
// autoload
require_once __DIR__ . '/vendor/autoload.php';
// Generate CSRF token and store it in session (start session if not already started)


require_once __DIR__ . '/core/Services/CSRFService.php'; // added back the CSRFService.php file require to fix the namespace issue
use CryptoTrade\Services\CSRFService;
CSRFService::generateToken();
// routing logic (frontend routes and API routes detection)
require_once __DIR__ . '/core/Services/routing_service.php';
?>

<!-- HTML content -->
<?php require_once 'views/components/header.php'; ?>
<nav> <?php require_once 'views/components/navbar.php'; ?> </nav>
<div class="main-content"><?php require $page; ?></div>
<?php require_once 'views/components/footer.php'; ?>



<!-- CSRF token for AJAX requests -->
<script>const CSRF_TOKEN = "<?= $_SESSION['csrf_token']; ?>";</script>
<!-- index.php or layout.php or wherever your scripts are included -->
<script type="module"  src="/views/static/chart_viewer.js"></script>
<script type="module"  src="/views/static/live_prices.js"></script>
<script type="module" src="/views/static/transactionHistory.js"></script>
<script type="module" src="/views/static/user_form.js"></script>
<script type="module" src="/views/static/admin_users.js"></script>
<script type="module" src="/views/static/notification.js"></script>
<script type="module" src="/views/static/user_alerts.js"></script>
<script type="module" src="/views/static/transaction_form.js"></script>
<script type="module" src="/views/static/app.js"></script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
