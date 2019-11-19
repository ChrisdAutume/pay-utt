<?php

namespace App\Models;

use App\Excel\ReportExport;
use App\Mail\sendReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Report extends Model
{
    protected static function boot()
    {
        parent::boot();
        self::saving(function ($model){
            $model->refreshAmount();
        });
        self::updating(function ($model){
            $model->refreshAmount();
        });
    }

    public function refreshAmount()
    {
        $solde = 0;
        foreach($this->transactions as $transaction)
        {
            if($transaction->step != 'PAID')
                continue;

            switch (true)
            {
                case $transaction instanceof ImmediateTransaction:
                    $solde += $transaction->amount;
                    break;
                case $transaction instanceof RefundTransaction:
                    $solde -= $transaction->amount;
                    break;
            }
        }
        $this->amount = $solde;
    }
    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getExcelTransactionsExport()
    {
        return (new ReportExport($this))->download( 'etupay_report_'.$this->id.'.xlsx',\Maatwebsite\Excel\Excel::XLSX);
    }

    public function sendReport()
    {
        if(filter_var($this->service->fundation->mail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($this->service->fundation->mail)
                ->queue(new sendReport($this));
        } else {
            Mail::to('bde@utt.fr')
                ->queue(new sendReport($this));
        }
    }
}
