<?php

namespace CloudyPress\Database\Query;

class Expression
{

    public function __construct(
        protected string|int|float $value
    )
    {
    }

    /**
     * @return float|int|string
     */
    public function getValue(): float|int|string
    {
        return $this->value;
    }
}