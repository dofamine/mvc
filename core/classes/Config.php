<?php

class Config{
    private function __construct()
    {
    }

    public static function load(string $name):?object {
        if(file_exists(APP_CONFIG_PATH.$name.".php")){
            return (object)include APP_CONFIG_PATH.$name.".php";
        }
        if(file_exists(CONFIG_PATH.$name.".php")){
            return (object)include CONFIG_PATH.$name.".php";
        }
        return null;
    }
}