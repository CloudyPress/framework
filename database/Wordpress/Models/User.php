<?php

namespace CloudyPress\Database\Wordpress\Models;

use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Wordpress\Models\User\UserMeta;

class User extends Model
{

    protected string $tableName = 'users';

    protected string $keyName = "ID";

    protected array $hidden = [
        "user_pass",
        "user_activation_key"
    ];

    public function meta()
    {
        return $this->hasMany( UserMeta::class );
    }
}