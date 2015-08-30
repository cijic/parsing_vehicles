<?php

namespace App\Console\Commands;

use DB;
use Excel;
use Illuminate\Console\Command;

class ExportCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parsing:export {table=properties} {--filename=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export specified table into csv file.';

    /**
     * Create a new command instance.
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
        $table = $this->argument('table');
        $filename = $table;

        if ($this->option('filename')) {
            $filename = $this->option('filename');
        }

        $timeStart = microtime(true);
        $this->exportSequentially($table, $filename);
        $timeEnd = microtime(true);
        echo 'Exporting data time: ' . ($timeEnd - $timeStart) . PHP_EOL;
    }

    private function exportSequentially($table, $filename)
    {
        Excel::create($filename, function ($excel) use ($table) {
            $excel->sheet('sheet1', function ($sheet) use ($table) {
                $count = DB::table($table)->count();
                $step = 10;

                for ($i = 0; $i < $count; $i += $step) {
                    $resultRows = DB::table($table)->skip($i)->take($step)->get();
                    $countResultRows = count($resultRows);
                    $rows = [];

                    for ($j = 0; $j < $countResultRows; $j++) {
                        $rows[] = (array)$resultRows[$j];
                    }

                    $sheet->rows($rows);
                }
            });
        })->store('csv', storage_path('export/csv'));
    }
}
