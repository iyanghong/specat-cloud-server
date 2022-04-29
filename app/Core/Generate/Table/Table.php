<?php


namespace App\Core\Generate\Table;


class Table
{
    /**
     * @var string 表名
     */
    private string $name = '';
    /**
     * @var string 表说明
     */
    private string $info = '';
    /**
     * @var string 主键
     */
    private string $primaryKey = '';
    /**
     * @var array 字段列表
     */
    private array $column = [];
    /**
     * @var array 外键列表
     */
    private array $foreignKey = [];



    private array $autoHandleField = [];
    private array $autoFillField = [];


    private array $exceptTimeReal = ['createTimeField','updateTimeField','deleteTimeField'];

    private Option $config;

    /**
     * Table constructor.
     * @param Option|null $config
     */
    public function __construct(?Option $config = null)
    {
        if($config === null){
            $this->config = new Option();
        }else{
            $this->config =$config;
        }
    }




    /**
     * @Notes: 添加字段
     * @Interface addColumn
     * @param Column $column
     * @Author: TS
     * @Time: 2020-06-17   16:02
     */
    public function addColumn(Column $column){
        $this->column[$column->getName()] = $column;
    }

    /**
     * @Notes: 获取字段列表
     * @Interface getColumnList
     * @return array
     * @Author: TS
     * @Time: 2020-06-17   16:02
     */
    public function getColumnList(){
        return $this->column;
    }

    /**
     * @Notes: 获取某个字段
     * @Interface getColumn
     * @param string $columnName
     * @return mixed|null
     * @Author: TS
     * @Time: 2020-06-17   16:03
     */
    public function getColumn($columnName = ''):?Column
    {
        return isset($this->column[$columnName]) ? $this->column[$columnName] : null;
    }


    /**
     * @param array $foreignKey
     */
    public function setForeignKey(array $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @param mixed $autoHandleField
     */
    public function setAutoHandleField(array $autoHandleField): void
    {
        $this->autoHandleField = $autoHandleField;
    }

    /**
     * @param mixed $autoFillField
     */
    public function setAutoFillField(array $autoFillField): void
    {
        $this->autoFillField = $autoFillField;
    }

    /**
     * @param array $foreignKey
     */
    public function appendForeignKey(?array $foreignKey): void
    {
        if(is_array($foreignKey)){
            $this->foreignKey = array_merge($this->foreignKey,$foreignKey);
        }else{
            $this->foreignKey[] = $foreignKey;
        }

    }

    /**
     * @param mixed $autoHandleField
     */
    public function appendAutoHandleField(?array $autoHandleField): void
    {
        if(is_array($autoHandleField)){
            $this->autoHandleField = array_merge($this->autoHandleField,$autoHandleField);
        }else{
            $this->autoHandleField[] = $autoHandleField;
        }
    }

    /**
     * @param mixed $autoFillField
     */
    public function appendAutoFillField(?array $autoFillField): void
    {
        if(is_array($autoFillField)){
            $this->autoFillField = array_merge($this->autoFillField,$autoFillField);
        }else{
            $this->autoFillField[] = $autoFillField;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getInfo(): string
    {
        return $this->info;
    }

    /**
     * @param string $info
     */
    public function setInfo(string $info): void
    {
        $this->info = $info;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return array
     */
    public function getForeignKey(): array
    {
        return $this->foreignKey;
    }

    /**
     * @return array
     */
    public function getAutoHandleField(): array
    {
        return $this->autoHandleField;
    }

    /**
     * @return array
     */
    public function getAutoFillField(): array
    {
        return $this->autoFillField;
    }


    public function toJson() :array
    {
        $columns = [];
        /** @var $item Column */
        foreach ($this->column as $item){
            $columns[] = $item->toJson();
        }
        $data = [
            'name' => $this->name,
            'info' => $this->info,
            'primaryKey' => $this->primaryKey,
            'columns' => $columns,
            'foreignKey' => $this->foreignKey,
            'autoHandleField' => $this->autoHandleField,
            'autoFillField' => $this->autoFillField
        ];
        return $data;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.

        return json_encode($this->toJson());
    }
    public function getFillable() : array
    {
        $fillable = [];
        /* @var $column Column */
        foreach ($this->column as $column){
            if($column->isPrimaryKey() === false && !in_array($column->getReal(),$this->exceptTimeReal)){
                $fillable[] = $column->getName();
            }
        }
        return $fillable;
    }

    /**
     * 获取数据表规则列表
     * @return array
     */
    public function getRuleList():array
    {
        $rules = [];
        /* @var $column Column*/
        foreach ($this->column as $column) {
            if(
                $column->isPrimaryKey() === false
                && !empty($column->getRule())
                && !in_array($column->getReal(),$this->exceptTimeReal)
            ){
                $rules[$column->getName()] = $column->getRuleString();
            }
        }
        return $rules;
    }

    /**
     * 获取字段规则
     * @param string $key
     * @return string|null
     * @throws \ErrorException
     */
    public function getAttributeRule(string $key):?string
    {
        if($key === $this->primaryKey){
            return 'primary key';
        }
        /* @var $column Column */
        foreach ($this->column as $column){
            if($column->getName() === $key){
                return $column->getRuleString();
            }
        }
        throw new \ErrorException(sprintf("数据表不存在列`%s`",$key));
    }

    private function getRealByKey(string $name) :?string
    {
        $key = null;
        /* @var $column Column */
        foreach ($this->column as $column){
            if($column->getReal() === $name){
                $key = $column->getName();
                break;
            }
        }
        return $key;
    }

    public function getAttributeType(string $columnName):?string
    {
        /* @var $column Column */
        foreach ($this->column as $column){
            if($column->getName() === $columnName){
                return $column->getType();
            }
        }
        return null;
    }

    public function getAttributeAliasList():array
    {
        $alias = [];
        /* @var $column Column */
        foreach ($this->column as $column){
            if(!empty($column->getComment())){
                $alias[$column->getName()] = $column->getComment();
            }
        }
        return $alias;
    }

    /**
     * 获取字段别名
     * @param string $key
     * @return string|null
     * @throws \ErrorException
     */
    public function getAttributeAlias(string $key):?string
    {
        /* @var $column Column */
        foreach ($this->column as $column){
            if($column->getName() === $key){
                return empty($column->getComment()) ? $key : $column->getComment();
            }
        }
        throw new \ErrorException(sprintf("数据表不存在列`%s`",$key));
    }


    public function getColumnTypesList():array
    {
        $list = [];
        /* @var $column Column */
        foreach ($this->column as $column){
            $list[$column->getName()] = "{$column->getType()}:{$column->getLength()},{$column->getPrecision()}";
        }
        return $list;
    }


    /**
     * 获取创建时间字段
     * @return string|null
     */
    public function getCreateTimeKeyName(): ?string
    {
        return $this->getRealByKey('createTimeField');
    }

    /**
     * 获取更新时间字段
     * @return string|null
     */
    public function getUpdateTimeKeyName():?string
    {
        return $this->getRealByKey('updateTimeField');
    }

    /**
     * 获取删除时间字段
     * @return string|null
     */
    public function getDeleteTimeKeyName() :?string
    {
        return $this->getRealByKey('deleteTimeField');
    }

    /**
     * 获取UUID字段
     * @return string|null
     */
    public function getUuidKeyName(): ?string
    {
        return $this->getRealByKey('uuid');
    }

}