<?php


return [
    // ----------------------------------- FRONTEND ROUTING -----------------------------------
    // **IMPORTANT**: Do not put forward slashes in the route names, they are getting sanitized
    // Public Routes (No Authentication Required)
    'home' => 'views/pages_public/home.php',
    'about' => 'views/pages_public/about.php',
    'contact' => 'views/pages_public/contact.php',
    'login' => 'views/pages_public/login.php',
    'register' => 'views/pages_public/register.php',
    'email-verification' => 'views/pages_public/email-verification.php',
    '404' => 'views/pages_public/404.php',

    // common (all logged-in users)
    'profile' => 'views/pages_public/profile.php',
    // User Dashboard Routes (JWT Required)
    'user-dashboard' => 'views/pages_user/dashboard.php',
    'user-alerts' => 'views/pages_user/alerts.php',
    'user-wallet' => 'views/pages_user/wallet.php',
    'user-market' => 'views/pages_user/market.php',
    'user-history' => 'views/pages_user/history.php',
    'user-report' => 'views/pages_user/report.php',
    'user-checkout' => 'views/pages_user/add_balance.php',
//    'user-payment' => 'views/pages_user/payment.php',

// Admin Dashboard Routes (JWT + Admin Role Required)
    'admin-dashboard' => 'views/pages_admin/dashboard.php',
    'admin-users' => 'views/pages_admin/users.php',
    'admin-transactions' => 'views/pages_admin/transactions.php',
    'admin-settings' => 'views/pages_admin/settings.php',
    'admin-alerts' => 'views/pages_admin/alerts.php',
    'admin-report' => 'views/pages_admin/report.php',
    'admin-logs' => 'views/pages_admin/logs.php',
    'admin-cryptos' => 'views/pages_admin/cryptos.php',
//    'admin-payments' => 'views/pages_admin/payments.php',


    // ---------------------------- USER API ROUTES (PSR-4 autoloaded) ----------------------------
    //**IMPORTANT**: the user API routes are prefixed with "api/user" to avoid conflicts with other APIs
    // format controller@functions is a convention used in the project (parsed by the custom-made router)

    // Public API Routes (No JWT Required)
    'api/user/register' => 'CryptoTrade\Controllers\UserController@register',
    'api/user/login' => 'CryptoTrade\Controllers\UserController@login',
    'api/user/logout' => 'CryptoTrade\Controllers\UserController@logout',
    'api/user/verify-email' => 'CryptoTrade\Controllers\UserController@confirmEmail',
    'api/user/reset-password' => 'CryptoTrade\Controllers\UserController@resetPassword',
    'api/user/resend-verification-email' => 'CryptoTrade\Controllers\UserController@resendVerificationEmail',
    'api/user/checkout' => 'CryptoTrade\Controllers\CheckoutController@checkout',                   // NOTE: not sure if we should
    'api/user/checkout-status' => 'CryptoTrade\Controllers\CheckoutController@sessionStatus',       //  verify those two

    // Protected API Routes (JWT Required)
    'api/user/verify' => 'CryptoTrade\Controllers\UserController@verify',
    'api/user/getByEmail' => 'CryptoTrade\Controllers\UserController@getUserByEmail',
    'api/user/getById' => 'CryptoTrade\Controllers\UserController@getUserById',
    'api/user/update' => 'CryptoTrade\Controllers\UserController@update',

    // Admin-Only Routes (JWT + Admin Role Required)
    'api/user/getAll' => 'CryptoTrade\Controllers\UserController@getAll',
    'api/user/delete' => 'CryptoTrade\Controllers\UserController@delete',

    // ---------------------------- MARKET PRICE API ROUTES (PSR-4 autoloaded) ----------------------------
    //**IMPORTANT**: the market price changes are handled by a daemon service that runs in the background
    // and updates the database. The API endpoints here are for fetching the data only.
    // public endpoints
    'api/prices/live' => 'CryptoTrade\Controllers\MarketPriceController@getLivePrices',
    'api/prices/chart' => 'CryptoTrade\Controllers\MarketPriceController@getChartData',
    'api/prices/with_previous' => 'CryptoTrade\Controllers\MarketPriceController@getWithPrevious',

    // ---------------------------- USER WALLET API ROUTES (PSR-4 autoloaded) ----------------------------
    //hybrid endpoints -> accept a user_id param for admin or use the logged-in user
    'api/user/wallet' => 'CryptoTrade\Controllers\UserWalletController@getMyWallet',
    'api/user/wallet/update' => 'CryptoTrade\Controllers\UserWalletController@updateWallet',
    'api/user/wallet/delete' => 'CryptoTrade\Controllers\UserWalletController@deleteWallet',

    // ---------------------------- TRANSACTION API ROUTES (PSR-4 autoloaded) ----------------------------
    'api/user/transactions' => 'CryptoTrade\Controllers\TransactionController@getMyTransactions',
    'api/user/transactions/add' => 'CryptoTrade\Controllers\TransactionController@addTransaction',
    'api/user/transactions/sell' => 'CryptoTrade\Controllers\TransactionController@sellCrypto',
    'api/user/transactions/sell_all' => 'CryptoTrade\Controllers\TransactionController@sellAll',
    'api/user/transactions/buy' => 'CryptoTrade\Controllers\TransactionController@buyCrypto',
    'api/user/transactions/getById' => 'CryptoTrade\Controllers\TransactionController@getTransactionById',
    'api/user/transactions/getAll' => 'CryptoTrade\Controllers\TransactionController@getAllTransactions',
    'api/user/transactions/getByUserId' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByUserId',
    'api/user/transactions/getByCryptoId' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByCryptoId',
    'api/user/transactions/getByType' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByType',
    'api/user/transactions/getByDate' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByDate',
    'api/user/transactions/getByDateRange' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByDateRange',
    'api/user/transactions/getByPriceRange' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByPriceRange',
    'api/user/transactions/getByAmountRange' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByAmountRange',
    'api/user/transactions/getByTransactionId' => 'CryptoTrade\Controllers\TransactionController@getTransactionsByTransactionId',

    // ---------------------------- NOTIFICATION API ROUTES (PSR-4 autoloaded) ----------------------------
    'api/user/notifications/unseen' => 'CryptoTrade\Controllers\NotificationController@getUnseen',
    'api/user/notifications/all' => 'CryptoTrade\Controllers\NotificationController@getAll',
    'api/user/notifications/mark_seen' => 'CryptoTrade\Controllers\NotificationController@markSeen',

    // ---------------------------- ALERT API ROUTES (PSR-4 autoloaded) ----------------------------
    'api/user/alerts/create' => 'CryptoTrade\Controllers\AlertController@create',
    'api/user/alerts/delete' => 'CryptoTrade\Controllers\AlertController@delete',
    'api/user/alerts/getAll' => 'CryptoTrade\Controllers\AlertController@getAll', // Admin use
    'api/user/alerts/getById' => 'CryptoTrade\Controllers\AlertController@getById',
    'api/user/alerts/getByUserId' => 'CryptoTrade\Controllers\AlertController@getByUserId',
    'api/user/alerts/update' => 'CryptoTrade\Controllers\AlertController@update',
    'api/user/alerts/toggle' => 'CryptoTrade\Controllers\AlertController@toggleStatus',

    // ---------------------------- ADMIN SETTINGS API ROUTES (PSR-4 autoloaded) ----------------------------
    'api/admin/settings/getAll' => 'CryptoTrade\Controllers\AdminSettingController@getAll',
    'api/admin/settings/update' => 'CryptoTrade\Controllers\AdminSettingController@update',
    'api/admin/settings/delete' => 'CryptoTrade\Controllers\AdminSettingController@delete',

    // ---------------------------- ADMIN/USER REPORT API ROUTES (PSR-4 autoloaded) ----------------------------
    'api/user/report' => 'CryptoTrade\Controllers\ReportController@getUserReport',
    'api/admin/report' => 'CryptoTrade\Controllers\ReportController@getAdminReport',
    'api/pdf/report' => 'CryptoTrade\Controllers\ReportController@downloadPDFReport',
];

?>
