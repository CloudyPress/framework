<?php

namespace CloudyPress\Core;

class Paginated implements \JsonSerializable
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
            'data' => $this->data,
            "pagination" => [
                "current_page" => $this->currentPage,
                "per_page" => $this->perPage,
                "total" => $this->total
            ]
        ];
    }
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}