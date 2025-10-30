<?php

namespace CloudyPress\Database\Nimbus;

use CloudyPress\Core\Attribute;
use CloudyPress\Core\Support\Str;
use CloudyPress\Database\Nimbus\Relations\HasMany;

/**
 * @method static Builder select($columns = ["*"])
 * @method static Builder where($column, $operator = null, $value = null)
 * @method static Builder with( ...$relations )
 * @method static paginate(int $page = 1, int $perPage = 15, array|string $columns = '*') Paginate the results
 */
abstract class Model implements \JsonSerializable
{
    protected string $tableName;

    protected string $keyName = "id";

    protected array $attributes = [];

    protected array $hidden = [];


    //-------------------------------------------
    // MAGIC METHODS
    //-------------------------------------------

    public function __construct( \WP_Post|array $attr = [] )
    {
        if ( $attr instanceof \WP_Post ) {

        }else{
            $this->attributes = $attr;
        }
    }

    public static function __callStatic(string $name, array $arguments)
    {


        // If calling static from query
        if ( method_exists( static::$builder, $name) ) {
            return static::query()->{$name}(...$arguments);
        }

        $model = new static();

        $query = $model->callScope($name, $arguments);

        if ( $query )
            return $query;

        return null;
    }

    public function __get(string $name)
    {
        if ( isset($this->attributes[$name]) )
        {
            return $this->attributes[$name];
        }

        if ( isset( $this->relations[$name] ) )
        {
            return $this->relations[$name];
        }

        if ( $this->mappingExists($name) )
            return $this->mappingGet($name)->getValue();

        return null;
    }

    //-------------------------------------------
    // MAPPING
    //-------------------------------------------

    protected function mappings(): array
    {
        return [];
    }

    protected function mappingExists(string $name): bool
    {
        return isset( $this->mappings()[$name] );
    }

    protected function mappingGet(string $name): Attribute
    {
        return $this->mappings()[$name];
    }

    //-------------------------------------------
    // QUERY
    //-------------------------------------------

    protected static string $builder = Builder::class;

    public function getTableName(): string
    {
        global $wpdb;
        return $wpdb->base_prefix.($this->tableName ?? Str::snake( class_basename($this) ) );
    }

    public function getKeyName()
    {
        return $this->keyName;
    }

    public static function new($attr = []): static
    {
        return new static( $attr );
    }
    /**
     * Return the key value, like $post->ID, or the primary key set it
     * @return mixed|null
     */
    public function getKey()
    {
        return $this->{$this->getKeyName()};
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


    public static function query(): Builder
    {
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
        // Remove hidden attributes from the model's raw attributes.
        // - array_diff_key is efficient because it compares keys directly.
        // - array_flip turns the $hidden list into a hash map for O(1) lookups.
        $attrs = array_diff_key(
            $this->attributes,
            array_flip($this->hidden ?? [])
        );

        // Track which attributes were transformed by mappings.
        $mappings = [];

        foreach ( $attrs as $key => $value )
        {
            if ( $this->mappingExists($key) )
            {
                // Replace the raw value with the mapped/transformed value.
                $attrs  [$key] = $this->mappingGet($key);
                // Record that this mapping was applied.
                $mappings[$key] = $key;
            }

        }

        // Return the final array representation of the model:
        // - Spread operator merges the transformed attributes.
        // - "_mappings" shows which declared mappings were NOT applied
        //   (difference between all mappings() and those actually used).
        // - "relations" includes any loaded relationships.
        return [
            ...$attrs,
            "_mappings" => array_diff(
                $this->mappings(),
                $mappings
            ),
            "relations" => $this->relations,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
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

        return $this->getTableName().'.'.$column;
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



    public function callScope(string $name, mixed $scopeArgs, $query = null): Builder|null
    {
        $scoped = 'scope' . ucfirst($name);

        if (! method_exists($this, $scoped)) {
            return null;
        }

        if ( is_null($query) )
            $query = $this->newQuery();

        // Call the scope and capture query
        $this->{$scoped}($query, ...$scopeArgs);

        return $query;
    }
}