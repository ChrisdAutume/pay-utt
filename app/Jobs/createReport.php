<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\Service;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class createReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $service, $start, $end, $sendReport;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Service $service, \DateTime $start = null, \DateTime $end = null, bool $sendReport = false)
    {
        $this->service = $service;
        $this->start = $start;
        $this->end = $end;
        $this->sendReport = $sendReport;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $trs = Transaction::select('id', 'amount', 'step', 'type')
            ->where('service_id', $this->service->id)
            ->where('provider', '!=', 'devMode')
            ->whereNull('report_id');
        if(isset($this->start))
        {
            $trs = $trs->where('created_at', '>=',  $this->start);
        }
        if(isset($this->end))
        {
            $trs = $trs->where('created_at', '<',  $this->end);
        }

        $trs = $trs->get();

        if($trs->count() == 0)
            return true;

        $report = new Report();
        $report->service_id = $this->service->id;
        $report->save();

        foreach($trs as $tr)
        {
            $tr->report_id = $report->id;
            $tr->save();
        }
        $report = Report::find($report->id);
        $report->refreshAmount();
        $report->save();

        if($this->sendReport)
            $report->sendReport();

    }
}
