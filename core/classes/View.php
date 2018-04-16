<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 21.03.2018
 * Time: 14:48
 */

class View
{
    private $view, $template = null;
    private $params = [];
    private static $twig = null;
    private static $twig_temp = null;

    private static function twigInit()
    {
        $loader = new Twig_Loader_Filesystem(VIEWS_PATH);
        $loader_tmp = new Twig_Loader_Filesystem(TEMPLATES_PATH);
        self::$twig = new Twig_Environment($loader);
        self::$twig_temp = new Twig_Environment($loader_tmp);
    }

    public function __construct(string $name)
    {
        if (self::$twig === null || self::$twig_temp === null) self::twigInit();
        $this->view = $name;
    }

    protected function _renderView()
    {
        return self::$twig->render($this->view . ".twig", $this->params);
    }

    protected function _renderViewWithTemplate()
    {
        $this->params["view"] = $this->_renderView();
        return self::$twig_temp->render($this->template . ".twig", $this->params);
    }

    public function render(array $data = [])
    {
        $this->params = array_merge($this->params, $data);
        return $this->template === null ? $this->_renderView() : $this->_renderViewWithTemplate();
    }

    public function __toString()
    {
        return $this->render();
    }

    public function setParam(string $name, $value): void
    {
        $this->params[$name] = $value;
    }

    public function __set(string $name, $value)
    {
        $this->params[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->params[$name];
    }

    public function useTemplate(string $name = "default"): void
    {
        $this->template = $name;
    }

}