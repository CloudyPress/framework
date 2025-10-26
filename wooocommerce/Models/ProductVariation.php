<?php

namespace CloudyPress\Woocommerce\Models;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Wordpress\PostType;
use CloudyPress\Woocommerce\HasProductContent;

class ProductVariation extends PostType
{

    use HasProductContent;

    protected string $postType = "product_variation";
    protected string $tableName = "posts";


}