<?php

namespace CloudyPress\Core;

use IteratorAggregate;
use Traversable;

class Paginated implements \JsonSerializable, IteratorAggregate
{

    public function __construct(
        protected array $data,
        protected int $perPage,
        protected int $currentPage,
        protected int $total
    )
    {
    }

    public function toArray()
    {
        return [
            'data' =>  $this->data,
            "pagination" => [
                "current_page" => $this->currentPage,
                "per_page" => $this->perPage,
                "total" => $this->total
            ]
        ];
    }

    public function get(int $index)
    {
        return $this->data[$index];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}