<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\AdminSettings;

class AdminSettingRepository extends Repository
{
    protected function __construct()
    {
        parent::__construct();
        $this->table = "admin_settings";
        $this->columns = AdminSettings::getFieldNames();
    }

    public function getAllAdminSettings(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = AdminSettings::fromArray($list[$i]);
        }
        return $list;
    }

    public function getAdminSettingById($id): AdminSettings
    {
        return AdminSettings::fromArray(parent::get_by_id($id));
    }

    public function createAdminSetting(AdminSettings $adminSetting)
    {
        parent::insert($adminSetting->toArray());
    }

    public function updateAdminSetting(AdminSettings $adminSetting)
    {
        parent::update($adminSetting->toArray());
    }

    public function deleteAdminSetting(AdminSettings $adminSetting)
    {
        parent::delete($adminSetting->id);
    }
}