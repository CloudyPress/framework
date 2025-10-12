<?php

namespace CloudyPress\Database\Wordpress\Models\User;

use CloudyPress\Database\Nimbus\Model;

class UserMeta extends Model
{

    protected string $tableName = "usermeta";

    protected string $keyName = "umeta_id";
}