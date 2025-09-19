<?php

use Auth\Auth;
use Fuel\Core\Config;
use Fuel\Core\DB;
use Fuel\Core\Session;

class Model_Auth extends \Fuel\Core\Model {
  // ユーザー登録
  public function register_user($username, $email, $password, $bio = null) {
    try {
      // デフォルトグループを設定から取得（なければ1）
      $default_group = Config::get('application.user.default_group', 1);

      Auth::create_user(
        $username,
        $password,
        $email,
        $default_group,
        ['bio' => $bio]
      );

      return [
        'success' => true,
        'error' => null
      ];
    } catch (\Exception $e) {
      $error_message = $this->get_registration_error_message($e->getCode());

      return [
        'success' => false,
        'error' => $error_message ?: $e->getMessage()
      ];
    }
  }

  //ログイン
  public function login_user($username, $password, $remember = false) {
    // ログイン試行
    if (Auth::instance()->login($username, $password)) {
      // Remember meクッキーの設定
      if ($remember) {
        //覚えてほしい場合
        Auth::remember_me();
      } else {
        //覚えてほしくない場合かつ、remember_meが設定されている場合に削除
        Auth::dont_remember_me();
      }

      return [
        'success' => true,
        'error' => null
      ];
    }

    return [
      'success' => false,
      'error' => 'ユーザー名またはパスワードが違います。'
    ];
  }

  // ログアウト
  public function logout_user() {
    Auth::dont_remember_me();
    Auth::logout();
    Session::instance()->destroy();
  }

  // メアドの使用とユーザー名の使用の確認
  private function get_registration_error_message($code) {
    $messages = [
      2 => 'このメールアドレスは既に使用されています。',
      3 => 'このユーザー名は既に使用されています。'
    ];

    return isset($messages[$code]) ? $messages[$code] : null;
  }

  //ログインユーザーの情報
  public function get_current_user() {
    if (!Auth::check()) {
      return null;
    }

    $user_id_array = Auth::get_user_id();
    if (!$user_id_array || !isset($user_id_array[1])) {
      return null;
    }

    $user_id = $user_id_array[1];

    return DB::select()
      ->from('users')
      ->where('id', $user_id)
      ->execute()
      ->current();
  }
}
