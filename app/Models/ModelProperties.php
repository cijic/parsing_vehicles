<?php namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelProperties extends Model
{
    public $timestamps = false;
    protected $table = 'properties';

    public function insert($data)
    {
        $insertSQL = '
            INSERT IGNORE INTO ' . $this->table . ' (names_id, modification_id, value)
            VALUES ';

        $size = count($data);

        for ($i = 0; $i < $size; $i++) {
            $insertSQL .= '('
                . '"' . $data[$i]['names_id'] . '"' . ','
                . '"' . $data[$i]['modification_id'] . '"' . ','
                . '"' . addslashes($data[$i]['value']) . '"' .
                ')';

            if ($i < (count($data) - 1)) {
                $insertSQL .= ',';
            }
        }

        DB::insert($insertSQL);
    }
}
