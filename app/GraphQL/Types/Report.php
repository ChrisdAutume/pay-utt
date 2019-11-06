<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class Report extends GraphQLType
{
    protected $attributes = [
        'name' => 'Report',
        'description' => 'Report is a report'
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID of the report'
            ],
            'amount' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Host of the webservice'
            ],
            'fundation' => [
                'type' => GraphQL::type('Fundation'),
                'description' => 'The fundation of the service'
            ],
            'transactions' => [
                'type' => Type::listOf(GraphQL::type('Transaction')),
                'description' => 'List of transactions'
            ],
            'service' => [
                'type' => GraphQL::type('Service'),
                'description' => 'Service transaction owner',
            ],
            'created_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Creation date of the transaction'
            ],
            'updated_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Update date of the transaction'
            ],
            'validated_at' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Validation date of the report'
            ]
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return (string) $root->created_at;
    }

    protected function resolveUpdatedAtField($root, $args)
    {
        return (string) $root->updated_at;
    }

    protected function resolveValidatedAtField($root, $args)
    {
        return (string) $root->validated_at;
    }
}
