<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelPropertiesNames extends Model
{
    protected $table = 'properties_names';
    public $timestamps = false;

    public function insert($name, $typeID)
    {
        $insertSQL = '
            INSERT IGNORE INTO properties_names (type_id, name)
            VALUES (:type_id, :name)
            ';

        DB::insert($insertSQL, [
            'name' => $name,
            'type_id' => $typeID
        ]);
    }

    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }
}