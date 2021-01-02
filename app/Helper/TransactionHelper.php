<?php

namespace App\Helper;
use Firebase\JWT\JWT;
use App\Models\User;
use App\Models\Transaction;

class TransactionHelper
{
    const WAITING_PAYMENT = 1;
    const PAID = 2;
    const PRODUCT_SENT = 3;
    const PRODUCT_RECEIVED = 4;
    const COMPLETED = 5;

    const TRANSFER_BANK = 1;
    const CREDIT_CARD = 2;
    const PAYLATER = 3;
    const OVO = 4;
    const JENIUS = 5;
    const GOPAY = 6;

    public static function updateStatus($transactionId) {
        $transaction = Transaction::findOrFail($transactionId);

        if($transaction && $transaction->status < 5) {
            $transaction->icrement('status');
            return true;
        }else{
            return false;
        }
    }
}
