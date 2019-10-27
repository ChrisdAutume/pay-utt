<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\PaymentProvider\PaylineProvider;
use Illuminate\Console\Command;

class paylineCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payline:check {--id=* : payline transaction id to force reconciliate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permet de réconsilier les transaction de la base avec Payline';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $opt_id = $this->option('id');
        $paylineProvider = new PaylineProvider();
        $updated = 0;

        if($opt_id)
        {
            foreach ($opt_id as $tr_id)
            {
                $tr = $paylineProvider->getTransactionByPaylineId($tr_id);
                if($tr)
                {
                    $etupay_id = (explode('_', $tr['order']['ref']))[1];
                    $transaction = Transaction::find($etupay_id);
                    if ($transaction)
                    {
                        $this->info('Processing callback transaction ' . $transaction->id);
                        $this->processTransaction($transaction, $tr);
                        $updated++;
                    } else {
                        $this->error("Transaction not found ! ".$tr['order']['ref']." Payline: ".$tr_id);
                    }
                }
            }

        } else {
            $transactions = Transaction::all();
            $this->info(count($transactions) . ' transactions.');
            $this->info('Lancement de la tentative de résolution avec Payline');

            $this->output->progressStart(count($transactions));
            foreach ($transactions as $transaction) {
                $tr = $paylineProvider->getTransaction($transaction->id);
                if ($tr) {
                    $this->info('Processing callback transaction ' . $transaction->id);
                    if ($transaction->step == 'INITIALISED') {
                        $this->processTransaction($transaction, $tr);
                        $updated++;
                    }
                    // Vérification des incohérences

                }

                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
        }

        $this->info('Nombre de transaction consilié: '.$updated);
    }

    private function processTransaction(Transaction $transaction, $tr)
    {
        $transaction->data = json_encode($tr);
        $transaction->bank_transaction_id = $tr['transaction']['id'];
        $transaction->provider = 'Payline';

        switch ($tr['result']['shortMessage'])
        {
            case 'ACCEPTED':
                if($tr['payment']['amount'] != $transaction->amount)
                {
                    throw new \Exception('Discordance in transaction amount');
                    $transaction->save();
                    $this->error('Discordance in transaction amount '.$transaction->id);
                    return false;
                }
                $transaction->callbackAccepted();
                break;
            case 'CANCELLED':
                //Transaction expiré
                $transaction->step = 'CANCELED';
                $transaction->save();
                break;
            case 'ERROR':
            case 'REFUSED':
                $transaction->callbackRefused();
                break;
            case 'INPROGRESS':
            case 'ONHOLD_PARTNER':
            case 'PENDING_RISK':
                $this->info('#'.$transaction->id.' '.$tr['result']['shortMessage'].' '.$tr['result']['longMessage']);
                break;
        }
        $transaction->save();
    }
}
