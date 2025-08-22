<?php

use Fuel\Core\View;
use Auth\Auth;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Database_Exception;
use Fuel\Core\Validation;
use Fuel\Core\DB;

class Controller_Portfolios extends Controller_Base
{
  public function before()
  {
    parent::before();
  }

  public function action_detail($id = null)
  {
    $id = (int)$id;
    if ($id <= 0) {
      return Response::redirect('dashboard');
    }

    $p = DB::select(
      'id',
      'user_id',
      'title',
      'description',
      'project_url',
      'source_code_url',
      'screenshot_url',
      'created_at'
    )
      ->from('portfolios')
      ->where('id', $id)
      ->execute()
      ->current();
    if (!$p) {
      throw new \HttpNotFoundException;
    }

    $tags = DB::select(['t.name', 'name'])
      ->from(['portfolio_technologies', 'pt'])
      ->join(['tags', 't'], 'INNER')->on('pt.tag_id', '=', 't.id')
      ->where('pt.portfolio_id', $id)
      ->execute()
      ->as_array(null, 'name');

    $p['tags']        = $tags;
    $p['placeholder'] = '/assets/img/placeholder.png';

    $v = View::forge('portfolios/detail');
    $v->set('p', $p, false);
    $this->template->title   = '作品詳細';
    $this->template->content = $v;
  }

  public function action_create()
  {
    if (!Auth::check()) return Response::redirect('login');

    $errors = [];

    if (Input::method() === 'POST') {
      $val = Validation::forge();
      $val->add_field('title', 'タイトル', 'required|max_length[255]');
      $val->add_field('description', '概要', 'required');
      $val->add_field('project_url', '作品URL', 'valid_url');
      $val->add_field('source_code_url', 'ソースURL', 'valid_url');
      $val->add_field('tags', 'タグ', 'max_length[512]');

      if ($val->run()) {
        $uid  = Auth::get_user_id()[1];
        $tit  = trim((string)Input::post('title'));
        $desc = (string)Input::post('description');
        $purl = (string)Input::post('project_url') ?: null;
        $surl = (string)Input::post('source_code_url') ?: null;
        $tags_csv = (string)Input::post('tags');

        $screenshot_url = null;
        if (!empty($_FILES['screenshot']['name'])) {
          $screenshot_url = $this->handle_upload($_FILES['screenshot'], $errors);
        }

        if (empty($errors)) {
          try {
            DB::start_transaction();
            list($pid,) = DB::insert('portfolios')->set([
              'user_id'        => $uid,
              'title'          => $tit,
              'description'    => $desc,
              'project_url'    => $purl,
              'source_code_url' => $surl,
              'screenshot_url' => $screenshot_url,
            ])->execute();

            $tag_ids = $this->ensure_tags($tags_csv);
            $this->sync_portfolio_technologies($pid, $tag_ids);

            DB::commit_transaction();
            return Response::redirect('dashboard');
          } catch (Database_Exception $e) {
            DB::rollback_transaction();
            $errors[] = '保存に失敗しました（DB）: ' . $e->getMessage();
          }
        }
      } else {
        foreach ($val->error() as $e) $errors[] = $e->get_message();
      }
    }

    $v = View::forge('portfolios/create');
    $v->set('errors', $errors, false);
    $v->set('old', [
      'title' => Input::post('title', ''),
      'description' => Input::post('description', ''),
      'project_url' => Input::post('project_url', ''),
      'source_code_url' => Input::post('source_code_url', ''),
      'tags' => Input::post('tags', ''),
    ], false);
    $this->template->title   = '新規投稿';
    $this->template->content = $v;
  }

  public function action_edit($id = null)
  {

    if (!Auth::check()) return Response::redirect('login');

    $errors = [];
    $uid = Auth::get_user_id()[1];
    $id  = (int)$id;

    $row = DB::select()->from('portfolios')
      ->where('id', $id)->where('user_id', $uid)
      ->execute()->current();
    if (!$row) return Response::redirect('dashboard');

    $tags = DB::select(array('t.name', 'name'))
      ->from(array('portfolio_technologies', 'pt'))
      ->join(array('tags', 't'), 'INNER')->on('pt.tag_id', '=', 't.id')
      ->where('pt.portfolio_id', $id)
      ->execute()
      ->as_array(null, 'name');

    $tags_csv_default = implode(', ', $tags);

    if (Input::method() === 'POST') {
      $val = Validation::forge();
      $val->add_field('title', 'タイトル', 'required|max_length[255]');
      $val->add_field('description', '概要', 'required');
      $val->add_field('project_url', '作品URL', 'valid_url');
      $val->add_field('source_code_url', 'ソースURL', 'valid_url');
      $val->add_field('tags', 'タグ', 'max_length[512]');

      if ($val->run()) {
        $tit  = trim((string)Input::post('title'));
        $desc = (string)Input::post('description');
        $purl = (string)Input::post('project_url') ?: null;
        $surl = (string)Input::post('source_code_url') ?: null;
        $tags_csv = (string)Input::post('tags');

        $new_screenshot_url = $row['screenshot_url'];
        if (!empty($_FILES['screenshot']['name'])) {
          $new = $this->handle_upload($_FILES['screenshot'], $errors);
          if ($new && empty($errors)) {

            $this->delete_file_if_local($row['screenshot_url']);
            $new_screenshot_url = $new;
          }
        }

        if (empty($errors)) {
          try {
            DB::start_transaction();
            DB::update('portfolios')->set([
              'title'          => $tit,
              'description'    => $desc,
              'project_url'    => $purl,
              'source_code_url' => $surl,
              'screenshot_url' => $new_screenshot_url,
            ])->where('id', $id)->where('user_id', $uid)->execute();

            $tag_ids = $this->ensure_tags($tags_csv);
            $this->sync_portfolio_technologies($id, $tag_ids);

            DB::commit_transaction();
            return Response::redirect('dashboard');
          } catch (Database_Exception $e) {
            DB::rollback_transaction();
            $errors[] = '更新に失敗しました（DB）: ' . $e->getMessage();
          }
        }
      } else {
        foreach ($val->error() as $e) $errors[] = $e->get_message();
      }
    }

    $v = View::forge('portfolios/edit');
    $v->set('errors', $errors, false);
    $v->set('data', [
      'id' => $row['id'],
      'title' => Input::post('title', $row['title']),
      'description' => Input::post('description', $row['description']),
      'project_url' => Input::post('project_url', $row['project_url']),
      'source_code_url' => Input::post('source_code_url', $row['source_code_url']),
      'tags' => Input::post('tags', $tags_csv_default),
      'screenshot_url' => $row['screenshot_url'],
    ], false);
    $this->template->title   = '投稿を編集';
    $this->template->content = $v;
  }

