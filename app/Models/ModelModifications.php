<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelModifications extends Model
{
    protected $table = 'modifications';
    protected $fillable = ['status_id', 'brand_model_id', 'url', 'name'];

    /**
     * Add new model modification.
     *
     * @param string $url : URL to modification source data.
     * @param string $name : Model modification name.
     * @param string $status : Parsing status.
     * @param int $brandModelID : Brand model ID.
     */
    public function insert($url, $name, $status, $brandModelID)
    {
        $modelStatus = new ModelStatus();
        $statusID = $modelStatus->getID($status);
        self::firstOrCreate([
            'status_id' => $statusID,
            'brand_model_id' => $brandModelID,
            'url' => $url,
            'name' => $name
        ]);
    }

    /**
     * Get modification ID.
     *
     * @param string $url : URL to modification source data.
     * @return mixed : Modification ID.
     */
    public function getID($url)
    {
        return DB::table($this->table)
            ->select('id')
            ->where('url', '=', $url)
            ->first()->id;
    }

    /**
     * Update status for modification.
     *
     * @param string $status : New status.
     * @param string $url : URL to modification source data.
     */
    public function updateStatus($status, $url)
    {
        $updateSQL = '
            UPDATE modifications
            SET status_id =
                (
                    SELECT id
                    FROM status
                    WHERE name = "' . $status . '"
                ),
                updated_at = :updated_at
            WHERE url = :url
            ';

        DB::update($updateSQL, [
            'url' => $url,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get modication parsing status.
     *
     * @param string $url : URL to modification source data.
     * @return array|string : Parsing status.
     */
    public function getStatus($url)
    {
        $checkSQL = '
            SELECT status.name
            FROM status
            WHERE status.id = (SELECT status_id FROM modifications WHERE url = :url)';
        $result = DB::select($checkSQL, ['url' => $url]);

        if (count($result) !== 0) {
            $result = $result[0]->name;
            return $result;
        }

        return '';
    }
}