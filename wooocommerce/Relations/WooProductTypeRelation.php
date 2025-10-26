<?php

namespace CloudyPress\Woocommerce\Relations;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Nimbus\Relations\Relation;

class WooProductTypeRelation extends Relation
{

    public function __construct(
        Builder $query,
        Model $parent
    )
    {
        parent::__construct($query, $parent);
    }


    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, "simple");
        }

        return $models;
    }

    /**
     * @inheritDoc
     */
    public function applyFilterByParents(array $models)
    {
        $ids = [];
        foreach ($models as $model) {
            $ids[] = $model->{$this->parent->getKeyName()};
        }

        global $wpdb;
        $prefix = $wpdb->prefix;

        /*
         SELECT tr.object_id, t.name
            FROM wp_term_relationships tr
            INNER JOIN wp_term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN wp_terms t ON tt.term_id = t.term_id
            WHERE tt.taxonomy = 'product_type'
              AND tr.object_id IN (101, 102, 103);

         */
        $this->query
            ->select(["object_id", "t.slug"])
            ->join("{$prefix}term_taxonomy tt", "{$this->query->getTableName()}.term_taxonomy_id", "=", "tt.term_taxonomy_id")
            ->join("{$prefix}terms t", "tt.term_id", "=", "t.term_id")
            ->where("tt.taxonomy", "product_type")
            ->whereIn("{$this->query->getTableName()}.object_id", $ids);
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        /** @var Model $model */
        foreach ($models as $model) {
            $term = array_find($results, fn($i) => $i->object_id == $model->{$this->parent->getKeyName()});

            $model->setRelation($name, $term->slug ?? "simple");
        }

        return $models;
    }
}