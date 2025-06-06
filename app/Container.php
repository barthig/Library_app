<?php

namespace App;

class Container
{
    private static array $bindings = [];
    private static array $instances = [];

    public static function bind(string $abstract, callable $factory): void
    {
        self::$bindings[$abstract] = $factory;
    }

    public static function get(string $abstract)
    {
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        if (isset(self::$bindings[$abstract])) {
            self::$instances[$abstract] = (self::$bindings[$abstract])(self::class);
            return self::$instances[$abstract];
        }

        if (!class_exists($abstract)) {
            throw new \RuntimeException("Class $abstract not found");
        }

        $reflection = new \ReflectionClass($abstract);
        $constructor = $reflection->getConstructor();
        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            $instance = new $abstract();
        } else {
            $deps = [];
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                if ($type && !$type->isBuiltin()) {
                    $deps[] = self::get($type->getName());
                } else {
                    $deps[] = null;
                }
            }
            $instance = $reflection->newInstanceArgs($deps);
        }
        self::$instances[$abstract] = $instance;
        return $instance;
    }
}
