<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelBrandModel extends Model
{
    protected $table = 'brand_model';
    protected $fillable = ['brand_id', 'status_id', 'name'];

    /**
     * Add new record.
     *
     * @param int $brandID : Brand ID.
     * @param string $name : Brand model name.
     * @param string $status : Status name.
     */
    public function insert($brandID, $name, $status)
    {
        $modelStatus = new ModelStatus();
        $statusID = $modelStatus->getID($status);
        self::firstOrCreate([
            'brand_id' => $brandID,
            'status_id' => $statusID,
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