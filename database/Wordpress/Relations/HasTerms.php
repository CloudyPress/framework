<?php

namespace CloudyPress\Database\Wordpress\Relations;

trait HasTerms
{

    /**
     * @param array<string> $taxonomy
     * @return TermRelation
     */
    public function hasTerms(array $taxonomy)
    {
        return new TermRelation(
            $this->newQuery(),
            $this,
            $taxonomy
        );
    }
}