<?php

use Fuel\Core\View;
use Fuel\Core\Input;
use Fuel\Core\Uri;
use Fuel\Core\DB;

class Controller_Dashboard extends Controller_Base
{
  public function action_index()
  {
    $page     = max(1, (int)Input::get('page', 1));
    $per_page = 24;
    $offset   = ($page - 1) * $per_page;

    // 総数
    $total = (int)DB::select(DB::expr('COUNT(*) AS c'))
      ->from('portfolios')
      ->execute()
      ->get('c', 0);

    // 投稿一覧の取得
    $rows = DB::select('id', 'user_id', 'title', 'screenshot_url', 'created_at')
      ->from('portfolios')
      ->order_by('created_at', 'DESC')
      ->limit($per_page)
      ->offset($offset)
      ->execute()
      ->as_array();

    $boot = [
      'portfolios'  => array_values($rows),
      'create_url'  => Uri::create('portfolios/create'),
      'view_base'   => Uri::create('portfolios/detail/'),
      'placeholder' => '/assets/img/placeholder.png',
    ];

    $v = View::forge('dashboard/index');
    $v->set('boot', json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), false);
    $v->set('list', $rows, false);
    $v->set('placeholder', '/assets/img/placeholder.png', false);

    $pager = [
      'page'     => $page,
      'per_page' => $per_page,
      'total'    => $total,
      'has_prev' => $page > 1,
      'has_next' => ($offset + count($rows)) < $total,
      'prev_url' => Uri::create('dashboard', [], ['page' => $page - 1]),
      'next_url' => Uri::create('dashboard', [], ['page' => $page + 1]),
    ];
    $v->set('pager', $pager, false);

    $this->template->title   = 'みんなの最新作品';
    $this->template->content = $v;
  }
}
