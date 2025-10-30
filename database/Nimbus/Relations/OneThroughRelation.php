<?php

namespace CloudyPress\Database\Nimbus\Relations;

use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Nimbus\Relations\Relation;

class OneThroughRelation extends Relation
{

    public function __construct(
        Builder $query,
        Model $parent,
        protected string $throughTable,   // intermediate model class
        protected Model $related,   // final model class
        protected string $firstKey,  // foreign key on through table (post_id)
        protected string $secondKey, // foreign key on related table (ID)
        protected string $localKey,  // local key on parent (ID)
        protected string $throughKey, // local key on through (meta_value)
    ){
        parent::__construct($query, $parent);
    }

    protected function callScope($method, $parameters): Builder|null
    {
        return $this->related->callScope($method, $parameters, $this->query);
    }

    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }
        return $models;
    }

    /**
     * @inheritDoc
     */
    public function applyFilterByParents(array $models)
    {
        $ids = array_map(fn($m) => $m->getKey(), $models);

        if (empty($ids)) {
            $this->eagerKeysWereEmpty = true;
            return;
        }

        // Build query: join through table, filter by parent IDs and meta_key
        $relatedTable = $this->related->getTableName();

        $this->query
            ->join($this->throughTable, "{$this->throughTable}.{$this->throughKey}", "=", "{$relatedTable}.{$this->secondKey}")
            ->whereIn("{$this->throughTable}.{$this->firstKey}", $ids)
        ->where("{$this->throughTable}.meta_key", "_thumbnail_id");
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        // Index results by parent_id (postmeta.post_id)
        $dictionary = [];
        foreach ($results as $result) {
            $parentId = $result->post_parent_id ?? $result->post_id ?? null;
            if ($parentId) {
                $dictionary[$parentId] = $result;
            }
        }

        foreach ($models as $model) {
            $key = $model->getKey();
            if (isset($dictionary[$key])) {
                $model->setRelation($name, $dictionary[$key]);
            }
        }

        return $models;
    }
}