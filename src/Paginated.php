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
            "pagination" => $this->getPagination(),
        ];
    }

    public function get(int $index)
    {
        return $this->data[$index];
    }

    public function getPagination()
    {
        return [
            "currentPage" => $this->currentPage,
            "perPage" => $this->perPage,
            "total" => $this->total
        ];
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