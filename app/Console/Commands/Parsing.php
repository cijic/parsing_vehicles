<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Parsing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parsing:auto {service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing autoservices for collecting data.';

    /**
     * Create a new command instance.
     *
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
        $this->info('Starting parsing ' . $this->argument('service') . '...');

        $this->info('Ended parsing of ' . $this->argument('service') . '.');
    }
}
