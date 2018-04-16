<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 09.04.2018
 * Time: 14:11
 */
require_once MODULES_PATH . "ModuleHash.php";
require_once MODULES_PATH . "ModuleDatabaseConnection.php";
require_once MODULES_PATH . "ModuleAuth/UserSession.php";

use ModuleAuth\UserSession;

class ModuleAuth
{
    private $hasher, $db, $session;
    private $user = null;
    private $is_auth = null;
    private static $instance = null;

    public static function instance()
    {
        return self::$instance === null
            ? self::$instance = new self()
            : self::$instance;
    }

    private function __construct()
    {
        $this->hasher = ModuleHash::getPassHasher();
        $this->db = ModuleDatabaseConnection::instance();
        $this->session = UserSession::instance();
    }

    public function register(string $login, string $pass, array $data = []): int
    {
        if ($this->db->users->countOfWhere("login=?", [$login]) > 0) throw new Exception("user already exist");
        $data["login"] = $login;
        $data["pass"] = $this->hasher->passHash($pass);
        return $this->db->users->insert($data);
    }

    public function login(string $login, string $pass, bool $save = false)
    {
        $user = $this->db->users->where("login", $login)->first();
        if (!$user) throw new Exception("login does not exists");
        if (!$this->hasher->comparePass($pass, $user["pass"]))
            throw new Exception("invalid password");
        $this->session->createSession($user['id'], $save);
    }

    public function logout(bool $deep = false)
    {
        $this->session->destroySession($deep);
    }

    public function isAuth():?bool
    {
        if ($this->is_auth === null)
            $this->is_auth = $this->session->validateSession();
        return $this->is_auth;
    }

    public function getUser():?array
    {
        if ($this->user === null) {
            if (!$this->isAuth()) throw new Exception("no auth");
            $id = $this->session->getUserIdFromSession();
            $this->user = $this->db->users->getElementById($id);
        }
        return $this->user;
    }

    public function hasRole(string $roles_name):bool
    {
        $user=$this->getUser();
        $roles = $this->db->roles
            ->join("user_roles","role_id")
            ->where("user_id",(int)$user["id"])
            ->all();
        foreach ($roles as $role){
            if ($role["name"] === $roles_name) return true;
        }
        return false;
    }

    public function getRoles():?array
    {
        if (!$this->isAuth()) throw new Exception("no auth");
        $roles = $this->db->roles
            ->fields(["name"])
            ->join("user_roles","role_id","id")
            ->where("user_id",(int)$this->session->getUserIdFromSession())
            ->all();
        $result = [];
        foreach ($roles as $role){
            $result[] = $role["name"];
        }
        return $result;
    }
}