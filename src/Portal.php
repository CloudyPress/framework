<?php

namespace CloudyPress\Core;

abstract class Portal
{

    protected static array $instances = [];

    protected static function getInstance(): object
    {
        $class = static::getPortalClass();

        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new $class();
        }

        return static::$instances[$class];
    }

    protected static function getPortalClass(): string
    {
        throw new \Exception('Not implemented');
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $instance = static::getInstance();

        if (method_exists($instance, $name)) {
            return $instance->$name(...$arguments);
        }

        throw new \BadMethodCallException("Method $name not found on " . get_class($instance));
    }
}