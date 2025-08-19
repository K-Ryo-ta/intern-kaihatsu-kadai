<?php

use Fuel\Core\Controller_Template;
use Fuel\Core\View;

class Controller_Base extends Controller_Template
{
  public $template = 'template';

  public function before()
  {
    parent::before();
    $this->template->header = View::forge('base/header');
    if (! isset($this->template->title)) {
      $this->template->title = 'My App';
    }
  }

  public function after($response)
  {
    return parent::after($response);
  }
}
