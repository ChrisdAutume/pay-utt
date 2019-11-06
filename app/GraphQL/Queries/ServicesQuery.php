<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\Service;
use Closure;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Auth;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;

class ServicesQuery extends Query
{
    protected $attributes = [
        'name' => 'ServiceQuery',
        'description' => 'Query service data'
    ];

    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        if (Auth::user() instanceof Service && isset($args['id'])) {
            return Auth::user()->id == $args['id'];
        }

        return false;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Service'));
    }

    public function args(): array
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int(), 'defaultValue' => (Auth::user() instanceof Service?Auth::user()->id:null)],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        /** @var SelectFields $fields */
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();
        $services = Service::select($select)->with($with);

        if (isset($args['id'])) {
            $services = $services->where('id' , $args['id']);
        }

        return $services->get();
    }
}
