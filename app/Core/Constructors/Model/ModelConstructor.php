<?php


namespace App\Core\Constructors\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModelConstructor
{
    private Model $model;
    private Request $request;

    public function __construct(Model $model, ?Request $request = null)
    {
        $this->model = $model;
        if ($request !== null) {
            $this->request = $request;
        } else {
            $this->request = \request();
        }
    }

    /**
     * 批量修改
     * @param $data
     * @return bool|int|void
     */
    public function batchUpdate(array $data)
    {
        try {
            if (empty($data)) return;
            $sql = "UPDATE {$this->model->getTable()} SET ";
            $firstRow = current($data);
            $updateColumn = array_keys($firstRow);
            if (sizeof($updateColumn) < 2) {
                return false;
            }
            if (isset($firstRow[$this->model->getUuidColumnName()])) {
                $referenceColumn = $this->model->getUuidColumnName();
            } else if (isset($firstRow[$this->model->getKeyName()])) {
                $referenceColumn = $this->model->getKeyName();
            } else {
                $referenceColumn = $firstRow[0];
            }
            $sets = [];
            $bindings = [];
            foreach ($updateColumn as $column) {
                if ($column == $referenceColumn) continue;
                $setSql = " `$column` = (CASE ";
                foreach ($data as $item) {
                    //若是没有指定键则不作处理
                    if ($item[$referenceColumn]) {
                        $setSql .= " WHEN `$referenceColumn` = ? THEN ? ";
                        $bindings[] = $item[$referenceColumn];
                        $bindings[] = $item[$column];
                    }
                }
                $setSql .= " END) ";
                $sets[] = $setSql;
            }
            $sql .= implode(', ', $sets);
            $whereIn = collect($data)->pluck($referenceColumn)->values()->all();
            $bindings = array_merge($bindings, $whereIn);
            $whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
            $sql = rtrim($sql, ", ") . " WHERE `$referenceColumn` IN ($whereIn)";
            return DB::connection($this->model->getConnectionName())->update($sql, $bindings);
        } catch (\Exception $exception) {
            return false;
        }

    }


}