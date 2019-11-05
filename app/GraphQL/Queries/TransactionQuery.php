<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\RefundTransaction;
use App\Models\Service;
use App\Models\Transaction;
use Closure;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;

class TransactionQuery extends Query
{
    protected $attributes = [
        'name' => 'TransactionQuery',
        'description' => 'Query specific transaction data'
    ];

    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        if (Auth::user() instanceof Service && isset($args['id'])) {
            $transaction = Transaction::find($args['id']);
            return Auth::user()->id == $transaction->service->id;
        }

        return false;
    }

    public function type(): Type
    {
        return GraphQL::type('Transaction');
    }

    public function args(): array
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::nonNull(Type::int())],

        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();
        $query = Transaction
            ::with($with)
            ->select($select)
            ->where('id', $args['id']);
        return $query->first();
    }
}
