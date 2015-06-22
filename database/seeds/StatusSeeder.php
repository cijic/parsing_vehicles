<?php

use Illuminate\Database\Seeder;
use App\Models\ModelStatus;

class StatusSeeder extends Seeder
{
    protected $table = 'status';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clearing existing data.
        DB::table($this->table)->delete();

        // Filling table with set of records.
        $this->fill();
    }

    /**
     * Fill table with set of records.
     */
    protected function fill()
    {
        $this->addRecord('parsed');
        $this->addRecord('expected');
    }

    /**
     * Adding record in table.
     *
     * @param string $name : Status name.
     */
    protected function addRecord($name)
    {
        $model = new ModelStatus();
        $model->name = $name;
        $model->save();
    }
}
