<?php

namespace CloudyPress\Database\Wordpress\Models;

use CloudyPress\Database\Wordpress\PostType;

class WpAttachment extends PostType
{

    protected string $postType = "attachment";

    public function metaToLoad(): array
    {
        return [
            "_wp_attached_file"
        ];
    }

    public function getUrl(): string{
        $uploads = wp_get_upload_dir();

        return $uploads['baseurl'] . '/' . $this->meta->_wp_attached_file?->meta_value;
    }
}