<?php

namespace CloudyPress\Database\Wordpress\Relations;

use CloudyPress\Database\Nimbus\Relations\Relation;

class ThumbnailRelation extends Relation
{

    public function initRelation(array $models, string $relation): array
    {
        return $models;
    }

    /**
     * @inheritDoc
     */
    public function applyFilterByParents(array $models)
    {
        $ids = [];

        foreach ($models as $model) {
            $ids[] = $model->getKey();
        }

        global $wpdb;
        $postmeta = $wpdb->prefix . "postmeta";
        $this->query->join(
            $postmeta,
            "{$postmeta}.post_id", "=",
            "{$this->parent->getTableName()}.{$this->parent->getKeyName()}"
        )->join(
            $this->parent->getTableName(),
            "{$postmeta}.meta_value",
            "=",
            "{$this->parent->getTableName()}.{$this->parent->getKeyName()}"
        )
            ->where("{$postmeta}.meta_key", "_thumbnail_id")
            ->whereIn("{$this->parent->getTableName()}.{$this->parent->getKeyName()}", $ids);

        dd( $this->query->get(), $this->query->toSql(), $this->query->getBindings() );
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        return $models;
    }
}