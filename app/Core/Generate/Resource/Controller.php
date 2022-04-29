<?php


namespace App\Core\Generate\Resource;


class Controller implements ResourceInterface
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

    public function __construct(ResourceFile $resourceFile, ?array $resourceMethods = null)
    {
        $this->resourceFile = $resourceFile;
        $this->config = $resourceFile->getConfig();

        if ($resourceMethods === null) {
            $this->resourceMethods = ['index', 'store', 'get', 'show', 'update', 'destroy'];
        } else {
            $this->resourceMethods = $resourceMethods;
        }
    }

    public function handle(): bool
    {
        $disk = localDisk();
        $fileExist = $disk->check($this->resourceFile->getControllerPath());
        if ($fileExist === true && $this->force === false) {
            $this->success = false;
            $this->message = '请开启强制模式';
            return false;
        }
        if ($fileExist === false) {
            $disk->mkFile($this->resourceFile->getControllerPath(), '');
        }
        $res = $disk->update($this->resourceFile->getControllerPath(), $this->getFileContent());
        if (!$res) {
            $this->success = false;
            $this->message = '数据写入失败';
            return false;
        }
        $this->message = "Controller`{$this->resourceFile->getName()}`生成成功";
        return true;
    }

    public function getFileContent(): string
    {
        return $this->header()
            . $this->getResourceContent()
            . $this->getLineFeed(0) . "}";
    }

    private function header(): string
    {
        return "<?php"
            . "{$this->getLineFeed(0,2)}namespace {$this->resourceFile->getControllerNamespace()};"
            . "{$this->getLineFeed(0,2)}use App\Http\Controllers\Controller;"
            . "{$this->getLineFeed(0,2)}use Illuminate\Http\Request;"
            . "{$this->getLineFeed(0)}use {$this->resourceFile->getModelNamespace()}\\{$this->resourceFile->getName()};"
            . "{$this->getLineFeed(0,2)}class {$this->resourceFile->getName()}Controller  extends Controller"
            . "{$this->getLineFeed()}{";
    }

    private function getResourceContent(): string
    {
        $content = "";
        foreach ($this->resourceMethods as $method) {
            $content .= $this->{"getResource" . ucfirst($method)}();
        }
        return $content;
    }

    private function getResourceIndex(): string
    {
        // Display a listing of the resource
        $note = $this->getFunctionNote('列表', 'index'); //方法注释

        return "{$note}{$this->getLineFeed(1)}public function index(): string"
            . "{$this->getLineFeed(1)}{"
            . "{$this->getLineFeed(2)}return resourceConstructor(new {$this->resourceFile->getName()}())->index();"
            . "{$this->getLineFeed(1)}}";
    }

    private function getResourceStore(): string
    {
        //
        $note = $this->getFunctionNote('创建', 'store'); //方法注释

        return "{$note}{$this->getLineFeed(1)}public function store(Request \$request): string"
            . "{$this->getLineFeed(1)}{"
            . "{$this->getLineFeed(2)}return resourceConstructor(new {$this->resourceFile->getName()}())->store(\$request);"
            . "{$this->getLineFeed(1)}}";
    }

    private function getResourceGet(): string
    {
        //Display the specified resource.
        $note = $this->getFunctionNote('详情(多条件)', 'get'); //方法注释

        return "{$note}{$this->getLineFeed(1)}public function get(Request \$request): string"
            . "{$this->getLineFeed(1)}{"
            . "{$this->getLineFeed(2)}return resourceConstructor(new {$this->resourceFile->getName()}())->get(\$request);"
            . "{$this->getLineFeed(1)}}";
    }

    private function getResourceShow(): string
    {
        //Display the specified resource.
        $note = $this->getFunctionNote('详情', 'get'); //方法注释

        return "{$note}{$this->getLineFeed(1)}public function show(\$id): string"
            . "{$this->getLineFeed(1)}{"
            . "{$this->getLineFeed(2)}return resourceConstructor(new {$this->resourceFile->getName()}())->show(\$id);"
            . "{$this->getLineFeed(1)}}";
    }

    private function getResourceUpdate(): string
    {
        //Update the specified resource in storage.
        $note = $this->getFunctionNote('修改', 'update'); //方法注释

        return "{$note}{$this->getLineFeed(1)}public function update(Request \$request, \$id): string"
            . "{$this->getLineFeed(1)}{"
            . "{$this->getLineFeed(2)}return resourceConstructor(new {$this->resourceFile->getName()}())->update(\$request,\$id);"
            . "{$this->getLineFeed(1)}}";
    }

    private function getResourceDestroy(): string
    {
        //Remove the specified resource from storage.
        $note = $this->getFunctionNote('删除', 'destroy'); //方法注释

        return "{$note}{$this->getLineFeed(1)}public function destroy(\$id): string"
            . "{$this->getLineFeed(1)}{"
            . "{$this->getLineFeed(2)}return resourceConstructor(new {$this->resourceFile->getName()}())->destroy(\$id);"
            . "{$this->getLineFeed(1)}}";
    }

    private function getFunctionNote(string $note, string $method): string
    {
        $author = 'System Generate';
        $date = date('Y-m-d H:i:s');

        $content = "{$this->getLineFeed(1,2)}/**"
            . "{$this->getLineFeed(1)}* {$note}"
            . "{$this->getLineFeed(1)}* @Author:{$author}"
            . "{$this->getLineFeed(1)}* @Date:{$date}";

        if (in_array($method, ['store', 'update', 'get'])) {
            $content .= "{$this->getLineFeed(1)}* @param  Request  \$request";
        }

        if (in_array($method, ['show'])) {
            $content .= "{$this->getLineFeed(1)}* @param  int  \$id";
        }

        $content .= "{$this->getLineFeed(1)}* @return string"
            . "{$this->getLineFeed(1)}*/";
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
