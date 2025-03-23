<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Log;

class LogRepository extends Repository
{
    protected function __construct()
    {
        $this->table = 'logs';
        $this->columns = Log::getFieldNames();
        parent::__construct();
    }

    public function getAllLogs(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = Log::fromArray($list[$i]);
        }
        return $list;
    }

    public function getLogById($id): Log
    {
        return Log::fromArray(parent::get_by_id($id));
    }

    public function createLog(Log $log)
    {
        parent::insert($log->toArray());
    }

    public function updateLog(Log $log)
    {
        parent::update($log->toArray());
    }

    public function deleteLog(Log $log)
    {
        parent::delete($log->id);
    }
}