<?php

namespace CloudyPress\Woocommerce;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Relations\Relation;
use CloudyPress\Woocommerce\Models\ProductVariation;
use CloudyPress\Woocommerce\Relations\WooProductVariationRelation;

/**
 * @property-read ProductVariation[] variations
 * @method static Builder withWooMeta(array $meta = null) Load specific meta for product
 * @method static Builder withWooMetaAndVariations(array $columns = ["*"], array|bool $meta = null) Load variations and meta in case need, set $meta = [] to avoid loading meta
 * @method static Builder withWooVariations(array $columns = ["*"], array|bool $meta = null) Load variations and meta in case need, set $meta = [] to avoid loading meta
 */
trait HasProductContent
{
    public array $variationSelect = ["ID","post_parent"];
    public array $wooMeta = ["_price", "_regular_price", "_sale_price", "_sku", "attribute_%"];

    /**
     * Load Meta, Variations and meta from relation based on what default to load
     * @param Builder $query
     * @param array|null $columns
     * @param array|null $meta
     * @return Builder
     */
    public function scopeWithWooMetaAndVariations(Builder $query, array $columns = null, array $meta = null)
    {
        $meta = is_null($meta) ? $this->wooMeta : $meta;
        $columns = is_null($columns) ? $this->variationSelect : $columns;

        $toLoad = [
            "meta" => $this->prepareMetaToLoad(array_merge( $meta, $this->metaToLoad() ) ),
            "variations" => function ($q) use ($columns) {
                $q->select($columns);
            }
        ];

        if ( count($meta) > 0 ) {
            $toLoad["variations.meta"] = $this->prepareMetaToLoad( $meta) ;
        }
        return $query->with($toLoad);
    }

    protected function prepareMetaToLoad(array $meta)
    {
        return function (Relation $q) use ($meta) {
            // exact matches (no %)
            $metaIn = array_values(array_filter($meta, fn($i) => !str_contains($i, '%')));
            // wildcard matches
            $metaLike = array_values(array_filter($meta, fn($i) => str_contains($i, '%')));

            if (!empty($metaIn)) {
                $q->whereIn("meta_key", $metaIn);
            }

            foreach ($metaLike as $like) {
                $q->orWhere("meta_key", "LIKE", $like);
            }
        };
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
        ))->select($this->variationSelect);
    }
}