<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelBrandModel extends Model
{
    public $timestamps = false;
    protected $table = 'brand_model';

    /**
     * @param $brandID
     * @param $name
     * @param $status
     */
    public function insert($brandID, $name, $status)
    {
        $insertSQL = '
            INSERT IGNORE INTO ' . $this->table . ' (
                brand_id,
                name,
                status_id,
                created_at,
                updated_at)
            VALUES (
                :brand_id,
                :name,
                (SELECT id
                 FROM status
                 WHERE name = "' . $status . '"),
                :created_at,
                :updated_at
                   )
            ';

        DB::insert($insertSQL, [
            'brand_id' => $brandID,
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
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
                ),
                updated_at = :updated_at
            WHERE name = :name
            ';

        DB::update($updateSQL, [
            'name' => $name,
            'updated_at' => date('Y-m-d H:i:s')
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