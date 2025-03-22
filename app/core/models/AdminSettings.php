<?php

namespace CryptoTrade\Models;

class AdminSettings implements  RepoCompatibility
{
    public int $id;
    public string $setting_key;
    public string $setting_value;

    private const FIELD_NAMES = [
        "id",
        "setting_key",
        "setting_value",
    ];

    public function __construct(int $id, string $setting_key, string $setting_value)
    {
        $this->id = $id;
        $this->setting_key = $setting_key;
        $this->setting_value = $setting_value;
    }
    public static function fromArray($array): AdminSettings {
        return new AdminSettings(
            $array['id'],
            $array['setting_key'],
            $array['setting_value']
        );
    }
    public function toArray(): array {
        return [
            'id' => $this->id,
            'setting_key' => $this->setting_key,
            'setting_value' => $this->setting_value
        ];
    }

    static function getFieldNames(): array
    {
        return self::FIELD_NAMES;
    }
}