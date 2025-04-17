<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\AdminSettingService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\CSRFService;
use Exception;

class AdminSettingController
{
    private AdminSettingService $adminSettingService;

    public function __construct()
    {
        $this->adminSettingService = new AdminSettingService();
    }

    public function getAll(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $settings = $this->adminSettingService->getAllSettings();
            echo json_encode(['success' => true, 'settings' => $settings]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function update(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $key = $_POST['setting_key'] ?? null;
            $value = $_POST['setting_value'] ?? null;

            if (!$key || $value === null) {
                throw new Exception("Missing setting_key or setting_value.");
            }

            $this->adminSettingService->updateSetting($key, $value);
            echo json_encode(['success' => true, 'message' => 'Setting updated.']);
            // JS redirect to /admin-settings
            echo "<script>window.location.href = '/admin-settings';</script>";
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function delete(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $key = $_POST['setting_key'] ?? null;
            if (!$key) {
                throw new Exception("Missing setting_key.");
            }

            $this->adminSettingService->deleteSetting($key);
            echo json_encode(['success' => true, 'message' => 'Setting deleted.']);
            // JS redirect to /admin-settings
            echo "<script>window.location.href = '/admin-settings';</script>";
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
    }

    private function sendError(Exception $e): void
    {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
