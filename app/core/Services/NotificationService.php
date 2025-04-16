<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\NotificationRepository;

class NotificationService
{
    private NotificationRepository $notificationRepo;

    public function __construct()
    {
        $this->notificationRepo = new NotificationRepository();
    }

    public function createNotification(int $userId, int $alertId, string $message): void
    {
        $data = [
            'user_id' => $userId,
            'alert_id' => $alertId,
            'message' => $message,
            'seen' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->notificationRepo->insert($data);
    }

    public function markNotificationAsSeen(int $notificationId): void
    {
        $this->notificationRepo->markAsSeen($notificationId);
    }

    public function getUnseenNotifications(int $userId): array
    {
        return $this->notificationRepo->getUnseenByUser($userId);
    }

    public function getAllNotifications(int $userId): array
    {
        return $this->notificationRepo->getAllByUser($userId);
    }

    public function delete(int $id): bool
    {
        return $this->notificationRepo->delete($id);
    }
}
