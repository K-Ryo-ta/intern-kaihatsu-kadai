<?php

use Fuel\Core\Input;
use Fuel\Core\DB;

class Controller_Api_Search extends Controller_Api_Base {
  protected $format = 'json';

  public function before() {
    parent::before();
    $this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
  }

  public function get_index() {
    $q        = Input::get('q', '');
    $tags_csv = Input::get('tags', '');

    $query = DB::select('p.id', 'p.user_id', 'p.title', 'p.screenshot_url', 'p.created_at')
      ->from(['portfolios', 'p']);

    if ($q !== '') {
      $like = '%' . $q . '%';
      $query->where_open()
        ->where('p.title', 'like', $like)
        ->or_where('p.description', 'like', $like)
        ->where_close();
    }

    $tag_names = $this->parse_tags_csv($tags_csv);
    if (!empty($tag_names)) {
      $query->join(['portfolio_technologies', 'pt'], 'INNER')->on('pt.portfolio_id', '=', 'p.id')
        ->join(['tags', 't'], 'INNER')->on('t.id', '=', 'pt.tag_id')
        ->where('t.name', 'in', $tag_names)
        ->group_by('p.id');
    }

    $rows = $query->order_by('p.created_at', 'DESC')
      ->execute()
      ->as_array();

    return $this->response(['status' => 'success', 'data' => $rows]);
  }

  private function parse_tags_csv($csv) {
    if ($csv === null || $csv === '') return [];
    $parts = preg_split('/[,、]/u', (string)$csv);
    $names = [];
    foreach ($parts as $s) {
      $s = str_replace('　', ' ', $s);
      $s = preg_replace('/\s+/', ' ', $s);
      $s = trim($s);
      if ($s !== '') $names[] = $s;
    }
    return array_values(array_unique($names));
  }
}
