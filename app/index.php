<?php
// autoload
require_once __DIR__ . '/vendor/autoload.php';
// Generate CSRF token and store it in session (start session if not already started)


require_once __DIR__ . '/core/services/CSRFService.php'; // added back the CSRFService.php file require to fix the namespace issue
use CryptoTrade\Services\CSRFService;
CSRFService::generateToken();
// routing logic (frontend routes and API routes detection)
require_once __DIR__ . '/core/services/routing_service.php';
?>

<!-- HTML content -->
<?php require_once 'views/components/header.php'; ?>
<nav> <?php require_once 'views/components/navbar.php'; ?> </nav>
<div class="main-content"><?php require $page; ?></div>
<?php require_once 'views/components/footer.php'; ?>



<!-- CSRF token for AJAX requests -->
<script>const CSRF_TOKEN = "<?= $_SESSION['csrf_token']; ?>";</script>
<script src="views/static/app.js"></script>
<script src="views/static/chart_viewer.js"></script>
<script src="views/static/live_prices.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
