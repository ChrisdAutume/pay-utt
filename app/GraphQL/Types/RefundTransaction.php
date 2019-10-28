<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class RefundTransaction extends GraphQLType
{
    protected $attributes = [
        'name' => 'RefundTransaction',
        'description' => 'A type',
        'model' => \App\Models\RefundTransaction::class
    ];

    public function fields(): array
    {
        return [

        ];
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type('Transaction')
        ];
    }
}
