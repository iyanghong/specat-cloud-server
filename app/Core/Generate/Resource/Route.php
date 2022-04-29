<?php


namespace App\Core\Generate\Resource;


use App\Service\Disk\LocalDiskInterface;
use Illuminate\Support\Str;

class Route implements ResourceInterface
{
    /**
     * @var bool 是否强制执行
     */
    private bool $force = false;

    private Config $config;

    private ResourceFile $resourceFile;

    private bool $success = true;
    private string $message = '';

    private array $resourceMethods = [];

    private LocalDiskInterface $disk;


    private string $lcName = '';

    private string $groupName = '';

    public function __construct(ResourceFile $resourceFile, ?array $resourceMethods = null)
    {
        $this->resourceFile = $resourceFile;
        $this->config = $resourceFile->getConfig();
        $this->lcName = lcfirst($resourceFile->getName());

        $groups = explode('/', $resourceFile->getPath());
        for ($i = 0; $i < sizeof($groups); $i++) {
            $groups[$i] = lcfirst($groups[$i]);
        }
        $groupNames = implode('/', $groups);
        if (!empty($groupNames)) {
            $this->groupName = $groupNames;
        }
        if ($resourceMethods === null) {
            $this->resourceMethods = ['index', 'store', 'get', 'show', 'update', 'destroy'];
        } else {
            $this->resourceMethods = $resourceMethods;
        }
        $this->disk = localDisk();
    }

    public function handle()
    {
        // TODO: Implement handle() method.

        $fileExist = $this->disk->check($this->config->getRouterPath());
        if ($fileExist === true && $this->force === false) {
            $this->success = false;
            $this->message = '请开启强制模式';
            return false;
        }
        if ($fileExist === false) {
            $this->disk->mkFile($this->config->getRouterPath(), $this->getNewFileContent());
        }
        if ($this->resourceFile->getPath()) {
            return $this->buildGroupFile();
        }
        return $this->buildFile();
    }

    private function buildGroupFile(): bool
    {

        $router = $this->disk->get($this->config->getRouterPath());

        $reg = $this->getCheckGroup();
        $groupIsExist = stripos($router, $reg);

        if ($groupIsExist === false) {
            $content = $this->getGroupFileContent();
            $flag = $this->disk->fill($this->config->getRouterPath(), $content);
            if (!$flag) {
                return $this->setFail();
            }
        } else {
            $content = $this->getFileContent(true);
            $reg = $this->getRegExp(true);
            $routerFileContent = preg_replace($reg, $content, $router, -1, $count);
            //当拥有group且拥有item项
            if ($count > 0) {
                $flag = $this->disk->update($this->config->getRouterPath(), $routerFileContent);
                if (!$flag) {
                    return $this->setFail();
                }
            } else {//当拥有group没有item项
                preg_match($this->getGroupItemContentRegExp(), $router, $match);

                if (empty($match)) {
                    return $this->setFail('没有在group下找到内容');
                }

                $realGroupRoutes = $match[1];
                $newItemContent = $this->getFileContent(true, Str::endsWith($realGroupRoutes, "\n") ? 0 : 2);

                $content = $this->getCheckGroup() . $realGroupRoutes . $newItemContent . "{$this->getLineFeed(0,2)}});";
                $router = str_replace($match[0], $content, $router);
                $flag = $this->disk->update($this->config->getRouterPath(), $router);
                if (!$flag) {
                    return $this->setFail();
                }
            }
        }

        return $this->setSuccess();
    }

    private function buildFile(): bool
    {
        $router = $this->disk->get($this->config->getRouterPath());
        $content = $this->getFileContent();
        $reg = $this->getRegExp();
        $router = preg_replace($reg, $content, $router, -1, $count);
        $res = false;
        if ($count > 0) {
            $res = $this->disk->update($this->config->getRouterPath(), $router);
        } else {
            $res = $this->disk->fill($this->config->getRouterPath(), $content);

        }
        if (!$res) {
            $this->setFail();
        }
        return $this->setSuccess();

    }


