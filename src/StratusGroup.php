<?php

namespace CloudyPress\Core;

use JsonSerializable;

class StratusGroup implements JsonSerializable
{
    public function __construct(
        protected array $items = [],
        protected int $currentPage = 1,
        protected int $perPage = 1,
        protected int $total = 1
    )
    {
    }

    public function toArray(): array
    {
        return [
            "data" => $this->items,
            "pagination" => [
                "total" => $this->total,
                "currentPage" => $this->currentPage,
                "perPage" => $this->perPage,
            ]
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}