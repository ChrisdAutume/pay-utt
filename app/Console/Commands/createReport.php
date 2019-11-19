<?php

namespace App\Console\Commands;

use App\Jobs\createReport as AppCreateReport;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class createReport extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:create';
    protected $dateFormat = "Y-m-d H:i";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permet de lancer la création d\'un report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->info('Please answer to question below.');
        $this->info('Service registred: ');
        $this->table(['id','name'], Service::all(['id','host']));
        $service = Service::findOrFail($this->ask('Service ID: '));

        $start = $this->ask('Date de début ('.$this->dateFormat.'): ', null);
        $start = \DateTime::createFromFormat($this->dateFormat, $start);

        $end = $this->ask('Date de fin ('.$this->dateFormat.'): ', null);
        $end = \DateTime::createFromFormat($this->dateFormat, $start);

        $sendReport = $this->ask('Envoyer par mail ? [y/n]', 'y');

        $this->dispatch(new \App\Jobs\createReport($service,($start?$start:null),($end?$end:null), ($sendReport == 'y')));
        $this->info('Création du report programmé');
    }
}
