<?php

namespace CloudyPress\Core\Wordpress;

use CloudyPress\Database\Wordpress\Models\WpPostMeta;

class MetaTermList implements \JsonSerializable
{

    /** @var WpPostMeta[]  */
    protected array $terms = [];

    public function __construct(
        array $terms
    )
    {
        foreach ($terms as $term) {
            $this->terms[$term->meta_key] = $term;
        }
    }

    public function __get(string $name)
    {
        return $this->getByKey($name);
    }

    public function getByKey($name): WpPostMeta|null
    {
        return $this->terms[$name] ?? null;
    }

    public function toArray(): array
    {
        return array_map( fn($i) => $i->meta_value, $this->terms);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function all(): array
    {
        return array_values($this->terms);
    }
}