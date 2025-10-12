<?php

namespace CloudyPress\Core\Support;

class Str
{
    /**
     * Convert a string to snake_case (or custom delimiter).
     *
     * Examples:
     * - "UserProfile"        => "user_profile"
     * - "HTTPServerID"       => "http_server_id"
     * - "userID"             => "user_id"
     * - "UUIDv4Token"        => "uuid_v4_token"
     * - "user-name value"    => "user_name_value"
     * - "Posts.Comments"     => "posts_comments"
     *
     * @param string $str
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $str, string $delimiter = '_'): string
    {
        if ($str === '') {
            return '';
        }

        // Normalize whitespace and common separators to single spaces
        $normalized = preg_replace('/[.\-\s]+/u', ' ', $str);

        // Insert spaces between camelCase / PascalCase boundaries and acronyms:
        // - Between a lowercase/digit and uppercase (userID -> user ID)
        // - Between acronym and next word (HTTPServer -> HTTP Server)
        $normalized = preg_replace('/([a-z0-9])([A-Z])/u', '$1 $2', $normalized);
        $normalized = preg_replace('/([A-Z]+)([A-Z][a-z])/u', '$1 $2', $normalized);

        // Insert spaces between letters and numbers (UUID4Token -> UUID 4 Token)
        $normalized = preg_replace('/([a-zA-Z])([0-9])/u', '$1 $2', $normalized);
        $normalized = preg_replace('/([0-9])([a-zA-Z])/u', '$1 $2', $normalized);

        // Lowercase and collapse multiple spaces
        $normalized = strtolower(trim(preg_replace('/\s+/u', ' ', $normalized)));

        // Replace spaces with the chosen delimiter
        return str_replace(' ', $delimiter, $normalized);
    }
}