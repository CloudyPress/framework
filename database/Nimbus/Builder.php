<?php

namespace CloudyPress\Database\Nimbus;

use PDO;

class Builder
{
    protected Model $model;

    protected QueryBuilder $query;

    public function __construct()
    {
        $this->query = new QueryBuilder();
    }

    /**
     * @param Model $model
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
        global $wpdb;
        return $wpdb->base_prefix.$this->model->getTableName();
    }

    public function get(): array
    {
        $modelClass = get_class($this->model);

        return array_map(
            fn($d) => new $modelClass($d),
            $this->query->execute()
        );
    }

    public function where( string $column, string $operator, string|null $value = null ): Builder
    {
        $this->query->where( $column, $operator, $value );

        return $this;
    }
}