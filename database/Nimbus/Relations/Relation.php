<?php

namespace CloudyPress\Database\Nimbus\Relations;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Model;

abstract class Relation
{

    protected bool $eagerKeysWereEmpty = false;



    public function __construct(
        protected Builder $query,
        protected Model $parent
    )
    {
    }

    abstract public function initRelation(array $models, string $relation): array;


    /**
     * Tell the relation which parent models we’re about to eager load for.
     *
     * Example: if you’re eager loading posts for 10 users,
     * this method adds a WHERE user_id IN (1,2,3,...)
     * constraint to the relation’s query.
     * @param array $models
     * @return mixed
     */
    abstract public function applyFilterByParents(array $models);

    /**
     * Get the relationship for eager loading.
     * @return array
     */
    public function fetchRelatedModels(): array
    {
        return $this->eagerKeysWereEmpty
            ? []
            : $this->get();
    }

    public function get($columns = ['*'])
    {
        return $this->query->get($columns);
    }

    abstract public function matchWithParents( array $models, $results, $name ): array;
}