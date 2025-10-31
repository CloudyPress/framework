<?php

namespace CloudyPress\Core\Form;

use WP_REST_Request;

abstract class FormBase
{

    protected string $method = 'POST';

    public function resolve(WP_REST_REQUEST $request)
    {

        $rules = $this->prepareRules();

        dd( $rules );
        //execute the actual code
        $this->handle();
    }

    abstract public function rules(): array;

    abstract public function handle();

    protected function prepareRules(): array
    {
        return array_map( function($rule){
            if ( is_array($rule) )
                return $rule;

            return explode("|", $rule);
        },  $this->rules() );
    }
}