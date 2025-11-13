<?php

namespace CloudyPress\Woocommerce\Models;

use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Wordpress\PostType;

class OrderModel extends Model
{
    protected string $keyName = "id";

    protected string $tableName = "wc_orders";

}