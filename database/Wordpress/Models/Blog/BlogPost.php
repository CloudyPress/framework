<?php

namespace CloudyPress\Database\Wordpress\Models\Blog;

use CloudyPress\Core\Wordpress\HasThumbnail;
use CloudyPress\Database\Wordpress\PostType;
use CloudyPress\Database\Wordpress\Relations\HasTerms;

class BlogPost extends PostType
{
    use HasTerms, HasThumbnail;

    protected string $postType = "post";

    public function terms()
    {
        return $this->hasTerms(["category"]);
    }

}