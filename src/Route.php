<?php

namespace CloudyPress\Core;

use CloudyPress\Core\Routing\Router;

/**
 * @method static get(string $endpoint, $action)
 */
class Route extends Portal
{

    protected static function getPortalClass(): string
    {
        return Router::class;
    }

}