<?php

use Fuel\Core\View;

class Controller_Search extends \Controller_Base {
  public function action_index() {
    $v = View::forge('search/index');
    $this->template->title   = '検索結果';
    $this->template->content = $v;
  }
}
