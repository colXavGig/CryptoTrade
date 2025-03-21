<ul>
    <li><a href="home" class="spa-link">Home</a></li>
    <li><a href="about" class="spa-link">About</a></li>
    <li><a href="contact" class="spa-link">Contact</a></li>

    <?php 
    if (isset($_SESSION['jwt'])) :
        require_once __DIR__ . '/../../core/services/jwt_service.php';

        $user = JWTService::getUserFromToken($_SESSION['jwt']);
    ?>
        <li><a href="profile" class="spa-link">Profile</a></li>
        <li><?php echo "Welcome " . $user['email']; ?></li>
        <li><a href="#" id="logout-link">Logout</a></li>

    <?php else : ?>
        <li><a href="login" class="spa-link">Login</a></li>
        <li><a href="register" class="spa-link">Register</a></li>
    <?php endif; ?>
</ul>


