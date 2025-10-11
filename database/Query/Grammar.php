<?php

namespace CloudyPress\Database\Query;

use CloudyPress\Database\Nimbus\QueryBuilder;

class Grammar
{

    protected array $components = [
        "wheres"
    ];

    protected array $params = [];

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function compile(QueryBuilder $query): string
    {
        $columns = implode(", ", $query->getColumns());

        $compiled = $this->compileComponents($query);

        return "SELECT {$columns} FROM {$query->getTable()} ".implode(" ", $compiled);
    }

    protected function compileComponents(QueryBuilder $query)
    {
        $sql = [];

        foreach ($this->components as $component) {
            $method = "compile".ucfirst($component);

            $sql[] = $this->{$method}($query);
        }

        return $sql;
    }
    public function compileWheres(QueryBuilder $query): string
    {
        $processed = [];

        foreach ( $query->wheres as $where) {
            extract($where);

            // Simulate PDO, we need to create an id
            $id = uniqid("where_");
            $this->params[$id] = $value;

            $processed[] = "{$boolean} WHERE {$column} {$operator} :{$id}";
        }

        return $this->removeLeadingBooleans( implode(" ", $processed) );
    }

    protected function removeLeadingBooleans( string $value ): string
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
}