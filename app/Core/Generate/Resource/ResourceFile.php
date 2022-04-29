<?php


namespace App\Core\Generate\Resource;


use Illuminate\Support\Str;

class ResourceFile
{
    /**
     * @var string 文件路径
     */
    private string $path = '';
    /**
     * @var string 文件名字
     */
    private string $name = '';
    /**
     * @var string Model命名空间
     */
    private string $namespace = '';


    /**
     * @var string 数据表名
     */
    private string $tableName = '';

    private string $tableInfo = '';


    private string $pathSeparator = '/';



    private Config $config;
    /**
     * ResourceFile constructor.
     * @param $name
     * @throws \ErrorException
     */
    public function __construct($name)
    {

        if(empty($name)){
            throw new \ErrorException("名称不能为空");
        }
        $this->config = new Config();

        //解析数据表名
        $tmpTableName = explode($this->config->getPathSeparator(), $name);
        $this->tableName = $tmpTableName[sizeof($tmpTableName) - 1];


        $this->pathSeparator = $this->config->getPathSeparator();
        $separator = strpos($name,"\\") === false ? "/" : "\\"; //分隔符

        $name = ucfirstAll($name, $separator, $separator);
        $name = ucfirstAll($name,'_');

        $tempName = explode($separator,$name); //切割数组
        $this->name = $tempName[sizeof($tempName) - 1];
        $this->name = Str::singular($this->name);//转单数形式
        unset($tempName[sizeof($tempName) - 1]);


        if(sizeof($tempName) > 0){
            for ($i = 0; $i < sizeof($tempName); $i++) {
                if ($i != 0) {
                    $this->namespace .= "\\";
                    $this->path .= $this->pathSeparator;
                }
                $this->path .= $tempName[$i];
                $this->namespace .= $tempName[$i];
            }
        }else{
            $this->path = '';
            $this->namespace = '';
        }

    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }


    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
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
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getPathSeparator(): string
    {
        return $this->pathSeparator;
    }

    /**
     * @param string $pathSeparator
     */
    public function setPathSeparator(string $pathSeparator): void
    {
        $this->pathSeparator = $pathSeparator;
    }



    public function getModelPath():string
    {
        $path = $this->config->getModelPath();
        if(!empty($this->path)){
            $path .= $this->config->getPathSeparator() . $this->path;
        }
        $path .= $this->config->getPathSeparator() . $this->name . ".php";
        return $path;
    }

    public function getModelNamespace():string
    {
        $namespace = $this->config->getModelNamespace();
        if(!empty($this->namespace)) {
            $namespace .= "\\{$this->namespace}";
        }
        return $namespace;
    }

    public function getControllerPath():string
    {
        $path = $this->config->getControllerPath();
        if(!empty($this->path)){
            $path .= $this->config->getPathSeparator() . $this->path;
        }
        $path .= $this->config->getPathSeparator() . $this->name . "Controller.php";
        return $path;
    }

    public function getControllerNamespace():string
    {
        $namespace = $this->config->getControllerNamespace();
        if(!empty($this->namespace)) {
            $namespace .= "\\{$this->namespace}";
        }
        return $namespace;
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


    public function toJson() : array
    {
        $data = [];
        foreach ($this as $key => $value){
            $key != 'config' && $data[$key] = $value;
        }
        return $data;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
        return json_encode($this->toJson());
    }


}