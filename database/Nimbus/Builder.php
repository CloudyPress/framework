<?php

namespace CloudyPress\Database\Nimbus;

use CloudyPress\Core\Paginated;
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
        $this->query->from( $this->getTableName() )
            ->select(["*"]);
        return $this;
    }

    public function getTableName(): string
    {
        return $this->model->getTableName();
    }

    public function select(array|string $columns = "*")
    {
        $this->query->select($columns);

        return $this;
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

    public function paginate(int $page = 1, int $perPage = 15, array|string $columns = '*')
    {
        $this->query->select($columns);

        $perPage = $_GET["perPage"] ?? $perPage ;
        $page = $_GET["page"] ?? $page;

        $this->query->inPage($page, $perPage);
        $models = $this->get();

        return new Paginated(
            $models,
            $perPage,
            $page,
            $this->query->getCountForPagination($columns)
        );
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

    // @var array<string, \Closure|bool> $eagerLoad
    /** @var array<String, WithRelation> $eagerLoad */
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

        foreach ( $relations as $relation => $constraint )
        {
            //Giving ["rel1", "rel2"]
            if ( is_numeric($relation) && is_string($constraint) )
            {
                $parts = explode(".", $constraint);
                $parent = array_shift($parts);

                if (!isset($results[$parent])) {
                    $results[$parent] = WithRelation::parseNameAndAttribute($parent);
                }

                $results[$parent]->addNested( implode('.', $parts) );
            }

            // Giving [ "meta" => fn($q) => $q->where(...) ]
            if ( is_string($relation) && $constraint instanceof \Closure)
            {
                $parts = explode(".", $relation);
                $parent = array_shift($parts);
                if (!isset($results[$parent])) {
                    $results[$parent] = WithRelation::parseNameAndAttribute($parent, count($parts) == 0 ? $constraint : null);
                }

                $results[$parent]->addNested( implode('.', $parts), $constraint);
            }
        }

        return $results;
    }

    public function eagerLoadRelationToModel(array $models): array
    {


        //load all top-level models
        foreach ($this->eagerLoad as $name => $relation) {
            // Load relation from top level to down

            $models = WithRelation::eagerLoadRelation($this->model, $models, $name, $relation);
        }

        return $models;
    }

    protected function eagerLoadRelation( array $models, string $name, WithRelation $withRelation)
    {

        if (!method_exists($this->model, $name)) {
            throw new \RuntimeException("Relation {$name} does not exist on model");
        }

        /** @var Relation $relation */
        $relation = $this->model->{$name}();

        $relation->applyFilterByParents($models);

        $constraint = $withRelation->getConstraints();
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

    public function join(string $table, string $first, string $operator = null, string $second = null, $type = 'inner', $where = false)
    {
        $this->query->join( $table, $first, $operator, $second, $type, $where );
        return $this;
    }
}