<?php

use Fuel\Core\DB;

class Model_Portfolio extends \Fuel\Core\Model {
  /**
   * ユーザーのポートフォリオを取得
   */
  public function get_user_portfolios($user_id) {
    return DB::select('id', 'title', 'screenshot_url', 'created_at')
      ->from('portfolios')
      ->where('user_id', $user_id)
      ->order_by('created_at', 'DESC')
      ->execute()
      ->as_array();
  }

  public function get_all_portfolios() {
    return DB::select('id', 'user_id', 'title', 'screenshot_url', 'created_at')
      ->from('portfolios')
      ->order_by('created_at', 'DESC')
      ->execute()
      ->as_array();
  }

  public function find_one($id) {
    return DB::select('id', 'user_id', 'title', 'description', 'project_url', 'source_code_url', 'screenshot_url', 'created_at')
      ->from('portfolios')
      ->where('id', (int)$id)
      ->execute()
      ->current();
  }

  public function find_tags($portfolio_id) {
    return DB::select(array('t.name', 'name'))
      ->from(array('portfolio_technologies', 'pt'))
      ->join(array('tags', 't'), 'INNER')->on('pt.tag_id', '=', 't.id')
      ->where('pt.portfolio_id', (int)$portfolio_id)
      ->execute()
      ->as_array(null, 'name');
  }

  public function create($user_id, array $data) {
    DB::start_transaction();
    try {
      $tag_names = $this->parse_tags($data['tags'] ?? '');

      $tag_ids = $this->get_or_create_tag_ids($tag_names);

      $now = time();

      list($pid,) = DB::insert('portfolios')->set(array(
        'user_id'         => (int)$user_id,
        'title'           => (string)$data['title'],
        'description'     => (string)$data['description'],
        'project_url'     => $data['project_url'] ?? null,
        'source_code_url' => $data['source_code_url'] ?? null,
        'screenshot_url'  => $data['screenshot_url'] ?? null,
        'created_at' => $now,
        'updated_at' => $now,
      ))->execute();
      $pid = (int)$pid;

      $this->attach_tags_to_portfolio($pid, $tag_ids);

      $this->refresh_user_skills($user_id);

      DB::commit_transaction();
      return $pid;
    } catch (\Exception $e) {
      DB::rollback_transaction();
      throw $e;
    }
  }

  public function update($id, $user_id, array $data) {
    DB::start_transaction();

    try {
      $tag_names = $this->parse_tags($data['tags'] ?? '');

      $tag_ids   = $this->get_or_create_tag_ids($tag_names);

      $now = time();

      DB::update('portfolios')->set(array(
        'title'           => (string)$data['title'],
        'description'     => (string)$data['description'],
        'project_url'     => $data['project_url'] ?? null,
        'source_code_url' => $data['source_code_url'] ?? null,
        'screenshot_url'  => $data['screenshot_url'] ?? null,
        'updated_at'      => $now,
      ))
        ->where('id', (int)$id)
        ->where('user_id', (int)$user_id)
        ->execute();

      $this->attach_tags_to_portfolio($id, $tag_ids);

      $this->refresh_user_skills($user_id);

      DB::commit_transaction();
    } catch (\Exception $e) {
      DB::rollback_transaction();
      throw $e;
    }
  }

  public function delete_portfolio($id, $user_id) {
    DB::start_transaction();
    try {
      DB::delete('portfolios')
        ->where('id', (int)$id)
        ->where('user_id', (int)$user_id)
        ->execute();
      DB::commit_transaction();
    } catch (\Exception $e) {
      DB::rollback_transaction();
      throw $e;
    }
  }

  private function parse_tags($tag) {
    if ($tag === null || $tag === '') return array();
    $parts = preg_split('/[,、]/u', (string)$tag);
    $names = array();
    foreach ($parts as $part) {
      $part = str_replace('　', ' ', $part);         // 全角スペースから半角にする処理
      $part = preg_replace('/\s+/', ' ', $part);     // スペース畳み込み処理
      $part = trim($part);
      if ($part !== '') $names[] = $part;
    }
    // 重複を除去する
    return array_values(array_unique($names));
  }

  private function get_or_create_tag_ids(array $tag_names) {
    if (empty($tag_names)) return array();

    $existing_rows = DB::select('id', 'name')
      ->from('tags')
      ->where('name', 'in', $tag_names)
      ->execute()
      ->as_array();

    $existing = array();
    foreach ($existing_rows as $rows) {
      $existing[$rows['name']] = (int)$rows['id'];
    }

    $ids = array();
    foreach ($tag_names as $name) {
      if (isset($existing[$name])) {
        $ids[] = $existing[$name];
        continue;
      }
      try {
        list($tag_id,) = DB::insert('tags')->set(array('name' => $name))->execute();
        $ids[] = (int)$tag_id;
      } catch (\Database_Exception $e) {
        $row = DB::select('id')->from('tags')->where('name', $name)->execute()->current();
        if ($row) $ids[] = (int)$row['id'];
      }
    }
    return array_values(array_unique($ids));
  }

  private function attach_tags_to_portfolio($portfolio_id, array $tag_ids) {
    DB::delete('portfolio_technologies')
      ->where('portfolio_id', (int)$portfolio_id)
      ->execute();

    if (empty($tag_ids)) return;

    foreach ($tag_ids as $tid) {
      DB::insert('portfolio_technologies')->set(array(
        'portfolio_id' => (int)$portfolio_id,
        'tag_id'       => (int)$tid,
      ))->execute();
    }
  }

  public function get_user_tag_ids($user_id) {
    $rows = DB::select(DB::expr('DISTINCT pt.tag_id AS tag_id'))
      ->from(array('portfolios', 'p'))
      ->join(array('portfolio_technologies', 'pt'), 'INNER')->on('pt.portfolio_id', '=', 'p.id')
      ->where('p.user_id', (int)$user_id)
      ->execute()
      ->as_array();
    $ids = array();
    foreach ($rows as $r) $ids[] = (int)$r['tag_id'];
    return $ids;
  }

  public function refresh_user_skills($user_id) {
    DB::delete('user_skills')->where('user_id', (int)$user_id)->execute();
    $tag_ids = $this->get_user_tag_ids($user_id);
    foreach ($tag_ids as $tid) {
      DB::insert('user_skills')->set(array('user_id' => (int)$user_id, 'tag_id' => (int)$tid))->execute();
    }
  }
}
