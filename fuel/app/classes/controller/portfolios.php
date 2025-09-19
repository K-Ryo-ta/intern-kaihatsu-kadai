<?php

use Fuel\Core\View;
use Fuel\Core\Response;
use Auth\Auth;

class Controller_Portfolios extends Controller_Base {
  public function before() {
    parent::before();
  }

  public function action_detail() {
    $v = View::forge('portfolios/detail');
    $this->template->title   = '作品詳細';
    $this->template->content = $v;
  }

  public function action_create() {
    if (!Auth::check()) return Response::redirect('login');

    $v = View::forge('portfolios/create');
    $this->template->title   = '新規投稿';
    $this->template->content = $v;
  }

  public function action_edit() {
    if (!Auth::check()) return Response::redirect('login');

    $v = View::forge('portfolios/edit');
    $this->template->title   = '投稿を編集';
    $this->template->content = $v;
  }
}
