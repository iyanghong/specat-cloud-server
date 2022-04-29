<?php


namespace App\Core\Routing;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;

class ResourceRegistrar extends OriginalRegistrar
{
    //protected $resourceDefaults = ['index', 'create', 'store', 'get','show' ,'edit', 'update', 'destroy'];

    protected $resourceDefaults = ['index','store','get','show','update','destroy'];

    /**
     * Add the get method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceGet($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/get';

        $action = $this->getResourceAction($name, $controller, 'get', $options);

        return $this->router->get($uri, $action);
    }

}