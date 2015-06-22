<?php namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelBrandModel extends Model
{
    public $timestamps = false;
    protected $table = 'brand_model';

    public function insert($brandID, $name, $status)
    {
        $insertSQL = '
            INSERT IGNORE INTO ' . $this->table . ' (brand_id, name, status_id)
            VALUES (
                :brand_id,
                :name,
                (SELECT id
                 FROM status
                 WHERE name = "' . $status . '")
                   )
            ';

        DB::insert($insertSQL, [
            'brand_id' => $brandID,
            'name' => $name
        ]);
    }

    public function getID($brandID, $name)
    {
        return DB::table($this->table)
            ->select('id')
            ->where('name', '=', $name)
            ->where('brand_id', '=', $brandID)
            ->first()->id;
    }

    public function updateStatus($status, $name)
    {
        $updateSQL = '
            UPDATE ' . $this->table . '
            SET status_id =
                (
                    SELECT id
                    FROM status
                    WHERE name = "' . $status . '"
                )
            WHERE name = :name
            ';

        DB::update($updateSQL, [
            'name' => $name
        ]);
    }

    public function getStatus($name)
    {
        $checkSQL = '
            SELECT status.name
            FROM status
            WHERE status.id = (SELECT status_id FROM ' . $this->table . ' WHERE name = :name)';
        $result = DB::select($checkSQL, ['name' => $name]);

        if (count($result) !== 0) {
            $result = $result[0]->name;
            return $result;
        }

        return '';
    }
}