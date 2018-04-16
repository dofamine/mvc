<?php
include_once MODULES_PATH."ModuleDatabase/Executor.php";
use ModuleDatabase\Executor;


class ModuleDatabaseConnection
{
    private static $inst = null;
    private $dbh = null;
    private $tables = [];
    public static function instance():self
    {
        return self::$inst ? self::$inst : self::$inst = new self();
    }

    private function __construct()
    {
        $config = Config::load("database");
        $connecting = "mysql:dbname={$config->dbname};host={$config->host};charset={$config->charset}";
        $this->dbh = new PDO($connecting, $config->login, $config->pass,
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        $this->tables = $this->dbh->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function __get($name)
    {
        if(!in_array($name,$this->tables)) throw new Exception("Table not exist");
        return new Executor($this->dbh,$name);
    }
}
