<?php

namespace CloudyPress\Woocommerce\Relations;

use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Nimbus\Relations\Relation;

class WooProductVariationRelation extends Relation
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
        $ids = [];

        dd( $models );
        /** @var Model $model */
        foreach ($models as $model) {
            $ids[] = $model->getKey();
        }

        $this->query->where("post_type", "product_variation")
            ->whereIn("post_parent ", $ids);
    }

    public function matchWithParents(array $models, $results, $name): array
    {
        /** @var Model $model */
        foreach ($models as $model) {
            $model->setRelation(
                $name,
                array_filter( $results, fn($i) => $i->post_parent == $model->getKey())
            );
        }

        return $models;
    }
}