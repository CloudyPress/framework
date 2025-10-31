<?php

namespace CloudyPress\Core;

use JsonSerializable;

class StratusGroup implements JsonSerializable
{

    protected int|null $currentPage = null;
    protected int|null $perPage = null;
    protected int $total;

    public function __construct(
        protected array $items = [],
    )
    {
        $this->total = count($this->items);
    }

    public function withPagination(int $currentPage, int $perPage): StratusGroup
    {
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        return $this;
    }
    public function toArray(): array
    {
        $res = [
            "data" => $this->items,
        ];

        if ( $this->currentPage && $this->perPage ) {
            $res["pagination"] = [
                "total" => $this->total,
                "currentPage" => $this->currentPage,
                "perPage" => $this->perPage,
            ];
        }

        return $res;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}