<?php


namespace App\Core\Generate\Table;


class Column
{
    /**
     * @var string 字段名
     */
    private string $name;
    /**
     * @var string 字段类型
     */
    private string $type;
    /**
     * @var string 字段说明
     */
    private string $comment;
    /**
     * @var int 字段长度
     */
    private int $length;
    /**
     * @var int 字段精度
     */
    private int $precision;
    /**
     * @var bool 允许为空
     */
    private bool $allowNull = false;
    /**
     * @var bool 唯一
     */
    private bool $unique = false;
    /**
     * @var string 默认值
     */
    private ?string $default;
    /**
     * @var array 字段验证规则
     */
    private array $rule;
    /**
     * @var array 外键
     */
    private array $foreignKey = [];
    /**
     * @var bool 是否为主键
     */
    private bool $isPrimaryKey = false;


    private string $real;

    /**
     * @Notes: 解析字段，主方法
     * @Interface resolve
     * @param string $content
     * @param Option $option
     * @return bool
     * @throws \ErrorException
     * @Author: TS
     * @Time: 2020-06-17   16:04
     */
    public function resolve($content, Option $option)
    {
        $list = explode(' ', $content);
        if (!isset($list[1])) return false;
        //解析字段名
        $this->name = $this->purification($list[0]);
        if ($this->resolveType($content) == false) {
            return false;
        }
        //解析字段说明
        preg_match("/comment\s*['\"](.*?)['\"]/", $content, $fieldAlias);
        if (isset($fieldAlias[1])) {
            $this->comment = $fieldAlias[1];
        }

        // 注释法外键
        preg_match("/--.*?exists:\s*['\"](.*?)['\"]/", $content, $exists);
        if (isset($exists[1])) {
            $tmpExists = explode(',', $exists[1]);
            if (isset($tmpExists[1])) {
                $this->foreignKey = $tmpExists;
            }
        }
        //sql语法外键
        preg_match("/references\s*(.*?)\(\s*(.*?)\s*\)/si", $content, $references);
        if (isset($references[2])) {
            $this->foreignKey = [$references[1], $references[2]];
        }


        //默认值
        preg_match("/\s+default\s+'*\"*(.*?)\'*\"*\s+/", $content, $default);
        if (isset($default[1])) {
            $this->default = $default[1];
        }


        //解析是否允许为空
        if (strpos($content, 'not null') != false) {
            $this->allowNull = false;
        }

        //解析是否为主键
        if (strpos($content, 'primary key') !== false) {
            $this->isPrimaryKey = true;
        }

        if (strpos($content, 'unique') !== false) {
            $this->unique = true;
        }


        //字段默认补全
        $this->completion();


        //开始配置选项
        if ($option->getPrimaryKey() == $this->name) {
            $this->isPrimaryKey = true;
        }


        foreach ($option->getForeignKey() as $key => $item) {
            if (is_array($item)) {
                $keys = array_keys($item);
                $values = array_values($item);
                if (isset($keys[0]) && isset($values)) {
                    if ($keys[0] === $this->name) {
                        $this->foreignKey = $values;
                    }
                }
            }
        }

        $this->resolveRule($option,true);
        $this->checkReal($option);
        return true;
    }

    /**
     * @Notes: 解析规则
     * @Interface resolveRule
     * @param bool $auto
     * @param Option $option
     * @Author: TS
     * @Time: 2020-06-17   19:29
     */
    public function resolveRule(Option $option,$auto = true)
    {
        //非空核查
        if ($this->isPrimaryKey === false) {
            if ($this->allowNull === false && $this->default == '') {
                $this->setRule('required');
            }
        }
        //唯一值核查
        if ($this->unique == true) {
            $this->setRule('unique');
        }
        //允许自动判断
        if ($auto && $this->allowNull === false) {
            if (strpos($this->name, 'email') !== false) {
                $this->setRule('email');
            }
            if (strpos($this->name, 'phone') !== false || strpos($this->name, 'tel') !== false) {
                $this->setRule('phone');
            }
            if (strpos($this->name, 'ip') !== false) {
                $this->setRule('ip');
            }
        }
        //外键
        if ($this->foreignKey != null && is_array($this->foreignKey) && isset($this->foreignKey[1])) {
            $this->setRule('exists:' . $this->foreignKey[0] . ',' . $this->foreignKey[1]);
        } else {
            $columnRealResolver = new ColumnRealResolver($this, $option);
            $checkUuid = $columnRealResolver->checkUuid();
            if ($checkUuid) {
                $this->real = 'uuid';
            }
        }


    }

    /**
     * @Notes: 核查原型
     * @Interface checkReal
     * @Author: TS
     * @Time: 2020-06-20   0:57
     * @param Option $option
     * @return bool
     * @throws \ErrorException
     */
    private function checkReal(Option $option)
    {
        if($option->isAutoResolve()){
            $columnRealResolver = new ColumnRealResolver($this, $option);
            if($columnRealResolver->handle()) {
                $this->real = $columnRealResolver->getReal();
                return true;
            }
        }
        return false;
    }

