<?php

use Fuel\Core\View;

class Controller_Dashboard extends Controller_Base {
  public function action_index() {
    $v = View::forge('dashboard/index');
    $this->template->title   = 'みんなの最新作品';
    $this->template->content = $v;
  }
}
