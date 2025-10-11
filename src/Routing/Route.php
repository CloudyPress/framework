<?php

namespace CloudyPress\Core\Routing;

use Exception;

class Route
{

    private \Closure|string $action;

    public function __construct(
        private string $uri,
        \Closure|array|string $action,
        private array $methods
    )
    {
        $this->action = $this->parseAction($action);
    }

    /**
     * Uri ready for wordpress
     * From
     * /posts/{post}/comments
     * to
     * /posts/(?P<post>[^/]+)/comments
     * @return string
     */
    public function getUri(): string
    {
        $mapped = array_map( function ($item){
            if ( preg_match( '#^\{(.+)\}$#', $item, $match) ){
                $match = str_replace( ['{', '}'], '', $match[1] );

                return "(?P<$match>[^/]+)";
            }

            return $item;
        }, explode('/', $this->uri) );

        return '/' . implode("/", $mapped);
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }


    /**
     * Function to use when setting up register_rest_route in callback
     * 'callback' => fn(...) => $route->getAction(...)
     * @return mixed
     * @throws Exception
     */
    public function getAction(...$args)
    {

        if ( is_string($this->action) )
        {
            [ $className, $methodName ] = explode("@", $this->action);

            if (!method_exists($className, $methodName))
                throw new Exception("Method '{$methodName}' does not exist on {$className}.");

            $action = new $className();

            //TODO, pass args

            return $action->{$methodName}(...$args);
        }

        // Closure case
        return call_user_func($this->action, ...$args);
    }

    /**
     * Parse action content into content that this class can understand
     * From Examples:
     * Route::get("test1", ["DevPlugin\Controllers\TestController", "index"] );
     * Route::get("test2", [\DevPlugin\Controllers\TestController::class, "index"] );
     * Route::get("test3" , function(){ echo "hola ";} );
     *
     * To:
     * "DevPlugin\Controllers\TestController@index"
     * "DevPlugin\Controllers\TestController@index"
     * @see self::getAction() \Closure (Ready to call in getAction )
     *
     * @param \Closure|array|string $action
     * @return \Closure|string
     * @throws Exception
     */
    private function parseAction( \Closure|array|string $action): \Closure|string
    {
        if ( is_string($action) )
        {
            if ( count(explode("@", $action)) <= 1 )
            {
                throw new Exception("Action is not defined");
            }

            return $action;
        }

        if ( is_array($action) )
        {
            if ( count($action) <= 1 )
            {
                throw new Exception("Action is not defined");
            }

            if ( !class_exists($action[0]) )
                throw new Exception("Class does not exist");

            return "{$action[0]}@{$action[1]}";
        }

        return $action;
    }
}