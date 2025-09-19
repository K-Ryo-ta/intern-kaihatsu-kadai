<?php

use Fuel\Core\Input;
use Auth\Auth;
use Fuel\Core\Validation;

/**
 * @property \Fuel\Core\Response $response
 */

class Controller_Api_Profile extends Controller_Api_Base {
  protected $user_model;
  protected $skill_model;
  protected $user_id;

  public function before() {
    parent::before();

    // 認証チェック
    if (!Auth::check()) {
      $this->response(array(
        'status' => 'error',
        'message' => 'Unauthorized'
      ), 401);
      return $this->response;
    }

    // ログイン中のuser_idを取得
    $this->user_id = Auth::get_user_id()[1];

    // 共通モデルのインスタンス化
    $this->user_model = new \Model_User();
    $this->skill_model = new \Model_Skill();
  }

  //ユーザーの情報を取得
  //ユーザーの認証を確認する必要あり(beforeで実施)
  public function get_index() {
    try {
      $user = $this->user_model->get_user_info($this->user_id);
      $skills = $this->skill_model->get_user_skill_names($this->user_id);

      return $this->response(array(
        'status' => 'success',
        'data' => array(
          'username' => $user['username'],
          'email' => $user['email'],
          'bio' => $user['bio'],
          'skills' => $skills
        )
      ));
    } catch (\Exception $e) {
      return $this->response(array('status' => 'error', 'message' => $e->getMessage()), 500);
    }
  }

  //ユーザーの情報を更新
  //ユーザーの認証を確認する必要あり(beforeで実施)
  public function post_index() {
    try {
      $user = $this->user_model->get_user_info($this->user_id);
      $input = Input::json() ?: Input::post();

      $val = Validation::forge();
      $val->add_field('email', 'メール', 'required|valid_email|max_length[255]');
      $val->add_field('bio', '自己紹介', 'max_length[65535]');

      if (isset($input['old_password']) && !empty($input['old_password'])) {
        $val->add_field('old_password', '旧パスワード', 'min_length[8]');
      }

      if (isset($input['new_password']) && !empty($input['new_password'])) {
        $val->add_field('new_password', '新パスワード', 'min_length[8]');
      }

      if (!$val->run($input)) {
        $errors = array();
        foreach ($val->error() as $error) {
          $errors[] = $error->get_message();
        }
        return $this->response(array('status' => 'error', 'errors' => $errors), 400);
      }

      $this->user_model->update_profile(
        $user,
        $input['email'],
        $input['bio'] ?? '',
        $input['new_password'] ?? null,
        $input['old_password'] ?? null,
      );

      return $this->response(array('status' => 'success', 'message' => 'プロフィールを更新しました'));
    } catch (\Exception $e) {
      return $this->response(array('status' => 'error', 'message' => $e->getMessage()), 500);
    }
  }
}
