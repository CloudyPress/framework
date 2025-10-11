<?php

namespace CloudyPress\Database\Nimbus;

abstract class Model implements \JsonSerializable
{
    protected string $tableName;

    protected string $primaryKey;

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

    public function toArray(): array
    {

        // array_diff_key is faster because it works directly on keys
        return array_diff_key(
            $this->attributes,
            array_flip($this->hidden) // turn hidden list into keys for quick lookup
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}