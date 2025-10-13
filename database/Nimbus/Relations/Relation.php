<?php

namespace CloudyPress\Database\Nimbus\Relations;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Query\Queryable;

abstract class Relation implements Queryable
{

    protected bool $eagerKeysWereEmpty = false;



    public function __construct(
        protected Builder $query,
        protected Model $parent
    )
    {
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->query, $method)) {
            return $this->query->$method(...$parameters);
        }

        throw new \BadMethodCallException("Method {$method} does not exist on Relation or QueryBuilder.");
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

    public function toSql(): string
    {
        return $this->query->toSql();
    }

    public function getBindings(): array
    {
        return $this->query->getBindings();
    }
}