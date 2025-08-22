<?php

use Fuel\Core\Controller_Template;
use Fuel\Core\Uri;
use Fuel\Core\View;
use Auth\Auth;

class Controller_Base extends Controller_Template
{
  public $template = 'template';

  public function before()
  {
    parent::before();

    $is = Auth::check();

    $session_boot = [
      'loggedIn' => (bool)$is,
      'urls' => [
        'dashboard' => Uri::create('/'),
        'login' => Uri::create('login'),
        'register' => Uri::create('register'),
        'mypage' => Uri::create('mypage'),
        'logout' => Uri::create('logout'),
      ],
    ];

    $v = View::forge('base/header');
    $v->set('session_boot', json_encode($session_boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), false);
    $this->template->header = $v;

    if (!isset($this->template->title)) {
      $this->template->title = 'My App';
    }
  }
}
