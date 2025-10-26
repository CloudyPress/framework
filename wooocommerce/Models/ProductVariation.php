<?php

namespace CloudyPress\Woocommerce\Models;

use CloudyPress\Database\Wordpress\PostType;
use CloudyPress\Woocommerce\HasProductContent;

class ProductVariation extends PostType
{

    use HasProductContent;

    protected string $tableName = "posts";
}