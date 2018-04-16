<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 06.04.2018
 * Time: 10:54
 */

class ModuleDebug
{
    private static function _dump($array)
    {
        echo "<ul>";
        foreach ($array as $key => $value) {
            echo "<li style='color: green'><span style='color: red;'>{$key} => </span>";
            if (!is_array($value)) {
                echo "{$value} ("
                    . gettype($value)
                    . ")</li>";
            } else self::_dump($value);
            echo "</li>";
        }
        echo "</ul>";
    }

    public static function dump($value)
    {
        if (is_array($value)) self::_dump($value);
        else {
            echo "<pre>";
            var_dump($value);
        }
    }

    private static function _baseDump($value)
    {
        echo "<style>body{padding-top: 50vh !important;}</style>";
        echo "<div style='position: fixed;top: 0;left: 0;right: 0;height: 50vh;overflow-y: scroll;z-index: 10;'>";
        self::_dump($value);
        echo "</div>";
    }

    public static function dd($value)
    {
        self::_baseDump($value);
        die();
    }
}