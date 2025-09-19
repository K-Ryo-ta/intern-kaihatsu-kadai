<?php

use Fuel\Core\DB;
use Fuel\Core\Database_Exception;

class Model_Skill extends \Fuel\Core\Model
{
  public function get_user_skill_names($user_id)
  {
    return DB::select(['t.name', 'name'])
      ->from(['user_skills', 'us'])
      ->join(['tags', 't'], 'INNER')->on('us.tag_id', '=', 't.id')
      ->where('us.user_id', (int)$user_id)
      ->order_by('t.name', 'ASC')
      ->execute()
      ->as_array(null, 'name');
  }
}
