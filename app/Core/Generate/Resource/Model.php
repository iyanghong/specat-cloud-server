<?php


namespace App\Core\Generate\Resource;


use App\Core\Generate\Table\Column;
use App\Core\Generate\Table\Table;
use Illuminate\Support\Str;

class Model implements ResourceInterface
{

    /**
     * @var Table 解析数据表
     */
    private Table $table;
    /**
     * @var bool 是否强制执行
     */
    private bool $force = false;

    private Config $config;

    private ResourceFile $resourceFile;

    private bool $success = true;
    private string $message = '';

    private bool $hasTimeField = false;

    public function __construct(ResourceFile $resourceFile, Table $table)
    {
        $this->resourceFile = $resourceFile;
        $this->config = $resourceFile->getConfig();
        $this->table = $table;
    }

    public function handle()
    {
        $disk = localDisk();
        $fileExist = $disk->check($this->resourceFile->getModelPath());
        if ($fileExist === true && $this->force === false) {
            $this->success = false;
            $this->message = '请开启强制模式';
            return false;
        }
        if ($fileExist === false) {
            $disk->mkFile($this->resourceFile->getModelPath(), '');
        }
        $res = $disk->update($this->resourceFile->getModelPath(), $this->getFileContent());
        if (!$res) {
            $this->success = false;
            $this->message = '数据写入失败';
            return false;
        }
        $this->message = "Model`{$this->resourceFile->getName()}`生成成功";
        return true;
    }

    /**
     * 获取资源内容
     * @return string
     */
    public function getFileContent(): string
    {
        $content = $this->header()
            . $this->baseModelData()
            . $this->handleTimeField()
            . $this->fillable()
            . $this->rule()
            . $this->attributeAlias()
            . $this->allowFuzzySearch()
            . $this->autoHandle()
            . $this->autoFill()
            . $this->casts()
            . $this->fieldTypes()
            . $this->getLineFeed(0) . "}";
        return $content;
    }

    /**
     * 文件头
     * @return string
     */
    private function header(): string
    {
        $content = "<?php"
            . "{$this->getLineFeed(0,2)}namespace {$this->resourceFile->getModelNamespace()};"
            . "{$this->getLineFeed(0,2)}use {$this->config->getBaseModelNamespace()} as BaseModel;"
            . "{$this->getLineFeed(0,2)}class {$this->resourceFile->getName()} extends BaseModel"
            . "{$this->getLineFeed()}{";
        return $content;
    }

    /**
     * model基本信息
     * @return string
     */
    private function baseModelData(): string
    {
        $tableInfo = empty($this->table->getInfo()) ? $this->table->getName() : $this->table->getInfo();
        $content = "{$this->getLineFeed(1)}protected  \$table = '{$this->table->getName()}';      //数据表名"
            . "{$this->getLineFeed(1)}protected \$primaryKey = '{$this->table->getPrimaryKey()}';        //表主键";
        $uuidKeyName = $this->table->getUuidKeyName();
        if ($uuidKeyName) {
            $content .= "{$this->getLineFeed(1)}protected string \$primaryUuidField = '{$uuidKeyName}'; //唯一标识字段";
        }
        $content .= "{$this->getLineFeed(1)}public string \$tableInfo = '{$tableInfo}';        //表简介";
        return $content;
    }

    /**
     * 解析自动处理时间字段
     * @return string
     */
    private function handleTimeField(): string
    {
        $createTimeKeyName = $this->table->getCreateTimeKeyName();
        $updateTimeKeyName = $this->table->getUpdateTimeKeyName();
        $deleteTimeKeyName = $this->table->getDeleteTimeKeyName();
        //都没有设置自动处理字段则退出
        if ($createTimeKeyName === null && $updateTimeKeyName === null && $deleteTimeKeyName === null) {
            return "{$this->getLineFeed(1)}public \$timestamps = false; // 不自动管理时间";
        }
        $this->hasTimeField = true;

        $columnType = '';

        if($createTimeKeyName === null){
            $createTimeKeyName = 'null';
        }else{
            $columnType = $this->table->getAttributeType($createTimeKeyName);
            $createTimeKeyName = "'{$createTimeKeyName}'";
        }
        if($updateTimeKeyName === null){
            $updateTimeKeyName = 'null';
        }else{
            $columnType = $this->table->getAttributeType($updateTimeKeyName);
            $updateTimeKeyName = "'{$updateTimeKeyName}'";
        }
        if($deleteTimeKeyName === null){
            $deleteTimeKeyName = 'null';
        }else{
            $columnType = $this->table->getAttributeType($deleteTimeKeyName);
            $deleteTimeKeyName = "'{$deleteTimeKeyName}'";
        }

        $content = "{$this->getLineFeed(1)}public \$timestamps = true;        //是否自动处理时间字段"
            . "{$this->getLineFeed(1)}const CREATED_AT = {$createTimeKeyName};"
            . "{$this->getLineFeed(1)}const UPDATED_AT = {$updateTimeKeyName};"
            . "{$this->getLineFeed(1)}const DELETED_AT = {$deleteTimeKeyName};";



        //解析时间字段
        $dateFormat = '';

        switch ($columnType){
            case 'bigint':
            case 'int':
                $dateFormat = 'U';
                break;
            case 'datetime':
            case 'date':
                $dateFormat = 'Y-m-d H:i:s';
                break;
        }

        if(!empty($dateFormat)){
            $content .= "{$this->getLineFeed(1)}protected \$dateFormat = '{$dateFormat}';";
        }
        return $content;
    }

