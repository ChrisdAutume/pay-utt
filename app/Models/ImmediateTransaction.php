<?php

namespace App\Models;

class ImmediateTransaction extends Transaction
{
    protected $type = 'checkout';

    public function bind($data)
    {
        parent::bind($data);
        $this->attributes['type'] = 'PAYMENT';
        $this->attributes['capture_day'] = 0;
    }
    /**
     * Refactor, old function to get AtosParameter
     *
     * @return void
     */
    public function getAtosParameter()
    {
        return [
            'customer_email' => $this->attributes['client_mail'],
            'capture_day' => $this->attributes['capture_day'],
            'caddie' => $this->attributes['id'],
        ];
    }

    protected static function boot()
    {
        static::addGlobalScope(new TransactionScope('payment'));
        parent::boot();
    }
    /**
     * Order a refund order based on amount order
     *
     * @param integer $amount
     * @return boolean
     */
    public function doRefund(float $amount): bool
    {
        $amount = intval($amount);
        if ($amount <= 0 || $amount > $this->getSolde()) {
            return false;
        }

        if ($this->step != 'PAID') {
            return false;
        }

        $refund_tr = new RefundTransaction();

        if (!$this->parent) {
            $refund_tr->parent = $this->id;
        } else {
            $refund_tr->parent = $this->parent;
        }

        $refund_tr->amount = $amount;
        $refund_tr->save();

        if ($this->getProvider()->doRefund($refund_tr)) {
            return true;
        }

        return false;
    }
}
