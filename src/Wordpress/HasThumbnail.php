<?php

namespace CloudyPress\Core\Wordpress;

use CloudyPress\Database\Nimbus\Relations\OneThroughRelation;
use CloudyPress\Database\Wordpress\Models\WpAttachment;
use CloudyPress\Database\Wordpress\Models\WpPostMeta;

trait HasThumbnail
{

    protected array $thumbnailSelect = ["ID", "guid", "post_id"];
    public function thumbnail()
    {
        $throughTable = WpPostMeta::new()->getTableName();

        return (new OneThroughRelation(
            (new WpAttachment)->newQuery(),
            $this,
            $throughTable,   // through
            WpAttachment::new(), // related
            'post_id',           // firstKey (postmeta.post_id)
            'ID',                // secondKey (attachments.ID)
            'ID',                // localKey (teacher.ID)
            'meta_value',        // throughKey (postmeta.meta_value)
        ))->where("{$throughTable}.meta_key", "_thumbnail_id")
            ->select($this->thumbnailSelect)->withMeta();
    }
}