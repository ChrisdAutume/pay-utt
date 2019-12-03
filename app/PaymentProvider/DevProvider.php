<?php
/**
 * User: chris
 * Date: 07/08/2016
 * Time: 15:10
 */

namespace App\PaymentProvider;

use App\Models\RefundTransaction;
use App\Models\Transaction;

class DevProvider implements PaymentGateway
{
    public function doRefund(RefundTransaction $transaction)
    {
        // Refund always successfull in dev mode
        $transaction->provider = $this->getName();
        $transaction->step = 'PAID';
        $transaction->save();
        return true;
    }

    public function getName()
    {
        return 'Dev';
    }

    public function getChoosePage(Transaction $transaction)
    {
        return view('gateways.dev.basket', ['transaction' => $transaction]);
    }

    public function requestPayment(Transaction $transaction)
    {
        // TODO: Implement requestPayment() method.
    }

    public function canBeUsed(Transaction $transaction): bool
    {
        if ($transaction->service->isDevMode()) {
            return true;
        } else {
            return false;
        }

    }

    public function getHumanisedReport(Transaction $transaction)
    {
        return "Validation en mode dev, cette transaction n'est pas réelle.";
    }

    public function getTransactionFee(Transaction $transaction): int
    {
        return 0;
    }
}
