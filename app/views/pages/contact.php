<?php
use CryptoTrade\Services\MailService;
use CryptoTrade\Services\JWTService;


// If session not started

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// User info defaults
$user_name = '';
$user_email = '';

// Check session JWT
if (isset($_SESSION['jwt'])) {
    $user = JWTService::getUserFromToken($_SESSION['jwt']);
    if ($user) {
        $user_name = $user['email'];
        $user_email = $user['email'];
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    if (!$email) {
        echo 'Invalid email format!';
    } else {
        $to = 'a3emond@gmail.com';
        $subject = 'Contact Form Submission';
        $body = "Name: $name\nEmail: $email\nMessage:\n$message";

        // Send via MailService
        $mailer = new MailService();
        $sent = $mailer->send($to, $subject, $body);

        echo $sent ? 'Thank you for your message!' : 'Error: Unable to send message. Try again later.';
    }
}
?>

<h1>Contact Us</h1>

<style>
    form {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 500px;
    }

    label {
        margin-top: 1rem;
    }

    input, textarea {
        padding: 0.5rem;
        margin-top: 0.5rem;
        width: 100%;
    }

    input[type="submit"] {
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }
</style>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user_name) ?>">

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user_email) ?>">

    <label for="message">Message:</label>
    <textarea name="message" id="message" required></textarea>

    <input type="submit" value="Send">
</form>
