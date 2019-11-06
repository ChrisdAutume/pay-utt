<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Mail\paidTransaction;
use App\Mail\refundedTransaction;
use App\Mail\refusedTransaction;
use App\Models\Transaction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Models\ImmediateTransaction;
use App\Models\RefundTransaction;

class TransactionNotify extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Transaction $transaction)
    {
        $this->sendMailNotification();
    }

    public function sendMailNotification()
    {
        if(!filter_var($this->transaction->client_mail, FILTER_VALIDATE_EMAIL))
            return false;

        switch ($this->transaction->step)
        {
            case 'PAID':
                if ($this->transaction instanceof ImmediateTransaction) {
                    $sujet = 'Confirmation de paiement, transaction n°'.$this->transaction->id;
                    Mail::to($this->transaction->client_mail)->queue(new paidTransaction($this->transaction, $sujet));
                } else if ($this->transaction instanceof RefundTransaction) {
                    $sujet = 'Remboursement de votre transaction';
                    Mail::to($this->transaction->client_mail)->queue(new refundedTransaction($this->transaction, $sujet));
                }
                break;

            case 'AUTHORISATION':
            case 'REFUNDED':
                break;

            case 'REFUSED':
                if (!$this->transaction instanceof RefundTransaction) {
                    $sujet = 'Échec de la transaction';
                    Mail::to($this->transaction->client_mail)->queue(new refusedTransaction($this->transaction, $sujet));
                }
                break;

            case 'CANCELED':
                if (!$this->transaction instanceof RefundTransaction) {
                    $sujet = 'Abandon de la transaction';
                    Mail::to($this->transaction->client_mail)->queue(new refusedTransaction($this->transaction, $sujet));
                }

                break;
        }

    }
}
