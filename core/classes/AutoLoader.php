<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 21.03.2018
 * Time: 16:14
 */

class AutoLoader
{
    private function __construct()
    {
    }
    private static $pathes = [
        [
            "pattern" => "/^Model([A-Z0-9][a-z0-9]*)+$/",
            "path" => MODELS_PATH
        ],
        [
            "pattern" => "/^Module([A-Z0-9][a-z0-9]*)+$/",
            "path" => MODULES_PATH
        ],
        [
            "pattern" => "/^Entity\\\([A-Z0-9][a-z0-9]*)+$/",
            "path" => APP_PATH."entities/"
        ],
        [
            "pattern" => "/^Controller([A-Z0-9][a-z0-9]*)+$/",
            "path" => CONTROLLERS_PATH
        ]
    ];

    public static function load($name)
    {
        foreach (self::$pathes as $path) {
            if (preg_match($path["pattern"], $name)) {
                $className = explode("\\", $name);
                $name = end($className);
                require_once $path["path"] . $name . ".php";
                return;
            }
        }
    }
}