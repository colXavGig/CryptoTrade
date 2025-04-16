<?php

namespace CryptoTrade\DataAccess;

use CryptoTrade\Models\Transaction;
use DateMalformedStringException;

class TransactionRepository extends Repository
{
    private static ?TransactionRepository $instance = null;
    protected function __construct()
    {
        $this->table = 'transactions';
        $this->columns = Transaction::getFieldNames();
        parent::__construct();
    }

    // Singleton pattern to ensure only one instance of TransactionRepository
    public static function getInstance(): TransactionRepository
    {
        if (self::$instance === null) {
            self::$instance = new TransactionRepository();
        }
        return self::$instance;
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
        $arr = $transaction->toArray();
        $arr['created_at'] = $transaction->created_at->format('Y-m-d H:i:s');
        parent::insert($arr);
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