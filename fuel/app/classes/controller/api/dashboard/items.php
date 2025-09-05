<?php

class Controller_Api_Dashboard_Items extends \Fuel\Core\Controller_Rest
{
  protected $format = 'json';

  public function get_index()
  {
    $page     = max(1, (int)\Input::get('page', 1));
    $per_page = 24;
    $offset   = ($page - 1) * $per_page;

    $portfolio_model = new \Model_Portfolio();
    $total = $portfolio_model->get_count_all_portfolios();
    $rows  = $portfolio_model->get_all_portfolios($per_page, $offset);

    $has_prev = $page > 1;
    $has_next = ($offset + count($rows)) < $total;

    return $this->response([
      'status' => 'success',
      'data'   => $rows,
      'meta'   => [
        'total'    => (int)$total,
        'page'     => $page,
        'per_page' => $per_page,
        'has_prev' => $has_prev,
        'has_next' => $has_next,
      ],
    ]);
  }
}
