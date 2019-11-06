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

class TransactionsQuery extends Query
{
    protected $attributes = [
        'name' => 'TransactionsQuery',
        'description' => 'Query transaction data'
    ];

    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        if (Auth::user() instanceof Service && isset($args['service_id'])) {
            return Auth::user()->id == $args['service_id'];
        }

        return false;
    }

    public function type(): Type
    {
        return GraphQL::paginate('Transaction');
    }

    public function args(): array
    {
        return [
            'limit' => ['name' => 'limit', 'type' => Type::int(), 'defaultValue' => 50],
            'page' => ['name' => 'page', 'type' => Type::int(), 'defaultValue' => 1],
            'service_id' => ['name' => 'service_id', 'type'=> Type::int(), 'defaultValue' => (Auth::user() instanceof Service?Auth::user()->id:null)],
            'step' => ['name' => 'step', 'type'=> GraphQL::type('Step')],
            'type' => ['name' => 'type', 'type'=> GraphQL::type('TransactionType')],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $select[] = 'transactions.type';
        $with = $fields->getRelations();
        $query = Transaction
            ::with($with)
            ->select($select);

        if(isset($args['service_id']))
        {
            $query = $query->where('service_id', $args['service_id']);
        }
        if(isset($args['step']))
        {
            $query = $query->where('step', $args['step']);
        }
        if(isset($args['type']))
        {
            $query = $query->where('type', $args['type']);
        }
        return $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
