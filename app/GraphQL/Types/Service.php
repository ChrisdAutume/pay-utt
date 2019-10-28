<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class Service extends GraphQLType
{
    protected $attributes = [
        'name' => 'Service',
        'description' => 'A service represente an application making transaction',
        'model' => \App\Models\Service::class
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID of the service'
            ],
            'host' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Host of the webservice'
            ],
            'fundation' => [
                'type' => GraphQL::type('Fundation'),
                'description' => 'The fundation of the service'
            ],
            'return_url' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Redirection URL when transaction finished.'
            ],
            'callback_url' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'Notification URL when transaction state change',
            ],
            'dev_mode' => [
                'type' => Type::nonNull(Type::boolean()),
                'description' => 'Development mode activation state',
                'alias' => 'is_dev_mode'
            ]
        ];
    }
}
