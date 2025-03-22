<?php
return [
    // Public Routes (No Authentication Required)
    'home' => 'views/pages/home.php',
    'about' => 'views/pages/about.php',
    'contact' => 'views/pages/contact.php',
    'login' => 'views/pages/login.php',
    'register' => 'views/pages/register.php',
    'profile' => 'views/pages/profile.php',
    'email-verification' => 'views/pages/email-verification.php', // Email verification page (after registration) with form to enter token
    '404' => 'views/pages/404.php',

    // -------------------------------------------USER ROUTES-------------------------------------------
    // Public API Routes (No JWT Required)
    'api/user/register' => 'core/controllers/UserController.php@register',
    'api/user/login' => 'core/controllers/UserController.php@login',
    'api/user/logout' => 'core/controllers/UserController.php@logout',
    'api/user/verify-email' => 'core/controllers/UserController.php@verifyEmail', // used for email verification with email token
    // Protected API Routes (JWT Required)
    'api/user/verify' => 'core/controllers/UserController.php@verify', // Authenticated users
    'api/user/getByEmail' => 'core/controllers/UserController.php@getUserByEmail', // Authenticated users
    'api/user/update' => 'core/controllers/UserController.php@update', // Authenticated users
    'api/user/reset-password' => 'core/controllers/UserController.php@resetPassword', // Authenticated users
    // Admin-Only Routes (JWT & Admin Role Required)
    'api/user/getAll' => 'core/controllers/UserController.php@getAll',
    'api/user/delete' => 'core/controllers/UserController.php@delete',

    
];

?>
