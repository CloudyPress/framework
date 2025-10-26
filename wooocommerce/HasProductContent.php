<?php

namespace CloudyPress\Woocommerce;

use CloudyPress\Database\Nimbus\Builder;

/**
 * @method static Builder withWooMeta(array $meta = null) Load specific meta for product
 * @method static Builder withWooVariations(array $columns = ["*"], array|bool $meta = null) Load variations and meta in case need, set $meta = [] to avoid loading meta
 */
trait HasProductContent
{
    protected array $wooMeta = ["_price", "_regular_price", "_sale_price", "_sku"];

    public function scopeWithMeta(Builder $query, array $meta = null): Builder
    {
        $meta = is_null($meta) ? $this->wooMeta : $meta;

        return $query->with(["meta" => function ($q) use ($meta) {
            $q->whereIn("meta_key", $meta);
        }]);
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
}