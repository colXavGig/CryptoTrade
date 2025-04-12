<?php


return [
    // Public Routes (No Authentication Required)
    'home' => 'views/pages_public/home.php',
    'about' => 'views/pages_public/about.php',
    'contact' => 'views/pages_public/contact.php',
    'login' => 'views/pages_public/login.php',
    'register' => 'views/pages_public/register.php',
    'profile' => 'views/pages_public/profile.php',
    'email-verification' => 'views/pages_public/email-verification.php',
    '404' => 'views/pages_public/404.php',

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
