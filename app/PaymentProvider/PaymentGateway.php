<?php

namespace App\PaymentProvider;

use App\Models\Transaction;
use App\Models\RefundTransaction;

interface PaymentGateway
{
    public function requestPayment(Transaction $transaction);
    public function canBeUsed(Transaction $transaction): bool;

    public function doRefund(RefundTransaction $transaction);

    public function getName();
    public function getChoosePage(Transaction $transaction);

    public function getHumanisedReport(Transaction $transaction);

    public function getTransactionFee(Transaction $transaction): int;

}
