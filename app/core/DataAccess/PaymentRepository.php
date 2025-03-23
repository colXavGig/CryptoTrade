<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Payment;

class PaymentRepository extends Repository
{
    protected function __construct()
    {
        parent::__construct();
        $this->table = "payments";
        $this->columns = Payment::getFieldNames();
    }

    public function getAllPayments(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = Payment::fromArray($list[$i]);
        }
        return $list;
    }

    public function getPaymentById($id): Payment
    {
        return Payment::fromArray(parent::get_by_id($id));
    }

    public function createPayment(Payment $payment)
    {
        parent::insert($payment->toArray());
    }

    public function updatePayment(Payment $payment)
    {
        parent::update($payment->toArray());
    }

}