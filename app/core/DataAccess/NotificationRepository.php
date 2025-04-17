<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Notification;

class NotificationRepository extends Repository
{
    protected $table = 'notifications';
    protected $columns;

    public function __construct()
    {
        parent::__construct();
        $this->columns = Notification::getFieldNames();
    }

    public function getUnseenByUser(int $userId): array
    {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id AND seen = 0 ORDER BY created_at DESC";
        $query = $this->db->prepare($sql);
        $query->execute(['user_id' => $userId]);
        return array_map(fn($row) => Notification::fromArray($row), $query->fetchAll());
    }

    public function markAsSeen(int $notificationId): void
    {
        $sql = "UPDATE notifications SET seen = 1 WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $notificationId]);
    }

    public function getAllByUser(int $userId): array
    {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
        $query = $this->db->prepare($sql);
        $query->execute(['user_id' => $userId]);
        return array_map(fn($row) => Notification::fromArray($row), $query->fetchAll());
    }

    public function getLatestForAlert(int $id): \CryptoTrade\Models\RepoCompatibility
    {

        $sql = "SELECT * FROM notifications WHERE alert_id = :alert_id ORDER BY created_at DESC LIMIT 1";
        $query = $this->db->prepare($sql);
        $query->execute(['alert_id' => $id]);
        return Notification::fromArray($query->fetch());
    }
}