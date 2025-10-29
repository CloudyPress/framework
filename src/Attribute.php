<?php

namespace CloudyPress\Core;

use Closure;

class Attribute implements \JsonSerializable
{

    public static function make(
        \Closure $get,
        Closure|null $set = null
    ){
        return new static($get, $set);
    }

    public function __construct(
        protected \Closure $get,
        protected Closure|null $set = null,
    )
    {
    }

    public function getValue()
    {
        return call_user_func($this->get);
    }

    public function jsonSerialize()
    {
        return $this->getValue();
    }
}