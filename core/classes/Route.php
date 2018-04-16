<?php

class Route
{
    private $components = [];
    private $params = [];

    const PARAM_REGEXP = "/\{\??([a-z][a-z0-9]*)\}/i";
    const OPTIONAL_PARAM_REGEXP = "/\{\?([a-z][a-z0-9]*)\}/i";

    public function __construct(string $pattern, array $default_params = [])
    {
        $pattern = trim(URLROOT . $pattern, "/");
        $this->components = explode("/", $pattern);
        $this->params = $default_params;
    }

    private function isParam(string $name, string $value): bool
    {
        if (!preg_match(self::PARAM_REGEXP, $name, $arr)) return false;
        else $this->params[$arr[1]] = strtolower($value);
        return true;
    }

    private function isOptionalParam(string $name): bool
    {
        return preg_match(self::OPTIONAL_PARAM_REGEXP, $name);
    }

    public function exec(array $real_route_components)
    {
        $count = count($this->components);
        if (count($real_route_components) > $count) return false;
        for ($i = 0; $i < $count; $i++) {
            if ($real_route_components[$i] === $this->components[$i]) continue;
            if (empty($real_route_components[$i]) && $this->isOptionalParam($this->components[$i])) return true;
            if (empty($real_route_components[$i])) return false;
            if (!$this->isParam($this->components[$i], $real_route_components[$i])) return false;
        }
        return true;
    }

    public function getController()
    {
        return @$this->params["controller"];
    }

    public function getAction()
    {
        return @$this->params["action"];
    }

    public function getParam($name)
    {
        return @$this->params[$name];
    }
}