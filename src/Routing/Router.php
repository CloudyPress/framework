<?php

namespace CloudyPress\Core\Routing;

use Elementor\Modules\Ai\SitePlannerConnect\Wp_Rest_Api;
use WP_REST_Request;

class Router
{

    private array $routes = [];

    private string $group;
    private string $version;

    public function __construct()
    {
    }

    /**
     * Make a container of routes to save, so, all routes will save on
     * specific namespace for wordpress to use.
     * @param string $group
     * @param string $version
     * @param callable $callback
     * @return void
     */
    public function namespace(string $group, string $version, callable $callback)
    {
        $this->setNamespace($group, $version);

        $callback($this);

        $this->setNamespace("", "");
    }

    public function setNamespace(string $group, string $version): void
    {
        $this->group = $group;
        $this->version = $version;
    }

    public function getNamespace()
    {
        return $this->getNamespaceFrom($this->group, $this->version);
    }

    private function getNamespaceFrom(string $group, string $version)
    {
        return $group . "/" . $version;
    }

    public function get(string $endpoint, $action)
    {
        $this->addRoute(['GET'], $endpoint, $action);
    }

    public function post(string $endpoint, $action)
    {
        $this->addRoute(['POST'], $endpoint, $action);
    }

    public function put(string $endpoint, $action)
    {
        $this->addRoute(['PUT'], $endpoint, $action);
    }

    public function patch(string $endpoint, $action)
    {
        $this->addRoute(['PATCH'], $endpoint, $action);
    }

    public function delete(string $endpoint, $action)
    {
        $this->addRoute(['DELETE'], $endpoint, $action);
    }

    private function addRoute(array $methods , string $uri, $action)
    {
        $this->routes[$this->getNamespace()][] = new Route(
            $uri,
            $action,
            $methods,
        );
    }

    public function boot(string $group, $version)
    {
        if ( !isset( $this->routes[$this->getNamespaceFrom($group, $version)] ) ) {
            throw new \Exception("No namespace found for {$this->getNamespaceFrom($group, $version)}");
        }

        $routes = $this->routes[$this->getNamespaceFrom($group, $version)];

        add_action( 'rest_api_init', function () use ($routes, $group, $version) {
            // routes

            /** @var Route $route */
            foreach ( $routes as $route )
            {
                register_rest_route( $this->getNamespaceFrom($group, $version), $route->getUri(), array(
                    'methods' => $route->getMethods(),
                    'callback' => function(WP_REST_Request  $request) use ($route) {
                        header("Content-Type: text/html");
                        $route->getAction( ...[...$request->get_params(), $request] );
                    }
                ) );
            }
        });
    }

    public function dd()
    {
        dd($this->routes);
    }
}