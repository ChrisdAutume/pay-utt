<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Jobs\createReport;
use App\Models\Report;
use App\Models\Service;
use App\Models\Transaction;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;

class GenerateReportMutation extends Mutation
{
    use DispatchesJobs;

    protected $attributes = [
        'name' => 'GenerateReport',
        'description' => 'Generate a new etupay report.'
    ];

    public function authorize($root, array $args, $ctx, ResolveInfo $resolveInfo = null, Closure $getSelectFields = null): bool
    {
        if (Auth::user() instanceof Service) {
            return Auth::user()->id == $args['service_id'];
        }

        return false;
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'service_id' => ['name' => 'service_id', 'type'=> Type::nonNull(Type::int()), 'defaultValue' => (Auth::user() instanceof Service?Auth::user()->id:null)],
            'start' => ['name' => 'start', 'type'=> GraphQL::type('DateTimeType')],
            'end' => ['name' => 'end', 'type'=> GraphQL::type('DateTimeType')],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $fields = $getSelectFields();
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $service = Service::find($args['service_id']);
        $this->dispatch(new createReport($service, (isset($args['start'])?$args['start']:null), (isset($args['end'])?$args['end']:null)));

        return true;
    }
}
