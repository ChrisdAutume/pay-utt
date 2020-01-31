<?php

namespace App\Classes;

use App\Models\Report;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Dolibarr
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.picsou.base_url') . '/',
            'headers' => ['DOLAPIKEY' => config('services.picsou.api_key')]
        ]);
    }
    public function createSupplierInvoice(Report $report)
    {
        $socid = $report->service->fundation->picsou_society_id;
        $order_lines = [];
        $types = DB::table('transactions')
            ->where('report_id',$report->id)
            ->groupBy('transactions.type')
            ->select(['transactions.type as type', DB::raw('count(transactions.id) as qty'), DB::raw('sum(transactions.amount) as total')])
            ->get();
        //Let's form order form factor
        foreach ($types as $type)
        {
            $total = $type->total / 100;
            if($type->type == 'REFUND')
            {
                $total *= -1;
            }
            $order_lines[] = [
                "desc"		=> $type->qty . ' transaction(s) de type ' . $type->type,
                "subprice"	=> floatval($total),
                "qty"		=> 1,
                "tva_tx"	=> floatval(0),
            ];
        }

        if($report->bank_fee)
        {
            $order_lines[] = [
                "desc"		=> 'Frais de service',
                "subprice"	=> floatval($report->bank_fee/100),
                "qty"		=> 1,
                "tva_tx"	=> floatval(0),
            ];
        }
        $order = [
            'socid' => ($socid?$socid:config('services.picsou.default_society')),
            'type' => 0,
            'lines' => $order_lines,
            'libelle' => 'Etupay report ' . $report->id,
            'ref_supplier' => 'ETUPAY-' . $report->id,
            'note_public' => "Importation automatique depuis EtuPay - Rapport #" . $report->id . " - Service: " . $report->service->host ." - Fondation: " . $report->service->fundation->name,
            'fk_project' => config('services.picsou.default_project'),
        ];
        try {
            $response = $this->client->post('api/index.php/invoices', [
                'form_params' => $order
            ]);
        } catch (GuzzleException $e) {
            return false;
        }
        $json = json_decode($response->getBody()->getContents(), true);

        if (isset($json['error']))
            return false;
        else{
            $report->picsou_id = intval($json);
            $report->save();
            return $report;
        }
    }

    public function createInvoice(Collection $transactions, $socid = null)
    {

        //Let's form order form factor
        $line = [
            "desc"		=> $ref2,
            "subprice"	=> $prix2,
            "qty"		=> $qtt2,
            "tva_tx"	=> floatval(0),
        ];
        $order_lines = [];
        $order = [
            'socid' => ($socid?$socid:config('services.picsou.default_society')),
            'type' => 0,
            'lines' => $order_lines,
            'note_private' => "Importation automatique depuis EtuPay",
            'fk_project' => config('services.picsou.default_project'),
        ];
        try {
            $response = $this->client->post('api/index.php/invoices', [
                'form_params' => $order
            ]);
        } catch (GuzzleException $e) {
            return null;
        }
        $json = json_decode($response->getBody()->getContents(), true);

        if (isset($json['error']))
            return null;
        else return $json;
    }

}
