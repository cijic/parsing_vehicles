<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelStatus extends Model
{
    protected $table = 'status';

    /**
     * Get status ID.
     * @param string $status : Status name.
     * @return mixed : Status ID.
     */
    public function getID($status)
    {
        return self::where('name', $status)->first()->id;
    }
}