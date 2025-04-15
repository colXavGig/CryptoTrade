<?php
namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\EmailTokenRepository;
use CryptoTrade\DataAccess\UserRepository;
use CryptoTrade\Models\EmailToken;
use CryptoTrade\Models\EmailTokenType;
use DateTime;
use InvalidArgumentException;
use Random\RandomException;

class EmailTokenService
{
    public static function confirmEmail($token): void
    {
        if (!self::verifyToken($token, EmailTokenType::EMAIL_CONFIRMATION)) {
            throw new InvalidArgumentException("Invalid email confirmation token.");
        }

        $email_token = EmailTokenRepository::getInstance()->getTokenByToken($token);
        $user = (new UserRepository())->get_by_id($email_token->getUserId());

        if (!$user) {
            throw new InvalidArgumentException("User not found.");
        }

        $user['two_factor_enabled'] = true;
        (new UserRepository())->update($user->toArray());

        self::deleteToken($token);
    }

    private static function verifyToken($token, EmailTokenType $type): bool
    {
        $email_token = EmailTokenRepository::getInstance()->getTokenByToken($token);

        if (!$email_token) return false;
        if (!$email_token->getType()->equals($type)) return false;
        if ($email_token->getExpiresAt() < new DateTime()) return false;

        return true;
    }

    private static function deleteToken($token): void
    {
        EmailTokenRepository::getInstance()->deleteByToken($token);
    }

    public static function resetPassword($token): void
    {
        if (!self::verifyToken($token, EmailTokenType::PASSWORD_RESET)) {
            throw new InvalidArgumentException("Invalid password reset token.");
        }

        $email_token = EmailTokenRepository::getInstance()->getTokenByToken($token);
        $user = (new UserRepository())->get_by_id($email_token->getUserId());

        if (!$user) {
            throw new InvalidArgumentException("User not found.");
        }

        $user['password_hash'] = password_hash($token, PASSWORD_DEFAULT);
        (new UserRepository())->update($user->toArray());

        self::deleteToken($token);
    }

    public static function resendVerificationToken($email, EmailTokenType $type): void
    {
        $user = (new UserRepository())->get_by_email($email);
        if (!$user) throw new InvalidArgumentException("User not found.");

        $repo = EmailTokenRepository::getInstance();

        $existing = $repo->getTokenByUserId($user['id']);
        if ($existing) {
            $repo->deleteByUserId($user['id']);
        }

        $token = self::generateToken($user['id'], $type);
        self::sendToken($email, $token, $type);
    }

    public static function generateToken($user_id, EmailTokenType $type): string
    {
        try {
            $token = random_int(100000, 999999);
        } catch (RandomException $e) {
            throw new RandomException("Failed to generate random token.");
        }

        $expires_at = new DateTime('+30 minutes');
        $email_token = new EmailToken($user_id, $token, $type, $expires_at);

        EmailTokenRepository::getInstance()->createToken($email_token);

        return (string) $token;
    }

    public static function sendToken($email, $token, EmailTokenType $type): void
    {
        $mailer = new MailService();
        $subject = '';
        $message = '';

        if ($type === EmailTokenType::EMAIL_CONFIRMATION) {
            $subject = 'Email Confirmation';
            $message = "Your email confirmation token is: $token (valid for 30 minutes)\nPlease enter this token in the form at http://localhost:8080/email-verification";
        } elseif ($type === EmailTokenType::PASSWORD_RESET) {
            $subject = 'Password Reset';
            $message = "Your temporary password is: $token";
        }

        $mailer->send($email, $subject, $message);
    }
}