    /**
     * fillable列表
     * @return string
     */
    private function fillable(): string
    {
        $fillable = implode('","', $this->table->getFillable());
        $content = "{$this->getLineFeed(1)}protected \$fillable = [\"{$fillable}\"];";
        return $content;
    }

    /**
     * 验证规则列表
     * @return string
     */
    private function rule(): string
    {
        $rules = $this->table->getRuleList();
        $content = "{$this->getPropertyNote('验证规则列表','array')}"
            . "{$this->getLineFeed(1)}protected array \$rule = [";
        foreach ($rules as $key => $value) {
            $content .= "{$this->getLineFeed(2)}'{$key}' => '{$value}',";
        }
        $content .= "{$this->getLineFeed(1)}];";
        return $content;
    }

    /**
     * 字段中文说明列表
     * @return string
     */
    private function attributeAlias(): string
    {
        $list = $this->table->getAttributeAliasList();
        $content = "{$this->getPropertyNote('字段中文说明列表','array')}"
            . "{$this->getLineFeed(1)}protected array \$attributeAlias = [";
        foreach ($list as $key => $value) {
            $content .= "{$this->getLineFeed(2)}'{$key}' => '{$value}',";
        }
        $content .= "{$this->getLineFeed(1)}];";
        return $content;
    }

    /**
     * 允许模糊查询列表
     * @return string
     */
    private function allowFuzzySearch(): string
    {
        $content = "{$this->getPropertyNote('允许模糊查询列表','array')}{$this->getLineFeed(1)}protected array \$allowFuzzySearch = [];";
        return $content;
    }


    /**
     * 自动处理字段列表
     * @return string
     */
    private function autoHandle(): string
    {
        $list = $this->table->getAutoHandleField();
        $content = "{$this->getPropertyNote('自动处理字段列表','array')}"
            . "{$this->getLineFeed(1)}protected array \$autoHandle = [";
        if (empty($list)) {
            $content .= "];";
        } else {
            foreach ($list as $key => $value) {
                $content .= "{$this->getLineFeed(2)}'{$key}' => '{$value}',";
            }
            $content .= "{$this->getLineFeed(1)}];";
        }
        return $content;
    }

    /**
     * 自动填充字段内容列表
     * @return string
     */
    private function autoFill(): string
    {
        $list = $this->table->getAutoFillField();
        $content = "{$this->getPropertyNote('自动填充字段内容列表','array')}"
            . "{$this->getLineFeed(1)}protected array \$autoFill = [";
        if (empty($list)) {
            $content .= "];";
        } else {
            foreach ($list as $key => $value) {
                $content .= "{$this->getLineFeed(2)}'{$key}' => '{$value}',";
            }
            $content .= "{$this->getLineFeed(1)}];";
        }
        return $content;
    }

    private function casts():string
    {
        $content = "";
        if($this->hasTimeField){
            $content .= "{$this->getPropertyNote('自动处理字段类型','array')}"
                . "{$this->getLineFeed(1)}protected \$casts = [";
            if($this->table->getCreateTimeKeyName()) {
                $content .= "{$this->getLineFeed(2)} '{$this->table->getCreateTimeKeyName()}' => 'date:Y-m-d H:i:s',";
            }
            if($this->table->getUpdateTimeKeyName()) {
                $content .= "{$this->getLineFeed(2)} '{$this->table->getUpdateTimeKeyName()}' => 'date:Y-m-d H:i:s',";
            }
            if($this->table->getDeleteTimeKeyName()) {
                $content .= "{$this->getLineFeed(2)} '{$this->table->getDeleteTimeKeyName()}' => 'date:Y-m-d H:i:s',";
            }
            $content = Str::replaceLast(',','',$content);
            $content .= "{$this->getLineFeed(1)}];";
        }
        return $content;
    }
    /**
     * 字段类型列表
     * @return string
     */
    private function fieldTypes(): string
    {
        $list = $this->table->getColumnTypesList();
        $content = "{$this->getPropertyNote('字段类型列表','array')}"
            . "{$this->getLineFeed(1)}protected array \$fieldTypes = [";
        foreach ($list as $key => $value) {
            $content .= "{$this->getLineFeed(2)}'{$key}' => '{$value}',";
        }
        $content .= "{$this->getLineFeed(1)}];";
        return $content;
    }


    private function getPropertyNote(string $note, string $type = 'string', int $num = 1): string
    {
        return "{$this->getLineFeed(1,2)}/**"
            . "{$this->getLineFeed(1)}* {$note}"
            . "{$this->getLineFeed(1)}* @var {$type}"
            . "{$this->getLineFeed(1)}*/";
    }


    private function getLineFeed(int $num = 0, int $nNum = 1): string
    {
        $content = "";
        for ($i = 0; $i < $nNum; $i++) {
            $content .= "\n";
        }
        for ($i = 0; $i < $num; $i++) {
            $content .= "\t";
        }
        return $content;
    }


    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @param Table $table
     */
    public function setTable(Table $table): void
    {
        $this->table = $table;
    }

    /**
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * @param bool $force
     * @return ResourceInterface
     */
    public function setForce(bool $force): ResourceInterface
    {
        $this->force = $force;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @return ResourceFile
     */
    public function getResourceFile(): ResourceFile
    {
        return $this->resourceFile;
    }

    /**
     * @param ResourceFile $resourceFile
     * @return ResourceInterface
     */
    public function setResourceFile(ResourceFile $resourceFile): ResourceInterface
    {
        $this->resourceFile = $resourceFile;
        return $this;
    }


}
