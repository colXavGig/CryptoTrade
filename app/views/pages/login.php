<h1>Login</h1>

<form id="login-form" method="POST" action="/api/user/login">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="text" name="email" id="email" required> <!-- Fixed type (to enable entries without email pattern) -->
    </div>
    <div class="form-group">
        <label for="password">Password</label> <!-- Fixed name -->
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit">Login</button>
</form>

<style>
#login-form {
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
</style>
