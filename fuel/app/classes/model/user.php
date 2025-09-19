<?php

use Fuel\Core\DB;
use Auth\Auth;
use Auth\SimpleUserUpdateException;

class Model_User extends \Fuel\Core\Model {
  /**
   * ユーザー情報を取得
   */
  public function get_user_info($user_id) {
    $result = DB::select('id', 'username', 'email', 'profile_fields')
      ->from('users')
      ->where('id', $user_id)
      ->execute()
      ->current();

    $profile = $this->unserialize_profile($result['profile_fields']);
    $bio = isset($profile['bio']) ? $profile['bio'] : '';

    return [
      'id' => $result['id'],
      'username' => $result['username'],
      'email' => $result['email'],
      'bio' => $bio,
      'profile' => $profile
    ];
  }

  /**
   * プロフィール情報を更新
   */
  public function update_profile($user, $email, $bio, $new_password, $old_password) {
    try {
      // プロフィールフィールドの更新
      $profile = $user['profile'];
      $profile['bio'] = $bio;

      // 更新データの準備
      $update_data = [
        'email' => $email,
        'profile_fields' => serialize($profile)
      ];

      // パスワード変更がある場合
      if (!empty($new_password)) {
        if (empty($old_password)) {
          throw new \Exception('パスワードを変更するには現在のパスワードが必要です');
        }
        $update_data['password'] = $new_password;
        $update_data['old_password'] = $old_password;
      }

      $result = Auth::update_user($update_data, $user['username']);

      return $result;
    } catch (SimpleUserUpdateException $e) {
      throw new \Exception('ユーザー更新エラー: ' . $e->getMessage());
    } catch (\Exception $e) {
      throw $e;
    }
  }

  //SimpleAuth が シリアライズされた文字列として users テーブルに保存するので、配列に戻す必要がある
  /**
   * プロフィールフィールドを配列に変換
   */
  private function unserialize_profile($profile_fields) {
    if (empty($profile_fields)) {
      return [];
    }

    $profile = @unserialize($profile_fields);
    return is_array($profile) ? $profile : [];
  }
}
