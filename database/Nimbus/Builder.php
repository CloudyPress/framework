<?php

namespace CloudyPress\Database\Nimbus;

use CloudyPress\Database\Nimbus\Relations\Relation;
use CloudyPress\Database\Query\Queryable;
use PDO;

class Builder implements Queryable
{
    // ---------------------------------------------------------------------
    // 🔧 Properties & Constructor
    // ---------------------------------------------------------------------

    protected Model $model;

    protected QueryBuilder $query;

    public function __construct()
    {
        $this->query = new QueryBuilder();
    }

    // ---------------------------------------------------------------------
    // ⚙️ Model Setup
    // ---------------------------------------------------------------------

    /**
     * Attach a model instance and initialize the query.
     * @param Model $model
     * @return Builder
     */
    public function setModel(Model $model): Builder
    {
        $this->model = $model;
        $this->query->setTable( $this->getTableName() )
            ->select(["*"]);
        return $this;
    }

    public function getTableName(): string
    {
        return $this->model->getTableName();
    }


    // ---------------------------------------------------------------------
    // 📥 Retrieval
    // ---------------------------------------------------------------------

    public function toSql(): string
    {
        return $this->query->toSql();
    }

    public function getBindings(): array
    {
        return $this->query->getBindings();
    }

    public function get(): array
    {
        $modelClass = get_class($this->model);

        $models = array_map(
            fn($d) => new $modelClass($d),
            $this->query->get()
        );

        if ( count( $models) > 0)
        {
            $models = $this->eagerLoadRelationToModel($models);
        }


        return $models;
    }

    public function first( string|array $columns = '*' ): Model|null
    {
        if (is_string($columns))
            $columns = [$columns];

        $data = $this->get();

        return $data[0] ?? null;
    }

    public function find( string|int $key, string|array $columns = '*' ): Model|null
    {
        return $this->whereKey($key)->first($columns);
    }


    // ---------------------------------------------------------------------
    // 🔍 Conditions
    // ---------------------------------------------------------------------

    public function where( string $column, string $operator, string|null $value = null ): Builder
    {
        $this->query->where( $column, $operator, $value );

        return $this;
    }

    public function whereKey( string|int $value): Builder
    {
        $this->query->where( $this->model->getKeyName(), $value );

        return $this;
    }

    public function whereIn(string $column, Queryable|\Closure|array $values): Builder
    {
        $this->query->whereIn( $column, $values );
        return $this;
    }

    // ---------------------------------------------------------------------
    // 🔍 Relations
    // ---------------------------------------------------------------------

    /** @var array<string, \Closure|bool> $eagerLoad */
    protected array $eagerLoad = [];

    public function with( array|string $relations )
    {

        $this->eagerLoad = array_merge($this->eagerLoad, $this->parseWithRelationships(
            is_string($relations) ? func_get_args() : $relations
        ) );

        return $this;
    }

    protected function parseWithRelationships( array $relations): array
    {
        if ($relations === []) {
            return [];
        }

        $results = [];

        foreach ($this->prepareNestedWithRelationships($relations) as $name => $constraint) {

            /*
             * The ORM will try to eager load posts.comments.author directly,
             * but it has no idea how to get there because posts and posts.comments were never registered.
             * → Result: either nothing loads, or you get N+1 queries because it falls back to lazy loading.
             */

            $results = $this->addNestedWiths($name, $results);
            $results[$name] = $constraint;
        }

        return $results;
    }

    protected function prepareNestedWithRelationships(array $relations, string $prefix = ""): array
    {
        $preparedRelationships = [];

        if ($prefix !== '') {
            $prefix .= '.';
        }

        /*
        // If any of the relationships are formatted with the [$attribute => array()]
        // syntax, we shall loop over the nested relations and prepend each key of
        // this array while flattening into the traditional dot notation format.
        When you call Nimbus like:
        User::with([
            'posts' => [
                'comments' => [
                    'author'
                ]
            ]
        ])->get();

        Nimbus needs to flatten that nested array into dot notation:
        [
          'posts' => true,
          'posts.comments' => true,
          'posts.comments.author' => true,
        ]
        */
        foreach ($relations as $key => $value) {
            if (! is_string($key) || ! is_array($value)) {
                continue;
            }

            [$attribute, $attributeSelectConstraint] = $this->parseNameAndAttributeSelectionConstraint($key);

            $preparedRelationships = array_merge(
                $preparedRelationships,
                ["{$prefix}{$attribute}" => $attributeSelectConstraint],
                $this->prepareNestedWithRelationships($value, "{$prefix}{$attribute}"),
            );

            unset($relations[$key]);
        }


        // Pass from "posts:id,name" to, "post => () => $query->select(["id", "name"])
        foreach ($relations as $key => $value) {
            if (is_numeric($key) && is_string($value))
                [ $key, $value ] = $this->parseNameAndAttributeSelectionConstraint($value);

            $preparedRelationships[$prefix.$key] = $this->combineConstraints([
                $value,
                $preparedRelationships[$prefix.$key] ?? static function () {
                    //
                },
            ]);
        }

        return $preparedRelationships;
    }

    protected function parseNameAndAttributeSelectionConstraint($name)
    {
        return str_contains($name, ":")
            ? $this->createSelectWithConstraint($name)
            : [ $name, static function () {

        }];
    }


    /**
     * Parse selection columns from "posts:id,name" to "SELECT id, name FROM....
     * @param string $name
     * @return array
     */
    protected function createSelectWithConstraint(string $name)
    {
        return [
            explode(":", $name)[0],
            static function ($query) use ($name) {
                $query->select( explode(',', explode(':', $name)[1]) );
            }
        ];
    }

    /**
     * Combine an array of constraints into a single constraint.
     *
     * @param  array  $constraints
     * @return \Closure
     */
    protected function combineConstraints(array $constraints)
    {
        return function ($builder) use ($constraints) {
            foreach ($constraints as $constraint) {
                $builder = $constraint($builder) ?? $builder;
            }

            return $builder;
        };
    }

    /**
     * ensures that when you eager load a deep relation,
     * all parent relations are also registered in the eager‑load map,
     * each with at least a no‑op closure.
     *
     * This guarantees the ORM can walk the chain step by step without missing any intermediate relation.
     * @param $name
     * @param $results
     * @return mixed
     */
    protected function addNestedWiths($name, $results)
    {
        $progress = [];

        // If the relation has already been set on the result array, we will not set it
        // again, since that would override any constraints that were already placed
        // on the relationships. We will only set the ones that are not specified.
        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] = static function () {
                    //
                };
            }
        }


        return $results;
    }
    public function eagerLoadRelationToModel(array $models): array
    {
        foreach ($this->eagerLoad as $name => $constraint) {
            // Load first relation on toplevel
            if ( !str_contains(".", $name) )
            {
                $models = $this->eagerLoadRelation($models, $name, $constraint);
            }
        }

        return $models;
    }

        protected function eagerLoadRelation( array $models, string $name, \Closure $constraint)
        {
            if (!method_exists($this->model, $name)) {
                throw new \RuntimeException("Relation {$name} does not exist on model");
            }

            /** @var Relation $relation */
            $relation = $this->model->{$name}();


            $relation->applyFilterByParents($models);

            $constraint($relation);

            return $relation->matchWithParents(
                $relation->initRelation( $models, $name),
                $relation->fetchRelatedModels(),
                $name
            );
        }

    // ---------------------------------------------------------------------
    // 🛠️ Future Extensions
    // ---------------------------------------------------------------------
    // Here you could add:
    // - insert/update/delete methods
    // - relationship helpers
    // - aggregation (count, sum, avg)
    // - scopes
}