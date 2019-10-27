<?php

namespace App\PaymentProvider;

use App\Models\AuthorisationTransaction;
use App\Models\ImmediateTransaction;
use App\Models\RefundTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Payline\PaylineSDK;

class PaylineProvider implements PaymentGateway
{
    private $sdk;

    public function __construct()
    {
        $this->sdk = new PaylineSDK(config('payment.payline.merchant_id'), config('payment.payline.acces_key'), null, null, null, null, (config('payment.payline.env') == 'PROD' ? PaylineSDK::ENV_PROD : PaylineSDK::ENV_HOMO));
    }

    public function getName()
    {
        return 'Payline';
    }

    public function getTransaction($id)
    {
        $param = [
            'transactionId' => null,
            'orderRef' => 'etupay_' . $id,
            'startDate' => null,
            'endDate' => null,
            'transactionHistory' => null,
            'archiveSearch' => null,
        ];
        $req = $this->sdk->getTransactionDetails($param);
        if ($req['result']['shortMessage'] != "ERROR") {
            return $req;
        } else {
            return null;
        }

    }

    public function getTransactionByPaylineId($id)
    {
        $param = [
            'transactionId' => $id,
            'orderRef' => null,
            'startDate' => null,
            'endDate' => null,
            'transactionHistory' => null,
            'archiveSearch' => null,
        ];
        $req = $this->sdk->getTransactionDetails($param);
        if ($req['result']['shortMessage'] != "ERROR") {
            return $req;
        } else {
            return null;
        }

    }
    public function getTransactionList($date)
    {
        $param = array(
            'transactionId' => null,
            'orderRef' => null,
            'startDate' => $date,
            'endDate' => $date,
            'contractNumber' => null,
            'authorizationNumber' => null,
            'returnCode' => null,
            'paymentMean' => null,
            'transactionType' => null,
            'name' => null,
            'firstName' => null,
            'email' => null,
            'cardNumber' => null,
            'currency' => null,
            'minAmount' => null,
            'maxAmount' => null,
            'walletId' => null,
            'sequenceNumber' => null,
            'token' => null,
        );
        $req = $this->sdk->transactionsSearch($param);
        if ($req['result']['code'] == "00000") {
            return $req['transactionList'];
        } else {
            return [];
        }

    }

    public function doRefund(RefundTransaction $transaction)
    {
        if (is_object($transaction->parent)) {
            $id = $transaction->parent->bank_transaction_id;
        } else {
            $id = $transaction->parent()->first()->bank_transaction_id;
        }
        $tr = $this->getTransactionByPaylineId($id);
        if ($tr && $tr['result']['code'] == '00000') {

            $param = [];
            $param['transactionID'] = $tr['transaction']['id'];
            $param['payment']['amount'] = $transaction->amount;
            $param['payment']['currency'] = 978;
            $param['payment']['mode'] = 'CPT';
            $param['payment']['action'] = 421;
            $param['payment']['contractNumber'] = config('payment.payline.contract_number');
            $param['sequenceNumber'] = null;

            $this->sdk->resetPrivateData();
            $this->sdk->addPrivateData(['key' => 'linked_etupay_id', 'value' => $transaction->id]);
            $param['comment'] = 'Remboursement automatisé';

            $return = $this->sdk->doRefund($param);
            if ($return['result']['code'] == '00000') {
                $transaction->data = json_encode($return);
                $transaction->provider = $this->getName();
                $transaction->bank_transaction_id = $return['transaction']['id'];
                $transaction->callbackAccepted();
                return $return;
            } else {
                $transaction->callbackRefused();
                Log::error("PaylineProvider - Refund - Transaction " . $transaction->id . " - " . $return['result']['code'] . ": " . $return['result']['longMessage']);
                return false;
            }
        } else {
            return false;
        }

    }

    public function renewAuthorisation(AuthorisationTransaction $transaction)
    {
        $this->sdk->resetPrivateData();

        $param = [];

        $param['returnURL'] = url()->route('return.payline');
        $param['cancelURL'] = url()->route('return.payline');

        //URL::forceRootUrl('http://46d42550.ngrok.io');
        $param['notificationURL'] = url()->route('callback.payline');
        $param['securityMode'] = 'SSL';

        $param['transactionID'] = $transaction->bank_transaction_id;
        $param['payment']['amount'] = $transaction->amount;
        $param['payment']['currency'] = 978;
        $param['payment']['mode'] = 'CPT';

        $this->sdk->addPrivateData(['key' => '3ds_nocheck', 'value' => 1]);
        $param['payment']['action'] = 202;

        $param['payment']['contractNumber'] = config('payment.payline.contract_number');
        $param['contracts'] = [config('payment.payline.contract_number')];
        $param['version'] = 19;

        $this->sdk->addPrivateData(['key' => 'etupay_id', 'value' => $transaction->id]);
        $return = $this->sdk->doReAuthorization($param);
        if ($return['result']['code'] == '00000') {
            return $return;
        } else {
            Log::error("PaylineProvider - Transaction " . $transaction->id . " - " . $return['result']['code'] . ": " . $return['result']['longMessage']);
            return false;
        }
    }

