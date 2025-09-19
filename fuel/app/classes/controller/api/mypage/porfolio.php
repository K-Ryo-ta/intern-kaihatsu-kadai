<?php

// namespace Controller\Api\Mypage;

// class Portfolio extends Base
// {
//   public function get_index($id = null)
//   {
//     try {
//       if ($id) {
//         $portfolio = $this->portfolio_model->get_portfolio($id, $this->user_id);

//         if (!$portfolio) {
//           return $this->response(array(
//             'status' => 'error',
//             'message' => 'Portfolio not found'
//           ), 404);
//         }

//         return $this->response(array(
//           'status' => 'success',
//           'data' => $portfolio
//         ));
//       } else {
//         $portfolios = $this->portfolio_model->get_user_portfolios($this->user_id);

//         return $this->response(array(
//           'status' => 'success',
//           'data' => $portfolios
//         ));
//       }
//     } catch (\Exception $e) {
//       return $this->response(array(
//         'status' => 'error',
//         'message' => $e->getMessage()
//       ), 500);
//     }
//   }
// }
