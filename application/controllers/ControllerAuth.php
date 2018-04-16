<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 11.04.2018
 * Time: 13:30
 */

class ControllerAuth extends Controller
{
    public function action_register()
    {
        try{
            $login = @$_POST["login"];
            $pass = @$_POST["pass"];
            $conf = @$_POST["conf"];
            $mail = @$_POST["email"];
            if (empty($login) || empty($pass) || empty($mail) || empty($conf)) throw new Exception("Enter all fields");
            if ($pass !== $conf) throw new Exception("Passwords are not similar");
            ModuleAuth::instance()->register($login,$pass,["email"=>$mail]);
            $this->redirect(URLROOT);
        } catch (Exception $e){
            $this->response($e->getMessage());
        }
    }

    public function action_login()
    {
        try{
            $login = @$_POST["login"];
            $pass = @$_POST["pass"];
            $remember = isset($_POST["remember"]);
            if (empty($login) || empty($pass)) throw new Exception("Enter login and password");
            ModuleAuth::instance()->login($login,$pass,$remember);
            $this->redirect(URLROOT."todo");
        } catch (Exception $e){
           $this->response($e->getMessage());
        }
    }

    public function action_logout()
    {
        ModuleAuth::instance()->logout();
        $this->redirect(URLROOT);
    }

    public function action_logoutAll()
    {
        ModuleAuth::instance()->logout(true);
        $this->redirect(URLROOT);
    }
}