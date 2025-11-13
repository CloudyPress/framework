<?php

namespace CloudyPress\Database\Wordpress;

use CloudyPress\Database\Nimbus\Model;

class Term extends Model
{

    protected string $tableName = "terms";

    protected string $primaryKey = "term_id";

    public function __construct(array|\WP_Term|\WP_Post $attr = [])
    {
        if ( $attr instanceof \WP_Term)
        {
            /**
             * +term_id: 84
             * +name: "En Plataforma"
             * +slug: "grabado"
             * +term_group: 0
             * +term_taxonomy_id: 84
             * +taxonomy: "pa_modalidad"
             * +description: ""
             * +parent: 0
             * +count: 35
             * +filter: "raw"
             */

            $attr = $attr->to_array();
        }

        parent::__construct($attr);

    }
}