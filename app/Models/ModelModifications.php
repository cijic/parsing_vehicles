<?php namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class ModelModifications extends Model
{
    public $timestamps = false;
    protected $table = 'modifications';

    public function insert($url, $name, $status, $brandModelID)
    {
        $insertSQL = '
            INSERT IGNORE INTO modifications (
                status_id,
                url,
                name,
                brand_model_id)
            VALUES (
              (SELECT id
               FROM status
               WHERE name = "' . $status . '"),
              :url,
              :name,
              :brand_model_id)
            ';

        DB::insert($insertSQL, [
            'url' => $url,
            'name' => $name,
            'brand_model_id' => $brandModelID
        ]);
    }

    public function getID($url)
    {
        return DB::table($this->table)
            ->select('id')
            ->where('url', '=', $url)
            ->first()->id;
    }

    public function updateStatus($status, $url)
    {
        $updateSQL = '
            UPDATE modifications
            SET status_id =
                (
                    SELECT id
                    FROM status
                    WHERE name = "' . $status . '"
                )
            WHERE url = :url
            ';

        DB::update($updateSQL, [
            'url' => $url
        ]);
    }

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