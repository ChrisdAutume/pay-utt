<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Service;
use App\Models\Transaction;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Auth;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;

class CreateRefundTransaction extends Mutation
{
    protected $attributes = [
        'name' => 'CreateRefundTransaction',
        'description' => 'Operation to initialiate a refund transaction'
    ];

    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        $this->transaction = Transaction::findOrFail($args['parent_id']);
        if (Auth::user() instanceof Service) {
        return Auth::user()->id == $this->transaction->service_id;
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
            'parent_id' => ['name' => 'parent_id', 'type' => Type::nonNull(Type::int())],
            'amount' => ['name' => 'amount', 'type' => Type::nonNull(Type::int())]
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $this->transaction->doRefund($args['amount']);
    }
}
