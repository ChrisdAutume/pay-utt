<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\RefundTransaction;
use Closure;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;

class RefundTransactionsQuery extends Query
{
    protected $attributes = [
        'name' => 'RefundTransactionsQuery',
        'description' => 'Query transaction data'
    ];

    public function type(): Type
    {
        return GraphQL::paginate('RefundTransaction');
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
        return RefundTransaction
            ::with($with)
            ->select($select)
            ->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
