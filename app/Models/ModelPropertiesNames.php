<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelPropertiesNames extends Model
{
    protected $table = 'properties_names';
    protected $fillable = ['name', 'type_id'];

    public function insert($name, $typeID)
    {
        self::firstOrCreate([
            'name' => $name,
            'type_id' => $typeID
        ]);
    }

    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }
}