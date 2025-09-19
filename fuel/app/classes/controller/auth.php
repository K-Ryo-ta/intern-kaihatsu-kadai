<?php

use Fuel\Core\View;
use Fuel\Core\Response;
use Auth\Auth;

class Controller_Auth extends Controller_Base {
  public function before() {
    parent::before();
  }

  public function action_register() {
    $v = View::forge('register/index');
    $this->template->title   = 'Register';
    $this->template->content = $v;
  }


  public function action_login() {
    // ログインしてたら、トップへリダイレクト
    if (Auth::check()) {
      return Response::redirect('/');
    }
    $v = View::forge('login/index');
    $this->template->title   = 'Login';
    $this->template->content = $v;
  }
}
