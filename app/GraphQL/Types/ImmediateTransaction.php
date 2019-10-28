<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ImmediateTransaction extends GraphQLType
{
    protected $attributes = [
        'name' => 'ImmediateTransaction',
        'description' => 'A type',
        'model' => \App\Models\ImmediateTransaction::class
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
