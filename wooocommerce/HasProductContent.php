<?php

namespace CloudyPress\Woocommerce;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Woocommerce\Models\ProductVariation;
use CloudyPress\Woocommerce\Relations\WooProductVariationRelation;

/**
 * @method static Builder withWooMeta(array $meta = null) Load specific meta for product
 * @method static Builder withWooMetaAndVariations(array $columns = ["*"], array|bool $meta = null) Load variations and meta in case need, set $meta = [] to avoid loading meta
 * @method static Builder withWooVariations(array $columns = ["*"], array|bool $meta = null) Load variations and meta in case need, set $meta = [] to avoid loading meta
 */
trait HasProductContent
{
    protected array $wooMeta = ["_price", "_regular_price", "_sale_price", "_sku"];

    public function scopeWithWooMetaAndVariations(Builder $query, array $columns = ["*"], array $meta = null)
    {
        $meta = is_null($meta) ? $this->wooMeta : $meta;

        $toLoad = [
            "meta" => function ($q) use ($meta) {
                $q->whereIn("meta_key", $meta);
            },
            "variations" => function ($q) use ($columns) {
                $q->select($columns);
            }
        ];

        if ( count($meta) > 0 ) {
            $toLoad["variations.meta"] = function ($q) use ($meta) {
                $q->whereIn("meta_key", $meta);
            };
        }
        return $query->with($toLoad);
    }

    public function scopeWithWooVariations(Builder $query, array $columns = ["*"], array|bool $meta = null): Builder
    {
        $meta = is_null($meta) ? $this->wooMeta : $meta;

        $toLoad = [
            "variations" => function ($q) use ($columns) {
                $q->select($columns);
            }
        ];

        if ( count($meta) > 0 ) {
            $toLoad["variations.meta"] = function ($q) use ($meta) {
                $q->whereIn("meta_key", $meta);
            };
        }
        return $query->with($toLoad);
    }


    public function variations()
    {
        return (new WooProductVariationRelation(
            ProductVariation::query(),
            $this
        ))->select(["ID","post_parent"]);
    }
}