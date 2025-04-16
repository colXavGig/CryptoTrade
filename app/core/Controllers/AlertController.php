<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\AlertService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\CSRFService;
use Exception;

class AlertController
{
    private AlertService $alertService;

    public function __construct()
    {
        $this->alertService = new AlertService();
    }

    public function create(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $data = [
                'user_id' => $authUser['user_id'],
                'crypto_id' => $_POST['crypto_id'] ?? throw new Exception("Missing crypto_id."),
                'price_threshold' => $_POST['price_threshold'] ?? throw new Exception("Missing threshold."),
                'alert_type' => $_POST['alert_type'] ?? throw new Exception("Missing alert type."),
                'active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'last_triggered_at' => null
            ];

            $id = $this->alertService->create($data);
            echo json_encode(['success' => true, 'id' => $id]);
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

            $id = $_POST['alert_id'] ?? throw new Exception("Missing alert_id.");
            $success = $this->alertService->delete((int)$id);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getAll(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT(); // Only allow authenticated (admin will be enforced by frontend)

            $alerts = $this->alertService->getAll();
            echo json_encode(['success' => true, 'alerts' => $alerts]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getById(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $id = $_POST['alert_id'] ?? throw new Exception("Missing alert_id.");
            $alert = $this->alertService->getById((int)$id);
            echo json_encode(['success' => true, 'alert' => $alert]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getByUserId(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $userId = $_POST['user_id'] ?? throw new Exception("Missing user_id.");
            $alerts = $this->alertService->getByUserId((int)$userId);
            echo json_encode(['success' => true, 'alerts' => $alerts]);
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

            $id = $_POST['alert_id'] ?? throw new Exception("Missing alert_id.");

            $data = [
                'id' => (int)$id,
                'crypto_id' => $_POST['crypto_id'] ?? throw new Exception("Missing crypto_id."),
                'price_threshold' => $_POST['price_threshold'] ?? throw new Exception("Missing threshold."),
                'alert_type' => $_POST['alert_type'] ?? throw new Exception("Missing alert_type."),
            ];

            $success = $this->alertService->update($data);
            echo json_encode(['success' => $success]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function toggleStatus(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $id = $_POST['alert_id'] ?? throw new Exception("Missing alert_id.");
            $active = filter_var($_POST['active'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!isset($active)) throw new Exception("Missing or invalid 'active' value (true/false).");

            $data = [
                'id' => (int)$id,
                'active' => $active
            ];

            $success = $this->alertService->update($data);
            echo json_encode(['success' => $success, 'message' => $active ? 'Activated' : 'Deactivated']);
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
