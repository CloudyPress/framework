<?php

namespace CloudyPress\Database\Wordpress\Models;

use CloudyPress\Database\Nimbus\Model;

class Post extends Model
{

    protected string $tableName = "posts";

    protected string $keyName = "ID";

    protected array $hidden = [
        "ping_status",
        "to_ping"
    ];
}