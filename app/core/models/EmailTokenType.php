<?php

namespace CryptoTrade\Models;

enum EmailTokenType
{
    case EMAIL_CONFIRMATION;
    case PASSWORD_RESET;
}
