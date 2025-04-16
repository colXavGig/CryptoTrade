<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Alert;

class AlertRepository extends Repository
{
    protected $table = 'alerts';
    protected $columns;

    public function __construct()
    {
        parent::__construct();
        $this->columns = Alert::getFieldNames();
    }

    public function getActiveAlertsForCrypto(int $cryptoId): array
    {
        $sql = "SELECT * FROM alerts WHERE crypto_id = :crypto_id AND active = 1";
        $query = $this->db->prepare($sql);
        $query->execute(['crypto_id' => $cryptoId]);
        return array_map(fn($row) => Alert::fromArray($row), $query->fetchAll());
    }

    public function updateLastTriggered(int $alertId): void
    {
        $sql = "UPDATE alerts SET last_triggered_at = CURRENT_TIMESTAMP WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $alertId]);
    }

    public function deactivateAlert(int $alertId): void
    {
        $sql = "UPDATE alerts SET active = 0 WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->execute(['id' => $alertId]);
    }

    // get alerts by user
    public function getAlertsByUser(int $userId): array
    {
        $sql = "SELECT * FROM alerts WHERE user_id = :user_id";
        $query = $this->db->prepare($sql);
        $query->execute(['user_id' => $userId]);
        return array_map(fn($row) => Alert::fromArray($row), $query->fetchAll());
    }
}