<?php
/* this page is not used in development environment */

// if session is not started, start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Initialize user variables
$user_name = '';
$user_email = '';

// Check if the user is logged in
if (isset($_SESSION['jwt'])) {
    $user = JWTService::getUserFromToken($_SESSION['jwt']);
    if ($user) {
        $user_name = $user['email']; // Use email as name
        $user_email = $user['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    if (!$email) {
        echo 'Invalid email format!';
    } else {
        // Prepare email headers to prevent injection attacks
        $to = 'a3emond@gmail.com';
        $subject = 'Contact Form Submission';
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Email content
        $body = "Name: $name\nEmail: $email\nMessage:\n$message";

        // Send email and confirm
        if (mail($to, $subject, $body, $headers)) {
            echo 'Thank you for your message!';
        } else {
            echo 'Error: Unable to send message. Try again later.';
        }
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
    <label for="name">Name:</label><br>
    <input type="text" name="name" id="name" required value="<?= $user_name ?>">
    <br>
    <label for="email">Email:</label><br>
    <input type="email" name="email" id="email" required value="<?= $user_email ?>">
    <br>
    <label for="message">Message:</label><br>
    <textarea name="message" id="message" required></textarea>
    <br>
    <input type="submit" value="Send">
</form>
