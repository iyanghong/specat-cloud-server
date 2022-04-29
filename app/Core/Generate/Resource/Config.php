<?php


namespace App\Core\Generate\Resource;


class Config
{
    /**
     * @var string
     */
    private string $controllerPath = 'app/Http/Controllers';

    /**
     * @var string
     */
    private string $controllerNamespace = 'App\Http\Controllers';

    /**
     * @var string
     */
    private string $modelPath = 'app/Models';

    /**
     * @var string
     */
    private string $modelNamespace = 'App\Models';

    /**
     * @var string
     */
    private string $routerPath = 'routes/api.php';

    private string $baseModelNamespace = 'App\Core\Constructors\Model\BaseModel';

    private string $pathSeparator = '/';

    public function __construct()
    {
        PHP_OS != 'Linux' && $this->pathSeparator = '\\';
    }

    /**
     * @return string
     */
    public function getBaseModelNamespace(): string
    {
        return $this->baseModelNamespace;
    }


    /**
     * @return string
     */
    public function getControllerPath(): string
    {
        return $this->controllerPath;
    }

    /**
     * @return string
     */
    public function getControllerNamespace(): string
    {
        return $this->controllerNamespace;
    }

    /**
     * @return string
     */
    public function getModelPath(): string
    {
        return $this->modelPath;
    }

    /**
     * @return string
     */
    public function getModelNamespace(): string
    {
        return $this->modelNamespace;
    }

    /**
     * @return string
     */
    public function getRouterPath(): string
    {
        return $this->routerPath;
    }

    /**
     * @return string
     */
    public function getPathSeparator(): string
    {
        return $this->pathSeparator;
    }



}