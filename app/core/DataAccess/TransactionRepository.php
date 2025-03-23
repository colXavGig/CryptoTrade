<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Transaction;
use DateMalformedStringException;

class TransactionRepository extends Repository
{
    protected function __construct()
    {
        $this->table = 'transactions';
        $this->columns = Transaction::getFieldNames();
        parent::__construct();
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getTransactionById($id): Transaction
    {
        return Transaction::fromArray(parent::get_by_id($id));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getAllTransactions(): array
    {
        $list = parent::get_all();
        for ($i = 0; $i < count($list); $i++) {
            $list[$i] = Transaction::fromArray($list[$i]);
        }
        return $list;
    }

    public function createTransaction(Transaction $transaction)
    {
        parent::insert($transaction->toArray());
    }

    public function updateTransaction(Transaction $transaction)
    {
        parent::update($transaction->toArray());
    }

    public function deleteTransaction(Transaction $transaction)
    {
        parent::delete($transaction->id);
    }
}