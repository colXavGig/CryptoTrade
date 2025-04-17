<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\AdminSettingRepository;
use CryptoTrade\Models\AdminSettings;

class AdminSettingService
{
    private AdminSettingRepository $adminSettingRepo;

    public function __construct()
    {
        $this->adminSettingRepo = new AdminSettingRepository();
    }

    public function getAllSettings(): array
    {
        return $this->adminSettingRepo->getAllAdminSettings();
    }

    public function getSettingValue(string $key, $default = null): ?string
    {
        foreach ($this->getAllSettings() as $setting) {
            if ($setting->setting_key === $key) {
                return $setting->setting_value;
            }
        }

        return $default;
    }

    public function updateSetting(string $key, string $value): void
    {
        foreach ($this->getAllSettings() as $setting) {
            if ($setting->setting_key === $key) {
                $setting->setting_value = $value;
                $this->adminSettingRepo->updateAdminSetting($setting);
                return;
            }
        }

        $newSetting = new AdminSettings(0, $key, $value);
        $this->adminSettingRepo->createAdminSetting($newSetting);
    }

    public function deleteSetting(string $key): void
    {
        foreach ($this->getAllSettings() as $setting) {
            if ($setting->setting_key === $key) {
                $this->adminSettingRepo->deleteAdminSetting($setting);
                return;
            }
        }
    }
}
