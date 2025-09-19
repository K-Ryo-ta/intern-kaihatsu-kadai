
<?php

// namespace Controller\Api\Mypage;

// use Fuel\Core\Input;
// use Auth\Auth;
// use Fuel\Core\Controller_Rest;

// class Base extends Controller_Rest
// {
//   protected $format = 'json';
//   protected $user_model;
//   protected $skill_model;
//   protected $portfolio_model;
//   protected $user_id;

//   public function before()
//   {
//     parent::before();

//     // CORS設定
//     $this->response->set_header('Access-Control-Allow-Origin', '*');
//     $this->response->set_header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
//     $this->response->set_header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');

//     // OPTIONSリクエストの処理（プリフライト）
//     if (Input::method() === 'OPTIONS') {
//       $this->response->status = 200;
//       return $this->response;
//     }

//     // 認証チェック
//     if (!Auth::check()) {
//       $this->response(array(
//         'status' => 'error',
//         'message' => 'Unauthorized'
//       ), 401);
//       return $this->response;
//     }

//     // ログイン中のuser_idを取得
//     $this->user_id = Auth::get_user_id()[1];

//     // 共通モデルのインスタンス化
//     $this->user_model = new \Model_User();
//     $this->skill_model = new \Model_Skill();
//     $this->portfolio_model = new \Model_Portfolio();
//   }
// }
