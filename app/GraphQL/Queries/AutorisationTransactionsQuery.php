<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\AuthorisationTransaction;
use Closure;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;

class AutorisationTransactionsQuery extends Query
{
    protected $attributes = [
        'name' => 'AutorisationTransactionsQuery',
        'description' => 'Query transaction data'
    ];

    public function type(): Type
    {
        return GraphQL::paginate('AutorisationTransaction');
    }

    public function args(): array
    {
        return [
            'limit' => ['name' => 'limit', 'type' => Type::int()],
            'page' => ['name' => 'page', 'type' => Type::int()],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();
        return AuthorisationTransaction
            ::with($with)
            ->select($select)
            ->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
