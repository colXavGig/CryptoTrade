<?php
return [
    // Public Routes (No Authentication Required)
    'home' => 'views/pages/home.php',
    'about' => 'views/pages/about.php',
    'contact' => 'views/pages/contact.php',
    'login' => 'views/pages/login.php',
    'register' => 'views/pages/register.php',
    'profile' => 'views/pages/profile.php',
    '404' => 'views/pages/404.php',

    // -------------------------------------------USER ROUTES-------------------------------------------
    // Public API Routes (No JWT Required)
    'api/user/register' => 'core/controllers/user_controller.php@register',
    'api/user/login' => 'core/controllers/user_controller.php@login',
    'api/user/logout' => 'core/controllers/user_controller.php@logout',
    // Protected API Routes (JWT Required)
    'api/user/verify' => 'core/controllers/user_controller.php@verify', // Authenticated users
    'api/user/getByEmail' => 'core/controllers/user_controller.php@getUserByEmail', // Authenticated users
    'api/user/update' => 'core/controllers/user_controller.php@update', // Authenticated users
    // Admin-Only Routes (JWT & Admin Role Required)
    'api/user/getAll' => 'core/controllers/user_controller.php@getAll',
    'api/user/delete' => 'core/controllers/user_controller.php@delete',

    
];

?>
