<?php
namespace CloudyPress\Database\Query;

use CloudyPress\Database\Query\DBDriver;

class WPDB implements DBDriver
{
    public static function run(string $sql, array $params = []): array
    {
        global $wpdb;

        // Find named placeholders like :name
        if (!preg_match_all('/:([a-zA-Z_]\w*)/', $sql, $matches)) {
            // no named params -> run directly (but still sanitize array result)
            return $wpdb->get_results($sql, ARRAY_A) ?: [];
        }

        $names = $matches[1]; // ordered appearance of names in SQL
        $bindings = [];

        // Build the SQL with sprintf-style placeholders (%s, %d)
        $sqlWithPlaceholders = preg_replace_callback(
            '/:([a-zA-Z_]\w*)/',
            function ($m) use ($params, &$bindings) {
                $name = $m[1];

                if (!array_key_exists($name, $params)) {
                    throw new \InvalidArgumentException("Missing param: {$name}");
                }

                $value = $params[$name];

                // Detect type - prefer explicit typing in your API if possible
                if (is_int($value)) {
                    $bindings[] = $value;
                    return '%d';
                }

                if (is_float($value)) {
                    $bindings[] = $value;
                    return '%f';
                }

                // For safety, treat nulls specially (use IS NULL or %s with 'NULL' ?)
                if ($value === null) {
                    $bindings[] = $value;
                    // Use %s and let prepare quote '': prefer caller to use IS NULL instead.
                    return '%s';
                }

                // Fallback to string
                $bindings[] = $value;
                return '%s';
            },
            $sql
        );

        // Use wpdb->prepare and run the prepared SQL
        $prepared = $wpdb->prepare($sqlWithPlaceholders, ...$bindings);

        if ($prepared === null) {
            throw new \RuntimeException('wpdb->prepare returned null; check placeholders vs bindings.');
        }

        $rows = $wpdb->get_results($prepared, ARRAY_A);
        return $rows ?: [];
    }
}
