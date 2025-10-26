<?php

namespace CloudyPress\Database\Query;

interface DBDriver
{

    public static function run(string $sql, array $params = [] ): array;
}