    public function processCallback(string $token): Transaction
    {
        $req = $this->sdk->getWebPaymentDetails(['token' => $token]);

        if (!isset($req['result']['code'])) {
            throw new \Exception('Missing payline return.');
        }

        if (!isset($req['privateDataList']['privateData'][0]['value'])) {
            throw new \Exception('Missing payline privateData.');
        }

        $tr_id = null;
        // On récupére l'id de transaction
        foreach ($req['privateDataList']['privateData'] as $privateData) {
            if ($privateData['key'] == 'etupay_id') {
                $tr_id = $privateData['value'];
            }

        }

        if (!$transaction = Transaction::find($tr_id)) {
            throw new \Exception('No transaction found.');
        }

        if ($transaction->step != 'INITIALISED') {
            Log::info('Transaction ' . $transaction->id . ' already processed. ABORDING');
            return $transaction;
        }

        $transaction->data = json_encode($req);
        $transaction->bank_transaction_id = $req['transaction']['id'];
        $transaction->provider = $this->getName();

        switch ($req['result']['shortMessage'])
        {
            case 'ACCEPTED':
                if($req['payment']['amount'] != $transaction->amount)
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
                $this->info('#'.$transaction->id.' '.$req['result']['shortMessage'].' '.$req['result']['longMessage']);
                break;
        }

        Log::info('Processing callback transaction ' . $transaction->id);
        $transaction->save();
        return $transaction;
    }

    public function getChoosePage(Transaction $transaction)
    {
        if ($request = $this->doWebRequest($transaction)) {
            return view('gateways.payline.basket', [
                'transaction' => $transaction,
                'payline_token' => $request['token'],
            ]);
        } else {
            return null;
        }

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
        //Chek min
        if ($transaction->amount > Config::get('transaction.atos.max_amount', 100000) || $transaction->amount < Config::get('payment.atos.min_amount', 100)) {
            return false;
        }

        return true;
    }

    protected function doWebRequest(Transaction $transaction)
    {
        $this->sdk->resetPrivateData();

        $param = [];

        $param['returnURL'] = url()->route('return.payline');
        $param['cancelURL'] = url()->route('return.payline');

        //URL::forceRootUrl('http://46d42550.ngrok.io');
        $param['notificationURL'] = url()->route('callback.payline');
        $param['securityMode'] = 'SSL';

        $param['payment']['amount'] = $transaction->amount;
        $param['payment']['currency'] = 978;
        $param['payment']['mode'] = 'CPT';

        if ($transaction instanceof ImmediateTransaction) {
            $param['payment']['action'] = 101;
            $this->sdk->addPrivateData(['key' => '3ds_nocheck', 'value' => 0]);
        }
        if ($transaction instanceof AuthorisationTransaction) {
            $this->sdk->addPrivateData(['key' => '3ds_nocheck', 'value' => 1]);
            $param['payment']['action'] = 100;
        }

        $param['order']['ref'] = 'etupay_' . $transaction->id;
        $param['order']['amount'] = $transaction->amount;
        $param['order']['currency'] = 978;
        $param['order']['date'] = $transaction->created_at->format('d/m/Y H:i');

        $param['buyer'] = [
            'lastName' => $transaction->lastname,
            'firstName' => $transaction->firstname,
            'email' => $transaction->client_mail,
        ];
        $param['shippingAddress'] = $param['billingAddress'] = [];
        $param['payment']['contractNumber'] = config('payment.payline.contract_number');
        $param['contracts'] = [config('payment.payline.contract_number')];
        $param['version'] = 19;
        $param['owner'] = $param['ownerAddress'] = [];

        $this->sdk->addPrivateData(['key' => 'etupay_id', 'value' => $transaction->id]);
        $return = $this->sdk->doWebPayment($param);
        if ($return['result']['code'] == '00000') {
            return $return;
        } else {
            Log::error("PaylineProvider - Transaction " . $transaction->id . " - " . $return['result']['code'] . ": " . $return['result']['longMessage']);
            return false;
        }

    }

    public function getHumanisedReport(Transaction $transaction)
    {
        $trs = json_decode($transaction->data);
        if ($transaction instanceof ImmediateTransaction && $transaction->step == 'PAID') {
            return "Transaction par carte bancaire n°" . $trs->transaction->id;
        } else if ($transaction instanceof AuthorisationTransaction && $transaction->step == 'PAID') {
            return "Authorisation bancaire n°" . $trs->authorization->number;
        } else if ($transaction instanceof RefundTransaction && $transaction->step == 'PAID') {
            return "Remboursement bancaire n°" . $trs->transaction->id;
        } else if ($transaction->step == 'REFUSED') {
            return "Echec de la transaction, raison: " . $trs->result->longMessage;
        }
    }
}
