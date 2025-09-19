<?php

use Auth\Auth;

class Controller_Api_Dashboard_Items extends Controller_Api_Base {
  protected $format = 'json';
  protected $portfolio_model;

  public function before() {
    parent::before();
    $this->portfolio_model = new \Model_Portfolio();
    $this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
  }

  public function get_index() {
    $portfolios  = $this->portfolio_model->get_all_portfolios();

    return $this->response([
      'status' => 'success',
      'data'   => $portfolios,
    ]);
  }

  //ユーザーのポートフォリオ一覧を取得
  //ユーザーの認証を確認する必要あり
  public function get_user_portfolios() {
    if (!Auth::check()) {
      return $this->response(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    $user_id = Auth::get_user_id()[1];

    if ($user_id <= 0) {
      return $this->response(['status' => 'error', 'message' => 'bad user id'], 400);
    }

    $portfolios = $this->portfolio_model->get_user_portfolios($user_id);

    return $this->response(['status' => 'success', 'data' => $portfolios]);
  }
}
