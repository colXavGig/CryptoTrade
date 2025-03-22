<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\DataAccess\Repository;
use CryptoTrade\Models\Alert;

class AlertRepository extends Repository
{
    protected function __construct() {
        parent::__construct();
        $this->table = "alerts";
        $this->columns = Alert::getFieldNames();
    }
    public function getAllAlerts() : array {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = Alert::fromArray($list[$i]);
        }
        return $list;
    }
    public function getAlertById($id): Alert {
        return Alert::fromArray(parent::get_by_id($id));
    }
    public function createAlert(Alert $alert) {
        parent::insert($alert->toArray());
    }
    public function updateAlert(Alert $alert) {
        parent::update($alert->toArray());
    }
    public function deleteAlert(Alert $alert) {
        parent::delete($alert->id);
    }
}