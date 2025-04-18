<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\LogRepository;
use DateTime;

class LogService
{
    private LogRepository $logRepo;

    public function __construct()
    {
        $this->logRepo = new LogRepository();
    }

    public function getRecentLogsForUser(int $userId): array
    {
        return $this->logRepo->getLogsByUserId($userId, 20);
    }

    public function getActiveUsersToday(): int
    {
        $today = (new DateTime())->format('Y-m-d');
        return $this->logRepo->countDistinctUsersByDate($today);
    }

    public function getSummaryByAction(): array
    {
        return $this->logRepo->getLogCountsByAction();
    }

    public function countFailedLoginsToday(): int
    {
        $today = (new DateTime())->format('Y-m-d');
        return $this->logRepo->countLogsByActionAndDate('Failed login', $today);
    }

    public function getAllLogsRecent(): array
    {
        return $this->logRepo->getAllLogs(50);
    }
}
