<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 09.04.2018
 * Time: 14:12
 */
require_once MODULES_PATH . "ModuleHash/PassHasher.php";

use \ModuleHash\PassHasher;

class ModuleHash
{
    private static $passHasher = null;
    public static function getPassHasher():PassHasher
    {
        $pass_hasher_config = Config::load("hash")->passHasher;
        $salt_pos = $pass_hasher_config["salt_pos"];
        $salt_len = $pass_hasher_config["salt_len"];
        $alg = $pass_hasher_config["alg"];
        return self::$passHasher !== null
            ? self::$passHasher
            : self::$passHasher = new PassHasher($alg,$salt_pos,$salt_len);
    }
}