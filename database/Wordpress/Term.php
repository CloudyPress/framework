<?php

namespace CloudyPress\Database\Wordpress;

use CloudyPress\Database\Nimbus\Model;

class Term extends Model
{

    protected string $tableName = "terms";

    protected string $primaryKey = "term_id";
}