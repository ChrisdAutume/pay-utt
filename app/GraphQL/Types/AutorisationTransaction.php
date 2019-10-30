<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class AutorisationTransaction extends GraphQLType
{
    protected $attributes = [
        'name' => 'AutorisationTransaction',
        'description' => 'A type',
        'model' => \App\Models\AuthorisationTransaction::class
    ];

    public function fields(): array
    {
        $interface = GraphQL::type('Transaction');
        return $interface->getFields();
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type('Transaction')
        ];
    }
}
