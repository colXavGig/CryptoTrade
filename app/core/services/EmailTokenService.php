<?php

namespace CryptoTrade\Services;

class EmailTokenService
{
    // Generate and store email token
    public static function generateToken($user_id, $type): string
    {
        // 6 digit token
        $token = random_int(100000, 999999);
        $expires_at = new \DateTime('+30 minutes'); // Token expires in 30 minutes

        $email_token = new \CryptoTrade\Models\EmailToken($user_id, $token, $type, $expires_at);
        $email_token_repo = new \CryptoTrade\DataAccess\EmailTokenRepository();
        $email_token_repo->insert($email_token);

        return $token;
    }

    // Verify email token
    private static function verifyToken($token, $type): bool
    {
        $email_token_repo = new \CryptoTrade\DataAccess\EmailTokenRepository();
        $email_token = $email_token_repo->get_by_token($token);

        if (!$email_token) {
            return false;
        }

        if ($email_token['type'] !== $type) {
            return false;
        }

        if ($email_token['expires_at'] < new \DateTime()) {
            return false;
        }

        return true;
    }

    // Delete email token
    private static function deleteToken($token)
    {
        $email_token_repo = new \CryptoTrade\DataAccess\EmailTokenRepository();
        $email_token_repo->delete_by_token($token);
    }

    // Send email token
    public static function sendToken($email, $token, $type)
    {
        $subject = '';
        $message = '';

        if ($type === \CryptoTrade\Models\EmailTokenType::EMAIL_CONFIRMATION) {
            $subject = 'Email Confirmation';
            $message = 'Your email confirmation token is: ' . $token . ' (valid for 30 minutes) \n Please enter this token in the email confirmation form at http://localhost:8080/email-verification';
        } else if ($type === \CryptoTrade\Models\EmailTokenType::PASSWORD_RESET) {
            $subject = 'Password Reset';
            $message = 'Your temporary password is: ' . $token ;
        }

        mail($email, $subject, $message); // TODO: Configure mail settings
    }


    // TODO: implement user endpoints to call the following functions

    // Process email confirmation
    public static function confirmEmail($token)
    {
        if (!self::verifyToken($token, \CryptoTrade\Models\EmailTokenType::EMAIL_CONFIRMATION))
        {
            throw new \InvalidArgumentException("Invalid email confirmation token.");
        }

        $email_token_repo = new \CryptoTrade\DataAccess\EmailTokenRepository();
        $email_token = $email_token_repo->get_by_token($token);

        $user_repo = new \CryptoTrade\DataAccess\UserRepository();
        $user = $user_repo->get_by_id($email_token['user_id']);

        if (!$user) {
            throw new \InvalidArgumentException("User not found.");
        }

        // Update user email
        $user['two_factor_enabled'] = true;
        $user_repo->update($user->toArray());

        // Delete the token after successful update
        self::deleteToken($token);
    }



    // Reset password
    public static function resetPassword($token)
    {
        if (!self::verifyToken($token, \CryptoTrade\Models\EmailTokenType::PASSWORD_RESET))
        {
            throw new \InvalidArgumentException("Invalid password reset token.");
        }

        $user_repo = new \CryptoTrade\DataAccess\UserRepository();
        $user = $user_repo->get_by_id($token['user_id']);

        if (!$user) {
            throw new \InvalidArgumentException("User not found.");
        }

        // replace password by hashed token
        $user['password_hash'] = password_hash($token, PASSWORD_DEFAULT);
        $user_repo->update($user->toArray());

        // Delete the token after successful update
        self::deleteToken($token);
    }

}