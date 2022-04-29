<?php


namespace App\Core\Constructors\Controller;


use App\Core\Constructors\Model\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FilterConstructor
{

    private $model;
    private Request $request;

    private array $filter = [];

    private array $option;

    public function __construct(BaseModel $model, ?Request $request = null, $option = [])
    {
        $this->model = $model;
        if ($request !== null) {
            $this->request = $request;
        } else {
            $this->request = \request();
        }
        $this->option = $option;
    }

    /**
     *
     * @param array $filter
     * @param array|null $option
     * Date : 2021/4/25 23:18
     * Author : 孤鸿渺影
     * @return Builder
     * @throws \ErrorException
     */
    public function filter(array $filter = [], ?array $option = null): Builder
    {
        if (isset($option)) $this->option = $option;
        $field = $this->model->getAllField();   //获取模型所有字段
        $primaryKey = $this->model->getKeyName();   //获取主键
        $this->filter = array_merge($this->request->only($field), $filter);

        if (!empty($this->filter)) $this->filter = $this->formatField($this->filter);
        if ($this->request->exists('where')) {
            $moreFilter = json_decode($this->request->input('where'));
            if (!empty($moreFilter)) {
                foreach ($moreFilter as $key => $value) {
                    if (in_array($key, $field)) {
                        //拼接表名
                        $filterKey = strpos($key, '.') !== false ? $key : "{$this->model->getTable()}.{$key}";
                        if (is_array($value)) {
                            $this->setFilterByArray($filterKey, $value);
                        } else {
                            $this->filter[$filterKey] = $value;
                        }
                    }
                }
            }
        }

        if ($this->request->exists("like")) {
            $likeList = json_decode($this->request->input('like'), true);
            if (!empty($likeList)) {
                $allowFuzzySearch = $this->model->getAllowFuzzySearch();
                $tableName = $this->model->getTable();
                foreach ($likeList as $key => $value) {
                    if (in_array($key, $allowFuzzySearch) && !empty($value)) {
                        $likeColumnName = stripos($key, '.') === false ? ($tableName . "." . $key) : $key;
                        if (stripos($value, "%") === false) {
                            $value = "%" . $value . "%";
                        }
                        $this->filter[] = [$likeColumnName, 'like', $value];
                    }
                }
            }
        }


        foreach ($this->option as $key => $value) {
            $this->model = $this->optionResolver($key, $value);
        }

        $this->model = $this->model->where($this->filter);
        if ($this->request->exists("orderBy")) {
            $orderBy = json_decode($this->request->input('orderBy'), true);
            $orderBy && $this->model = $this->optionResolver('orderBy', $orderBy);
        } elseif (!isset($this->option['orderBy'])) {
            $this->model = $this->model->orderBy($primaryKey ?? $field[0], 'desc');
        }
        return $this->model;
    }

    private function optionResolver($key, $value)
    {
        switch ($key) {
            case 'with':
                $this->model = $this->model->with($value);
                break;
            case 'join':
                if (is_array($value[0])) {
                    foreach ($value as $joinTable) {
                        if (isset($joinTable[3])) {
                            $this->model = $this->model->join(...$joinTable);
                        }
                    }
                } else {
                    if (isset($value[3])) {
                        $this->model = $this->model->join(...$value);
                    }
                }
                break;
            case 'orderBy':
                if (is_string($value)) {
                    $this->model = $this->model->orderBy($value, 'desc');
                }elseif (is_array($value) && is_array($value[0])){
                    foreach ($value as $item){
                        $this->model = $this->model->orderBy($item[0], $item[1] ?? 'desc');
                    }
                } elseif (is_array($value) && isset($value[0])) {
                    $this->model = $this->model->orderBy($value[0], $value[1] ?? 'desc');
                }
                break;

        }
        return $this->model;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }


    public function getFilterString(): string
    {

        if (empty($this->filter)) {
            return '';
        }
        return json_encode($this->filter);
    }

    /**
     * 字段格式化拼接表名
     * @param $filter
     * @return array
     */
    private function formatField($filter)
    {
        $data = [];
        foreach ($filter as $key => $value) {
            $fillteKey = strpos($key, '.') !== false ? $key : "{$this->model->getTable()}.{$key}";
            $data[$fillteKey] = $value;
        }
        return $data;
    }

    /**
     * 数组形式
     * @param string $key
     * @param $value
     * @throws \ErrorException
     */
    private function setFilterByArray(string $key, $value): void
    {
        $allow = [">", "<", "=", "!="];
        if (!isset($value[1])) return;
        if (in_array($value[0], $allow)) {
            $this->model = $this->model->where($key, ...$value);
            //假如存在第二组 [">",10,"<",20]
            if (isset($value[3]) && $value[2] !== $value[0] && in_array($value[2], $allow)) {
                $this->model = $this->model->where($key, $value[2], $value[3]);
            }
        } elseif (in_array($value[0], ["in", "notIn"])) {
            if (is_array($value[1])) {
                $this->model = $value[0] === "in" ? $this->model->whereIn($key, $value[1]) : $this->model->whereNotIn($key, $value[1]);
            } else {
                throw new \ErrorException(sprintf('[%s] 的值必须为数组格式。`%s`不是规范的数组格式', $value[0], $value[1]));
            }
        } elseif ($value[0] === 'like') {
            $value[1] = (string)$value[1];
            if ($value[1] === '') {
                throw new \ErrorException(sprintf('`%s`模糊查询不能为空字符串。', $key));
            } else {
                if (stripos($value[1], '%') === false) $value[1] = "%{$value[1]}%";
                $this->model = $this->model->where($key, 'like', $value[1]);
            }
        }
    }
}
