<?php

namespace App\Console\Commands;

use App\Models\ImmediateTransaction;
use App\Models\Transaction;
use Illuminate\Console\Command;

class BatchRefund extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:batch-refund';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->info("Outils pour procéder a un remboursement de masse");
        $ids = $this->ask("Numéro de transactions (séparé par une virgule , )");
        $ids = explode(',', $ids);

        $this->info(count($ids) . ' transactions rentrée');

        foreach ($ids as $id) {
            $id = intval($id);
            $tr = Transaction::find($id);

            if ($tr) {
                if ($tr->getSolde() > 0 && $tr instanceof ImmediateTransaction) {
                    $op = $tr->doRefund($tr->getSolde());
                    if ($op->step == 'PAID') {
                        $this->info(' #' . $id . ' solde: ' . round($tr->getSolde() / 100, 2) . ' € remboursé !');
                    } else {
                        $this->error(' #' . $id . ' solde: ' . round($tr->getSolde() / 100, 2) . ' € ERREUR !');
                    }
                }
            } else {
                $this->error(' #' . $id . ' INCONNU');
            }
        }
    }
}
