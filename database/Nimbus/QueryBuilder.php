<?php

namespace CloudyPress\Database\Nimbus;

use Closure;
use CloudyPress\Database\Query\Expression;
use CloudyPress\Database\Query\Grammar;
use CloudyPress\Database\Query\Queryable;
use CloudyPress\Database\Query\Sql;
use InvalidArgumentException;

/**
 * Class for only query managements
 */
class QueryBuilder implements Queryable
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
     * If what to start a new query from beggining
     * like sub queries
     * @return $this
     */
    public function newQuery()
    {
        return new static($this->grammar);
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

    public function whereIn(string $column, Queryable|Closure|array $values , $boolean = "AND", $isNot = false){
        $type = $isNot ? 'NotIn' : 'IN';

        /*
         * If in case pass a param like:
         * ->whereIn("column", User::select(..)->.... )
         * So can use that query and put it inside condition
         */
        if( $this->isQueriable($values) )
        {
            [$query, $bindings] = $this->createSub($values);

            $values = [new Expression($query)];

            //add bindings
            $this->grammar->addParams( ...$bindings );
        }

        $this->wheres[] = compact( 'type', 'column', 'values', 'boolean');

        return $this;
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

    public function toSQL(): string
    {
        return $this->grammar->compile($this);
    }

    public function getBindings(): array
    {
        return $this->grammar->getParams();
    }

    // ---------------------------------------------------------------------
    // 🔍 Relations
    // ---------------------------------------------------------------------


    /**
     * Create a fresh sub query that we can use in certain functions
     * @param Queryable|Closure $query
     * @return array<string, array<string[]> > [Sql string, Bindings to SQL]
     */
    public function createSub(Queryable|Closure $query): array
    {
        // If the given query is a Closure, we will execute it while passing in a new
        // query instance to the Closure. This will give the developer a chance to
        // format and work with the query before we cast it to a raw SQL string.
        if ( $query instanceof Closure )
        {
            $callback = $query;
            $query = $this->newQuery();

            $callback($query);
        }

        return [ $query->toSQL(), $query->getBindings() ];
    }

    public function isQueriable($value)
    {
        return $value instanceof Queryable
            || $value instanceof Closure;
    }

}