<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 09.04.2018
 * Time: 14:12
 */

namespace ModuleHash;
class PassHasher
{
    private $alg, $salt_pos, $salt_len;

    /**
     * PassHasher constructor.
     * @param $alg
     * @param $salt_pos
     * @param $salt_len
     */

    public function __construct(string $alg, int $salt_pos, int $salt_len)
    {
        $this->alg = $alg;
        $this->salt_pos = $salt_pos;
        $this->salt_len = $salt_len;
    }

    private function _createSalt(): string
    {
        $first = md5(time());
        $second = md5(rand(0, 9999999));
        $salt = hash($this->alg, $first . $second);
        return substr($salt, 0, $this->salt_len);
    }

    private function _hashWithSalt(string $data, string $salt): string
    {
        $s1 = hash($this->alg, $data . $salt . substr($data, 3));
        $s2 = hash($this->alg, $salt . $data . substr($salt, 0));
        $hash = hash($this->alg, $s1 . $s2);
        return substr_replace($hash, $salt, $this->salt_pos, $this->salt_len);
    }

    private function _saltFromHash(string $hash):string
    {
        return substr($hash,$this->salt_pos,$this->salt_len);
    }

    public function passHash(string $pass):string
    {
        return $this->_hashWithSalt($pass,$this->_createSalt());
    }

    public function comparePass(string $pass,string $hash):bool
    {
        $salt = $this->_saltFromHash($hash);
        $hash_cur = $this->_hashWithSalt($pass,$salt);
        return  $hash_cur === $hash;
    }
}