<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelBrand extends Model
{
    protected $table = 'brand';
    protected $fillable = ['status_id', 'name'];

    /**
     * Add new brand in DB.
     *
     * @param $name : Brand name.
     * @param $status : Brand status.
     */
    public function insert($name, $status)
    {
        $modelStatus = new ModelStatus();
        $statusID = $modelStatus->getID($status);
        self::firstOrCreate([
            'status_id' => $statusID,
            'name' => $name
        ]);
    }

    /**
     * Get ID of brand.
     *
     * @param $name : Brand name.
     * @return mixed : ID.
     */
    public function getID($name)
    {
        return DB::table($this->table)->select('id')->where('name', '=', $name)->first()->id;
    }

    /**
     * Update status of brand parsing.
     * @param $status : New status.
     * @param $name : Brand name.
     */
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

    /**
     * Get status of parsing of brand.
     * @param $name : Brand name.
     * @return array|string: Status.
     */
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