    /**
     * @param bool $isItem 是否为子项
     * @return string
     */
    public function getFileContent(bool $isItem = false, int $nNum = 2): string
    {
        // TODO: Implement getFileContent() method.


        $content = "{$this->getRouteNote($isItem === false ? 0 : 1,$nNum)}{$this->getLineFeed($isItem === false ? 0 : 1,1)}Route::resource('{$this->lcName}','\\{$this->resourceFile->getControllerNamespace()}\\{$this->resourceFile->getName()}Controller')";
        if ($this->resourceFile->getPath()) {
            //把路径解析成 path1.path2 形式
            $groups = explode('/', $this->resourceFile->getPath());
            for ($i = 0; $i < sizeof($groups); $i++) {
                $groups[$i] = lcfirst($groups[$i]);
            }
            $routeNames = implode('.', $groups);
            $routeNames = lcfirst($routeNames);
            $content .= "->names('{$routeNames}.{$this->resourceFile->getName()}')";
        }
        $content .= ";";
        return $content;
    }

    private function getGroupFileContent(): string
    {
        $content = "{$this->getLineFeed(0,2)}Route::group(['prefix' => '{$this->groupName}'], function () {"
            . $this->getFileContent(true, 2)
            . "{$this->getLineFeed(0,2)}});";
        return $content;
    }

    private function getNewFileContent(): string
    {
        $content = "<?php";
        return $content;
    }

    private function getCheckGroup()
    {
//        $reg = "/{$this->getLineFeed(0,2)}Route::group\(\['prefix' => '{$this->resourceFile->getPath()}'\], function \(\) \{([\D\d]+)\}\);/U";
        $reg = "{$this->getLineFeed(0,2)}Route::group(['prefix' => '{$this->groupName}'], function () {";
        return $reg;
    }

    private function getGroupItemContentRegExp()
    {
        $groupName = $this->groupName;
        $groupName = str_replace("/", "\/", $groupName);
        $reg = "/{$this->getLineFeed(0,2)}Route::group\(\['prefix' => '{$groupName}'\], function \(\) \{([\D\d]+)\}\);/U";
        return $reg;
    }

    /**
     * @param bool $isItem 是否为子项
     * @return string
     */
    private function getRegExp(bool $isItem = false, int $nNum = 2): string
    {
        $note = $this->getRouteNote($isItem === false ? 0 : 1, $nNum);
        $excepts = ["*", "/"];
        foreach ($excepts as $except) {
            $note = str_replace($except, "\\" . $except, $note);
        }
        $reg = "/{$this->getLineFeed($isItem === false ? 0 : 1,$nNum)}\/\*(.*?){$this->getLineFeed($isItem === false ? 0 : 1,1)}Route::resource\('{$this->lcName}',(.*?)\);/U";
//        var_dump($reg);
        return $reg;
    }

    private function getRouteNote(int $num = 0, int $nNum = 1): string
    {
        $pathPrefix = $this->groupName;
        if ($pathPrefix) {
            $pathPrefix .= "/";
        }
        $pathPrefix .= $this->lcName;
        $name = empty($this->resourceFile->getTableInfo()) ? $this->resourceFile->getTableName() : $this->resourceFile->getTableInfo();
        $content = "{$this->getLineFeed($num,$nNum)}/** {$name} 资源接口集合 : {$pathPrefix} */";
        return $content;
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


    private function getLcName()
    {
        return lcfirst($this->resourceFile->getName());
    }


    private function setFail(string $message = ''): bool
    {
        $this->success = false;
        $this->message = empty($message) ? '数据写入失败' : $message;
        return false;
    }

    private function setSuccess(string $message = ''): bool
    {
        $this->success = true;
        $this->message = empty($message) ? "Route`{$this->resourceFile->getName()}`生成成功" : $message;
        return true;
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
     */
    public function setForce(bool $force): ResourceInterface
    {
        $this->force = $force;
        return $this;
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