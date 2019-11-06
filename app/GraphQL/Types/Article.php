<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class Article extends GraphQLType
{
    protected $attributes = [
        'name' => 'Article',
        'description' => 'A type'
    ];

    public function fields(): array
    {
        return [
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Name'
            ],
            'price' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Unit price'
            ],
            'qty' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Quantity'
            ],
        ];
    }
}
