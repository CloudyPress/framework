<?php

namespace CloudyPress\Database\Wordpress\Relations;

use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Nimbus\Relations\Relation;

class HasPostMeta extends Relation
{

    public function initRelation(array $models, string $relation): array
    {
        /** @var Model $model */
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
        dd( "applyFilterByParents MetaPost relation" );

        $ids = [];
        foreach ($models as $model) {
            $ids[] = $model->getKey();
        }

        $this->whereIn("post_id", $ids);
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        foreach ($models as $model) {
            $model->setRelation(
                $name,
                array_filter( $results, fn($i) => $i->post_id == $model->getKey() )
            );
        }

        return $models;
    }
}