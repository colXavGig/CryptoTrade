<?php

namespace CryptoTrade\Models;

enum AlertType: string
{
    case Higher = 'higher';
    case Lower = 'lower';
}
