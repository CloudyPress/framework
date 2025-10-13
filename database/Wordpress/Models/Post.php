<?php

namespace CloudyPress\Database\Wordpress\Models;

use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Wordpress\Models\Blog\BlogPost;

/**
 * Model to get all PostTypes
 * If u want a specific postType like BlogPost
 * @see BlogPost
 * @see Page
 */
class Post extends Model
{
    protected string $tableName = 'posts';
}