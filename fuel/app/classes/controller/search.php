<?php

use Fuel\Core\View;
use Fuel\Core\Input;

class Controller_Search extends \Controller_Base {
  public function action_index() {
    // クエリ初期値（表示用）
    $initial = [
      'q'    => Input::get('q', ''),
      'tags' => Input::get('tags', ''),
    ];
    $v = View::forge('search/index');
    $v->set('initial', $initial, false);

    $this->template->title   = '検索結果';
    $this->template->content = $v;
  }
}
