<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class paidTransaction extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction, $subject)
    {
        $this->transaction = $transaction;
        $this->subject($subject);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.paid')->with(['transaction' => $this->transaction, 'sujet' => $this->subject])->replyTo($this->transaction->service->fundation->mail, $this->transaction->service->fundation->name);
    }
}
