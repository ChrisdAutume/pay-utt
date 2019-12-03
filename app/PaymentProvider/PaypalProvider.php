<?php

namespace App\PaymentProvider;

use App\Models\AuthorisationTransaction;
use App\Models\RefundTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalProvider implements PaymentGateway
{
    public function canBeUsed(Transaction $transaction): bool
    {
        if (!config('payment.paypal.clientId', false) || !config('payment.paypal.clientSecret', false)) {
            return false;
        }

        if ($transaction->amount <= 0) {
            return false;
        }

        if (!$transaction instanceof AuthorisationTransaction) {
            return false;
        }

        return true;
    }

    public function doRefund(RefundTransaction $transaction)
    {
        // TODO: Implement doRefund() method.
        return false;
    }

    public function requestPayment(Transaction $transaction)
    {
        // TODO: Implement requestPayment() method.
    }

    public function getName()
    {
        return 'Paypal';
    }

    public function getChoosePage(Transaction $transaction)
    {
        return view('gateways.paypal.basket', ['url' => route('userFrontend.paypalRedirect', ['InitialisedTransaction' => $transaction])]);
    }

    public function processCallback(Request $request)
    {
        if ($request->query('paymentId')) {
            $payment = Payment::get($request->query('paymentId'), $this->getPaypalApiContext());
            $execution = new PaymentExecution();
            $execution->setPayerId($request->query('PayerID'));
            if ($transaction = Transaction::find($payment->transactions[0]->custom)) {
                $transaction->provider = $this->getName();
                if ($payment->state == 'created') {
                    try {
                        $payment->execute($execution, $this->getPaypalApiContext());
                    } catch (\Exception $e) {
                        die($e->getData());
                    }
                    $transaction->bank_transaction_id = $payment->id;
                    switch ($payment->state) {
                        case 'approved':
                            $transaction->data = $payment->toJSON();
                            $transaction->callbackAccepted();
                            break;
                        case 'failed':
                            $transaction->data = $payment->toJSON();
                            $transaction->callbackRefused();
                            break;
                    }
                }

                return $transaction;

            } else {
                abort(404, 'No transaction found !');
            }

        } else {
            abort(404, 'Wrong form.');
        }

    }

    public function getAuthorizeUrl(Transaction $transaction)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setCurrency('EUR')
            ->setTotal($transaction->amount / 100);

        $pTransaction = new \PayPal\Api\Transaction();
        $pTransaction->setAmount($amount)
            ->setDescription($transaction->description)
            ->setCustom($transaction->id);
        //   ->setNotifyUrl(url()->route('callback.paypal'));

        $redirectUrl = new RedirectUrls();
        $redirectUrl->setReturnUrl(url()->route('return.paypal'))
            ->setCancelUrl(url()->route('userFrontend.choose', ['InitialisedTransaction' => $transaction]));

        $payment = new Payment();
        $payment->setIntent('authorize')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrl)
            ->setTransactions([$pTransaction])
            ->setExperienceProfileId('XP-TD2U-N4G8-A23K-9T6B');

        try {
            $payment->create($this->getPaypalApiContext());
        } catch (\Exception $ex) {
            abort(503, 'Can\'t create PayPal transaction. ');
        }

        return $payment->getApprovalLink();
    }

    private function getPaypalApiContext()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                Config::get('payment.paypal.clientId'),
                Config::get('payment.paypal.clientSecret')
            )
        );

        $apiContext->setConfig(
            array(
                'mode' => 'live',
                'log.LogEnabled' => true,
                'log.FileName' => storage_path('logs/PayPal.log'),
                'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );

        return $apiContext;
    }

    public function getHumanisedReport(Transaction $transaction)
    {
        $trs = json_decode($transaction->data);
        return "Transaction paypal n°" . $trs->id;
    }

    public function getTransactionFee(Transaction $transaction): int
    {
        return 0;
    }
}
