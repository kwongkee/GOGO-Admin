<?php

namespace think;

use think\Config;

class MongoDB
{
    public static $conn;

    public static function init($dsn)
    {
        if (is_null(self::$conn)) {
            $class = 'MongoClient';
            if (!class_exists($class)) {
                $class = 'MongoDB\Driver\Manager';
            }
            self::$conn = new $class($dsn);
        }
        return self::$conn;
    }
}
