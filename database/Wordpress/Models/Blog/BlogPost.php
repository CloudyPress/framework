<?php

namespace CloudyPress\Database\Wordpress\Models\Blog;

use CloudyPress\Database\Wordpress\PostType;
use CloudyPress\Database\Wordpress\Relations\HasTerms;

class BlogPost extends PostType
{
    use HasTerms;

    protected string $postType = "post";

    public function terms()
    {
        return $this->hasTerms(["category"]);
    }
}