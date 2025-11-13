<?php

namespace CloudyPress\Core;

use CloudyPress\Core\Routing\Router;

/**
 * @method static get(string $endpoint, $action)
 * @method static post(string $endpoint, $action)
 * @method static put(string $endpoint, $action)
 * @method static patch(string $endpoint, $action)
 * @method static delete(string $endpoint, $action)
 */
class Route extends Portal
{

    protected static function getPortalClass(): string
    {
        return Router::class;
    }

}