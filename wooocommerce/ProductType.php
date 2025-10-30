<?php

namespace CloudyPress\Woocommerce;

enum ProductType: string
{
    case SIMPLE = 'simple';

    case VARIABLE = 'variable';

    case GROUPED = 'grouped';
}