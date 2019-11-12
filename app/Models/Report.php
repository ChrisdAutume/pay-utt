<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected static function boot()
    {
        parent::boot();
        self::saving(function ($model){
            $solde = 0;
            foreach($model->transactions as $transaction)
            {
                if($transaction->step != 'PAID')
                    continue;

                switch (true)
                {
                    case $model instanceof ImmediateTransaction:
                        $solde += $transaction->amount;
                        break;
                    case $model instanceof RefundTransaction:
                        $solde -= $transaction->amount;
                        break;
                }
            }
            $model->amount = $solde;
        });
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    public function service()
    {
        return $this->hasOne(Service::class);
    }
}
