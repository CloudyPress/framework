<?php

namespace CloudyPress\Core\Routing;

use CloudyPress\Core\Paginated;
use WP_REST_Response;

abstract class Controller
{
    public function response( mixed $data, int $code = 200 )
    {
        header( "Content-Type: application/json" );
        return new WP_REST_Response($data, $code);
    }
}