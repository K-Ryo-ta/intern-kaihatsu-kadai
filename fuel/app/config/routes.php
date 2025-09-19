<?php
return [
  '_root_' => 'dashboard/index',
  'register' => 'auth/register',
  'login' => 'auth/login',
  'logout' => 'auth/logout',
  'portfolios/create' => 'portfolios/create',
  'portfolios/detail/(:num)' => 'portfolios/detail/$1',
  'mypage' => 'mypage/index',

  // API
  'api/dashboard/items'      => 'api/dashboard/items',
  'api/dashboard/items.json' => 'api/dashboard/items',

  'api/mypage/profile' => 'api/mypage/profile/index',
  'api/mypage/portfolio' => 'api/mypage/portfolio/index',
  'api/mypage/portfolio/(:num)' => 'api/mypage/portfolio/index/$1',


  'api/portfolio'           => 'api/portfolio/index',
  'api/portfolio/detail/(:num)' => 'api/portfolio/detail/$1',
  'api/portfolio/(:num)'    => 'api/portfolio/index/$1',

  'api/profile'           => 'api/profile/index',
];
