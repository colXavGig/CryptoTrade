<h1>Register</h1>

<form method="POST" action="/api/user/register" id="register-form">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div class="form-group">
        <label for="password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </div>

    <button type="submit">Register</button>
</form>

<p id="error-message" class="fade" style="color: red;"></p>

<style>
    #register-form {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
    }

    input, button {
        width: 100%;
        padding: 0.5rem;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 0.25rem;
    }

    input {
        background: #fff;
    }

    button {
        background: #007bff;
        color: white;
        cursor: pointer;
        border: none;
    }

    button:hover {
        background: #0056b3;
    }

    /* Animation for the error message */
    .fade {
        transition: opacity 0.3s ease-in-out;
        opacity: 0;
        height: 1rem;
    }

    .fade.show {
        opacity: 1;
    }
</style>

<script>
    const form = document.getElementById('register-form');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const errorMessage = document.getElementById('error-message');

    function checkMatch() {
        if (confirmInput.value && passwordInput.value !== confirmInput.value) {
            errorMessage.textContent = "Passwords do not match.";
            errorMessage.classList.add("show");
            return false;
        } else {
            errorMessage.textContent = "";
            errorMessage.classList.remove("show");
            return true;
        }
    }

    // Live typing check
    passwordInput.addEventListener('input', checkMatch);
    confirmInput.addEventListener('input', checkMatch);

    // Prevent form submission if passwords don't match
    form.addEventListener('submit', function (e) {
        if (!checkMatch()) {
            e.preventDefault();
        }
    });
</script>
