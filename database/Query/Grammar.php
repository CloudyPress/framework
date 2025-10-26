<?php

namespace CloudyPress\Database\Query;

use CloudyPress\Database\Nimbus\QueryBuilder;

class Grammar
{

    protected array $components = [
        "columns",
        "aggregate",
        "from",
        "joins",
        "wheres",
        "limit",
        "offset"
    ];

    protected array $params = [];

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function addParams(...$params): void
    {
        $this->params = array_merge($this->params, $params);
    }

    public function compile(QueryBuilder $query): string
    {
        if ( is_null($query->columns) || count($query->columns) == 0) {
            $query->columns = ["*"];
        }

        $compiled = $this->compileComponents($query);

        return trim(
            implode(" ", array_filter( $compiled, fn($v) => !is_null($v)) )
        );
    }

    protected function compileComponents(QueryBuilder $query)
    {
        $sql = [];

        foreach ($this->components as $component) {
            $method = "compile".ucfirst($component);

            if ( !is_null($query->$component) )
            {
                $sql[] = $this->{$method}($query, $query->$component);
            }

        }

        return $sql;
    }


    public function compileColumns(QueryBuilder $query, array $columns): string|null
    {
        if ( !is_null($query->aggregate) ) {
            return null;
        }

        return "SELECT ".implode(", ", $columns);
    }

    public function compileAggregate(QueryBuilder $query, array $aggregate): string|null
    {
        if ( is_null($aggregate) ) {
            return null;
        }

        extract($aggregate);

        return "SELECT ".strtoupper($function)."(".implode(",", $columns).") AS aggregate";
    }


    public function compileFrom(QueryBuilder $query, string $from): string
    {
        return "FROM {$from}";
    }

    public function compileJoins(QueryBuilder $query, array $joins): string
    {
        $sql = [];

        foreach ($joins as $join) {
            extract($join);

            // string $table, string $first, string $operator = null, string $second = null, $type = 'inner', $method = "where° | "on"
            $sql[] ="{$type} JOIN {$table} {$method} {$first} {$operator} {$second}";
        }

        return implode(" ", $sql);
    }

    public function compileWheres(QueryBuilder $query): string
    {
        if (empty($query->wheres)) {
            return '';
        }
        $first = true;

        $processed = [];

        foreach ( $query->wheres as $where) {
            extract($where);


            $prefix = $first ? 'WHERE' : strtoupper($boolean);
            $first = false;

                // Simulate PDO, we need to create an id
            if ($type === "Expression") {
                $id = uniqid("where_");
                $this->params[$id] = $value;
                $processed[] = "{$prefix} {$column} {$operator} :{$id}";
            }

            if ($type === "IN") {
                // Subquery case
                if (isset($values[0]) && $values[0] instanceof Expression) {
                    $processed[] = "{$prefix} {$column} IN ({$values[0]->getValue()})";
                    continue;
                }

                // Flat array of values
                $placeholders = [];
                foreach ($values as $val) {
                    $id = uniqid("wherein_");
                    $this->params[$id] = $val;
                    $placeholders[] = ":{$id}";
                }

                $processed[] = "{$prefix} {$column} IN (" . implode(', ', $placeholders) . ")";
            }
        }

        return implode(" ", $processed);
        //return $this->removeLeadingBooleans( implode(" ", $processed) );
    }

    public function compileLimit(QueryBuilder $query, int $limit): string
    {

        return "LIMIT {$query->getLimit()}";
    }

    public function compileOffset(QueryBuilder $query, int $offset)
    {
        return "OFFSET {$offset}";
    }

    /*
    protected function removeLeadingBooleans( string $value ): string
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
    */
}