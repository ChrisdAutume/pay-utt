<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendReport extends Mailable
{
    use Queueable, SerializesModels;

    protected $report;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->subject('Rapport etupay #'.$report->id);
        $this->cc('bde@utt.fr');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.report')
            ->with(['report' => $this->report, 'sujet' => $this->subject])
            ->attach($this->report->getExcelTransactionsExport()->getFile(), ['as' => 'etupay_report_'.$this->report->id.'.xlsx']);
    }
}
