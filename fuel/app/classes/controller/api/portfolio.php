<?php

use Fuel\Core\Validation;
use Fuel\Core\Upload;
use Fuel\Core\Input;
use Auth\Auth;

/**
 * @property \Fuel\Core\Response $response
 */
class Controller_Api_Portfolio extends Controller_Api_Base {
  protected $format = 'json';
  protected $portfolio_model;

  public function before() {
    parent::before();
    $this->portfolio_model = new \Model_Portfolio();
    $this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
  }

  public function get_detail($id = null) {
    $id = (int)$id;
    if ($id <= 0) return $this->response(['status' => 'error', 'message' => 'bad id'], 400);

    $p = $this->portfolio_model->find_one($id);
    if (!$p) return $this->response(['status' => 'error', 'message' => 'not found'], 404);

    $p['tags'] = $this->portfolio_model->find_tags($id);
    return $this->response(['status' => 'success', 'data' => $p]);
  }

  // ポートフォリオ作成
  //ユーザーの認証を確認する必要あり
  public function post_index() {
    if (!Auth::check()) {
      return $this->response(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    $input = Input::post();

    // バリデーション
    $val = $this->makePortfolioValidation();
    if (!$val->run($input)) {
      $errors = array_map(function ($e) {
        return $e->get_message();
      }, $val->error());
      return $this->response(['status' => 'error', 'errors' => $errors], 400);
    }

    // 画像アップロード処理（シンプル版）
    $screenshot_url = $this->upload_image();
    if ($screenshot_url === false) {
      return $this->response([
        'status' => 'error',
        'message' => 'Image upload failed'
      ], 400);
    }

    $data = [
      'title'           => trim($input['title']),
      'description'     => $input['description'],
      'project_url'     => $input['project_url'] ?? null,
      'source_code_url' => $input['source_code_url'] ?? null,
      'screenshot_url'  => $screenshot_url,
      'tags' => $input['tags'] ?? '',
    ];

    try {
      $uid = Auth::get_user_id()[1];
      $pid = $this->portfolio_model->create($uid, $data);
      $created = $this->portfolio_model->find_one($pid);
      return $this->response(['status' => 'success', 'data' => $created], 201);
    } catch (\Exception $e) {
      return $this->response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  // 更新（要ログイン）
  public function post_update($id = null) {
    if (!Auth::check()) {
      return $this->response(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }

    $id  = (int)$id;
    $uid = Auth::get_user_id()[1];

    $row = $this->portfolio_model->find_one($id);
    if (!$row || (int)$row['user_id'] !== (int)$uid) {
      return $this->response(['status' => 'error', 'message' => 'not found or forbidden'], 404);
    }

    $input = Input::post();

    $val = $this->makePortfolioValidation();

    if (!$val->run($input)) {
      $errs = [];
      foreach ($val->error() as $e) $errs[] = $e->get_message();
      return $this->response(['status' => 'error', 'errors' => $errs], 400);
    }

    $screenshot_url = $this->upload_image();

    if ($screenshot_url === false) {
      return $this->response([
        'status' => 'error',
        'message' => 'Image upload failed'
      ], 400);
    }

    // 画像がアップロードされた場合は新しいURL、されなかった場合は古いURLを使用
    $final_screenshot_url = $screenshot_url ?: $row['screenshot_url'];

    // 古い画像を削除（新しい画像がアップロードされた場合のみ）
    if ($screenshot_url && $row['screenshot_url']) {
      $this->delete_old_image($row['screenshot_url']);
    }

    $data = [
      'title'           => trim((string)$input['title']),
      'description'     => (string)$input['description'],
      'project_url'     => (string)($input['project_url'] ?? null),
      'source_code_url' => (string)($input['source_code_url'] ?? null),
      'screenshot_url'  => $final_screenshot_url,
      'tags'            => $input['tags'] ?? '',
    ];

    try {
      $this->portfolio_model->update($id, $uid, $data);
      $updated = $this->portfolio_model->find_one($id);
      return $this->response(['status' => 'success', 'data' => $updated]);
    } catch (\Exception $e) {
      return $this->response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  // 削除（要ログイン）
  public function delete_index($id = null) {
    if (!Auth::check()) return $this->response(['status' => 'error', 'message' => 'Unauthorized'], 401);
    $id  = (int)$id;
    $uid = Auth::get_user_id()[1];

    $row = $this->portfolio_model->find_one($id);
    if (!$row || (int)$row['user_id'] !== (int)$uid) {
      return $this->response(['status' => 'error', 'message' => 'not found or forbidden'], 404);
    }

    try {
      $this->portfolio_model->delete_portfolio($id, $uid);
      $this->portfolio_model->refresh_user_skills($uid);
      return $this->response(['status' => 'success']);
    } catch (\Exception $e) {
      return $this->response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }

  private function upload_image() {
    if (empty($_FILES['screenshot']['name'])) {
      return null;
    }

    Upload::process([
      'path'          => DOCROOT . 'uploads/portfolios',
      'randomize'     => true,
      'ext_whitelist' => ['jpg', 'jpeg', 'png', 'webp'],
      'max_size'      => 5242880, // 5MB
    ]);

    if (Upload::is_valid()) {
      Upload::save();
      $file = Upload::get_files()[0] ?? null;
      return $file ? '/uploads/portfolios/' . $file['saved_as'] : null;
    }

    return false;
  }

  //パス名未チェック・ディレクトリ・トラバーサルに対応した。
  private function delete_old_image($image_url) {
    if (!$image_url) return;

    $base_directory = realpath(DOCROOT . 'uploads/portfolios');

    if ($base_directory === false)  return;

    $base_directory_os = $base_directory . DIRECTORY_SEPARATOR;

    $real = realpath(DOCROOT . ltrim($image_url, '/'));

    // 画像が保存されているディレクトリ外の場合は削除しない
    if ($real === false || strpos($real, $base_directory_os) !== 0) return;

    // ファイルが存在する場合に削除する
    if (file_exists($real) && is_file($real)) {
      @unlink($real);
    }
  }

  private function makePortfolioValidation(): Validation {
    $val = Validation::forge();
    $val->add_field('title', 'タイトル', 'required|max_length[255]');
    $val->add_field('description', '概要', 'required');
    $val->add_field('project_url', '作品URL', 'valid_url');
    $val->add_field('source_code_url', 'ソースURL', 'valid_url');
    $val->add_field('tags', 'タグ', 'max_length[512]');
    return $val;
  }
}
