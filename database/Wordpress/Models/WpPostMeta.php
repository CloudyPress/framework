<?php

namespace CloudyPress\Database\Wordpress\Models;

use CloudyPress\Database\Nimbus\Model;
class WpPostMeta extends Model
{
    protected string $keyName = "meta_id";

    protected string $tableName = "postmeta";
}