<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelPropertiesTypes extends Model
{
    protected $table = 'properties_types';
    protected $fillable = ['name'];

    public function insert($name)
    {
        self::firstOrCreate([
            'name' => $name
        ]);
    }

    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }
}