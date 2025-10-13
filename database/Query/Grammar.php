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

    public function addParams(...$params): void
    {
        $this->params = array_merge($this->params, $params);
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

    /*
    protected function removeLeadingBooleans( string $value ): string
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
    */
}