<?php

use Fuel\Core\Response;
use Fuel\Core\View;
use Auth\Auth;

class Controller_Mypage extends Controller_Base {
  public function before() {
    parent::before();

    if (!Auth::check()) {
      return Response::redirect('login');
    }
  }

  public function action_index() {
    $view = View::forge('mypage/index');
    $this->template->title = 'マイページ';
    $this->template->content = $view;
  }
}
