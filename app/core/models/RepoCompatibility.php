<?php

namespace CryptoTrade\Models;

interface RepoCompatibility
{
    static function fromArray(array $array): self;

    static function getFieldNames(): array;

    function toArray(): array;
}