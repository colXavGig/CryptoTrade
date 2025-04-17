<?php

namespace CryptoTrade\Models;

class Notification implements RepoCompatibility
{
    public int $id;
    public int $user_id;
    public int $alert_id;
    public string $message;
    public string $created_at;
    public bool $seen;

    public function __construct(
        int $id,
        int $user_id,
        int $alert_id,
        string $message,
        string $created_at,
        bool $seen
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->alert_id = $alert_id;
        $this->message = $message;
        $this->created_at = $created_at;
        $this->seen = $seen;
    }

    static function fromArray(array $array): RepoCompatibility
    {
        return new Notification(
            $array['id'],
            $array['user_id'],
            $array['alert_id'],
            $array['message'],
            $array['created_at'],
            (bool)$array['seen']
        );
    }

    static function getFieldNames(): array
    {
        return [
            "id",
            "user_id",
            "alert_id",
            "message",
            "created_at",
            "seen"
        ];
    }

    function toArray(): array
    {
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "alert_id" => $this->alert_id,
            "message" => $this->message,
            "created_at" => $this->created_at,
            "seen" => $this->seen
        ];
    }
}
