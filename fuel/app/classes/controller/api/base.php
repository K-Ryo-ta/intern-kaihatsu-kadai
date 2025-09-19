<?php

use Fuel\Core\Input;
use Fuel\Core\Security;
use Fuel\Core\Config;

/**
 * @property \Fuel\Core\Response $response
 */
class Controller_Api_Base extends \Fuel\Core\Controller_Rest {
  public function before() {
    parent::before();

    $method = Input::method();
    if (in_array($method, ['POST', 'DELETE'], true)) {
      $token = Input::headers('X-CSRF-Token');

      if (!$token) {
        $key   = Config::get('security.csrf_token_key', 'fuel_csrf_token');
        $token = Input::post($key);
      }

      if (!Security::check_token($token)) {
        return $this->response(['status' => 'error', 'message' => 'Invalid CSRF token'], 403);
      }
    }
  }

  //beforeにつけるとresponseが作成される前に適応されるため、afterでする。
  public function after($response) {
    $response = parent::after($response);

    // クリックジャッキング対策ヘッダ
    $response->set_header('X-Frame-Options', 'SAMEORIGIN', true);
    $response->set_header('Content-Security-Policy', "frame-ancestors 'self'", true);

    return $response;
  }
}
