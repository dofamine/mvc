<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 21.03.2018
 * Time: 16:54
 */

class Controller404 extends Controller
{
    public function action_index()
    {
        header("HTTP/1.1 404 Not Found", true,404);
        echo "<h1>404</h1>";
    }
}