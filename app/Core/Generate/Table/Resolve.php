<?php


namespace App\Core\Generate\Table;

class Resolve
{
    /**
     * @var string 数据内容
     */
    private $content;
    /**
     * @var Table 表对象
     */
    private Table $table;
    /**
     * @var string 表信息
     */
    private $tableInfo;
    /**
     * @var string 字段列表
     */
    private $columnList;
    /**
     * @var string 错误信息
     */
    public $msg = '';
    /**
     * @var bool 是否出错
     */
    public $isError = false;


    private Option $option;


    public function __construct($content, ?Option $option = null)
    {
        if (stripos($content, 'create table') === false) {
            $this->msg = 'sql格式不正确';
            $this->isError = true;
            return false;
        }
        $this->content = $content;
        if ($option === null) {
            $this->config = new Option();
        } else {
            $this->config = $option;
        }
        $this->table = new Table($option);
        $this->formatter();
        $this->incision();
        $this->main();
        return true;
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }


    /**
     * @Notes: 主方法
     * @Interface main
     * @Author: TS
     * @Time: 2020-06-17   16:00
     */
    private function main()
    {
        if ($this->isError) return;
        $this->resolveTableInfo();
        $this->resolveColumn();
        $this->fillPrimaryKey();
    }

    /**
     * @Notes: 填充主键
     * @Interface fillPrimaryKey
     * @Author: TS
     * @Time: 2020-06-17   22:05
     */
    private function fillPrimaryKey()
    {
        if (!$this->table->getPrimaryKey()) {
            /* @var $item Column */
            foreach ($this->table->getColumnList() as $item) {
                if ($item->isPrimaryKey()) {
                    $this->table->setPrimaryKey($item->getName());
                    return true;
                }
            }
            $this->isError = true;
            $this->msg = "数据表{$this->table->getName()}无主键";
        }
    }

    /**
     * @Notes: 格式化sql
     * @Interface formatter
     * @Author: TS
     * @Time: 2020-06-17   12:44
     */
    private function formatter()
    {
        $content = $this->content;
        $formatterList = [
            "/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS/" => "create table if not exists",
            "/CREATE\s+TABLE/" => "create table",
            "/\s+NOT\s+/" => " not ",
            "/\s+NULL\s+/" => " null ",
            "/\s+PRIMARY\s+KEY\s+/" => " primary key ",
            "/\s+COMMENT\s+/" => " comment ",
            "/\s+DEFAULT\s+/" => " default ",
            "/\s+UNIQUE\s+/" => " unique ",
            "/\s+AUTO_INCREMENT\s+/" => " auto_increment ",
            "/\s+\(/" => "(",
            "/CREATE/" => "create",
            "/TABLE/" => "table",
            "/IF/" => "if",
            "/EXISTS/" => "exists",
            "/FOREIGN KEY/" => "foreign key",
            "/REFERENCES/" => "references",
            "/COMMENT\s*=/" => "comment=",
            "/ENGINE\s*=/" => "engine=",
            "/CHARSET\s*=/" => "charset="
        ];
        $this->content = preg_replace(array_keys($formatterList), array_values($formatterList), $content);

    }

    /**
     * @Notes:切割表 表信息 与 字段列表
     * @Interface incision
     * @Author: TS
     * @Time: 2020-06-17   12:52
     */
    private function incision()
    {
        if ($this->isError) return;
        $content = $this->content;
//        preg_match("/create\s+table[\d\D]*?\((.*)\)[\d\D]*?\;$/si",$content,$tableInfo);
//        if(isset($tableInfo)
//        exit();
        $start = stripos($content, '(');
        $last = strripos($content, ')');
        $this->tableInfo = substr($content, 0, $start + 1);
        $this->columnList = substr($content, $start + 1, $last - $start - 1);


        return true;
    }


    /**
     * @Notes: 解析表信息
     * @Interface resolveTableInfo
     * @return bool|void
     * @Author: TS
     * @Time: 2020-06-17   13:14
     */
    private function resolveTableInfo()
    {
        if ($this->isError) return;
        $tableInfo = $this->tableInfo;
        //解析表头注释说明
        // #方式注释
        if (strpos($tableInfo, '#') !== false) {
            preg_match("/#\s*(.*?)\n/", $tableInfo, $info);
            if (isset($info[1])) {
                $this->table->setInfo($info);
            }
        }
        preg_match("/\)[\s\S]*?comment\s*=\s*['\"](.*?)['\"]/", $this->content, $comment);
        if (isset($comment[1])) {
            $this->table->setInfo($comment[1]);
        }
        // --方式注释
        if (strpos($tableInfo, '--') !== false) {
            preg_match("/--\s*(.*?)\n/", $tableInfo, $info);
            if (isset($info[1])) {
                $this->table->setInfo($info[1]);
            }
        }
        //  /**/方式注释
        if (strpos($tableInfo, '/*') !== false) {
            preg_match("/\/\*+\s*(.*?)\s*\**\/\n/", $tableInfo, $info);
            if (isset($info[1])) {
                $this->table->setInfo($info[1]);
            }
        }
        $tableInfo = preg_replace("/create\s*table\s*if\s*not\s*exists\s*/", "create table ", $tableInfo);
        preg_match("/create\s*table\s*(.*?)\(/si", $tableInfo, $tableName);
        if (!isset($tableName[1])) {
            $this->isError = true;
            $this->msg = '无法解析数据表名，请检查格式';
            return false;
        }
        $this->table->setName($this->purification($tableName[1]));
    }


