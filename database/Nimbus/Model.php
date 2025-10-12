<?php

namespace CloudyPress\Database\Nimbus;

use CloudyPress\Core\Support\Str;
use CloudyPress\Database\Nimbus\Relations\HasMany;

/**
     * @method static Builder where($column, $operator = null, $value = null)
     * @method static Builder with( ...$relations )
 */
abstract class Model implements \JsonSerializable
{
    protected string $tableName;

    protected string $keyName = "id";

    protected array $attributes;

    protected array $hidden = [];

    protected static string $builder = Builder::class;

    public function __construct( \WP_Post|array $attr = [] )
    {
        if ( $attr instanceof \WP_Post ) {

        }else{
            $this->attributes = $attr;
        }
    }



    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getKeyName()
    {
        return $this->keyName;
    }


    public function newQuery()
    {
        return $this->newNimbusBuilder();
    }

    public function newNimbusBuilder()
    {
        return (new static::$builder)
            ->setModel($this);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        // If calling static from query
        if ( method_exists( static::$builder, $name) ) {
            return static::query()->{$name}(...$arguments);
        }
    }


    public static function query(){
        return (new static())->newQuery()->setModel( new static() );
    }

    public static function all( array|string $columns = "*")
    {
        return static::query()->get(
            is_array($columns) ? $columns : func_get_args()
        );
    }


    protected array $relations = [];

    public function setRelation(string $name, mixed $value )
    {
        $this->relations[$name] = $value;
    }

    public function toArray(): array
    {

        // array_diff_key is faster because it works directly on keys

        return [
            ...array_diff_key(
                $this->attributes,
                array_flip($this->hidden) // turn hidden list into keys for quick lookup
            ),
            "relations" => $this->relations,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }



    public function getTable()
    {
        return $this->table ?? Str::snake( class_basename($this) );
    }

    public function getForeignKey()
    {
        return Str::snake(  class_basename($this)).'_'.$this->getKeyName();
    }

    /**
     * Qualify the given column name by the model's table.
     * @param $column
     * @return string
     */
    public function qualifyColumn($column)
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        return $this->getTable().'.'.$column;
    }


    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        /** @var Model $instance */
        $instance = new $related();

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasMany(
            $instance->newQuery(),
            $this,
            $instance->qualifyColumn($foreignKey),
            $localKey
        );
    }

    protected function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasMany($query, $parent, $foreignKey, $localKey);
    }

    public function __get(string $name)
    {
        if ( in_array($name, $this->attributes) )
        {
            return $this->attributes[$name];
        }
    }
}