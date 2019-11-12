<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Report;
use App\Models\Service;
use App\Models\Transaction;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\Auth;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;

class GenerateReportMutation extends Mutation
{
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
        return GraphQL::type('Report');
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

        $trs = Transaction::where('service_id', $args['service_id'])->whereIsNull('report_id');
        if(isset($args['start']))
        {
            $trs = $trs->where('created_at', '>=',  $args['start']);
        }
        if(isset($args['end']))
        {
            $trs = $trs->where('created_at', '<',  $args['end']);
        }

        $trs = $trs->get();

        if($trs->count = 0)
            return [];

        $report = new Report();
        $report->service_id = $args['service_id'];
        $report->transactions = $trs;
        $report->save();

        return $report;
    }
}
