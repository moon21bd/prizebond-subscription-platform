<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Api extends REST_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('subscription/api_model');
        $this->load->model('subscription/global_model');
        $this->load->model('subscription/subscription_model');
        $this->load->library('bitbirds');
        $this->load->library('muthofun');
        $this->load->library('form_validation');
    }

    public function redeemCoupon_post() {

        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('coupon_code', 'coupon code', 'trim|required|min_length[7]');
        $this->form_validation->set_rules('email', 'email', 'trim');
        $this->form_validation->set_rules('password', 'password', 'min_length[6]|max_length[20]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|min_length[13]|alpha_numeric');

        if ($this->form_validation->run()) {

            if (!$this->api_model->doesMatchApiKey($this->post('api_key'))) {
                $output['response']['error'] = 'api key not matched';
                $this->response($output);
            }

            $userId = FALSE;

            if ($this->post('access_token')) {
                $userId = $this->userAuthCheckByAccessToken($this->post('access_token'));
            } else {
                $userId = $this->api_model->userAuthCheck($this->post('email'), $this->post('password'));
            }

            $this->db->trans_start();

            if (!$userId) {
                $output['response']['error'] = $this->config->item('authentication_faild');
                $this->response($output);
            }

            $couponCode = $this->input->post('coupon_code');

            if (strlen($couponCode) < 7) {
                $this->invalidCouponTracker($userId);
            } else if (strpos($couponCode, '-') == FALSE) {

                $this->invalidCouponTracker($userId);
            } else {
                $couponArray = explode('-', $couponCode);
                $couponSeriesId = $this->api_model->getCouponSeriesIdByAppIdAndCouponSeries(trim($couponArray[0]));

                if (!$couponSeriesId) {
                    $this->invalidCouponTracker($userId);
                }

                $couponInfo = $this->global_model->get_data('subscription_coupons', array('coupon' => trim($couponArray[1]), 'series_id' => $couponSeriesId, 'used_by_user_id' => $userId));


                if (!$couponInfo) {
                    $this->invalidCouponTracker($userId);
                }


                if ($couponInfo['status'] == 'not used') {
                    $orderInfo = $this->global_model->get_data('subscription_order_list', array('reference_id' => $couponInfo['order_id']));

                    //'pending','failed','completed','confirmed','shipped','delivered','wrong_shipping_address','delivery_failed','waiting_for_confirmed','cancel','coupon_generated'
                    if ($orderInfo['order_status'] != 'pending' && ($orderInfo['order_status'] != 'failed')) {

                        $subscriptionInfoExist = FALSE;
                        $subscriptionInfoExist = $this->global_model->get_data('subscription_product_purchase_list', array('order_reference_id' => $orderInfo['reference_id'], 'user_id' => $userId, 'product_id' => $orderInfo['product_id']));
                        if ($subscriptionInfoExist) { // User already purchased with that order id
                            $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
                            $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];

                            //Service delivery failed;
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": Service delivery failed" . "</br> data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'User already purchased service with that order id');
                            $this->response($response, 200);
                        }



                        $paymentMethodInfo = $this->global_model->get_data('subscription_payment_method_types', array('id' => $orderInfo['payment_method_type_id']));

                        $this->api_model->update('subscription_coupons', array('status' => 'used'), array('id' => $couponInfo['id']));
                        $this->api_model->update('subscription_order_list', array('payment_status' => 'success', 'trx_type' => 'sale', 'payment_date_time' => date("Y-m-d H:i:s")), array('id' => $orderInfo['id']));

                        $paymentHistory = array(
                            'order_id' => $couponInfo['order_id'],
                            'payment_method_type_id' => $orderInfo['payment_method_type_id'],
                            'extra_info' => $couponInfo['note'],
                            'created_date_time' => date("Y-m-d H:i:s")
                        );
                        $couponRedeemedHistoryData = array(
                            'coupon_id' => $couponInfo['id'],
                            'order_id' => $couponInfo['order_id'], //not primary id
                            'coupon_code' => $couponCode,
                            'user_id' => $couponInfo['used_by_user_id'],
                            'product_id' => $orderInfo['product_id'],
                            'created_date_time' => date('Y-m-d H:i:s')
                        );

                        $this->global_model->insert('subscription_coupon_redeemed_history', $couponRedeemedHistoryData);
                        $this->global_model->insert('subscription_payment_history', $paymentHistory);


                        if ($this->deliverServiceToCustomer($orderInfo['product_id'], $userId, $paymentMethodInfo['name'], $couponInfo['order_id']) == TRUE) {


                            $subscriptionInfo = $this->global_model->get_data('subscription_product_purchase_list', array('order_reference_id' => $orderInfo['reference_id'], 'user_id' => $userId, 'product_id' => $orderInfo['product_id']));
                            $productInfo = $this->global_model->get_data('subscription_product_list', array('id' => $orderInfo['product_id']));
                            $subscriptionInfo2 = $this->api_model->getAUserSubscriptionInfo($userId);


                            $startDate = $this->convertEnglishDateTimeToBanglaDateTime(date("d F Y h:i a", strtotime($subscriptionInfo['valid_start_datetime'])));
                            $endDate = $this->convertEnglishDateTimeToBanglaDateTime(date("d F Y h:i a", strtotime($subscriptionInfo['valid_end_datetime'])));

                            $serviceInfo = array(
                                'product_name' => $productInfo['name'],
                                'start_date' => $startDate,
                                'end_date' => $endDate
                            );
                            $this->db->trans_complete();

                            if ($this->db->trans_status() == TRUE) {

                                $response['response']['success'] = $this->config->item('COUPON_REDEEM_SUCCESS_01')['success'];
                                $response['response']['code'] = $this->config->item('COUPON_REDEEM_SUCCESS_01')['code'];
                                $response['response']['data'] = $serviceInfo;
                                $response['response']['subscription'] = $subscriptionInfo2;

                                $this->logActivity('info', ": Subscription purchased successfully by COD</br>Source -> " . current_url());
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Subscription purchased successfully by COD');
                                $this->response($response, 200);
                            } else {

                                $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback on COD process';
                                $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data: " . json_encode($this->post()) . '</br>Source -> ' . current_url());
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed on COD process');
                                $this->response($response, 200);
                            }
                        } else {

                            $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
                            $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];

                            //Service delivery failed;
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": Service delivery failed" . "</br> data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Service delivery failed');
                            $this->response($response, 200);
                        }
                    } else {
                        $response['response']['error'] = 'Order status pending to delivered. Please contact with administrator';
                        $this->response($response, 200);
                    }
                } else {
                    $response['response']['error'] = $this->config->item('coupon_used');
                    $this->response($response, 200);
                }
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function getProductPriceByProductAppAndProductValue($productId, $appId = 0, $attribute) {

        $result = $this->global_model->get_data('products_attributes', array('name' => $attribute));
        if ($result) {
            $result1 = $this->global_model->get_data('product_attribute_values', array('product_id' => $productId, 'attribute_id' => $result['id']));
            if ($result1) {
                return $result1['value'];
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function getUserCouponFailedCount_post() {
        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $info = $this->api_model->getUserCouponFailedCount($userId);

                if (!empty($info)) {
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['data'] = array('counter' => $info['counter'], 'update_date' => $info['update_date_time']);
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {

            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    public function getSubscriptionItems_post() {
        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        if ($this->form_validation->run() == TRUE) {
            //$this->verifyAPIKey($this->api_key);
            $this->verifyAPIKey($this->post('api_key'));
            $items = $this->subscription_model->getSubscriptionList();
            if (!empty($items)) {
                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                $response['response']['data'] = $items;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                $this->response($response, 200);
            }
        } else {
            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    public function getUserSubscriptionList_post() {
        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('offset', 'offset', 'trim|is_natural');
        if ($this->form_validation->run()) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $offset = $this->post('offset');
                $offset = !empty($offset) ? $offset : 0;

                $subscription = $this->subscription_model->subscriptionListByUserId($userId);


                if (is_array($subscription)) {
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['data'] = $subscription;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user viewing subscription list');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    public function getUserPurchasePrizebondList_post() {
        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('offset', 'offset', 'trim|is_natural');
        if ($this->form_validation->run()) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $offset = $this->post('offset');
                $offset = !empty($offset) ? $offset : 0;

                $subscription = $this->subscription_model->prizebondSubscriptionListByUserId($userId);

                if (is_array($subscription)) {
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['data'] = $subscription;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user viewing subscription list');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    //created by a teammate - web request
    public function getAUserPurchasedStatus_post() {

        $this->detectValidWebRequest();

        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run()) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $userPurchaseStatus = $this->api_model->getAUserParchasedSummaryInfo($userId);
                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                $winInfo = $this->userWinInfoByUserId($userId);
                if ($userPurchaseStatus) {
                    $hash = md5(serialize($userPurchaseStatus));
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                    $response['response']['purchase_info'] = $userPurchaseStatus;
                    $response['response']['win_bond'] = $winInfo;
                    $response['response']['subscription'] = $subscriptionInfo;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'data found');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'data not found');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    //created by a teammate - web request
    public function getOrderInfo_post() {

        $this->detectValidWebRequest();

        $this->form_validation->set_rules('order_id', 'Order id', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run()) {

            $where = array(
                'reference_id' => $this->post('order_id')
            );

            $orderInfo = $this->api_model->get_data('subscription_order_list', $where);

            if (is_array($orderInfo)) {
                $userInfo = $this->api_model->get_data('user', array('id' => $orderInfo['user_id']));

                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                $response['response']['order_info'] = $orderInfo;
                $response['response']['user_info'] = array(
                    "user_id" => $userInfo["user_id"],
                    "name" => $userInfo["name"],
                    "email" => $userInfo["email"],
                    "mobile_number" => $userInfo["mobile_number"]
                );
                $this->logUserActivity($userInfo["id"], $response['response']['code'], $response['response']['success'], 'data not found');
                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                $this->response($response, 200);
            }
        } else {
            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    //created by a teammate : - web request
    public function createAnOrder_post() {

        $this->detectValidWebRequest();

        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('product_id', 'Product id', 'trim|required|is_natural_no_zero')
                ->set_rules('quantity', 'quantity', 'trim|required|greater_than_equal_to[1]')
                ->set_rules('shipping_cost', 'Shipping cost', 'trim')
                ->set_rules('service_charge', 'Service Charge', 'trim')
                ->set_rules('discount', 'Discount', 'trim|required')
                ->set_rules('shipping_address', 'Shipping Address', 'trim');

        if ($this->form_validation->run()) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $shippingAddress = $this->post('shipping_address') ? $this->post('shipping_address') : '';

                if ($this->post('shipping_cost') > 0 && ($this->post('service_charge') > 0)) {
                    $error = "Either shipping cost nor service charge is zero";
                    $response['response']['error'] = $error;
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], $error);
                    $this->response($response, 200);
                }

                $productId = $this->post('product_id');
                $productInfo = $this->subscription_model->getProductInfoByProductId($productId);


                $orderId = strtoupper(substr(date('D'), 0, 2)) . uniqid();
                $quantity = $this->post('quantity');
                $discount = $this->post('discount');
                $gatewayInfo = $this->getwayInfo();

                if ($productInfo['product_type'] == 'Physical Bond') {
                    if ($this->post('service_charge') < 1) {
                        $response['response']['error'] = $this->config->item('COMMON_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Service charge required');
                        $this->response($response, 200);
                    } else {
                        $serviceCharge = $this->post('service_charge');
                        $shippingCost = 0;
                        $totalReceivableAmount = (($quantity * $productInfo['price']) + $serviceCharge) - $discount;
                    }
                } else {
                    if ($this->post('shipping_cost') < 0) {
                        $response['response']['error'] = $this->config->item('COMMON_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Shipping cost required');
                        $this->response($response, 200);
                    } else {

                        $shippingCost = $this->post('shipping_cost') ? $this->post('shipping_cost') : 0;
                        $serviceCharge = 0;
                        $totalReceivableAmount = (($quantity * $productInfo['price']) + $shippingCost) - $discount;
                    }
                }

                $orderInfo = array(
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'reference_id' => $orderId,
                    'product_description' => $productInfo['product_name'],
                    'currency' => 'BDT',
                    'quantity' => $quantity,
                    'rate' => $productInfo['price'],
                    'payment_method_type_id' => 0,
                    'shipping_cost' => $shippingCost,
                    'service_charge' => $serviceCharge,
                    'discount' => $discount,
                    'total_receivable_amount' => $totalReceivableAmount,
                    'shipping_address' => $shippingAddress,
                    'order_created_date_time' => date('Y-m-d H:i:s')
                );

                if ($gatewayInfo['sandbox_mode'] == 'on') {
                    $orderInfo['sandbox'] = 1;
                } else {
                    $orderInfo['sandbox'] = 0;
                }

                if ($this->api_model->insert('subscription_order_list', $orderInfo)) {
                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                    $winInfo = $this->userWinInfoByUserId($userId);
                    $orderInfo['user_id'] = $this->getExternalUserIdByInternalUserId($orderInfo['user_id']);

                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                    $response['response']['order_info'] = $orderInfo;
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_info'] = $winInfo;
                    $this->logActivity('info', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['success'] . '</br>Source -> ' . current_url() . '</br>Order Data ->' . json_encode($orderInfo) . '</br>Post Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'order created');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'order could not created');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
            $response['response']['error'] = $error;
            $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
            $this->response($response, 200);
        }
    }

    //created by a teammate : will use it from web
    public function activateProductSubscription_post() {

        $this->detectValidWebRequest();

        if ($this->post()) {

            // FOR DEBUG
            if ($this->post('transaction_status') == 'success') {
                $this->sendEmailToDeveloper('1 new sale ' . date("Y-m-d"), json_encode($this->post()));
            } else {
                $this->sendEmailToDeveloper('1 new hit ' . date("Y-m-d"), json_encode($this->post()));
            }


            $response = array();
            $error = "";
            $paymentMethod = $this->api_model->getAllPaymentMethod();
            $paymentMethod = implode(',', $paymentMethod);
            $this->form_validation
                    ->set_rules('merchant_reference_id', 'merchant reference id', 'trim|required|min_length[15]')
                    ->set_rules('user_id', 'user id', 'trim|required|min_length[10]|alpha_dash')
                    ->set_rules('product_id', 'product id', 'trim|required|is_natural_no_zero')
                    ->set_rules('quantity', 'quantity', 'trim|required|greater_than_equal_to[1]')
                    ->set_rules('shipping_cost', 'Shipping cost', 'trim|greater_than_equal_to[0]')
                    ->set_rules('service_charge', 'Service Charge', 'trim|greater_than_equal_to[0]')
                    ->set_rules('discount', 'Discount', 'trim|required|greater_than_equal_to[0]')
                    ->set_rules('total_received_amount', 'total received amount', 'trim|required|greater_than[0]')
                    ->set_rules('shipping_address', 'Shipping Address', 'trim')
                    ->set_rules('transaction_id', 'transaction id', 'trim|required|min_length[5]')
                    ->set_rules('payment_method', 'payment method', 'trim|required|in_list[' . $paymentMethod . ']')
                    ->set_rules('transaction_status', 'transaction status', 'trim|required|in_list[success,failed,cancel]')
                    ->set_rules('offer_eligibility', 'offer eligibility', 'trim|in_list[yes,no]')
                    ->set_rules('payment_date', 'payment date format ex : date("Y-m-d H:i:s")', 'trim')
                    ->set_rules('note', 'Note', 'trim|required|min_length[5]');


            if ($this->form_validation->run()) {

                if ($this->post('shipping_cost') > 0 && ($this->post('service_charge') > 0)) {
                    $error = "Either shipping cost nor service charge is zero";
                    $response['response']['error'] = $error;
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->response($response, 200);
                }

                $merchantReferenceId = $this->post('merchant_reference_id');
                $userId = $this->getInternalUserIdByExternalUserId($this->post('user_id'));
                $productId = $this->post('product_id');
                $totalReceivedAmount = $this->post('total_received_amount');
                $transactionId = $this->post('transaction_id');
                $paymentMethod = $this->post('payment_method');
                $paymentDateFromGateway = $this->post('payment_date');
                $transactionStatus = $this->post('transaction_status');
                $note = $this->post('note');

                $quantity = $this->post('quantity');
                $discount = $this->post('discount');
                $shippingCost = $this->post('shipping_cost');
                $serviceCharge = $this->post('service_charge');
                $shippingAddress = $this->post('shipping_address');

                //$this->db->trans_start();


                $orderInfo = $this->subscription_model->getOrderInfo($merchantReferenceId);
                $productInfo = $this->subscription_model->getProductInfoByProductId($productId);

                if (empty($orderInfo)) {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'data not found');
                    $this->response($response, 200);
                } elseif ($userId == FALSE) {
                    $error = "user id not found";
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], $error);
                    $this->response($response, 200);
                } elseif ($paymentMethod == 'COD' && $productInfo['product_type'] == 'Physical Bond') {
                    $error = "COD is not allowed for this order";
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], $error);
                    $this->response($response, 200);
                } elseif (($orderInfo->order_status == 'pending') && ($orderInfo->payment_status == 'pending')) {
                    if ($orderInfo->user_id != $userId) {
                        $error = "user id not matched";
                        $response['response']['error'] = $error;
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'user_id of orderInfo not matched with current user id');
                        $this->response($response, 200);
                    } elseif ($orderInfo->product_id != $productId) {
                        $error = "product id not matched";

                        $response['response']['error'] = $error;
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'product id not matched');
                        $this->response($response, 200);
                    } elseif (($paymentMethod != 'COD') && $orderInfo->total_receivable_amount != $totalReceivedAmount) {
                        $error = "total amount not matched";

                        $response['response']['error'] = $error;
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], $error);
                        $this->response($response, 200);
                    }

                    if ($error == "") {

                        $paymentMethodInfo = $this->subscription_model->getPaymentMethodInfo($paymentMethod);

                        if ($paymentMethod == 'COD') {

                            $totalReceivableAmount = (($quantity * $orderInfo->rate) + $shippingCost) - $discount;

                            if ($this->post('total_received_amount') == $totalReceivableAmount) {
                                $data = array(
                                    'quantity' => $quantity,
                                    'shipping_cost' => $shippingCost,
                                    'discount' => $discount,
                                    'shipping_address' => $shippingAddress,
                                    'total_receivable_amount' => $totalReceivedAmount
                                );
                                $data['payment_method_type_id'] = $paymentMethodInfo->id;

                                if ($transactionStatus == 'success') {
                                    if ($this->post('offer_eligibility') == 'yes') {
                                        $data['is_offer'] = 'yes';
                                    }
                                    $data['order_status'] = 'waiting_for_confirmed';
                                } else if ($transactionStatus == 'failed') {
                                    $data['order_status'] = 'failed';
                                } else if ($transactionStatus == 'cancel') {
                                    $data['order_status'] = 'cancel';
                                }

                                $this->global_model->update('subscription_order_list', $data, array('reference_id' => $orderInfo->reference_id));

                                $this->db->trans_complete();

                                if ($this->db->trans_status() == TRUE) {

                                    if ($transactionStatus == 'success') {
                                        $this->sendMesageToSalesTeam($this->post(), $orderInfo);
                                    }

                                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                                    $this->logActivity('info', ": COD : subscription order completed</br>Source -> " . current_url());
                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'COD : subscription order completed');
                                    $this->response($response, 200);
                                } else {
                                    $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
                                    $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
                                    $this->response($response, 200);
                                }
                            } else {
                                $error = "COD total amount not matched";
                                $response['response']['error'] = $error;
                                $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'COD amount not matched');
                                $this->response($response, 200);
                            }
                        } else if ($paymentMethod != 'COD') {

                            if ($productInfo['product_type'] == 'Physical Bond') {
                                $totalReceivableAmount = (($quantity * $orderInfo->rate) + $serviceCharge) - $discount;
                            } else {
                                $totalReceivableAmount = (($quantity * $orderInfo->rate) + $shippingCost) - $discount;
                            }

                            if ($totalReceivableAmount == $totalReceivedAmount) {

                                if (!empty($paymentDateFromGateway)) {
                                    $paymentDate = date('Y-m-d H:i:s', strtotime($paymentDateFromGateway));
                                } else {
                                    $paymentDate = date("Y-m-d H:i:s");
                                }

                                $data['payment_method_type_id'] = $paymentMethodInfo->id;
                                $data['gateway_transaction_id'] = $transactionId;
                                if ($transactionStatus == 'success') {
                                    if ($this->post('offer_eligibility') == 'yes') {
                                        $data['is_offer'] = 'yes';
                                    }
                                    $data['payment_status'] = "success";
                                    $data['trx_type'] = "sale";
                                    $data['payment_date_time'] = $paymentDate;
                                } else if ($transactionStatus == 'failed') {
                                    $data['payment_status'] = "failure";
                                    $data['order_status'] = 'failed';
                                } else if ($transactionStatus == 'cancel') {
                                    $data['payment_status'] = "failure";
                                    $data['order_status'] = 'cancel';
                                }


                                if ($this->global_model->update('subscription_order_list', $data, array('reference_id' => $merchantReferenceId))) {

                                    if ($transactionStatus == 'success') {

                                        // for payment history table
                                        $paymentHistory = array(
                                            'order_id' => $orderInfo->reference_id,
                                            'payment_method_type_id' => $paymentMethodInfo->id,
                                            'extra_info' => $note,
                                            'created_date_time' => $paymentDate
                                        );
                                        $this->global_model->insert('subscription_payment_history', $paymentHistory);

                                        if ($productInfo['product_type'] == 'Physical Bond') {

                                            if ($this->savePhysicalbondData($productId, $userId, $paymentMethod, $merchantReferenceId) == TRUE) {
                                                ;
                                                $this->db->trans_complete();

                                                $this->sendphysicalBondPurchasedConfirmationMessageToUser($userId, $merchantReferenceId);
                                                $this->sendMesageToSalesTeamAfterSuccessfulPhysicalBond($this->post(), $orderInfo);

                                                if ($this->db->trans_status() == TRUE) {

                                                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                                                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                                                    $this->logActivity('info', ": Physical bond purchased successfully</br>Source -> " . current_url());
                                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Subscription purchased successfully');
                                                    $this->response($response, 200);
                                                } else {

                                                    $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
                                                    $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                                                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data: " . json_encode($this->post()) . '</br>Source -> ' . current_url());
                                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
                                                    $this->response($response, 200);
                                                }
                                            } else {
                                                $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
                                                $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
                                                $this->response($response, 200);
                                            }
                                        } else {

                                            if ($this->deliverServiceToCustomer($productId, $userId, $paymentMethod, $merchantReferenceId) == TRUE) {

                                                $this->db->trans_complete();

                                                if ($this->db->trans_status() == TRUE) {
                                                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                                                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                                                    $this->logActivity('info', ": Subscription purchased successfully</br>Source -> " . current_url());
                                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Subscription purchased successfully');
                                                    $this->response($response, 200);
                                                } else {
                                                    $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
                                                    $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                                                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data: " . json_encode($this->post()) . '</br>Source -> ' . current_url());
                                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
                                                    $this->response($response, 200);
                                                }
                                            } else {
                                                $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
                                                $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];

                                                //Service delivery failed;
                                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": Service delivery failed" . "</br> data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
                                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Service delivery failed');
                                                $this->response($response, 200);
                                            }
                                        }
                                    } else if ($transactionStatus == 'failed') {

                                        $this->db->trans_complete();

                                        if ($this->db->trans_status() == TRUE) {

                                            $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                                            $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                                            $this->logActivity('info', ": transaction status : failed, No service delivered </br>Source -> " . current_url());
                                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'transaction status : failed, received from gateway. No service delivered');
                                            $this->response($response, 200);
                                        } else {

                                            $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
                                            $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data: " . json_encode($this->post()) . '</br>Source -> ' . current_url());
                                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed. Service delivery failed');
                                            $this->response($response, 200);
                                        }
                                    }
                                }
                            } else {
                                $error = "Total amount not matched";
                                $response['response']['error'] = $error;
                                $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'COD amount not matched');
                                $this->response($response, 200);
                            }
                        }
                    } else {

                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": message:" . $error . "</br> data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
                    }
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . " </br>data:" . json_encode($orderInfo) . '</br>Source -> ' . current_url() . '</br> User Data ->' . json_encode($this->post()));
                    $this->response($response, 200);
                }
            } else {
                $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
                $response['response']['error'] = $error;
                $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }
        }
    }

    public function getOrderHistory_post() {

        if ($this->post()) {

            $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
            $this->form_validation->set_rules('offset', 'offset', 'trim|required|is_natural');

            if ($this->form_validation->run() == TRUE) {

                if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                    $offset = $this->post('offset');
                    $limit = 50;
                    $orderHistoryList = $this->api_model->getOrderHistoryByUserId($limit, $offset, $userId);

                    if (!empty($orderHistoryList)) {
                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                        $response['response']['data'] = $orderHistoryList;
                        $this->response($response, 200);
                    } else {
                        $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                        $this->response($response, 200);
                    }
                } else {
                    $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                    $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->response($response, 200);
                }
            } else {

                $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
                $response['response']['error'] = $error;
                $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }
        }
    }

    public function verifyAPIKey($apiKey, $secretKey = FALSE) {
        if (!$this->api_model->doesMatchApiKey($apiKey)) {
            $output['response']['error'] = 'api key not matched';
            $this->response($output);
            exit;
        } else {
            return TRUE;
        }
    }

    private function detectValidWebRequest() {


        $HTTP_HOSTS = array(
            'api.example.com',
            'prizebond-checker.com',
            'localhost',
            '192.168.50.54',
            '192.168.50.53'
        );

        if (isset($_SERVER['HTTP_HOST'])) {
            if (!in_array($_SERVER['HTTP_HOST'], $HTTP_HOSTS)) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' HTTP HOST : ' . $_SERVER['HTTP_HOST']);
                $this->response($response);
            }
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            if ($_SERVER['HTTP_REFERER'] != md5('prizebond-checker.com' . date('Y-m-d'))) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' Received HTTP Referer : ' . $_SERVER['HTTP_REFERER'] . ' Expected value : ' . md5('prizebond-checker.com' . date('Y-m-d')));
                $this->response($response);
            }

            return TRUE;
        }

        return FALSE;
    }

    private function getInternalUserIdByExternalUserId($userId) {

        $userInfo = $this->global_model->get_data('user', array('user_id' => $userId));

        if (!$userInfo) {
            return FALSE;
        }
        return $userInfo['id'];
    }

    private function invalidCouponTracker($userId) {
        $couponVerifyCounterInfo = $this->api_model->getVerifyCodeSentCounterForAUser($userId);
        if ($couponVerifyCounterInfo['counter'] == 3) {
            //$response['response']['error'] = 'Please insert valid coupon';
            $response['response']['error'] = $this->config->item('invalid_coupon_send_times'); //Invalid coupon already sent three times
            //$response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
            $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
            $this->response($response, 200);
        } else {
            $verifyCounterData = array(
                'counter' => $couponVerifyCounterInfo['counter'] + 1,
            );
            $this->api_model->updateInvalidCouponCounter($userId, $verifyCounterData);
            //$response['response']['error'] = 'Coupon not valid';
//            $response['response']['error'] = $this->config->item('invalid_coupon');
            $response['response']['error'] = $this->config->item('COUPON_REDEEM_FAILED_01')['error'];
            $response['response']['code'] = $this->config->item('COUPON_REDEEM_FAILED_01')['code'];

            $this->response($response, 200);
        }
    }

    private function convertEnglishDateToBangla($param) {
        //$param = date("l F j Y a g:i", strtotime($param));
        $param = date("m/d/Y l g:i a", strtotime($param));
        $string = '';
        //10/14/2016 Friday 3:08 pm
        $dataArray = explode(' ', $param);
        $dataArray1 = explode('/', $dataArray[0]);
        $wekday = $dataArray[1];
        $month = $dataArray1[0];
        $day = $dataArray1[1];
        $year = $dataArray1[2];
        $ampm = $dataArray[3];
        $extra = explode(':', $dataArray[2]);
        $hour = $extra[0];
        $minute = $extra[1];

        $banglaWeak = $this->convertStringToBangla($wekday);
        $string = '';
        $dayArray = str_split($day);
        foreach ($dayArray as $day) {
            $string .= $this->convertStringToBangla($day);
        }
        $banglaDay = $string;

        $string = '';
        $monthArray = str_split($month);
        foreach ($monthArray as $day) {
            $string .= $this->convertStringToBangla($day);
        }
        $banglaMonth = $string;
        //$banglaMonth = $this->convertStringToBangla($month);
        $yearArray = str_split($year);
        $string = '';
        foreach ($yearArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }
        $banglaYear = $string;

        $banglaampm = $this->convertStringToBangla($ampm);

        $hourArray = str_split($hour);
        $string = '';
        foreach ($hourArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }

        $banglaHour = $string;
        $minuteArray = str_split($minute);
        $string = '';
        foreach ($minuteArray as $number) {
            $string .= $this->convertStringToBangla($number);
        }

        $banglaMinute = $string;
        //return $banglaWeak . ", " . $banglaMonth . " " . $banglaDay . ", " . $banglaYear . " " . $banglaampm . ' ' . $banglaHour . '.' . $banglaMinute;
        return $banglaMonth . '/' . $banglaDay . '/' . $banglaYear . ' ' . $banglaWeak . ' ' . $banglaHour . ':' . $banglaMinute . ' ' . $banglaampm;
    }

    public function convertStringToBangla($param) {

        $param = trim($param);
        if ($param == "January") {
            return 'জানুয়ারী';
        } elseif ($param == "February") {
            return 'ফেব্রুয়ারি';
        } elseif ($param == "March") {
            return 'মার্চ';
        } elseif ($param == "April") {
            return 'এপ্রিল';
        } elseif ($param == "May") {
            return 'মে';
        } elseif ($param == "June") {
            return 'জুন';
        } elseif ($param == "July") {
            return 'জুলাই';
        } elseif ($param == "August") {
            return 'অগাস্ট';
        } elseif ($param == "September") {
            return 'সেপ্টেম্বর';
        } elseif ($param == "October") {
            return 'অক্টোবর';
        } elseif ($param == "November") {
            return 'নভেম্বর';
        } elseif ($param == "December") {
            return 'ডিসেম্বর';
        } elseif ($param == "0") {
            return "০";
        } elseif ($param == "1") {
            return "১";
        } elseif ($param == "2") {
            return "২";
        } elseif ($param == "3") {
            return "৩";
        } elseif ($param == "4") {
            return "৪";
        } elseif ($param == "5") {
            return "৫";
        } elseif ($param == "6") {
            return "৬";
        } elseif ($param == "7") {
            return "৭";
        } elseif ($param == "8") {
            return "৮";
        } elseif ($param == "9") {
            return "৯";
        } elseif ($param == "pm") {
            return "অপরাহ্ন";
        } elseif ($param == "am") {
            return "পূর্বাহ্ন";
        } elseif ($param == 'Saturday') {
            return 'শনিবার';
        } elseif ($param == 'Sunday') {
            return 'রবিবার';
        } elseif ($param == 'Monday') {
            return 'সোমবার';
        } elseif ($param == 'Tuesday') {
            return 'মঙ্গলবার';
        } elseif ($param == 'Wednesday') {
            return 'বুধবার';
        } elseif ($param == 'Thursday') {
            return 'বৃহস্পতিবার';
        } elseif ($param == 'Friday') {
            return 'শুক্রবার';
        }

        return $param;
    }

    private function userAuthCheckByAccessToken($accessToken) {

        if (!empty($accessToken)) {
            $userInfo = $this->api_model->get_data('user', array('access_token' => $accessToken));

            if (empty($userInfo)) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->response($response);
            }
            $expireTime = $userInfo['access_token_expire_time'];
            $currentTime = time();

            if ($userInfo['verify_status'] == 'NO') {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->response($response);
            } else if ($userInfo['status'] != 1) {
                $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_03')['error'];
                $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_03')['code'];
                $this->response($response);
            } elseif ($expireTime < $currentTime) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->response($response);
            } elseif ($expireTime >= $currentTime) {

                $tokenInformation = array(
                    'access_token_expire_time' => strtotime('+5 minutes')
                );
                $this->api_model->update('user', $tokenInformation, array('id' => $userInfo['id']));
            }
            return $userInfo['id'];
        } else {
            $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
            $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
            $this->response($response);
            exit;
        }
    }

    private function savePhysicalbondData($productId, $userId, $paymentMethodName, $referenceId) {

        $productInfo = $this->api_model->get_data('subscription_product_list', array('id' => $productId));

        $productAttribueValue = $this->subscription_model->getProductAttributeAndValue($productId);

        $productPurchaseListData = array(
            'user_id' => $userId,
            'product_category_id' => $productInfo['product_category_id'],
            'product_type_id' => $productInfo['product_type_id'],
            'app_types_id' => $productInfo['app_types_id'],
            'product_id' => $productInfo['id'],
            'purchased_by' => $paymentMethodName,
            'valid_start_datetime' => '0000-00-00 00:00:00',
            'valid_end_datetime' => '0000-00-00 00:00:00',
            'status' => 1,
            'order_reference_id' => $referenceId,
            'created_datetime' => date('Y-m-d H:i:s')
        );

        if ($this->subscription_model->insert('subscription_product_purchase_list', $productPurchaseListData)) {
            $this->subscription_model->update('subscription_order_list', array('order_status' => 'completed'), array('reference_id' => $referenceId));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function deliverServiceToCustomer($productId, $userId, $paymentMethodName, $referenceId) {

        $productInfo = $this->api_model->get_data('subscription_product_list', array('id' => $productId));

        $productAttribueValue = $this->subscription_model->getProductAttributeAndValue($productId);

        if (isset($productAttribueValue['unit']) && isset($productAttribueValue['validity(days)'])) {
            if (!empty($productAttribueValue['unit']) && $productAttribueValue['unit'] == 'bond') {
                $validity = $productAttribueValue['validity(days)'];
                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
            }
        }

        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
            if ($productAttribueValue['unit'] == 'bond') {
                $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
            }
        }

        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
            if ($productAttribueValue['unit'] == 'days') {
                $validity = $productAttribueValue['unit_value'];
                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
            }
        }

        if (!isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
            $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
        }

        $productPurchaseListData = array(
            'user_id' => $userId,
            'product_category_id' => $productInfo['product_category_id'],
            'product_type_id' => $productInfo['product_type_id'],
            'app_types_id' => $productInfo['app_types_id'],
            'product_id' => $productInfo['id'],
            'purchased_by' => $paymentMethodName,
            'valid_start_datetime' => date('Y-m-d H:i:s'),
            'valid_end_datetime' => $validEndDateTime,
            'status' => 1,
            'order_reference_id' => $referenceId,
            'created_datetime' => date('Y-m-d H:i:s')
        );

        if ($this->subscription_model->insert('subscription_product_purchase_list', $productPurchaseListData)) {
            $this->subscription_model->update('subscription_order_list', array('order_status' => 'completed'), array('reference_id' => $referenceId));
            $this->sendProductDeliveryConfirmationMessageToUser($userId, $referenceId);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function sendphysicalBondPurchasedConfirmationMessageToUser($userId, $orderId) {

        $message = 'Your Order ID ' . $orderId . ' has been received. We will contact with you soon. Thank you. - Prizebond-Checker Team';

        $devicePushInfo = $this->global_model->get_data('device_info', array('user_id' => $userId, 'device_status' => 'active'));
        $userInfo = $this->global_model->get_data('user', array('id' => $userId));

        // SEND GCM
        if (strlen($devicePushInfo['device_push_id']) > 25) {

            $this->load->library('gcm');
            $this->gcm->clearRecepients();
            $registrationIds = array();


            $registrationIds[] = (string) $devicePushInfo['device_push_id'];

            $this->gcm->setRecepients($registrationIds);
            $payloadData = array(
                'prizebond' => 'Custom content'
            );
            $this->gcm->setData($payloadData);
            $this->gcm->setMessage($message);
            $this->gcm->setGroup($message);

            if ($this->gcm->send()) {

                $this->logActivity('info', 'GCM sent : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'GCM sent : ' . $message);
            } else {
                $this->logActivity('error', 'GCM sent : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'GCM sent : ' . $message);
            }
        }



        // SEND SMS

        if ($userInfo['mobile_number'] != NULL) {
            //$senderText = $this->config->item('sms_sender_text');
            $smsText = $message;
            $gsm = $userInfo['mobile_number'];
            if ($this->sendSMSToCustomer($gsm, $smsText) == TRUE) {
                $this->logActivity('info', 'SMS verification Code sent : ' . $gsm . ' Message : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'SMS verification Code : ' . $gsm . ' Message : ' . $message);
            } else {
                $this->logActivity('error', 'SMS verification Code failed to sent : ' . $gsm . ' Message : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'SMS verification Code failed to sent : ' . $gsm . ' Message : ' . $message);
            }
        }
    }

    private function sendProductDeliveryConfirmationMessageToUser($userId, $orderId) {

        $message = 'Your subscription for Order ID ' . $orderId . ' has been activated. Thank you. - Prizebond-Checker Team';

        $devicePushInfo = $this->global_model->get_data('device_info', array('user_id' => $userId, 'device_status' => 'active'));
        $userInfo = $this->global_model->get_data('user', array('id' => $userId));

        // SEND GCM
        if (strlen($devicePushInfo['device_push_id']) > 25) {

            $this->load->library('gcm');
            $this->gcm->clearRecepients();
            $registrationIds = array();


            $registrationIds[] = (string) $devicePushInfo['device_push_id'];

            $this->gcm->setRecepients($registrationIds);
            $payloadData = array(
                'prizebond' => 'Custom content'
            );
            $this->gcm->setData($payloadData);
            $this->gcm->setMessage($message);
            $this->gcm->setGroup($message);

            if ($this->gcm->send()) {

                $this->logActivity('info', 'GCM sent : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'GCM sent : ' . $message);
            } else {
                $this->logActivity('error', 'GCM sent : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'GCM sent : ' . $message);
            }
        }



        // SEND SMS

        if ($userInfo['mobile_number'] != NULL) {
            //$senderText = $this->config->item('sms_sender_text');
            $smsText = $message;
            $gsm = $userInfo['mobile_number'];
            if ($this->sendSMSToCustomer($gsm, $smsText) == TRUE) {
                $this->logActivity('info', 'SMS verification Code sent : ' . $gsm . ' Message : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'SMS verification Code : ' . $gsm . ' Message : ' . $message);
            } else {
                $this->logActivity('error', 'SMS verification Code failed to sent : ' . $gsm . ' Message : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'SMS verification Code failed to sent : ' . $gsm . ' Message : ' . $message);
            }
        }
    }

    private function logActivity($type, $message) {

        if ($this->config->item('LOG_ACTIVITY') == TRUE) {
            if ($type == 'error') {
                log_message('error', $message);
            } elseif ($type == 'debug') {
                log_message('debug', $message);
            } else {
                log_message('info', $message);
            }
        }
    }

    //created by a teammate
    private function logUserActivity($userId = 0, $code = NULL, $message = NULL, $internalMessage = NULL) {

        $deviceUUID = !empty($this->input->post('device_uuid')) ? $this->input->post('device_uuid') : NULL;
        $userData = !empty($this->input->post()) ? $this->input->post() : NULL;
        $data = array(
            'user_id' => $userId,
            'device_uuid' => $deviceUUID,
            'code' => $code,
            'message' => $message,
            'internal_message' => $internalMessage,
            'user_data' => json_encode($userData),
            'source_url' => current_url()
        );
        $this->db->insert('user_activity_logs', $data);
    }

    private function sendSMSToCustomer($mobileNumber, $message) {

        $allowedBitBirds = array(
            '+88019',
            '+88017',
            '+88011'
        );

        $allowedMuthofun = array(
            '+88016',
            '+88018',
            '+88015'
        );

        $prefix = substr($mobileNumber, 0, 6);

        if (in_array($prefix, $allowedBitBirds)) {
            // call bitbirds code

            $SMSResponse = $this->bitbirds->sendSMS('', $message, $mobileNumber);
            if ($SMSResponse > 0) {

                $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

                return TRUE;
            } else {
                // call muthofun code
                $response = $this->muthofun->sendSMS($mobileNumber, $message);

                if ($response['type'] == 'success') {
                    $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());

                    return TRUE;
                }

                $this->logActivity('error', 'SMS Sending Failed to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());

                return FALSE;
            }
        } else if (in_array($prefix, $allowedMuthofun)) {
            // call muthofun code
            $response = $this->muthofun->sendSMS($mobileNumber, $message);
            if ($response['type'] == 'success') {
                $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());

                return TRUE;
            } else {
                $SMSResponse = $this->bitbirds->sendSMS('', $message, $mobileNumber);
                if ($SMSResponse > 0) {
                    $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

                    return TRUE;
                }
                $this->logActivity('error', 'SMS Sending Failed to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

                return FALSE;
            }
        } else {

            $response = $this->muthofun->sendSMS($mobileNumber, $message);
            if ($response['type'] == 'success') {
                $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());
                return TRUE;
            } else {
                $SMSResponse = $this->bitbirds->sendSMS('', $message, $mobileNumber);
                if ($SMSResponse > 0) {
                    $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

                    return TRUE;
                }
                $this->logActivity('error', 'SMS Sending Failed to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

                return FALSE;
            }
        }
    }

    //From API controller

    protected function userWinInfoByUserId($userId) {
        if (!empty($userId)) {
            $userWinInfo = $this->api_model->getAUsersWinInfo($userId);

            $winInfo = new stdClass();
            if (!empty($userWinInfo)) {
                $winInfo->total_bonds = count($userWinInfo);
            } else {
                $winInfo->total_bonds = 0;
            }
            return $winInfo;
        } else {
            $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
            $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
            $this->response($response);
            exit;
        }
    }

    private function getwayInfo() {
        $result = $this->global_model->get_data('subscription_gateway_settings', array('gateway_id' => 1));
        return $result;
    }

    private function getExternalUserIdByInternalUserId($userId) {
        $userInfo = $this->global_model->get_data('user', array('id' => $userId));
        if (!$userInfo) {
            return FALSE;
        }
        return $userInfo['user_id'];
    }

    private function sendEmailToDeveloper($subject, $message) {
        $this->load->library('email');
        $this->email->from('noreply@prizebond-checker.com', 'Prizebond');
        $this->email->to('reports-team@example.com,backend-team@example.com,web-team@example.com');
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
        return TRUE;
    }

    private function sendMesageToSalesTeamAfterSuccessfulPhysicalBond($postData, $orderInfo) {

        $query = $this->db->get_where('user', array('id' => $orderInfo->user_id));

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $customerName = $row->name;
                $customerEmail = $row->email;
                $customerMobileNumber = $row->mobile_number;
                $shiping_address = !empty($orderInfo->shipping_address) ? $orderInfo->shipping_address : "N/A";

                $senderEmail = 'no-reply@prizebond-checker.com';
                $senderName = 'PrizeBond';
                $receiverEmail = 'sales@example.com';
                $receiverName = 'Sales Team';
                $subject = 'PrizeBond Physical Bond Request';

                $message = "Dear Sales Team,<br> 1 new Physical Bond request has been submited. Please confirm the order. Order details are as follow:<br><br>";
                $message .= "Order ID : " . $postData['merchant_reference_id'] . '<br>';
                $message .= "Order Created Date : " . date('l jS \of F Y h:i:s A', strtotime($orderInfo->order_created_date_time)) . '<br>';
                $message .= "Order Submitted Date : " . date('l jS \of F Y h:i:s A', strtotime($postData['payment_date'])) . '<br>';
                $message .= "Cutomer Information : " . $customerName . ' - ' . $customerMobileNumber . ' - ' . $customerEmail . '<br>';
                $message .= "Shipping Address : " . $shiping_address . '<br>';
                $message .= "Service Charge : " . $postData['service_charge'] . '<br>';
                $message .= "Discount : " . $postData['discount'] . '<br>';
                $message .= "Quantity : " . $postData['quantity'] . '<br>';
                $message .= "Total Receivable Amount : " . $postData['total_received_amount'] . '<br>';

                if ($this->sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    private function sendMesageToSalesTeam($postData, $orderInfo) {

        $query = $this->db->get_where('user', array('id' => $orderInfo->user_id));

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $customerName = $row->name;
                $customerEmail = $row->email;
                $customerMobileNumber = $row->mobile_number;

                $senderEmail = 'no-reply@prizebond-checker.com';
                $senderName = 'PrizeBond';
                $receiverEmail = 'sales@example.com';
                $receiverName = 'Sales Team';
                $subject = 'PrizeBond COD Request';

                $message = "Dear Sales Team,<br> 1 new COD request has been submitted. Please confirm the order. Order details are as follow:<br><br>";
                $message .= "Order ID : " . $postData['merchant_reference_id'] . '<br>';
                $message .= "Order Created Date : " . date('l jS \of F Y h:i:s A', strtotime($orderInfo->order_created_date_time)) . '<br>';
                $message .= "Order Submitted Date : " . date('l jS \of F Y h:i:s A', strtotime($postData['payment_date'])) . '<br>';
                $message .= "Cutomer Information : " . $customerName . ' - ' . $customerMobileNumber . ' - ' . $customerEmail . '<br>';
                $message .= "Shipping Address : " . $postData['shipping_address'] ? $postData['shipping_address'] : 'N/A' . '<br>';
                $message .= "Shipping Cost : " . $postData['shipping_cost'] . '<br>';
                $message .= "Discount : " . $postData['discount'] . '<br>';
                $message .= "Quantity : " . $postData['quantity'] . '<br>';
                $message .= "Total Receivable Amount : " . $postData['total_received_amount'] . '<br>';

                if ($this->sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    private function convertEnglishDateTimeToBanglaDateTime($date) {
        $engDATE = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'am', 'pm');
        $bangDATE = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০', 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', '
বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'পূর্বাহ্ণ', 'অপরাহ্ণ'
        );
        $convertedDATE = str_replace($engDATE, $bangDATE, $date);
        return $convertedDATE;
    }

    private function sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message) {
        $data = array($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message);
        $this->load->library('email');
        $this->email->from($senderEmail, $senderName);
        $this->email->to($receiverEmail);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return TRUE;
        } else {
            $response['type'] = 'error';
            $response['message'] = $this->config->item('COMMON_ERROR_05')['error'];
            $response['code'] = $this->config->item('COMMON_ERROR_05')['code'];
            $this->logActivity('error', 'Code : ' . $response['code'] . ' -> ' . $response['message'] . '</br>Source -> ' . current_url() . '</br> Data ->' . json_encode($data));
            $this->response($response, 200);
        }
    }

}
