<?php

namespace App\Jobs;

use App\Facades\PaymentLoader;
use App\Jobs\Job;
use App\Models\RefundTransaction;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TransactionClientNotify extends Job implements ShouldQueue
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
        $transaction = $this->transaction;

        // Si refund pas de notification
        if ($transaction instanceof RefundTransaction) {
            return true;
        }
        // On fait la transaction
        $client = new Client();
        $payload = PaymentLoader::encryptFromService($transaction->service, $transaction->callbackReturn());
        $res = $client->request('POST', $transaction->service->callback_url . '?payload=' . $payload, [
            'json' => ['payload' => $payload],
            'verify' => false,
        ]);
        if ($res->getStatusCode() != 200) {
            throw new \Exception($res->getStatusCode() . ' error during #' . $transaction->id . ' callback');
        } else {
            Log::info('Callback #' . $transaction->id . ' success');
        }

    }
}
