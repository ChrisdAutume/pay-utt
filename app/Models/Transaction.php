<?php

namespace App\Models;

use App\Jobs\TransactionClientNotify;
use App\Jobs\TransactionNotify;
use App\Transformers\TransactionTransformer;
use Flugg\Responder\Contracts\Transformable;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Transaction extends Model implements Transformable
{
    protected $table = 'transactions';
    protected $type = 'default';
    protected $casts = [
        'articles' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });
    }

    /**
     * Order a refund order based on amount order
     *
     * @param integer $amount
     * @return boolean
     */
    public function doRefund(float $amount): bool
    {
        return false;
    }

    public function children()
    {
        return $this->hasMany('App\Models\Transaction', 'parent', 'id');
    }

    public function parent()
    {
        return $this->hasOne('App\Models\Transaction', 'id', 'parent');
    }

    public function getSolde(): float
    {
        $tr = $this;
        if ($this->parent) {
            $tr = $this->parent;
        }

        $solde = $tr->amount;
        foreach ($tr->children as $child) {
            if ($child instanceof ImmediateTransaction && $child->step == 'PAID') {
                $solde += $child->amount;
            }
            if ($child instanceof RefundTransaction && $child->step == 'PAID') {
                $solde -= $child->amount;
            }
        }
        return $solde;
    }

    public function callbackAccepted()
    {
        $this->step = 'PAID';
        $this->save();

        dispatch(new TransactionNotify($this));
        dispatch(new TransactionClientNotify($this));
    }

    public function callbackRefused()
    {
        $this->step = 'REFUSED';
        $this->save();

        dispatch(new TransactionNotify($this));
        dispatch(new TransactionClientNotify($this));
    }

    /**
     * To remove
     *
     * @return void
     */
    public function callbackRefunded()
    {
        $this->step = 'REFUNDED';
        $this->save();

        dispatch(new TransactionNotify($this));
        dispatch(new TransactionClientNotify($this));
    }

    public function callbackCanceled()
    {
        $this->step = 'CANCELED';
        $this->save();

        dispatch(new TransactionNotify($this));
        dispatch(new TransactionClientNotify($this));
    }

    public function service()
    {
        return $this->hasOne('App\Models\Service', 'id', 'service_id');
    }

    public function bind($data)
    {
        $data = (array) $data;
        $this->attributes['amount'] = intval($data['amount']);
        $this->attributes['description'] = (isset($data['description']) ? $data['description'] : null);
        $this->attributes['client_mail'] = (isset($data['client_mail']) ? $data['client_mail'] : null);
        $this->attributes['service_data'] = (isset($data['service_data']) ? $data['service_data'] : null);
        $this->firstname = (isset($data['firstname']) ? $data['firstname'] : null);
        $this->lastname = (isset($data['lastname']) ? $data['lastname'] : null);

        if (isset($data['articles']) && is_array($data['articles'])) {
            $articles = [];
            $total = 0;
            foreach ($data['articles'] as $article) {
                $article = (array) $article;
                $articles[] = [
                    'name' => $article['name'],
                    'price' => intval($article['price']),
                    'qty' => intval($article['quantity']),
                ];
                $total += intval($article['price']) * intval($article['quantity']);
            }

            if ($total != $this->attributes['amount']) {
                abort(400, 'Invalid total amount');
            } else {
                $this->articles = $articles;
            }

        } else {
            abort(400, 'Missing articles field.');
        }

    }

    public function callbackReturn()
    {
        $rtn = [
            'transaction_id' => $this->id,
            'type' => $this->getType(),
            'amount' => $this->amount,
            'service_data' => $this->service_data,
            'step' => $this->step,
        ];

        if (is_object($this->parent)) {
            $rtn['service_data'] = $this->parent->service_data;
            $rtn['parent_transaction_id'] = $this->parent->id;
        }

        return $rtn;
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        switch ($attributes->type) {
            case 'PAYMENT':
                $model = new ImmediateTransaction([], true);
                break;

            case 'AUTHORISATION':
                $model = new AuthorisationTransaction([], true);
                break;

            case 'REFUND':
                $model = new RefundTransaction([], true);
                break;

            default:
                $model = $this->newInstance([], true);
        }

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->connection);

        $model->checkExistence();
        return $model;
    }

    public function getType()
    {
        return $this->type;
    }
    public function checkExistence()
    {
        if ($this->attributes['id']) {
            $this->exists = true;
        }

    }

    public function getProvider()
    {
        if (isset($this->attributes['provider'])) {
            $provider = config('payment.gateway')[$this->provider];
            return new $provider;
        }
    }

    /**
     * Get a transformer for the class.
     *
     * @return \Flugg\Responder\Transformers\Transformer|string|callable
     */
    public function transformer()
    {
        return TransactionTransformer::class;
    }

}
