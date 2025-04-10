<?php


return [
    // Public Routes (No Authentication Required)
    'home' => 'views/pages/home.php',
    'about' => 'views/pages/about.php',
    'contact' => 'views/pages/contact.php',
    'login' => 'views/pages/login.php',
    'register' => 'views/pages/register.php',
    'profile' => 'views/pages/profile.php',
    'email-verification' => 'views/pages/email-verification.php',
    '404' => 'views/pages/404.php',

    // ---------------------------- USER API ROUTES (PSR-4 autoloaded) ----------------------------

    // Public API Routes (No JWT Required)
    'api/user/register' => 'CryptoTrade\Controllers\UserController@register',
    'api/user/login' => 'CryptoTrade\Controllers\UserController@login',
    'api/user/logout' => 'CryptoTrade\Controllers\UserController@logout',
    'api/user/verify-email' => 'CryptoTrade\Controllers\UserController@verifyEmail',
    'api/user/reset-password' => 'CryptoTrade\Controllers\UserController@resetPassword',
    'api/user/resend-verification-email' => 'CryptoTrade\Controllers\UserController@resendVerificationEmail',

    // Protected API Routes (JWT Required)
    'api/user/verify' => 'CryptoTrade\Controllers\UserController@verify',
    'api/user/getByEmail' => 'CryptoTrade\Controllers\UserController@getUserByEmail',
    'api/user/update' => 'CryptoTrade\Controllers\UserController@update',

    // Admin-Only Routes (JWT + Admin Role Required)
    'api/user/getAll' => 'CryptoTrade\Controllers\UserController@getAll',
    'api/user/delete' => 'CryptoTrade\Controllers\UserController@delete',
];

?>
