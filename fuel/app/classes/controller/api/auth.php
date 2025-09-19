<?php

use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Validation;

/**
 * @property \Fuel\Core\Response $response
 */
class Controller_Api_Auth extends Controller_Api_Base {
  protected $auth_model;

  public function before() {
    parent::before();

    // 共通モデルのインスタンス化
    $this->auth_model = new \Model_Auth();
  }

  //ユーザーの情報を取得
  //ユーザーの認証を確認する必要あり(beforeで実施)
  public function post_login() {

    try {
      $input = Input::json();

      $val = Validation::forge();
      $val->add_field('username', 'ユーザー名', 'required|min_length[3]|max_length[255]');
      $val->add_field('password', 'パスワード', 'required|min_length[8]');

      if (!$val->run($input)) {
        $errors = array_map(function ($e) {
          return $e->get_message();
        }, $val->error());
        return $this->response(['status' => 'error', 'errors' => $errors], 400);
      }

      $remember = !empty($input['remember']);
      $result = $this->auth_model->login_user(
        $input['username'],
        $input['password'],
        $remember
      );

      if (!empty($result['success'])) {
        // セッションIDを再生成（セッション固定攻撃対策）
        Session::instance()->rotate();
        return $this->response(['status' => 'success'], 200);
      }

      $message = !empty($result['error']) ? $result['error'] : 'ユーザー名またはパスワードが違います。';
      return $this->response(['status' => 'error', 'message' => $message], 401);
    } catch (\Exception $e) {
      return $this->response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  //ユーザーの情報を登録
  public function post_register() {
    try {
      $input = Input::json();

      // バリデーション
      $val = Validation::forge();
      $val->add_field('username', 'ユーザー名', 'required|min_length[3]|max_length[255]');
      $val->add_field('email',    'メールアドレス', 'required|valid_email|max_length[255]');
      $val->add_field('password', 'パスワード', 'required|min_length[8]');
      $val->add_field('bio',      '自己紹介', 'max_length[65535]');

      if (!$val->run($input)) {
        $errors = array();
        foreach ($val->error() as $error) {
          $errors[] = $error->get_message();
        }
        return $this->response(array('status' => 'error', 'errors' => $errors), 400);
      }

      $result = $this->auth_model->register_user(
        $input['username'],
        $input['email'],
        $input['password'] ?? null,
        $input['bio'] ?? ''
      );

      if ($result['success']) {
        return $this->response(['status' => 'success', 'message' => '新規登録しました。'], 201);
      }

      $msg = !empty($result['error']) ? $result['error'] : '登録に失敗しました。';
      return $this->response(['status' => 'error', 'message' => $msg], 400);
    } catch (\Exception $e) {
      return $this->response(array('status' => 'error', 'message' => $e->getMessage()), 500);
    }
  }

  public function post_logout() {
    try {
      $this->auth_model->logout_user();
      return $this->response(['status' => 'success', 'message' => 'ログアウトしました。'], 200);
    } catch (\Exception $e) {
      return $this->response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
}
