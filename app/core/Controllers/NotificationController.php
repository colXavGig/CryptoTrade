<?php

namespace CryptoTrade\Controllers;

use CryptoTrade\Services\NotificationService;
use CryptoTrade\Services\JWTService;
use CryptoTrade\Services\CSRFService;
use Exception;

class NotificationController
{
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    public function getUnseen(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $notifications = $this->notificationService->getUnseenNotifications($authUser['user_id']);
            echo json_encode(['success' => true, 'notifications' => $notifications]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function getAll(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            $authUser = JWTService::verifyJWT();

            $notifications = $this->notificationService->getAllNotifications($authUser['user_id']);
            echo json_encode(['success' => true, 'notifications' => $notifications]);
        } catch (Exception $e) {
            $this->sendError($e);
        }
    }

    public function markSeen(): void
    {
        $this->requirePost();

        try {
            CSRFService::verifyToken($_POST['csrf_token'] ?? '');
            JWTService::verifyJWT();

            $notificationId = $_POST['notification_id'] ?? null;
            if (!$notificationId) {
                throw new Exception("Missing notification_id.");
            }

            $this->notificationService->markNotificationAsSeen((int)$notificationId);
            echo json_encode(['success' => true, 'message' => 'Notification marked as seen.']);
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
