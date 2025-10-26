<?php

namespace CloudyPress\Database\Wordpress;

use CloudyPress\Core\Support\Str;
use CloudyPress\Database\Nimbus\Builder;
use CloudyPress\Database\Nimbus\Model;
use CloudyPress\Database\Wordpress\Models\WpPostMeta;
use CloudyPress\Database\Wordpress\Relations\HasPostMeta;

/**
 * @method static Builder publish()
 * @method static Builder draft()
 * @method static Builder trash()
 * @method static Builder pending()
 * @method static Builder future()
 * @method static Builder whereStatus(string|array $status)
 */
abstract class PostType extends Model
{
    protected string $postType;

    protected string $keyName = "ID";
    protected string $tableName = "posts";

    public function newQuery(): Builder
    {
        return parent::newQuery()->where("post_type", $this->getPostType());
    }

    public function getPostType(): string
    {
        return $this->postType ?? Str::snake( class_basename($this) );
    }

    public static function postType(): string
    {
        return (new static)->getPostType();
    }

    public function scopeWhereStatus($query, string|array $status)
    {
        if (is_array($status)) {
            return $query->whereIn("post_status", $status);
        }

        return $query->where("post_status", $status);
    }

    public function scopePublish($query)
    {
        return $query->where("post_status", "publish");
    }

    public function scopeDraft($query)
    {
        return $query->where("post_status", "draft");
    }

    public function scopeTrash($query)
    {
        return $query->where("post_status", "trash");
    }

    public function scopeFuture($query)
    {
        return $query->where("post_status", "future");
    }

    public function scopePending($query)
    {
        return $query->where("post_status", "pending");
    }

    public function meta()
    {
        return new HasPostMeta(
            WpPostMeta::query(),
            $this
        );
    }
}