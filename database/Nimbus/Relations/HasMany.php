<?php

namespace CloudyPress\Database\Nimbus\Relations;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Nimbus\Relations\Relation;

class HasMany extends Relation
{

    public function __construct(
        Builder $query,
        Model $parent,
        protected string $foreignKey,
        protected string $localKey
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

    }

    public function matchWithParents(array $models, $results, $name): array
    {
        if ( count($results) <= 0)
        {
            return $results;
        }

        /** @var Model $model */
        foreach ($models as $model) {
            /** @var Model $value */
            $data = array_filter( $results, function ( $value ) use ( $model, $name ) {
                return $model->{$this->localKey} == $value->{$this->foreignKey};
            } );

            $model->setRelation( $name, $data );
        }

        return $models;
    }
}