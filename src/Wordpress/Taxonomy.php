<?php
namespace CloudyPress\Core\Wordpress;

use CloudyPress\Core\Contracts\Runnable;
use WP_Taxonomy;

abstract class Taxonomy implements Runnable
{

    abstract public static function id(): string;

    abstract public function nameSingular(): string;
    public function namePlural(): string
    {
        return $this->nameSingular()."s";
    }

    public function getId(): string
    {
        return self::id();
    }

    public static function run()
    {
        $taxonomy = new static();

        \add_action( "init", [$taxonomy, "register"]);
    }

    public function getLabels(): array
    {
        return [];
    }

    public function register()
    {
        register_taxonomy('topics',array('post'), [
            'hierarchical' => true,
            'labels' => $this->labelGenerate(),
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'topic' ),
        ]);
    }


    protected function labelGenerate(): string
    {
        $labels = [];

        dd( WP_Taxonomy::get_default_labels() );
        $base = [

        ];

        $labels["name"] = $this->namePlural();
        $labels["singular_name"] = $this->nameSingular();

        return $labels;
    }
}