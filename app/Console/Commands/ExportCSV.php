<?php

namespace App\Console\Commands;

use App\Models\ModelBrand;
use App\Models\ModelModifications;
use App\Models\ModelProperties;
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
    protected $signature = 'parsing:export {table=properties}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export specified table into csv file.';

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
        $table = $this->argument('table');
        /*
                $timeStart = microtime(true);
                $data = $this->getData($table);
                $timeEnd = microtime(true);
                echo 'Data receiving time: ' . ($timeEnd - $timeStart) . PHP_EOL;

                $timeStart = microtime(true);
                $this->exportData($table, $data);
                $timeEnd = microtime(true);
                echo 'Exporting data time: ' . ($timeEnd - $timeStart) . PHP_EOL;
        */
        $timeStart = microtime(true);
        $model = $this->getModel($table);
        $timeEnd = microtime(true);
        echo 'Data receiving time: ' . ($timeEnd - $timeStart) . PHP_EOL;
        /*
                $timeStart = microtime(true);
                $this->exportModelData($table, $model);
                $timeEnd = microtime(true);
                echo 'Exporting data time: ' . ($timeEnd - $timeStart) . PHP_EOL;
        */

        $timeStart = microtime(true);
        $this->exportSequentially($table, $model);
        $timeEnd = microtime(true);
        echo 'Exporting data time: ' . ($timeEnd - $timeStart) . PHP_EOL;
    }

    private function getModel($table)
    {
        $model = null;

        switch ($table) {
            case 'brand':
                $model = new ModelBrand();
                break;

            case 'properties':
                $model = new ModelProperties();
                break;

            case 'modifications':
                $model = new ModelModifications();
                break;

            default:
                $model = new ModelProperties();
                break;
        }

        return $model;
    }

    private function exportSequentially($table, $model)
    {
        Excel::create($table . '_seq_model', function ($excel) use ($model) {
            $excel->sheet('sheet1', function ($sheet) use ($model) {
                $count = $model::count();

                $row = $model->first()->toArray();
                $sheet->appendRow($row);

                for ($i = 1; $i < $count; $i += 10) {
                    $rows = $model->skip($i)->take(10)->get()->toArray();
                    $sheet->rows($rows);
                }
            });
        })->store('csv', storage_path('export/csv'));
    }

    private function getData($table)
    {
        $rows = DB::table($table)->get();
        $data = [];
        $count = count($rows);

        for ($i = 0; $i < $count; $i++) {
            $row = $rows[$i];
            $keys = array_keys(get_object_vars($row));

            $result = [];

            foreach ($keys as $key) {
                $result[$key] = $row->$key;
            }

            $data[] = $result;
        }

        return $data;
    }

    private function exportData($table, $data)
    {
        Excel::create($table, function ($excel) use ($data) {
            $excel->sheet('sheet1', function ($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->store('csv', storage_path('export/csv'));
    }

    private function exportModelData($table, $model)
    {
        Excel::create($table . '_model', function ($excel) use ($model) {
            $excel->sheet('sheet1', function ($sheet) use ($model) {
                $sheet->fromModel($model::all());
            });
        })->store('csv', storage_path('export/csv'));
    }
}
