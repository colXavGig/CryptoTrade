<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\EmailTokenRepository;
use CryptoTrade\DataAccess\UserRepository;
use CryptoTrade\Models\EmailToken;
use CryptoTrade\Models\EmailTokenType;
use CryptoTrade\Models\User;
use DateTime;
use InvalidArgumentException;
use Random\RandomException;

class EmailTokenService
{
    public static function confirmEmail(string $token): void
    {
        if (!self::isTokenValid($token, EmailTokenType::EMAIL_CONFIRMATION)) {
            throw new InvalidArgumentException("Invalid email confirmation token.");
        }

        $emailToken = EmailTokenRepository::getInstance()->getTokenByToken($token);
        $userData = (new UserRepository())->get_by_id($emailToken->getUserId());

        if (!$userData) {
            throw new InvalidArgumentException("User not found.");
        }

        $user = User::fromArray($userData);
        $user->two_factor_enabled = true;

        (new UserRepository())->update($user->toArray());

        self::deleteToken($token);
    }

    public static function resetPassword(string $token): void
    {
        if (!self::isTokenValid($token, EmailTokenType::PASSWORD_RESET)) {
            throw new InvalidArgumentException("Invalid password reset token.");
        }

        $emailToken = EmailTokenRepository::getInstance()->getTokenByToken($token);
        $userData = (new UserRepository())->get_by_id($emailToken->getUserId());

        if (!$userData) {
            throw new InvalidArgumentException("User not found.");
        }

        $user = User::fromArray($userData);
        $user->password_hash = password_hash($token, PASSWORD_DEFAULT);

        (new UserRepository())->update($user->toArray());

        self::deleteToken($token);
    }

    public static function resendVerificationToken(string $email, EmailTokenType $type): void
    {
        $userData = (new UserRepository())->get_by_email($email);
        if (!$userData) {
            throw new InvalidArgumentException("User not found.");
        }

        $userId = $userData['id'];
        $repo = EmailTokenRepository::getInstance();

        $existing = $repo->getTokenByUserId($userId);
        if ($existing) {
            $repo->deleteByUserId($userId);
        }

        $token = self::generateToken($userId, $type);
        self::sendToken($email, $token, $type);
    }

    public static function generateToken(string $userId, EmailTokenType $type): string
    {
        try {
            $token = random_int(100000, 999999);
        } catch (RandomException $e) {
            throw new RandomException("Failed to generate random token.");
        }

        $expiresAt = new DateTime('+30 minutes');
        $emailToken = new EmailToken($userId, $token, $type, $expiresAt);

        EmailTokenRepository::getInstance()->createToken($emailToken);

        return (string)$token;
    }

    public static function sendToken(string $email, string $token, EmailTokenType $type): void
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

    private static function isTokenValid(string $token, EmailTokenType $type): bool
    {
        $emailToken = EmailTokenRepository::getInstance()->getTokenByToken($token);

        if (!$emailToken) return false;
        if (!$emailToken->getType()->equals($type)) return false;
        if ($emailToken->getExpiresAt() < new DateTime()) return false;

        return true;
    }

    private static function deleteToken(string $token): void
    {
        EmailTokenRepository::getInstance()->deleteByToken($token);
    }
}
