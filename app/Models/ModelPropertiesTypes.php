<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelPropertiesTypes extends Model
{
    protected $table = 'properties_types';
    public $timestamps = false;

    public function insert($name)
    {
        $insertSQL = '
            INSERT IGNORE INTO properties_types (name)
            VALUES (:name)
            ';

        DB::insert($insertSQL, [
            'name' => $name
        ]);
    }

    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }
}