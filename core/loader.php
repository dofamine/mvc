<?php
defined("DOCROOT") or die ("NO DIRECT ACCESS");
include CLASS_PATH . "Config.php";
include CLASS_PATH . "Router.php";
include CLASS_PATH . "View.php";
include CLASS_PATH . "Model.php";
include CLASS_PATH . "Entity.php";
include CLASS_PATH . "AutoLoader.php";
spl_autoload_register("Autoloader::load");

$router = Router::getInstance();

$router->addRoute(new Route("",
    [
        "controller"=>"main",
        "action"=>"index"
    ]));

$router->addRoute(new Route("register",
    [
        "controller"=>"main",
        "action"=>"register"
    ]));
$router->addRoute(new Route("regaction",
    [
        "controller"=>"auth",
        "action"=>"register"
    ]));
$router->addRoute(new Route("login",
    [
        "controller"=>"auth",
        "action"=>"login"
    ]));
$router->addRoute(new Route("todo",
    [
        "controller"=>"main",
        "action"=>"notes"
    ]));
$router->addRoute(new Route("logout",
    [
        "controller"=>"auth",
        "action"=>"logout"
    ]));
$router->addRoute(new Route("logoutAll",
    [
        "controller"=>"auth",
        "action"=>"logoutAll"
    ]));
$router->addRoute(new Route("addNote",
    [
        "controller"=>"main",
        "action"=>"addNote"
    ]));
$router->addRoute(new Route("new",
    [
        "controller"=>"main",
        "action"=>"new"
    ]));
$router->addRoute(new Route("delete/{id}",
    [
        "controller"=>"main",
        "action"=>"delete"
    ]));
$router->addRoute(new Route("change/{id}",
    [
        "controller"=>"main",
        "action"=>"change"
    ]));
$router->addRoute(new Route("done/{id}",
    [
        "controller"=>"main",
        "action"=>"done"
    ]));
$router->addRoute(new Route("changenote/{id}",
    [
        "controller"=>"main",
        "action"=>"updateNote"
    ]));
$router->addRoute(new Route("shownote/{id}",
    [
        "controller"=>"main",
        "action"=>"showNote"
    ]));
try {
    $router->run();
} catch (RouterException $exception) {
//    $router->redirect404();
    echo $exception->getMessage();
};

