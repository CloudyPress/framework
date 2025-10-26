<?php

namespace CloudyPress\Database\Wordpress\Relations;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Relations\Relation;
use CloudyPress\Database\Wordpress\PostType;

class TermRelation extends Relation
{

    public function __construct(
        Builder $query,
        PostType $parent,
        protected array $taxonomy,
    )
    {
        parent::__construct($query, $parent);
    }

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, [] );
        }

        return $models;
    }

    /**
     * @inheritDoc
     */
    public function applyFilterByParents(array $models)
    {
        // nothing here, we don't interact with db
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        if ( count($results) <= 0)
        {
            return [];
        }

        $terms = wp_get_object_terms( array_map( fn(PostType $i) => $i->{$i->getKeyName()}, $models), $this->taxonomy, [
            'fields' => 'all_with_object_id'
        ] );

        if ( $terms instanceof \WP_Error)
            throw new \Exception( $terms->get_error_message());


        /** @var PostType $model */
        foreach ($models as $model) {

            $data = array_values( array_filter( $terms, function($term) use ($model) {
                return $term->object_id == $model->{$model->getKeyName()};
            }) );

            $model->setRelation( $name, $data );
        }


        return $models;
    }
}