  public function action_delete($id = null)
  {
    if (!Auth::check()) return Response::redirect('login');
    if (Input::method() !== 'POST') return Response::redirect('/');

    $uid = Auth::get_user_id()[1];
    $id  = (int)$id;

    $row = DB::select('screenshot_url')->from('portfolios')
      ->where('id', $id)->where('user_id', $uid)->execute()->current();
    if (!$row) return Response::redirect('mypage');

    try {
      DB::start_transaction();

      DB::delete('portfolios')
        ->where('id', $id)->where('user_id', $uid)
        ->execute();

      DB::commit_transaction();
    } catch (Database_Exception $e) {
      DB::rollback_transaction();
      return Response::redirect('mypage');
    }

    $this->delete_file_if_local($row['screenshot_url']);
    return Response::redirect('mypage');
  }


  // ここより下が難しいので自分の理解のためにコメントを残しておきます
  /** 画像アップロード（成功で相対URLを返す / 失敗で null&$errors追記） */
  private function handle_upload(array $fileInput, array &$errors): ?string
  {
    $dir = DOCROOT . 'uploads/portfolios';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);

    // FuelPHP Uploadクラスを使わず安全に最小実装にする例
    if (!is_uploaded_file($fileInput['tmp_name'])) {
      $errors[] = 'ファイルのアップロードに失敗しました。';
      return null;
    }

    // 拡張子チェック（最低限）
    $ext = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
      $errors[] = '許可されていない拡張子です。jpg/jpeg/png/webp のみ。';
      return null;
    }

    // サイズチェック（5MB）
    if ((int)$fileInput['size'] > 5 * 1024 * 1024) {
      $errors[] = 'ファイルサイズが大きすぎます（最大5MB）。';
      return null;
    }

    // ランダム名で保存
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . DS . $name;
    if (!@move_uploaded_file($fileInput['tmp_name'], $dest)) {
      $errors[] = 'ファイル保存に失敗しました。';
      return null;
    }

    // パーミッション（任意）
    @chmod($dest, 0644);

    // 公開用の相対URLを返す
    return '/uploads/portfolios/' . $name;
  }

  private function ensure_tags($csv)
  {
    // 1) CSV を配列へ（全角カンマにも対応）
    $parts = preg_split('/[,、]/u', (string)$csv);

    // 2) 前後空白を削って、空要素を除去（アロー関数は使わない）
    $names = array();
    foreach ($parts as $s) {
      $s = trim(str_replace('　', ' ', $s));   // 全角空白→半角
      $s = preg_replace('/\s+/', ' ', $s);     // 連続空白を1つに
      if ($s !== '') {
        $names[] = $s;
      }
    }

    if (empty($names)) {
      return array();
    }

    // 3) 既存タグを取得（IN 句はクエリビルダに任せる）
    $existing_rows = DB::select('id', 'name')
      ->from('tags')
      ->where('name', 'in', $names)
      ->execute()
      ->as_array();

    // name => id のマップを作る
    $existing = array();
    foreach ($existing_rows as $r) {
      $existing[$r['name']] = (int) $r['id'];
    }

    // 4) 無いものは作成して ID を集める
    $ids = array();
    foreach ($names as $n) {
      if (isset($existing[$n])) {
        $ids[] = $existing[$n];
      } else {
        list($tid,) = DB::insert('tags')->set(array('name' => $n))->execute();
        $ids[] = (int) $tid;
      }
    }

    // 5) 重複除去して返す
    return array_values(array_unique($ids));
  }

  /** 中間テーブルの差分同期（全入れ替え） */
  private function sync_portfolio_technologies($portfolio_id, array $tag_ids)
  {
    DB::delete('portfolio_technologies')->where('portfolio_id', $portfolio_id)->execute();
    if (empty($tag_ids)) return;

    foreach ($tag_ids as $tid) {
      DB::insert('portfolio_technologies')->set(array(
        'portfolio_id' => (int)$portfolio_id,
        'tag_id'       => (int)$tid,
      ))->execute();
    }
  }

  /** ローカル保存のファイルなら削除（s3等なら無視） */
  private function delete_file_if_local(?string $url): void
  {
    if (!$url) return;
    if (strpos($url, '/uploads/portfolios/') !== 0) return;
    $path = DOCROOT . ltrim($url, '/');
    if (is_file($path)) @unlink($path);
  }
}
