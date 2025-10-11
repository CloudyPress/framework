<?php

namespace CloudyPress\Core;

use CloudyPress\Core\Routing\Router;

class Route extends Portal
{

    protected static function getPortalClass(): string
    {
        return Router::class;
    }

}