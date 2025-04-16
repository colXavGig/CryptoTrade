<?php

namespace CryptoTrade\Models;

enum TransactionType : string
{
    case BUY = 'buy';
    case SELL = 'sell';

}
