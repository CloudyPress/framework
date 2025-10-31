<?php

namespace CloudyPress\Woocommerce\Models;

use CloudyPress\Core\Wordpress\HasThumbnail;
use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Wordpress\Models\TermRelationship;
use CloudyPress\Database\Wordpress\Models\WpPostMeta;
use CloudyPress\Database\Wordpress\PostType;
use CloudyPress\Woocommerce\HasProductContent;
use CloudyPress\Woocommerce\ProductType;
use CloudyPress\Woocommerce\Relations\WooProductTypeRelation;
use CloudyPress\Woocommerce\Relations\WooProductVariationRelation;
use framework\database\Wordpress\Relations\HasPostMeta;

/**
 * @property-read ProductType $type
 */
class Product extends PostType
{
    use HasProductContent, HasThumbnail;

    protected string $postType = 'product';

    public function type()
    {
        $related = new TermRelationship();

        return new WooProductTypeRelation(
            $related->newQuery(),
            $this
        );
    }

    public function metaToLoad(): array
    {
        return [
            ...$this->wooMeta
        ];
    }
}