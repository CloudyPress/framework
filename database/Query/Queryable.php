<?php

namespace CloudyPress\Database\Query;
/**
 * Class helper to verify into the function that is queryable
 */
interface Queryable
{

    public function toSql(): string;


    public function getBindings(): array;

    public function toSqlCompiled(): string;
}