<?php

namespace CloudyPress\Database\Wordpress;

use CloudyPress\Database\Nimbus\Model;

class Taxonomy extends Model
{
    protected $table = 'wp_term_taxonomy';
    protected $primaryKey = 'term_taxonomy_id';


}