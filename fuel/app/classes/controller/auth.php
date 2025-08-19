<?php

class Controller_Auth extends Controller_Base
{
  public function action_register()
  {
    $errors = [];

    if (\Input::method() === 'POST') {
      $val = \Validation::forge();
      $val->add_field('username', 'ユーザー名', 'required|min_length[3]|max_length[255]');
      $val->add_field('email',    'メールアドレス', 'required|valid_email|max_length[255]');
      $val->add_field('password', 'パスワード', 'required|min_length[8]');
      $val->add_field('bio',      '自己紹介', 'max_length[65535]');

      if ($val->run()) {
        $username = trim((string)\Input::post('username'));
        $email    = trim((string)\Input::post('email'));
        $password = (string)\Input::post('password');
        $bio      = \Input::post('bio') ?: null;

        try {
          $group = \Config::get('application.user.default_group', 1);
          \Auth::create_user($username, $password, $email, $group, ['bio' => $bio]);
          return \Response::redirect('login');
        } catch (\SimpleUserUpdateException $e) {
          if ($e->getCode() == 2)      $errors[] = 'このメールアドレスは既に使用されています。';
          elseif ($e->getCode() == 3)  $errors[] = 'このユーザー名は既に使用されています。';
          else                         $errors[] = $e->getMessage();
        }
      } else {
        foreach ($val->error() as $e) $errors[] = $e->get_message();
      }
      $v = \View::forge('register/index');
      $v->set('errors', $errors, false);
      $v->set('old', [
        'username' => \Input::post('username', ''),
        'email'    => \Input::post('email', ''),
        'bio'      => \Input::post('bio', ''),
      ], false);
      $this->template->title   = 'Register';
      $this->template->content = $v;
      return;
    }
    $v = \View::forge('register/index');
    $v->set('errors', [], false);
    $v->set('old', ['username' => '', 'email' => '', 'bio' => ''], false);
    $this->template->title   = 'Register';
    $this->template->content = $v;
  }

  public function action_login()
  {
    $errors = [];

    if (\Auth::check()) {
      \Messages::info(__('login.already-logged-in'));
      \Response::redirect_back('dashboard');
    }

    if (\Input::method() === 'POST') {
      if (\Auth::instance()->login(\Input::param('username'), \Input::param('password'))) {
        if (\Input::param('remember', false)) {
          \Auth::remember_me();
        } else {
          \Auth::dont_remember_me();
        }
        \Response::redirect_back('dashboard');
      } else {
        $errors[] = 'ユーザー名またはパスワードが違います。';
      }

      $v = \View::forge('login/index');
      $v->set('errors', $errors, false);
      $v->set('old', ['username' => \Input::post('username', '')], false);
      $this->template->title   = 'Login';
      $this->template->content = $v;
      return;
    }
    $v = \View::forge('login/index');
    $v->set('errors', [], false);
    $v->set('old', ['username' => ''], false);
    $this->template->title   = 'Login';
    $this->template->content = $v;
  }


  public function action_logout()
  {
    \Auth::dont_remember_me();
    \Auth::logout();
    \Messages::success(__('login.logged-out'));
    \Response::redirect_back();
  }
}
