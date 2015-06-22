<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelPropertiesNames extends Model
{
    protected $table = 'properties_names';
    public $timestamps = false;

    public function insert($name, $typeID)
    {
        $insertSQL = '
            INSERT IGNORE INTO properties_names (
                type_id,
                name,
                created_at,
                updated_at
            )
            VALUES (
                :type_id,
                :name,
                :created_at,
                :updated_at
            )
            ';

        DB::insert($insertSQL, [
            'name' => $name,
            'type_id' => $typeID,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }
}