<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 09.04.2018
 * Time: 15:48
 */

namespace ModuleAuth;


class UserSession
{
    private $short, $long;
    private $current_session = null;
    private static $instance = null;
    const SESSION_KEY = "dsabdhavschj";

    public static function instance(): self
    {
        return self::$instance === null
            ? self::$instance = new self()
            : self::$instance;
    }

    private function __construct()
    {
        $config = \Config::load("user_session");
        $this->short = $config->short;
        $this->long = $config->long;
    }

    private static function getIp(): string
    {
        return md5($_SERVER["REMOTE_ADDR"]);
    }

    private static function getAgent(): string
    {
        return md5($_SERVER["HTTP_USER_AGENT"]);
    }

    private static function getToken(int $id): string
    {
        return \ModuleHash::getPassHasher()->passHash($id . self::getAgent() . self::getIp());
    }

    public function createSession(int $id, bool $long = false): bool
    {
        $time = $long ? $this->long : $this->short;
        $session_data = [
            "user_agent" => self::getAgent(),
            "user_ip" => self::getIp(),
            "user_id" => $id,
            "token" => self::getToken($id),
            "expires" => $time + time(),
            "created" => time()
        ];
        \ModuleDatabaseConnection::instance()->users_tokens->insert($session_data);
        return setcookie(self::SESSION_KEY, $session_data["token"], $session_data["expires"], URLROOT);
    }

    public function validateSession(): bool
    {
        if ($this->current_session !== null) return !empty($this->current_session);
        if (empty($_COOKIE[self::SESSION_KEY])) return false;
        $this->current_session = \ModuleDatabaseConnection::instance()
            ->users_tokens
            ->where("token", $_COOKIE[self::SESSION_KEY])
            ->first();
        if (empty($this->current_session)) return false;
        if ($this->current_session["user_ip"] !== self::getIp()) return false;
        if ($this->current_session["user_agent"] !== self::getAgent()) return false;
        if (time() > $this->current_session["expires"]) return false;
        if (time() > $this->current_session["expires"]
            - ($this->current_session["expires"] - $this->current_session["created"]) /2) $this->continueSession();
        return true;
    }

    public function getUserIdFromSession(): int
    {
        if (!$this->validateSession()) throw new \Exception("SESSION DOES NOT EXISTS");
        return (int)$this->current_session["user_id"];
    }

    private function _destroySession(bool $deep): bool
    {
        if (!$deep) {
            \ModuleDatabaseConnection::instance()
                ->users_tokens
                ->deleteById($this->current_session['id']);
            \ModuleDatabaseConnection::instance()
                ->users_tokens
                ->deleteWhere("id=? AND expires<?",[$this->current_session['id'],time()]);
            return setcookie(self::SESSION_KEY, "", time() - 1, URLROOT);
        } else {
            \ModuleDatabaseConnection::instance()
                ->users_tokens
                ->deleteWhere("user_id=?", [$this->current_session['user_id']]);
            return setcookie(self::SESSION_KEY, "", time() - 1, URLROOT);
        }
    }

    public function destroySession(bool $deep = false): bool
    {
        if (!$this->validateSession()) return false;
        return $this->_destroySession($deep);
    }

    public function continueSession(): bool
    {
        if (!$this->validateSession()) return false;
        $id = (int)$this->current_session["id"];
        $time = $this->current_session["expires"] - $this->current_session["created"];
        \ModuleDatabaseConnection::instance()
            ->users_tokens
            ->updateById($id, [
                "expires" => time() + $time,
                "created" => time()
            ]);
        return setcookie(self::SESSION_KEY, $this->current_session["token"], time() + $time, URLROOT);
    }
}