    /**
     * @Notes: 添加规则
     * @Interface setRule
     * @param $rule
     * @Author: TS
     * @Time: 2020-06-17   19:30
     */
    private function setRule($rule)
    {
        if ($this->rule === null) {
            $this->rule = [$rule];
        } else {
            array_push($this->rule, $rule);
        }
    }

    /**
     * @Notes: 净化名字， 去掉`
     * @Interface purification
     * @param $value
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-17   15:29
     */
    private function purification($value)
    {
        preg_match("/`(.*?)`/si", $value, $res);
        if (isset($res[1])) {
            return $res[1];
        }
        return $value;
    }

    /**
     * @Notes:解析字段类型
     * @Interface resolveType
     * @param $content
     * @return bool
     * @Author: TS
     * @Time: 2020-06-17   16:05
     */
    private function resolveType($content)
    {
        $list = array_values($this->formatterTypeList);
        //解析字段类型
        foreach ($list as $value) {
            if(preg_match("/\s+" . $value . "\s*\(*(.*?)\)*\s+/",$content)){
//            if (strpos($content, $value) !== false) {
                $this->type = $value;
                $reg = "/" . $value . "\s*\((.*?)\)/si";
                //解析类型长度
                preg_match($reg, $content, $match);
                if (isset($match[1])) {
                    $precision = explode(',', $match[1]);
                    $this->length = $precision[0];
                    if (isset($precision[1])) {
                        $this->precision = $precision[1];
                    }
                } else {
                    $this->completionPrecision($value);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @Notes: 自动补全默认值
     * @Interface completion
     * @Author: TS
     * @Time: 2020-06-17   14:48
     */
    public function completion()
    {
        !isset($this->comment) && $this->comment = '';
        !isset($this->length) && $this->length = 0;
        !isset($this->precision) && $this->precision = 0;
        !isset($this->allowNull) && $this->allowNull = true;
        !isset($this->unique) && $this->unique = false;
        !isset ($this->default) && $this->default = null;
        !isset ($this->rule) && $this->rule = [];
        !isset($this->isPrimaryKey) && $this->isPrimaryKey = false;
        !isset($this->foreignKey) && $this->foreignKey = [];
        !isset($this->real) && $this->real = '';

    }

    public function setData(Array $data)
    {
        try {
            //获取反射
            $rp = new \ReflectionClass($this);
            $properties = $propArr = $rp->getProperties(); //获取反射对象属性
            foreach ($properties as $item) {
                if (isset($data[$item->name])) {
                    $methodName = 'set' . ucfirstAll($item->name);
                    if ($rp->hasMethod($methodName)) {

                        $method = $rp->getMethod($methodName);

                        $docComment = $method->getDocComment();
                        $value = $data[$item->name];

                        if (stripos($docComment, 'array') !== false && is_string($value)) {
                            $temp = json_decode($value, true);
                            $value = is_array($temp) ? $temp : [];
                        }
                        if (stripos($docComment, 'int') !== false) {
                            $value = (int)$value ?? 0;
                        }
                        if (stripos($docComment, 'bool') !== false) {
                            $value = (bool)$value ?? false;
                        }
                        if ($method->isPublic()) {
                            $method->invokeArgs($this, [$value]);
                        }
                    }
                }
            }
        } catch (\ReflectionException $exception) {
            echo $exception->getMessage();
        }

    }

    /**
     * @Notes:获取数据
     * @Interface getArrayData
     * @return array
     * @Author: TS
     * @Time: 2020-08-11   2:36
     */
    public function getArrayData(): array
    {
        return [
            'column_name' => $this->name,
            'column_type' => $this->type,
            'column_length' => $this->length,
            'column_precision' => $this->precision,
            'column_comment' => $this->comment,
            'column_default' => $this->default ? 1 : 0,
            'column_unique' => $this->unique ? 1 : 0,
            'column_primary_key' => $this->isPrimaryKey ? 1 : 0,
            'column_allow_null' => $this->allowNull ? 1 : 0,
            'column_rule' => json_encode($this->rule),
            'column_foreign_key' => json_encode($this->foreignKey),
            'column_nature' => $this->real
        ];
    }

    public function toJson()
    {
        return [
            'column_name' => $this->name,
            'column_type' => $this->type,
            'column_length' => $this->length,
            'column_precision' => $this->precision,
            'column_comment' => $this->comment,
            'column_default' => $this->default ? 1 : 0,
            'column_unique' => $this->unique ? 1 : 0,
            'column_primary_key' => $this->isPrimaryKey ? 1 : 0,
            'column_allow_null' => $this->allowNull ? 1 : 0,
            'column_rule' => $this->rule,
            'column_foreign_key' => $this->foreignKey,
            'column_nature' => $this->real
        ];
    }

    /**
     * @Notes: 自动补全字符类型长度
     * @Interface completionPrecision
     * @param $type
     * @return bool
     * @Author: TS
     * @Time: 2020-06-17   14:47
     */
    public function completionPrecision($type)
    {
        $list = [
            'tinyint' => 4,
            'smallint' => 6,
            'mediumint' => 9,
            'int' => 11,
            'bigint' => 20,
            'decimal' => 10,
            'year' => 4,
            'char' => 1,
            'varchar' => 255
        ];
        foreach ($list as $key => $v) {
            if ($type == $key) {
                $this->length = $v;
                $this->precision = 0;
                return true;
            }
        }
    }

    //字段类型格式化
    private $formatterTypeList = [
        "/TINYINT/" => "tinyint",        //小整数值     1 bytes     (-128，127)
        "/SMALLINT/" => "smallint",     //大整数值      2 bytes     (-32 768，32 767)
        "/MEDIUMINT/" => "mediumint",   //大整数值      3 bytes     (-8 388 608，8 388 607)
        "/INTEGER/" => "integer",       //大整数值      4 bytes     (-2 147 483 648，2 147 483 647)
        "/BIGINT/" => "bigint",         //极大整数值     8 bytes    (-9,223,372,036,854,775,808，9 223 372 036 854 775 807)
        "/INT/" => "int",               //大整数值      4 bytes     (-2 147 483 648，2 147 483 647)
        "/FLOAT/" => "float",           //单精度浮点数值 4 bytes       (-3.402 823 466 E+38，-1.175 494 351 E-38)，0，(1.175 494 351 E-38，3.402 823 466 351 E+38)
        "/DOUBLE/" => "double",         //双精度浮点数值 8 bytes	(-1.797 693 134 862 315 7 E+308，-2.225 073 858 507 201 4 E-308)，0，(2.225 073 858 507 201 4 E-308，1.797 693 134 862 315 7 E+308)
        "/DECIMAL/" => "decimal",       //小数值 对DECIMAL(M,D) ，如果M>D，为M+2否则为D+2
        "/DATETIME/" => "datetime",     //混合日期和时间值 1000-01-01 00:00:00/9999-12-31 23:59:59
        "/DATE/" => "date",             //日期值 1000-01-01/9999-12-31
        "/TIME/" => "time",             //时间值或持续时间 -838:59:59'/'838:59:59'
        "/YEAR/" => "year",             //年份值 1901/2155
        "/VARCHAR/" => "varchar",       //变长字符串 0-65535 bytes
        "/TIMESTAMP/" => "timestamp",   //混合日期和时间值，时间戳
        "/CHAR/" => "char",             //定长字符串 0-255 bytes
        "/TINYBLOB/" => "tinyblob",     //不超过 255 个字符的二进制字符串 0-255 bytes
        "/TINYTEXT/" => "tinytext",     //短文本字符串 0-255 bytes
        "/BLOB/" => "blob",             //二进制形式的长文本数据 0-65 535 bytes
        "/TEXT/" => "text",             //长文本数据 0-65 535 bytes
        "/MEDIUMBLOB/" => "mediumblob", //二进制形式的中等长度文本数据 0-16 777 215 bytes
        "/MEDIUMTEXT/" => "mediumtext", //中等长度文本数据 0-16 777 215 bytes
        "/LONGBLOB/" => "longblob",     //二进制形式的极大文本数据 	0-4 294 967 295 bytes
        "/LONGTEXT/" => "longtext"      //极大文本数据 0-4 294 967 295 bytes
    ];

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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    /**
     * @return bool
     */
    public function isAllowNull(): bool
    {
        return $this->allowNull;
    }

    /**
     * @param bool $allowNull
     */
    public function setAllowNull(bool $allowNull): void
    {
        $this->allowNull = $allowNull;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * @param bool $unique
     */
    public function setUnique(bool $unique): void
    {
        $this->unique = $unique;
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    /**
     * @return array
     */
    public function getRule(): array
    {
        return $this->rule;
    }

    public function getRuleString($format = '|'): string
    {
        return implode($format, $this->rule ?? []);
    }


    /**
     * @return array
     */
    public function getForeignKey(): array
    {
        return $this->foreignKey;
    }

    /**
     * @param array $foreignKey
     */
    public function setForeignKey(array $foreignKey): void
    {
        $this->foreignKey = $foreignKey;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * @param bool $isPrimaryKey
     */
    public function setIsPrimaryKey(bool $isPrimaryKey): void
    {
        $this->isPrimaryKey = $isPrimaryKey;
    }

    /**
     * @return mixed
     */
    public function getReal()
    {
        return $this->real;
    }

    /**
     * @param mixed $real
     */
    public function setReal($real): void
    {
        $this->real = $real;
    }


}
