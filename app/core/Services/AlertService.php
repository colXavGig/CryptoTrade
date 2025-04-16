<?php

namespace CryptoTrade\Services;

use CryptoTrade\DataAccess\AlertRepository;
use CryptoTrade\Models\Alert;
use CryptoTrade\Models\AlertType;
use InvalidArgumentException;

class AlertService
{
    private AlertRepository $alertRepo;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->alertRepo = new AlertRepository();
        $this->notificationService = new NotificationService();
    }

    public function checkAlerts(int $cryptoId, float $price): void
    {
        $alerts = $this->alertRepo->getActiveAlertsForCrypto($cryptoId);

        foreach ($alerts as $alert) {
            $shouldTrigger = (
                $alert->type === AlertType::Higher && $price >= $alert->price_threshold
                ||
                $alert->type === AlertType::Lower && $price <= $alert->price_threshold
            );

            if ($shouldTrigger) {
                $message = sprintf( // sprintf is a method that formats a string
                    "Price alert: Crypto #%d is now at %.8f (%s than %.8f)",
                    $alert->crypto_id,
                    $price,
                    $alert->type->value,
                    $alert->price_threshold
                );

                $this->notificationService->createNotification(
                    $alert->user_id,
                    $alert->id,
                    $message
                );

                $this->alertRepo->updateLastTriggered($alert->id);
            }
        }
    }

    public function create(array $data): string|false
    {
        return $this->alertRepo->insert($data);
    }

    public function update(array $data): bool
    {
        return $this->alertRepo->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->alertRepo->delete($id);
    }

    public function getAll(): array
    {
        return $this->alertRepo->get_all();
    }

    public function getById(int $id): Alert
    {
        $row = $this->alertRepo->get_by_id($id);
        if (!$row) {
            throw new InvalidArgumentException("Alert not found.");
        }
        return Alert::fromArray($row);
    }

    public function getBy(array $where): ?Alert
    {
        $row = $this->alertRepo->get_by($where);
        return $row ? Alert::fromArray($row) : null;
    }
}
