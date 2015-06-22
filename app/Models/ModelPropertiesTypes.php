<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelPropertiesTypes extends Model
{
    protected $table = 'properties_types';
    public $timestamps = false;

    public function insert($name)
    {
        $insertSQL = '
            INSERT IGNORE INTO properties_types (
                name,
                created_at,
                updated_at
            )
            VALUES (
                :name,
                :created_at,
                :updated_at
            )
            ';

        DB::insert($insertSQL, [
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }
}