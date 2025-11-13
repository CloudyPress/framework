<?php

namespace CloudyPress\Woocommerce\Relations;

use CloudyPress\Database\Nimbus\Relations\Relation;
use CloudyPress\Database\Wordpress\Term;
use CloudyPress\Woocommerce\Models\WooAttributeTaxonomy;

class WooTermRelation extends Relation
{

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, []);
        }

        return $models;
    }

    /**
     * @inheritDoc
     */
    public function applyFilterByParents(array $models)
    {
        // TODO: Implement applyFilterByParents() method.
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        /** @var WooAttributeTaxonomy $model */
        foreach ($models as $model) {
            $terms = get_terms([
                'taxonomy'   => $model->attribute_name,
                'hide_empty' => false, // include even unused terms
            ]);

            if ( $terms && ! is_wp_error( $terms ) ) {
                $model->setRelation(
                    $name,
                    array_map(
                        fn( $i ) => new Term($i),
                        $terms
                    )
                );
            }else{
                dd_s($terms, $model->attribute_name);
            }
        }

        return $models;
    }
}