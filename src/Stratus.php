<?php

namespace CloudyPress\Core;

use CloudyPress\Database\Nimbus\Model;
use Darkredgm\CPC\Stratuses\Course\CourseItemStratus;

abstract class Stratus implements \JsonSerializable
{
    public function __construct(
        protected Model $model
    )
    {
    }

    public function __get(string $name)
    {
        return $this->model->{$name};
    }

    public function toArray(): array
    {
        return $this->model->toArray();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public static function group( Paginated|array $data )
    {
        $list = [];

        foreach ( $data as $item )
        {
            $list[] = new static( $item );
        }

        return $list;
    }

}