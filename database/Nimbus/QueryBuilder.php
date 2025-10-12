<?php

namespace CloudyPress\Database\Nimbus;

use CloudyPress\Database\Query\Grammar;
use CloudyPress\Database\Query\Sql;
use InvalidArgumentException;

/**
 * Class for only query managements
 */
class QueryBuilder
{
    public array $wheres = [];

    protected string $table = '';

    protected array $columns = [];

    protected Grammar $grammar;

    public function __construct(
         Grammar|null $grammar = null
    )
    {
        $this->grammar = $grammar ?? new Grammar();
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): QueryBuilder
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function select(array $columns): QueryBuilder
    {
        $this->columns = $columns;
        return $this;
    }

    public function where( string $column, string $operator = null, string $value = null, string $boolean = 'AND' )
    {

        if ( is_null($value) )
        {
            $value = $operator;
            $operator = '=';
        }

        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $type = "Expression";

        $this->wheres[] = compact( 'type',  'column', 'operator', 'value', 'boolean');

        return $this;
    }

    public function orWhere( string $column, string $operator = null, string $value = null )
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    protected function prepareValueAndOperator( $value, $operator, bool $useDefault)
    {
        if ( $useDefault )
        {
            return [$operator, "="];
        }elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
            ! in_array($operator, ['=', '<>', '!=']);
    }

    public function get()
    {
        return Sql::run( $this->grammar->compile($this), $this->grammar->getParams() );;
    }

    public function toSQL()
    {
        return $this->grammar->compile($this);
    }


    // ---------------------------------------------------------------------
    // 🔍 Relations
    // ---------------------------------------------------------------------






}