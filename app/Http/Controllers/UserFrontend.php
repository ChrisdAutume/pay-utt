<?php

namespace App\Http\Controllers;

use App\Facades\PaymentLoader;
use App\Models\Transaction;
use App\PaymentProvider\AtosProvider;
use App\PaymentProvider\PaylineProvider;
use App\PaymentProvider\PaypalProvider;
use Illuminate\Http\Request;

class UserFrontend extends Controller
{
    public function paymentGatewayChoice(Request $request, $uuid)
    {
        $transaction = Transaction::where('uuid', $uuid)->where('step', 'INITIALISED')->first();
        if (!$transaction) {
            abort(404);
        }

        $gws = $this->getPaymentGateway($transaction);

        return view('frontend.basket', ['transaction' => $transaction, 'gateways' => $gws]);
    }

    public function paylineCallback(Request $request)
    {
        $provider = new PaylineProvider();
        if ($transaction = $provider->processCallback($request->input('token'))) {
            $payload = PaymentLoader::encryptFromService($transaction->service, $transaction->callbackReturn());
            return redirect($transaction->service->return_url . '?payload=' . $payload);
        }
    }
    public function atosCallback(Request $request)
    {
        $provider = new AtosProvider();
        //if($transaction = $provider->getTransactionFromCallback($request->input('DATA')))
        if ($transaction = $provider->processCallback($request->input('DATA'))) {
            $payload = PaymentLoader::encryptFromService($transaction->service, $transaction->callbackReturn());
            return redirect($transaction->service->return_url . '?payload=' . $payload);
        }
    }

    public function paypalCallback(Request $request)
    {
        $provider = new PaypalProvider();
        if ($transaction = $provider->processCallback($request)) {
            $payload = PaymentLoader::encryptFromService($transaction->service, $transaction->callbackReturn());
            return redirect($transaction->service->return_url . '?payload=' . $payload);
        }
    }

    public function paypalRedirect(Transaction $transaction)
    {
        $paypal = new PaypalProvider();
        if (!$paypal->canBeUsed($transaction)) {
            abort(404, "Can't use Paypal for this transaction");
        }

        return redirect($paypal->getAuthorizeUrl($transaction));
    }

    public function devAction(Transaction $transaction, $action)
    {
        if (!$transaction->service->isDevMode()) {
            abort('404');
        }

        $transaction->provider = 'devMode';
        switch ($action) {
            case 'success':
                $transaction->callbackAccepted();
                break;
            case 'canceled':
                $transaction->callbackCanceled();
                break;
            case 'refused':
                $transaction->callbackRefused();
                break;
            default:
                abort('404', 'Inconnu action');

        }

        $payload = PaymentLoader::encryptFromService($transaction->service, $transaction->callbackReturn());
        return redirect($transaction->service->return_url . '?payload=' . $payload);
    }

    protected function getPaymentGateway(Transaction $transaction)
    {
        $providers = [];
        foreach (config('payment.gateway') as $gateway) {
            $provider = new $gateway;
            if ($provider->canBeUsed($transaction)) {
                $providers[] = $provider;
            }

        }
        return $providers;
    }
}
