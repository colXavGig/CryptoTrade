<?php

namespace CryptoTrade\Models;

use CryptoTrade\Models\RepoCompatibility;
use DateTime;

class Log implements RepoCompatibility
{
    public int $id;
    public int $user_id;
    public string $action;
    public string $ip_address;
    public string $user_agent;
    public DateTime $created_at;

    private const FIELD_NAMES = [
        "id",
        "user_id",
        "action",
        "ip_address",
        "user_agent",
        "created_at",
    ];

    public function __construct(int $id, int $user_id, string $action, string $ip_adress, string $user_agent, DateTime $created_at)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->action = $action;
        $this->ip_address = $ip_adress;
        $this->user_agent = $user_agent;
        $this->created_at = $created_at;
    }
    static function fromArray(array $array): Log
    {
        assert(is_array($array));
        assert(count($array) === count(self::FIELD_NAMES));
        assert(count($array) === count(array_intersect(array_keys($array), array_keys(self::FIELD_NAMES))));

        return new Log(
            $array['id'],
            $array['user_id'],
            $array['action'],
            $array['ip_address'],
            $array['user_agent'],
            $array['created_at']
        );
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }

    function toArray(): array
    {
        $list = [];
        foreach (self::FIELD_NAMES as $field) {
            $list[$field] = $this->$field;
        }
        return $list;
    }
}