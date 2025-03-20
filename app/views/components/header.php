<?php
// Define page title dynamically
$pageTitles = [
    'home' => 'Home',
    'about' => 'About Us',
    'contact' => 'Contact Us',
    'login' => 'Login',
    'register' => 'Register',
    'profile' => 'Profile',
    '404' => 'Page Not Found :('
];

$pageTitle = $pageTitles[$route] ?? 'CRYPTO-TRADE';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $pageTitle; ?></title>
    
    <link rel="stylesheet" href="views/static/style.css">
</head>
<body>

<header>
    <h1>CRYPTO-TRADE</h1>
</header>
