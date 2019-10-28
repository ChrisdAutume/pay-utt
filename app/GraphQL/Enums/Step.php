<?php

declare(strict_types=1);

namespace App\GraphQL\Enums;

use App\Models\Transaction;
use Rebing\GraphQL\Support\EnumType;

class Step extends EnumType
{
    protected $attributes = [
        'name' => 'Step',
        'description' => 'Different step of a transaction',
        'values' => [
            'INITIALISED' => [
                'value' => 'INITIALISED',
                'description' => 'First step of the transaction, before paying',
            ],
            'PAID' => [
                'value' => 'PAID',
                'description' => 'Transaction paid',
            ],
            'REFUSED' => [
                'value' => 'REFUSED',
                'description' => 'Transaction have been refused',
            ],
            'REFUNDED' => [
                'value' => 'REFUNDED',
                'description' => 'Transaction have been refunded',
            ],
            'AUTHORISATION' => [
                'value' => 'AUTHORISATION',
                'description' => 'Transaction have been authorized by the provider, not paid.',
            ],
            'CANCELED' => [
                'value' => 'CANCELED',
                'description' => 'Transaction have been canceled by the user',
            ],
        ],
    ];
}
