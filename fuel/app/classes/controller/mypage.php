<?php

use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\View;
use Auth\Auth;
use Fuel\Core\DB;
use Fuel\Core\Validation;
use Fuel\Core\Database_Exception;

class Controller_Mypage extends Controller_Base
{
  public function before()
  {
    parent::before();

    if (!Auth::check()) {
      return Response::redirect('login');
    }
  }

  public function action_index()
  {
    $user_id = Auth::get_user_id()[1];
    $user = $this->get_user_info($user_id);
    $skills = $this->get_user_skills($user_id);
    $portfolios = $this->get_user_portfolios($user_id);

    // メッセージ用の配列
    $errors = [];
    $messages = [];

    if ($this->is_profile_update()) {
      $result = $this->update_profile($user, $user_id);
      $errors = $result['errors'];
      $messages = $result['messages'];

      if (empty($errors) && !empty($messages)) {
        return Response::redirect('mypage');
      }
    }

    $this->display_view($user, $skills, $portfolios, $messages, $errors);
  }

  private function get_user_info($user_id)
  {
    $result = DB::select('id', 'username', 'email', 'profile_fields')
      ->from('users')
      ->where('id', $user_id)
      ->execute()
      ->current();

    $profile = [];
    if (!empty($result['profile_fields'])) {
      $temp = @unserialize($result['profile_fields']);
      if (is_array($temp)) {
        $profile = $temp;
      }
    }

    $bio = isset($profile['bio']) ? $profile['bio'] : '';

    return [
      'id' => $result['id'],
      'username' => $result['username'],
      'email' => $result['email'],
      'bio' => $bio,
      'profile' => $profile
    ];
  }

  private function get_user_skills($user_id)
  {
    $skills = DB::select(['tags.name', 'tag_name'])
      ->from('user_skills')
      ->join('tags', 'INNER')
      ->on('user_skills.tag_id', '=', 'tags.id')
      ->where('user_skills.user_id', $user_id)
      ->execute()
      ->as_array();

    $skill_names = [];
    foreach ($skills as $skill) {
      $skill_names[] = $skill['tag_name'];
    }

    return implode(', ', $skill_names);
  }

  private function get_user_portfolios($user_id)
  {
    return DB::select('id', 'title', 'screenshot_url', 'created_at')
      ->from('portfolios')
      ->where('user_id', $user_id)
      ->order_by('created_at', 'DESC')
      ->execute()
      ->as_array();
  }

  private function is_profile_update()
  {
    return Input::method() === 'POST' &&
      Input::post('form') === 'profile';
  }

  private function update_profile($user, $user_id)
  {
    $errors = [];
    $messages = [];

    $validation = $this->validate_input();

    if ($validation->run()) {
      $email = Input::post('email');
      $bio = Input::post('bio');
      $skills = Input::post('skills');
      $new_password = Input::post('new_password');

      try {
        $this->update_user_info($user, $email, $bio, $new_password);
        $messages[] = 'プロフィールを更新しました。';

        $this->update_user_skills($user_id, $skills);
        $messages[] = 'スキルを更新しました。';
      } catch (Exception $e) {
        $errors[] = 'エラーが発生しました: ' . $e->getMessage();
      }
    } else {
      foreach ($validation->error() as $error) {
        $errors[] = $error->get_message();
      }
    }

    return [
      'errors' => $errors,
      'messages' => $messages
    ];
  }

  private function validate_input()
  {
    $val = Validation::forge();

    $val->add_field('email', 'メール', 'required|valid_email|max_length[255]');

    $val->add_field('bio', '自己紹介', 'max_length[65535]');

    $val->add_field('skills', 'スキル', 'max_length[1024]');

    $val->add_field('new_password', '新パスワード', 'min_length[8]');

    $val->add_field('new_password_confirm', '確認', 'match_field[new_password]');

    return $val;
  }

  private function update_user_info($user, $email, $bio, $new_password)
  {
    $profile = $user['profile'];
    $profile['bio'] = $bio;

    $update_data = [
      'email' => $email,
      'profile_fields' => $profile
    ];

    if (!empty($new_password)) {
      $update_data['password'] = $new_password;
    }

    Auth::update_user($update_data, $user['username']);
  }

  private function update_user_skills($user_id, $skills_csv)
  {
    $skill_names = $this->parse_skills_csv($skills_csv);

    $tag_ids = $this->get_or_create_tags($skill_names);

    $this->save_user_skills($user_id, $tag_ids);
  }

  private function parse_skills_csv($csv)
  {
    $parts = preg_split('/[,、]/u', $csv);

    $names = [];
    foreach ($parts as $part) {
      $name = trim($part);

      $name = str_replace('　', ' ', $name);
      $name = preg_replace('/\s+/', ' ', $name);

      if (!empty($name)) {
        $names[] = $name;
      }
    }

    return $names;
  }

  private function get_or_create_tags($tag_names)
  {
    if (empty($tag_names)) {
      return [];
    }

    $tag_ids = [];

    foreach ($tag_names as $name) {
      $existing = DB::select('id')
        ->from('tags')
        ->where('name', $name)
        ->execute()
        ->current();

      if ($existing) {
        $tag_ids[] = $existing['id'];
      } else {
        try {
          list($new_id,) = DB::insert('tags')
            ->set(['name' => $name])
            ->execute();
          $tag_ids[] = $new_id;
        } catch (Database_Exception $e) {
          // 同時に作成された場合は再度検索
          $existing = DB::select('id')
            ->from('tags')
            ->where('name', $name)
            ->execute()
            ->current();
          if ($existing) {
            $tag_ids[] = $existing['id'];
          }
        }
      }
    }

    return array_unique($tag_ids);
  }


  private function save_user_skills($user_id, $tag_ids)
  {
    DB::delete('user_skills')
      ->where('user_id', $user_id)
      ->execute();

    foreach ($tag_ids as $tag_id) {
      DB::insert('user_skills')
        ->set([
          'user_id' => $user_id,
          'tag_id' => $tag_id
        ])
        ->execute();
    }
  }

  private function display_view($user, $skills, $portfolios, $messages, $errors)
  {
    $view = View::forge('mypage/index');

    $view->set('user', [
      'username' => $user['username'],
      'email' => $user['email'],
      'bio' => $user['bio'],
      'skills' => $skills
    ], false);

    $view->set('list', $portfolios, false);
    $view->set('messages', $messages, false);
    $view->set('errors', $errors, false);
    $view->set('placeholder', '/assets/img/placeholder.png', false);

    $this->template->title = 'マイページ';
    $this->template->content = $view;
  }
}
