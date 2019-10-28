<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class Fundation extends GraphQLType
{
    protected $attributes = [
        'name' => 'Fundation',
        'description' => 'Fundation is like a groupe of service (associations ...)'
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID of the fundation'
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Name'
            ],
            'name_prefix' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'French prefix for redaction purpose (l\', le ..)'
            ],
            'mail' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Adminitrative mail'
            ],
            'services' => [
                'type' => Type::listOf(GraphQL::type('Service')),
                'description' => 'Fundation\'s services',
                'always' => ['id', 'host']
            ],
        ];
    }
}
