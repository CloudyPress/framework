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
        $ids = [];
        foreach ($models as $model) {
            $ids[] = $model->{$this->localKey};
        }

        $this->query->whereIn($this->foreignKey, $ids);

        //dd($ids, $this->query->toSql(), $this->query->getBindings() );
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        if ( count($results) <= 0)
        {
            return [];
        }

        /** @var Model $model */
        foreach ($models as $model) {
            /** @var Model $value */
            $data = array_filter( $results, function ( $value ) use ( $model, $name ) {
                return $model->{$this->localKey} == $value->{$this->getCleanForeignKey()};
            } );

            $model->setRelation( $name, $data );
        }


        return $models;
    }

    /**
     * The foreignkey passed, is like: "table_name.column_name"
     * so whe need to clean it and get only "column_name" to
     * actually sync with the parent
     * @return string
     */
    protected function getCleanForeignKey(): string
    {
        $val = explode(".", $this->foreignKey);

        return end($val);
    }
}