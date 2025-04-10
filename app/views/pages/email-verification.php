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

<!-- Resend email verification token form with 30 second cooldown -->
<br><hr><br>
<h2>Didn't receive the code?</h2>
<p>Enter your email address and Click the button below to resend the code</p><br>
<form id="resend-email-verification-form" action="api/user/resend-verification-email" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="text" class="form-control" id="email" name="email" required>
    </div>
    <button type="submit" class="btn btn-primary" disabled>Resend Code</button>
</form>

<!-- script to enable resend button after 30 seconds with a live countdown -->
<script>
    let cooldown = 30;
    let resendButton = document.querySelector('#resend-email-verification-form button');
    // gray out the button and start countdown
    resendButton.style.backgroundColor = '#555';
    let countdown = setInterval(() => {
        cooldown--;
        resendButton.innerText = `Resend Code (${cooldown}s)`;
        if (cooldown === 0) {
            // remove gray background and enable button
            resendButton.style.backgroundColor = '';
            clearInterval(countdown);
            resendButton.removeAttribute('disabled');
            resendButton.innerText = 'Resend Code';
        }
    }, 1000);
</script>