<?php
include CLASS_PATH . "Route.php";
include CLASS_PATH . "exceptions/RouterException.php";
include CLASS_PATH . "Controller.php";


class Router
{
    private $routes = [];
    private $activeRoute = null;
    private $components = null;
    private static $instance = null;

    private function __construct()
    {
        $this->routes[] = new Route("404", [
            "controller" => "404",
            "action" => "index"
        ]);
    }

    private function parseUri(): void
    {
        if ($this->components !== null) return;
        $uri = explode("?", $_SERVER["REQUEST_URI"])[0];
        $uri = trim($uri, "/");
        $this->components = explode("/", $uri);
    }

    public static function getInstance(): Router
    {
        return self::$instance ? self::$instance : self::$instance = new self();
    }

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function run(): void
    {
        $this->parseUri();
        foreach ($this->routes as $route) {
            if (!$route->exec($this->components)) continue;
            $this->activeRoute = $route;
            $this->navigate($route->getController(), $route->getAction());
            return;
        }
        throw new RouterIncorrectUri("NOT FOUND VALID ROUTE");
    }

    public function redirect(string $uri): void
    {
        header("Location:" . $uri);
    }

    public function redirect404(): void
    {
        header("Location:" . URLROOT . "404");
    }

    public function navigate(string $controller, string $action): void
    {
        if (empty($controller) || empty($action)) throw new RouterException("EMPTY CONTROLLER OR ACTION NAME");
        $controller = "Controller" . ucfirst($controller);
        $action = "action_" . $action;
        $controller_path = CONTROLLERS_PATH . $controller . ".php";
        if (!file_exists($controller_path)) throw new RouterException("CONTROLLER {$controller} NOT FOUND");
        require_once $controller_path;
        $controller_instance = new $controller();
        if (!method_exists($controller_instance, $action)) throw new RouterException("METHOD {$action} IN {$controller_instance} DOES NOT EXIST");
        $controller_instance->$action();
        $controller_instance->sendResponse();
    }

    public function getParamFromActiveRoute(string $name):?string
    {
        return @$this->activeRoute->getParam($name);
    }
}