<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Log;
use PDO;
use RuntimeException;

class LogRepository extends Repository
{
    public function __construct()
    {
        $this->table = 'logs';
        $this->columns = Log::getFieldNames();
        parent::__construct();
    }

    public function getAllLogs(int $limit = 50): array
    {
        $sql = "SELECT * FROM logs ORDER BY created_at DESC LIMIT :limit";
        $query = $this->db->prepare($sql);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();

        $logs = $query->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($log) {
            $log = array_intersect_key($log, array_flip(Log::FIELD_NAMES));
            if (count($log) !== count(Log::FIELD_NAMES)) {
                throw new RuntimeException("Log data is missing required fields.");
            }
            return Log::fromArray($log);
        }, $logs);
    }

    public function getLogById($id): Log
    {
        $log = parent::get_by_id($id);
        if (!$log) {
            throw new RuntimeException("Log with ID $id not found.");
        }
        return Log::fromArray($log);
    }

    public function createLog(Log $log): void
    {
        parent::insert($log->toArray());
    }

    public function updateLog(Log $log): void
    {
        parent::update($log->toArray());
    }

    public function deleteLog(Log $log): void
    {
        parent::delete($log->id);
    }

    public function getLogsByUserId(int $userId, int $limit = 20): array
    {
        $sql = "SELECT * FROM logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit";
        $query = $this->db->prepare($sql);
        $query->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->execute();

        $logs = $query->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($log) {
            $log = array_intersect_key($log, array_flip(Log::FIELD_NAMES));
            if (count($log) !== count(Log::FIELD_NAMES)) {
                throw new RuntimeException("Log data is missing required fields.");
            }
            return Log::fromArray($log);
        }, $logs);
    }

    public function countDistinctUsersByDate(string $date): int
    {
        $sql = "SELECT COUNT(DISTINCT user_id) FROM logs WHERE DATE(created_at) = :date";
        $query = $this->db->prepare($sql);
        $query->execute(['date' => $date]);
        return (int)$query->fetchColumn();
    }

    public function getLogCountsByAction(): array
    {
        $sql = "SELECT action, COUNT(*) as count FROM logs GROUP BY action ORDER BY count DESC";
        $query = $this->db->query($sql);
        $result = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['action']] = (int)$row['count'];
        }
        return $result;
    }

    public function countLogsByActionAndDate(string $action, string $date): int
    {
        $sql = "SELECT COUNT(*) FROM logs WHERE action = :action AND DATE(created_at) = :date";
        $query = $this->db->prepare($sql);
        $query->execute(['action' => $action, 'date' => $date]);
        return (int)$query->fetchColumn();
    }
}