    /**
     * @Notes: 净化名字， 去掉`
     * @Interface purification
     * @param $value
     * @return mixed
     * @Author: TS
     * @Time: 2020-06-17   15:59
     */
    private function purification($value)
    {
        preg_match("/`(.*?)`/si", $value, $res);
        if (isset($res[1])) {
            return $res[1];
        }
        return $value;
    }

    //字段类型格式化
    private $formatterTypeList = [
        "/TINYINT/" => "tinyint",        //小整数值     1 bytes     (-128，127)
        "/SMALLINT/" => "smallint",     //大整数值      2 bytes     (-32 768，32 767)
        "/MEDIUMINT/" => "mediumint",   //大整数值      3 bytes     (-8 388 608，8 388 607)
        "/INT/" => "int",               //大整数值      4 bytes     (-2 147 483 648，2 147 483 647)
        "/INTEGER/" => "integer",       //大整数值      4 bytes     (-2 147 483 648，2 147 483 647)
        "/BIGINT/" => "bigint",         //极大整数值     8 bytes    (-9,223,372,036,854,775,808，9 223 372 036 854 775 807)
        "/FLOAT/" => "float",           //单精度浮点数值 4 bytes       (-3.402 823 466 E+38，-1.175 494 351 E-38)，0，(1.175 494 351 E-38，3.402 823 466 351 E+38)
        "/DOUBLE/" => "double",         //双精度浮点数值 8 bytes	(-1.797 693 134 862 315 7 E+308，-2.225 073 858 507 201 4 E-308)，0，(2.225 073 858 507 201 4 E-308，1.797 693 134 862 315 7 E+308)
        "/DECIMAL/" => "decimal",       //小数值 对DECIMAL(M,D) ，如果M>D，为M+2否则为D+2
        "/DATETIME/" => "datetime",     //混合日期和时间值 1000-01-01 00:00:00/9999-12-31 23:59:59
        "/DATE/" => "date",             //日期值 1000-01-01/9999-12-31
        "/TIME/" => "time",             //时间值或持续时间 -838:59:59'/'838:59:59'
        "/YEAR/" => "year",             //年份值 1901/2155
        "/TIMESTAMP/" => "timestamp",   //混合日期和时间值，时间戳
        "/CHAR/" => "char",             //定长字符串 0-255 bytes
        "/VARCHAR/" => "varchar",       //变长字符串 0-65535 bytes
        "/TINYBLOB/" => "tinyblob",     //不超过 255 个字符的二进制字符串 0-255 bytes
        "/TINYTEXT/" => "tinytext",     //短文本字符串 0-255 bytes
        "/MEDIUMBLOB/" => "mediumblob", //二进制形式的中等长度文本数据 0-16 777 215 bytes
        "/MEDIUMTEXT/" => "mediumtext", //中等长度文本数据 0-16 777 215 bytes
        "/TEXT/" => "text",             //长文本数据 0-65 535 bytes
        "/LONGBLOB/" => "longblob",     //二进制形式的极大文本数据 	0-4 294 967 295 bytes
        "/BLOB/" => "blob",             //二进制形式的长文本数据 0-65 535 bytes
        "/LONGTEXT/" => "longtext"      //极大文本数据 0-4 294 967 295 bytes
    ];

