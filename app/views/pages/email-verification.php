<!-- email verification page with form to enter token (action: api/user/verify-email-->

<h1>A 6-Digit code has been sent to your Email</h1>
<br><br>

<form id="email-verification-form" action="api/user/verify-email" method="POST">
    <div class="form-group">
        <label for="token">Enter 6-Digit Code</label>
        <input type="text" class="form-control" id="token" name="token" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
