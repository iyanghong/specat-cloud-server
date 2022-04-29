<?php


namespace App\Core\Constructors\Model;


use App\Core\Enums\SqlTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BaseModel extends Model
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected array $rule;
    /**
     * 规则报错信息
     *
     * @var array
     */
    protected $message;

    /**
     * 字段说明
     *
     * @var array
     */
    protected array $attributeAlias;
    /**
     * 自动填充字段
     *
     * @var array
     */
    protected array $autoFill;
    /**
     * 自动处理字段
     * @var array
     */
    protected array $autoHandle;
    /**
     * Uuid字段
     * @var string
     */
    protected string $primaryUuidField;
    /**
     * 数据表说明
     * @var string
     */
    protected string $tableInfo;
    /**
     * 允许模糊查询
     * @var array
     */
    protected array $allowFuzzySearch;

    /**
     * 据字段类型
     * @var array
     */
    protected array $fieldTypes;


    /**
     * 字段名是否转换驼峰命名
     * @var bool
     */
    protected bool $camelKey = false;

    /**
     * 字段名是否需要转换?
     * camel|snake
     * @var string
     */
    protected string $keyWordsTypes = '';

//    /**
//     * @Describe :  格式化时间
//     * @param $value
//     * @return false|int
//     * @Author : TS
//     * @Date : 2019/12/28 0:50
//     */
//    public function fromDateTime($value)
//    {
//        return strtotime(parent::fromDateTime($value));
//    }


    /**
     * 插入数据验证器
     * @param array|null $data
     * @param Request|null $request
     * @author : 孤鸿渺影
     * @return ModelValidatorInterface
     */
    public function createValidate(?array $data = null, ?Request $request = null): ModelValidatorInterface
    {
        $validator = new ModelValidator($this, $request);
        return $validator->validateCreate($data);
    }

    /**
     * 修改数据验证器
     * @param null $id
     * @param array|null $data
     * @param Request|null $request
     * @author : 孤鸿渺影
     * @return ModelValidatorInterface
     */
    public function updateValidator($id = null, ?array $data = null, ?Request $request = null): ModelValidatorInterface
    {
        $validator = new ModelValidator($this, $request);
        return $validator->validateUpdate($id, $data);
    }

    /**
     * 批量操作
     * @return BatchModelInterface
     */
    public function batch(): BatchModelInterface
    {
        return new BatchModel($this);
    }

    /**
     * 获取所有可操作字段
     * @return mixed
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * @Notes: 获取表字段（包括拼接table.）
     * @Interface getAllField
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-16   9:40
     */
    public function getAllField(): array
    {
        $field = $this->fillable;
        if ($this->primaryKey !== '') {
            array_push($field, $this->primaryKey);
        }
        $allField = $field;
        foreach ($field as $item) {
            array_push($allField, $this->table . '.' . $item);
        }
        return $allField;
    }

    /**
     * @Describe : 获取表字段
     * @return mixed
     * @Author : TS
     * @Date : 2019/12/28 23:37
     */
    public function getField($primaryKey = true): array
    {
        $field = $this->fillable;
        if ($primaryKey) {
            if ($this->primaryKey !== '') {
                array_push($field, $this->primaryKey);
            }
        }
        return $field;
    }

    /**
     * 根据uuid获取单个实体
     * @param string $uuid
     * @param array $columns
     * Date : 2021/4/24 12:44
     * Author : 孤鸿渺影
     * @return Builder|Model|object|null
     */
    public function findUuid(string $uuid, array $columns = ['*'])
    {
        if ($this->primaryUuidField) {
            /* @var $this Builder */
            return $this->where([$this->primaryUuidField => $uuid])->first($columns);
        }
        return null;
    }

    /**
     * 根据Id或者uuid获取单个实体
     * @param $value
     * @param array $columns
     * Date : 2021/4/24 12:44
     * Author : 孤鸿渺影
     * @return Builder|Builder[]|Collection|Model|object|null
     */
    public function findIdOrUuid($value, array $columns = ['*'],$filter = [])
    {
        if (empty($this->primaryUuidField)) {
            /* @var $this Builder */
            return $this->find($value, $columns);
        }
        $column = is_numeric($value) ? $this->primaryKey : $this->primaryUuidField;
        $filter[$column] = $value;
        /* @var $this Builder */
        return $this->where($filter)->first($columns);
    }

    /**
     * 重写toArray
     * 根据不同要求解析不同key形式：驼峰命名|蛇形命名
     * @return array
     */
    public function toArray(): array
    {
        if ($this->keyWordsTypes === '') {
            return parent::toArray();
        }
        $data = [];
        foreach (parent::toArray() as $key => $value) {
            $data[$this->transformKeyWords($key)] = $value;
        }
        return $data; // TODO: Change the autogenerated stub
    }

    /**
     * 单词转换，驼峰|下划线分隔
     * @param $name
     * @author : 孤鸿渺影
     * @return string
     */
    protected function transformKeyWords($name): string
    {
        switch ($this->keyWordsTypes) {
            case 'camel':
                $name = Str::camel($name);
                break;
            case 'snake':
                $name = Str::snake($name);
                break;
        }
        return $name;
    }

    /**
     * The attributes that should be hidden for arrays.
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * 获取数据类型转换
     * @return array
     */
    public function getCasts(): array
    {
        return $this->casts;
    }


    /**
     * @Notes: 获取数据源
     * @Interface getConnections
     * @return string
     * @Author: TS
     * @Time: 2020-06-15   16:31
     */
    public function getConnections(): string
    {
        if (!empty($this->connection)) {
            return $this->connection;
        }
        return '';
    }

    /**
     * 获取验证规则
     * @return array
     */
    public function getRule(): array
    {
        return $this->rule ?? [];
    }

    /**
     * 获取字段验证规则
     * @param string $attribute 字段名
     * @return string
     */
    public function getFieldRule(string $attribute): string
    {
        return $this->rule[$attribute] ?? '';
    }

    /**
     * @param array $rule
     */
    public function setRule(array $rule): void
    {
        $this->rule = $rule;
    }

    /**
     * 获取验证规则错误信息
     * @return array
     */
    public function getMessage(): array
    {
        return $this->message ?? [];
    }

    /**
     * 获取字段验证规则错误信息
     * @param string $attribute
     * @return string
     */
    public function getFieldMessage(string $attribute): string
    {
        return $this->message[$attribute] ?? '';
    }

    /**
     * @param array $message
     */
    public function setMessage(array $message): void
    {
        $this->message = $message;
    }

    /**
     * 获取字段说明列表
     * @return array
     */
    public function getAttributeAliasList(): array
    {
        return $this->attributeAlias ?? [];
    }

    /**
     * 获取字段说明
     * @param string $attribute 字段名
     * @return string
     */
    public function getAttributeAlias(string $attribute): string
    {
        return $this->attributeAlias[$attribute] ?? $attribute;
    }

    /**
     * @param array $attributeAlias
     */
    public function setAttributeAlias(array $attributeAlias): void
    {
        $this->attributeAlias = $attributeAlias;
    }

    /**
     * 获取自动填充字段列表
     * @return array
     */
    public function getAutoFill(): array
    {
        return $this->autoFill ?? [];
    }

    /**
     *
     * @param array $autoFill
     */
    public function setAutoFill(array $autoFill): void
    {
        $this->autoFill = $autoFill;
    }

    /**
     * 获取自动处理字段列表
     * @return array
     */
    public function getAutoHandle(): array
    {
        return $this->autoHandle ?? [];
    }

    /**
     * @param array $autoHandle
     */
    public function setAutoHandle(array $autoHandle): void
    {
        $this->autoHandle = $autoHandle;
    }

    /**
     * 获取UUid字段
     * @return string
     */
    public function getPrimaryUuidField(): string
    {
        return $this->primaryUuidField;
    }

    /**
     * @param string $primaryUuidField
     */
    public function setPrimaryUuidField(string $primaryUuidField): void
    {
        $this->primaryUuidField = $primaryUuidField;
    }

    /**
     * 获取表说明
     * @return string
     */
    public function getTableInfo(): string
    {
        return $this->tableInfo;
    }

    /**
     * @param string $tableInfo
     */
    public function setTableInfo(string $tableInfo): void
    {
        $this->tableInfo = $tableInfo;
    }

    /**
     * 获取允许模糊查询字段
     * @return array
     */
    public function getAllowFuzzySearch(): array
    {
        return $this->allowFuzzySearch ?? [];
    }

    /**
     * @param array $allowFuzzySearch
     */
    public function setAllowFuzzySearch(array $allowFuzzySearch): void
    {
        $this->allowFuzzySearch = $allowFuzzySearch;
    }

    /**
     * 获取字段类型列表
     * @return array
     */
    public function getFieldTypeList(): array
    {
        return $this->fieldTypes ?? [];
    }

    /**
     * 获取字段类型
     * @param string $attribute 字段名
     * @return string
     */
    public function getFieldType(string $attribute): string
    {
        if (isset($this->fieldTypes[$attribute])) {
            $types = $this->fieldTypes[$attribute];
            $temp = explode(':', $types);
            return SqlTypes::getMessage($temp[0]);
        }
        return '';
    }

    /**
     * 获取字段长度
     * @param string $attribute 字段名
     * @return int
     */
    public function getFieldLength(string $attribute): int
    {
        if (isset($this->fieldTypes[$attribute])) {
            $types = $this->fieldTypes[$attribute];
            $temp = explode(',', $types);
            $lengthTemp = explode(':', $temp[0]);
            return isset($lengthTemp[1]) ? $lengthTemp[1] : 0;
        }
        return 0;
    }

    /**
     * 获取字段精度
     * @param string $attribute 字段名
     * @return int
     */
    public function getFieldPrecision(string $attribute): int
    {
        if (isset($this->fieldTypes[$attribute])) {
            $temp = explode(':', $this->fieldTypes[$attribute]);
            return isset($temp[1]) ? $temp[1] : 0;
        }
        return 0;
    }

    /**
     * @param array $fieldTypes
     */
    public function setFieldTypes(array $fieldTypes): void
    {
        $this->fieldTypes = $fieldTypes;
    }

    /**
     * 获取记录创建用户
     * @author : 孤鸿渺影
     * @return $this
     */
    public function withCreateUser()
    {
        $this->with('CreateUser:user_name,user_header,user_uuid,user_id');
        return $this;
    }
    public function CreateUser()
    {
        return self::hasOne('\App\Models\User', 'user_id', 'create_user');
    }
}