    /**
     * @Notes: 解析字段
     * @Interface resolveColumn
     * @return bool
     * @throws \ErrorException
     * @Author: TS
     * @Time: 2020-06-17   15:27
     */
    private function resolveColumn()
    {

        $columnContent = preg_replace(array_keys($this->formatterTypeList), array_values($this->formatterTypeList), $this->columnList);
        $columnContent = preg_replace("/,/", "\n", $columnContent);
        $columnList = [];
        $columnTmp = explode("\n", $columnContent);
        $num = -1;
        foreach ($columnTmp as $key => $item) {
            if ($item == "" || $item == " " || $item == "   ") {
                continue;
            }
            //验证是否为字段行，防止后面有注释行
            $tmp = $this->verifyColunm($item);
            if ($tmp === true) {
                $num++;
                $columnList[$num] = $item;
            } else {
                if (sizeof($columnList) > 0) {
                    $columnList[$num] .= "," . $tmp;
                }
            }
        }
        /**
         * 解析字段
         */
        foreach ($columnList as $key => $item) {
            $columnList[$key] = preg_replace("/\s{2,}/", "", trim($item));
            $column = new Column();
            if (!$column->resolve($columnList[$key], $this->getColumnResolverOption())) {
                $this->msg = '解析字段行 ' . $columnList[$key] . " 出错";
                $this->isError = true;
                return false;
            }

            foreach ($this->config->getAllowApplier() as $applier) {
                if ($column->getReal() === $applier) {
                    //如果是时间字段则解析数据类型
                    if (in_array($column->getReal(), ['createTimeField', 'updateTimeField', 'deleteTimeField'])) {
                        $this->table->appendAutoFillField([
                            $column->getName() => $column->getReal() . $this->getDateTypeFormat($column->getType())
                        ]);
                    } elseif (in_array($column->getReal(), ['createUserField', 'updateUserField', 'deleteUserField'])) {
                        $this->table->appendAutoFillField([
                            $column->getName() => $column->getReal() . $this->getActionUserTypeFormat($column->getType())
                        ]);
                    } elseif ($column->getReal() === 'uuid') {
                        //如果为uuid字段，则计算匹配率，uuid字段应该为匹配率高的字段 即 table_uuid 优先度高于 table1_uuid
                        $uuidKeyName = $this->table->getUuidKeyName();
                        if ($uuidKeyName) {
                            similar_text($column->getName(), $this->table->getName() . '_uuid', $percentColumn);
                            similar_text($uuidKeyName, $this->table->getName() . '_uuid', $percentUuid);
                            if ($percentUuid > $percentColumn) {
                                $column->setReal('');
                            } else {
                                $this->table->getColumn($uuidKeyName)->setReal('');
                                $autoFill = $this->table->getAutoFillField();
                                $newAutoFill = [];
                                foreach ($autoFill as $key => $value) {
                                    if ($value !== 'uuid') {
                                        $newAutoFill[$key] = $value;
                                    }
                                }
                                $newAutoFill[$column->getName()] = 'uuid';
                                $this->table->setAutoFillField($newAutoFill);
                            }
                        } else {
                            $this->table->appendAutoFillField([$column->getName() => 'uuid']);
                        }
                    } else {
                        $this->table->appendAutoFillField([$column->getName() => $column->getReal()]);
                    }
                    break;
                }
            }
            $this->table->addColumn($column);

        }


        return true;
    }

    private function getDateTypeFormat($type): string
    {
        return match ($type) {
            'datetime' => ':datetime',
            'bigint' => ':bigint',
            default => ''
        };
    }

    private function getActionUserTypeFormat($type): string
    {
        $typeTimeKey = '';
        if ($type == 'char') {
            return ':uuid';
        }
        return $typeTimeKey;
    }

    /**
     * @Notes:解析字段选项参数
     * @Interface getColumnResolverOption
     * @Author: TS
     * @Time: 2020-06-20   0:54
     */
    private function getColumnResolverOption(): Option
    {
        $option = new Option();
        $option->setPrimaryKey($this->table->getPrimaryKey());
        $option->setForeignKey($this->table->getForeignKey());
        $option->setTableName($this->table->getName());
        return $option;
    }

    /**
     * @Notes: 验证是否为字段行
     * @Interface verifyColunm
     * @param $content
     * @return bool
     * @Author: TS
     * @Time: 2020-06-17   13:53
     */
    private function verifyColunm($content)
    {
        $typeList = array_values($this->formatterTypeList);
        foreach ($typeList as $value) {
            if (strpos($content, $value) !== false) {
                return true;
            }
        }
        if (strpos($content, 'primary key') !== false) {
            preg_match("/\(\s*(.*?)\s*\)/si", $content, $res);
            if (isset($res[1])) {
                $this->table->setPrimaryKey($this->purification($res[1]));
            }
            return '';
        }
        //FOREIGN KEY (id) REFERENCES table_name(id)
        //解析是否为外键
        if (strpos($content, 'foreign key') !== false) {
            //验证外键语法是否合法
            preg_match("/foreign\s*key\s*\(\s*(.*?)\s*\)/si", $content, $foreignKey);
            if (isset($foreignKey[1])) {
                preg_match("/references\s*(.*?)\(\s*(.*?)\s*\)/si", $content, $references);
                //$references : 1:外键表名，2：外键字段名
                if (isset($references[2])) {
                    $this->table->appendForeignKey([
                        $foreignKey[1] => [$references[1], $references[2]]
                    ]);
                }
            }
        }
        return $content;
    }
}
