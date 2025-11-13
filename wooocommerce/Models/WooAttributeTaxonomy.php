<?php

namespace CloudyPress\Woocommerce\Models;

use CloudyPress\Core\Attribute;
use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Wordpress\Relations\HasTerms;
use CloudyPress\Woocommerce\Relations\WooTermRelation;

/**
 * @property-read int attribute_id
 * @property-read string attribute_name
 * @property-read string attribute_label
 * @property-read string attribute_type
 * @property-read string attribute_orderby
 * @property-read bool attribute_public
 */
class WooAttributeTaxonomy extends Model
{
    use HasTerms;

    protected string $keyName = "attribute_id";

    protected string $tableName = "woocommerce_attribute_taxonomies";

    protected function mappings(): array
    {
        return [
            ...parent::mappings(),
            "attribute_name" => Attribute::make(
                get: fn ($value) => "pa_".$value
            )
        ];
    }

    public function terms()
    {
        return new WooTermRelation(
            static::query(),
            $this
        );
    }
}