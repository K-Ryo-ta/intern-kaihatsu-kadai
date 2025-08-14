<?php

use Fuel\Core\View;

class Controller_Login extends Controller_Base
{
  public function action_index()
  {
    $data = array(
      'userName' => 'Taro',
      'password'  => '123456'
    );

    $this->template->content = View::forge('login/index');
    $this->template->content->set_global('userData', json_encode($data), false);
  }
}
