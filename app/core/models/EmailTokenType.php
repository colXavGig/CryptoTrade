<?php

namespace CryptoTrade\Models;

enum EmailTokenType: string
{
    case EMAIL_CONFIRMATION = 'EMAIL_CONFIRMATION';
    case PASSWORD_RESET = 'PASSWORD_RESET';

    public static function fromValue(string $value): self
    {
        return match ($value) {
            'EMAIL_CONFIRMATION' => self::EMAIL_CONFIRMATION,
            'PASSWORD_RESET' => self::PASSWORD_RESET,
            default => throw new \InvalidArgumentException("Invalid EmailTokenType: $value"),
        };
    }
}
