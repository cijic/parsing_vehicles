<?php

namespace App\Console\Commands;

use App\Console\Commands\Parsers\ParserAutobanBy;
use App\Console\Commands\Parsers\ParserAvtomarketRu;
use Illuminate\Console\Command;

class Parsing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parsing:auto {--service=avtomarket.ru}';

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
        $service = $this->option('service');
        $parser = null;
        $parser = $this->parserCreation($service);

        if (!empty($parser)) {
            $this->info('Starting parsing ' . $this->option('service') . '...');
            $parser->parse();
            $this->info('Ended parsing of ' . $this->option('service') . '.');
        } else {
            $this->info('Parser stopped even not having begun.');
        }
    }

    protected function parserCreation($service)
    {
        if ($service === 'avtomarket.ru') {
            return new ParserAvtomarketRu();
        } elseif ($service === 'autoban.by') {
            return new ParserAutobanBy();
        }

        return null;
    }
}
