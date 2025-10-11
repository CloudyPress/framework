<?php

namespace CloudyPress\Core;

class CloudyPress
{

    public static function boot(string $pluginPath){
        return new self($pluginPath);
    }

    public function __construct(
        protected string $pluginPath
    )
    {
        $this->loadRoutes();
    }

    private function loadRoutes()
    {
        $routes = $this->getNamespaceAndVersionFromDir("routes");

        foreach ($routes as $route){
            Route::namespace($route["ns"][0], $route["ns"][1] ?? "v1", function() use ($route) {
                require_once $route["path"];
            } );

            Route::boot($route["ns"][0], $route["ns"][1]);
        }
    }

    private function getNamespaceAndVersionFromDir(string $dir): array
    {
        if ( !is_dir($this->pluginPath . "/$dir/") )
            return [];

        return array_map( function (string $file) use($dir) {
            return [
                "ns" => explode("-", str_replace(".php", "", $file) ),
                "path" => $this->pluginPath . "/$dir/$file"
            ];
        }, array_diff( scandir($this->pluginPath . "/$dir/"), ['.', '..'] ) );
    }
}