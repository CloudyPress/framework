<?php

namespace CloudyPress\Database\Nimbus;

use CloudyPress\Database\Nimbus\Relations\Relation;

class WithRelation
{

    public array $nested = [];

    protected array $constraints = [];

    public function __construct(
        protected string $name,
        ?\Closure $callback = null, // Just create when its need it
    )
    {
        if (!is_null($callback))
        $this->constraints[] = $callback;
    }

    /**
     * ensures that when you eager load a deep relation,
     *  all parent relations are also registered in the eager‑load map,
     *  each with at least a no‑op closure.
     * @param $name //Relation name or a path like: "author.meta"
     * @param \Closure|null $callback //Save it for the last nested child
     * @return void
     */
    public function addNested($name, \Closure $callback = null)
    {
        //Only continue if exists a sub rel, not an empty string
        if ($name === '') return;

        $parts = explode(".", $name);
        $parent = array_shift($parts);

        if ( !isset($this->nested[$parent])) {
            $this->nested[$parent] = new self($parent, count($parts) == 0 ? $callback : null);
        }else if( count($parts) == 0 && !is_null($callback)){
            $this->constraints[] = $callback;
        }

        $this->nested[$parent]->addNested( implode('.', $parts), $callback );
    }

    public function getConstraints(): \Closure
    {
        return static::combineClosures(
            count($this->constraints) > 0
                ? $this->constraints
                : [static function () {}]
        );
    }

    /**
     * Parse selection columns from "posts:id,name" to "SELECT id, name FROM....
     * @param string $name
     * @return WithRelation
     */
    public static function parseNameAndAttribute(string $name, \Closure $callback = null): self
    {
        if ( !str_contains($name, ":") ) {
            return new WithRelation($name, $callback);
        }

        [$relation, $columns] = explode(":", $name, 2);

        return new WithRelation(
            $relation,
            static function ($query) use ($columns) {
                // Separate posts:ID,post_name -> [post][ID,post_name]
                $query->select( explode(',', $columns) );
            }
        );
    }

    /**
     * Combine an array of constraints into a single constraint.
     *
     * @param  array  $constraints
     * @return \Closure
     */
    public static function combineClosures(array $constraints)
    {
        return function ($builder) use ($constraints) {
            foreach ($constraints as $constraint) {
                $builder = $constraint($builder) ?? $builder;
            }

            return $builder;
        };
    }

    public static function eagerLoadRelation(Model $parent, array &$models, string $name, self $withRelation): array
    {
        if (!method_exists($parent, $name)) {
            throw new \RuntimeException("Relation {$name} does not exist on model");
        }

        /** @var Relation $relation */
        $relation = $parent->{$name}();

        $relation->applyFilterByParents($models);

        $constraint = $withRelation->getConstraints();
        $constraint($relation);

        $hydrated = $relation->matchWithParents(
            $relation->initRelation( $models, $name),
            $relation->fetchRelatedModels(),
            $name
        );

        if ( count($hydrated) == 0 )
            return $hydrated;

        $subRelationModels = array_merge(
            ...array_map(
                fn(Model $model) => (array) $model->{$name},
                $hydrated
            )
        );

        if ( empty($subRelationModels) )
            return $hydrated;

        foreach ($withRelation->nested as $childName => $subWithRelation) {
            //Load sub relations
            static::eagerLoadRelation($subRelationModels[0], $subRelationModels, $childName, $subWithRelation);
        }

        return $hydrated;
    }
}