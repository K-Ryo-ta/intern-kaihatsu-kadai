<?php

// namespace Controller\Api\Mypage;

// use Fuel\Core\Input;
// use Fuel\Core\Validation;

// class Profile extends Base
// {
//   public function get_index()
//   {
//     try {
//       $user = $this->user_model->get_user_info($this->user_id);
//       $skills = $this->skill_model->get_user_skills_as_string($this->user_id);

//       return $this->response(array(
//         'status' => 'success',
//         'data' => array(
//           'username' => $user['username'],
//           'email' => $user['email'],
//           'bio' => $user['bio'],
//           'skills' => $skills
//         )
//       ));
//     } catch (\Exception $e) {
//       return $this->response(array('status' => 'error', 'message' => $e->getMessage()), 500);
//     }
//   }

//   public function post_index()
//   {
//     try {
//       $user = $this->user_model->get_user_info($this->user_id);
//       $input = Input::json() ?: Input::post();
//       $val = Validation::forge();
//       $val->add_field('email', 'メール', 'required|valid_email|max_length[255]');
//       $val->add_field('bio', '自己紹介', 'max_length[65535]');
//       $val->add_field('skills', 'スキル', 'max_length[1024]');

//       if (isset($input['new_password']) && !empty($input['new_password'])) {
//         $val->add_field('new_password', '新パスワード', 'min_length[8]');
//       }

//       if (!$val->run($input)) {
//         $errors = array();
//         foreach ($val->error() as $error) {
//           $errors[] = $error->get_message();
//         }
//         return $this->response(array('status' => 'error', 'errors' => $errors), 400);
//       }

//       $this->user_model->update_profile(
//         $user,
//         $input['email'],
//         $input['bio'] ?? '',
//         $input['new_password'] ?? null
//       );

//       if ($this->skill_model && isset($input['skills'])) {
//         $this->skill_model->update_user_skills($this->user_id, $input['skills']);
//       }

//       return $this->response(array('status' => 'success', 'message' => 'プロフィールを更新しました'));
//     } catch (\Exception $e) {
//       return $this->response(array('status' => 'error', 'message' => $e->getMessage()), 500);
//     }
//   }
// }
