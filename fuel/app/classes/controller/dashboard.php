<?php

use Fuel\Core\View;
use Fuel\Core\Uri;

class Controller_Dashboard extends Controller_Base
{
  public function action_index()
  {
    $v = View::forge('dashboard/index');
    $v->set('create_url',  Uri::create('portfolios/create'), false);
    $v->set('view_base',   Uri::create('portfolios/detail/'), false);
    $v->set('api_url',     Uri::create('api/dashboard/items.json'), false);
    $v->set('placeholder', '/assets/img/placeholder.png', false);

    $this->template->title   = 'みんなの最新作品';
    $this->template->content = $v;
  }
}
