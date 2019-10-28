<?php

declare(strict_types=1);

namespace App\GraphQL\Enums;

use Rebing\GraphQL\Support\EnumType;

class TransactionType extends EnumType
{
    protected $attributes = [
        'name' => 'Transaction Type',
        'description' => 'Types of a transaction',
        'values' => [
            'PAYMENT' => [
                'value' => 'PAYMENT',
                'description' => 'Direct transaction',
            ],
            'AUTHORISATION' => [
                'value' => 'AUTHORISATION',
                'description' => 'Delayed transaction, just making an authorisation',
            ],
            'REFUND' => [
                'value' => 'REFUND',
                'description' => 'Refund transaction',
            ],
        ],
    ];
}
