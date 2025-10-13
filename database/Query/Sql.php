<?php

namespace CloudyPress\Database\Query;

use InvalidArgumentException;
use RuntimeException;

class Sql
{

    public static function run(string $sql, array $params = [] ): array
    {

        // Create connection
        $mysqli = mysqli_init();
        $mysqli->real_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        // Set charset
        $mysqli->set_charset(DB_CHARSET);

        // Convert named params to positional ? and capture the order
        $order = [];
        $sqlWithQuestionMarks = preg_replace_callback(
            '/:([a-zA-Z_]\w*)/',
            function ($m) use (&$order) {
                $order[] = $m[1];
                return '?';
            },
            $sql
        );


        // Prepare
        $stmt = $mysqli->prepare($sqlWithQuestionMarks);
        if ($stmt === false) {
            $err = $mysqli->error;
            $mysqli->close();
            throw new RuntimeException("Prepare failed: {$err}; SQL: {$sqlWithQuestionMarks}");
        }

        // If there are params to bind, build types and values in order
        if (count($order) > 0) {
            $values = [];
            $types = '';

            foreach ($order as $name) {
                if (!array_key_exists($name, $params)) {
                    $stmt->close();
                    $mysqli->close();
                    throw new InvalidArgumentException("Missing parameter: {$name}");
                }
                $val = $params[$name];
                $values[] = $val;
                if (is_int($val)) {
                    $types .= 'i';
                } elseif (is_float($val) || is_double($val)) {
                    $types .= 'd';
                } elseif (is_null($val)) {
                    // treat null as string and let bind_param pass null by reference
                    $types .= 's';
                } else {
                    $types .= 's';
                }
            }

            // bind_param requires variables by reference
            $refs = [];
            foreach ($values as $i => $v) {
                $refs[$i] = &$values[$i];
            }

            // Prepend types
            array_unshift($refs, $types);

            // Call bind_param dynamically
            if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
                $err = $stmt->error;
                $stmt->close();
                $mysqli->close();
                throw new RuntimeException("bind_param failed: {$err}; SQL: {$sqlWithQuestionMarks}");
            }
        }

        // Execute
        $stmt->execute();

        // Fetch results (requires mysqlnd for get_result)
        $result = $stmt->get_result();
        if ($result === false) {
            $err = $stmt->error ?: $mysqli->error;
            $stmt->close();
            $mysqli->close();
            throw new RuntimeException("Query error: {$err}; SQL: {$sqlWithQuestionMarks}");
        }

        $rows = $result->fetch_all(MYSQLI_ASSOC);

        $result->free();
        $stmt->close();
        $mysqli->close();

        return $rows;
    }
}