<?php


namespace App\Core\Constructors\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BatchModel implements BatchModelInterface
{
    private BaseModel $model;

    public function __construct(BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * 批量修改
     * @param $data
     * @return bool|int|void
     */
    public function update(array $data)
    {
        try {
            if (empty($data)) return;
            $sql = "UPDATE {$this->model->getTable()} SET ";
            $firstRow = current($data);
            $updateColumn = array_keys($firstRow);
            if (sizeof($updateColumn) < 2) {
                return false;
            }
            if (isset($firstRow[$this->model->getPrimaryUuidField()])) {
                $referenceColumn = $this->model->getPrimaryUuidField();
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