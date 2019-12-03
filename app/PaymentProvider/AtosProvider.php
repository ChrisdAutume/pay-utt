<?php
/**
 * User: chris
 * Date: 07/08/2016
 * Time: 15:10
 */

namespace App\PaymentProvider;

use App\Classes\AtosRequest;
use App\Models\ImmediateTransaction;
use App\Models\RefundTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AtosProvider implements PaymentGateway
{
    public function doRefund(RefundTransaction $transaction)
    {
        // TODO: Implement doRefund() method.
        return false;
    }

    public function getName()
    {
        return 'Atos';
    }

    public function getChoosePage(Transaction $transaction)
    {
        $req = $this->doAtosRequest($transaction);
        if ($req->isSuccess()) {
            return view('gateways.atos.basket', ['bank_content' => $req->body->get('message')]);
        } else {
            return null;
        }

        //TODO: Implément error reporting
    }

    public function requestPayment(Transaction $transaction)
    {
        // TODO: Implement requestPayment() method.
    }

    public function canBeUsed(Transaction $transaction): bool
    {
        //Disable payment provider in dev mode
        if ($transaction->service->isDevMode()) {
            return false;
        }

        //Check config
        if (!config('payment.atos.merchand_id', false)) {
            return false;
        }

        //Check min an max amount
        if ($transaction->amount > Config::get('transaction.atos.max_amount', 100000) || $transaction->amount < Config::get('payment.atos.min_amount', 100)) {
            return false;
        }

        if (!$transaction instanceof ImmediateTransaction) {
            return false;
        }

        return true;
    }

    protected function doAtosRequest(Transaction $transaction)
    {
        $parameters = [
            'header_flag' => 'yes',
            'logo_id2' => 'BDE.gif',
        ];

        if (!filter_var($transaction->client_mail, FILTER_VALIDATE_EMAIL)) {
            $parameters['customer_email'] = $transaction->client_mail;
        }

        $parameters = array_merge($parameters, $transaction->getAtosParameter());
        $request = new AtosRequest(Config::get('payment.atos.merchand_id'), 'fr', Config::get('payment.atos.pathfile'), Config::get('payment.atos.requestPath'), Config::get('payment.atos.responsePath'), Config::get('payment.atos.isDebug'));
        return $request->requestGetCheckoutToken($transaction->amount, Config::get('atos.currencies'), $parameters);
    }
    public function getTransactionFromCallback($encryptedData)
    {
        $request = new AtosRequest(Config::get('payment.atos.merchand_id'), 'fr', Config::get('payment.atos.pathfile'), Config::get('payment.atos.requestPath'), Config::get('payment.atos.responsePath'), Config::get('payment.atos.isDebug'));
        $req = $request->requestDoCheckoutPayment($encryptedData);

        if ($transaction = Transaction::find($req->body->get('caddie'))) {
            return $transaction;
        } else {
            return false;
        }

    }
    public function processCallback($encryptedData)
    {
        $request = new AtosRequest(Config::get('payment.atos.merchand_id'), 'fr', Config::get('payment.atos.pathfile'), Config::get('payment.atos.requestPath'), Config::get('payment.atos.responsePath'), Config::get('payment.atos.isDebug'));
        $req = $request->requestDoCheckoutPayment($encryptedData);

        if ($req->isSuccess()) {
            if ($transaction = Transaction::find($req->body->get('caddie'))) {
                if ($transaction->step != 'INITIALISED') {
                    Log::error('Transaction ' . $transaction->id . ' already processed. ABORDING');
                    return $transaction;
                }
                $transaction->data = json_encode($req->body->all());
                $transaction->bank_transaction_id = $req->body->get('transaction_id');
                $transaction->provider = $this->getName();

                if ($req->body->get('amount') != $transaction->amount) {
                    throw new \Exception('Discordance in transaction amount');
                    $transaction->save();
                    Log::critical('Discordance in transaction amount ' . $transaction->id);
                    return false;
                }
                switch ($req->body->get('response_code')) {
                    case '00': // Accepted
                        $transaction->callbackAccepted();
                        break;

                    case '05': //Refused
                    case '02':
                    case '03':
                    case '12':
                    case '30':
                    case '34':
                    case '90':
                    case '75':
                        $transaction->callbackRefused();
                        break;

                    case '17': // Canceled
                        $transaction->callbackCanceled();
                        break;

                    default:
                        $transaction->callbackCanceled();
                        Log::warning('Inconnu atos return code. ' . $transaction->id . 'code: ' . $req->body->get('response_code'));

                }

                Log::info('Processing callback transaction ' . $transaction->id);
                $transaction->save();
                return $transaction;
            }
        }

    }

    public function getHumanisedReport(Transaction $transaction)
    {
        return "Transaction via ATOS";
    }

    public function getTransactionFee(Transaction $transaction): int
    {
        return 0;
    }
}
