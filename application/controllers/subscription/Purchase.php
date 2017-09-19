<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Purchase extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        $this->load->library('form_validation');
        $this->load->model('subscription/subscription_model');
        $this->load->model('subscription/global_model');
        $this->load->helper('form');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        $data = array();
        $status = '';
        $userId = $this->input->get('userId');
        $status = $this->input->get('status');
        if (!empty($status) && $status == 'success') {
            $data['success'] = 'Transaction has been successful';
        }

        $data['productList'] = $this->subscription_model->getSubscriptionList();
        $sessionData = array(
            'userId' => $userId
        );
        $this->session->set_userdata($sessionData);
        $this->session->unset_userdata('subscription_status');
        $this->load->view('subscription/subscription_list', $data);
    }

    public function mySubscriptionList() {
        $data = array();
        $userId = $this->input->get('userId');


        $userId = $this->getInternalUserIdByExternalUserId($userId);
        $data['productList'] = $this->subscription_model->subscriptionListByUserId($userId);
        $sessionData = array(
            'userId' => $userId
        );
        $this->session->set_userdata($sessionData);
        $this->load->view('subscription/my_subscription_list', $data);
    }

    public function getCouponDetails() {
        $data = array();
        $userId = $this->input->get('userId');


        $userId = $this->getInternalUserIdByExternalUserId($userId);
        $data['productList'] = $this->subscription_model->couponListByUserId($userId);
        $sessionData = array(
            'userId' => $userId
        );
        $this->session->set_userdata($sessionData);
        $this->load->view('subscription/coupon_redeem_historry', $data);
    }

    //created by a teammate
    private function getInternalUserIdByExternalUserId($userId) {
        $userInfo = $this->global_model->get_data('user', array('user_id' => $userId));
        if (!$userInfo) {
            return FALSE;
        }
        return $userInfo['id'];
    }

    public function payment($productId) {

        if ($this->session->userdata('userId')) {

            $productInfo = $this->subscription_model->getProductInfoByProductId($productId);
            $orderId = strtoupper(substr(date('D'), 0, 2)) . uniqid();
            $quantity = 1;
            $shippingCost = 0;
            $discount = 0;

            $gatewayInfo = $this->getwayInfo();
            $totalReceivableAmount = ($quantity * $productInfo['price']) - $shippingCost - $discount;
            $orderInfo = array(
                'user_id' => $this->session->userdata('userId'),
                'product_id' => $productId,
                'reference_id' => $orderId,
                'product_description' => $productInfo['product_name'],
                'currency' => 'BDT',
                'quantity' => $quantity,
                'rate' => $productInfo['price'],
                'payment_method_type_id' => 0,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total_receivable_amount' => $totalReceivableAmount,
                'order_created_date_time' => date('Y-m-d H:i:s')
            );
            if ($gatewayInfo['sandbox_mode'] == 'on') {
                $orderInfo['sandbox'] = 1;
            } else {
                $orderInfo['sandbox'] = 0;
            }
            $this->saveOrderInfo($orderInfo);

            $orderInfo['application_id'] = 123456;
            $orderInfo['merchant_id'] = '583540536a342';
            $orderInfo['user_ip_address'] = $this->input->ip_address();

            $jsonData = json_encode($orderInfo);
            $base64EncodeData = base64_encode($jsonData);
            $url = 'http://pgate.example.com/payment?data=' . $base64EncodeData;
            redirect($url);

            /* openssl_public_encrypt($base64EncodeData, $encrypted, $publicKey1);
              $this->sendEncriptedData(base64_encode($encrypted));
             * 
             */
        } else {
            redirect('subscription/purchase/sessionTimeOut');
        }
    }

    public function saveOrderInfo($orderInfo) {
//        $orderId = strtoupper(substr(date('D'), 0, 2)) . uniqid();
//        $ordarListData = array(
//            'user_id' => $this->session->userdata('userId'),
//            'order_id' => $orderId,
//            'product_id' => $productId,
//            'price' => $price,
//            'payment_method_type_id' => 0, //priv(2) edited by runa 11/24/2016 05:52pm
//            'payment_status' => 'pending',
//            'order_status' => 'pending',
//            'order_created_date_time' => date('Y-m-d H:i:s'),
//            'payment_date_time' => date('Y-m-d H:i:s')
//        );

        $this->db->insert('subscription_order_list', $orderInfo);
        //return $orderId;
    }

    public function sessionTimeOut() {
        //web view
    }

    private function sendEncriptedData($encrypted) {
        $url = 'http://pgate.example.com/merchants/getMerchantEncryptedData?data=' . $encrypted;
        redirect($url);
        exit;
    }

    public function termOfService() {
        $data = array();
        $this->load->view('subscription/term_of_service', $data);
    }

    // this method for curl get
    public function getUserPurchaseInfoByAppAndUserId($userId) {
        $where = array(
            'user_id' => $userId,
        );
        $result = $this->api_model->getUserPurchaseList($where);
        echo json_encode($result);
    }

    public function updateSubscriptionInformation() {
        $ordarData = array(
            'payment_status' => 'success',
            'order_status' => 'completed',
        );
        $whereOrder = array(
            'user_id' => $this->session->userdata('userId')
        );

        $this->global_model->update('subscription_order_list', $ordarData, $whereOrder);
        return TRUE;
    }

    public function transactionStatus() {
        // get method
        $data = array();
        $status = $this->input->get('status');
        if ($status == 'success') {
            $this->load->view('subscription/success', $data);
        } elseif ($status == 'failed') {
            $this->load->view('subscription/error', $data);
        }
    }

    private function sendMailToUser($data) {

        $this->load->library('email');
        $from = 'no-reply@example.com';
        $subject = 'post data';
        $this->email->from($from, 'prizebond');
        $this->email->to('backend-team@example.com');
        $this->email->subject($subject);
        $mailer = "Hi,<br/>";
        $mailer .= "<strong> post data  : </strong>" . $data . "<br/></br>";
        $this->email->message($mailer);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function ipnStatus() {
        $mail = json_encode($this->input->post());
        $this->sendMailToUser($mail);
        if ($this->input->post()) {
            $this->form_validation
                    ->set_rules('merchant_reference_id', 'merchant reference id', 'trim|required')
                    ->set_rules('user_id', 'user id', 'trim|required')
                    ->set_rules('product_id', 'product id', 'trim|required')
                    ->set_rules('total_received_amount', 'total received amount', 'trim|required')
                    ->set_rules('transaction_id', 'transaction id', 'trim|required')
                    ->set_rules('payment_method', 'payment method', 'trim|required')
                    ->set_rules('transaction_status', 'transaction status', 'trim|required');

            if ($this->form_validation->run()) {

                $error = "";
                $orderInfo = $this->subscription_model->getOrderInfo($this->input->post('merchant_reference_id'));

                if ($orderInfo->user_id != $this->input->post('user_id')) {
                    $error = "user id not matched";
                }
                if ($orderInfo->product_id != $this->input->post('product_id')) {
                    $error = "product id not matched";
                }
                if ($orderInfo->total_receivable_amount != $this->input->post('total_received_amount')) {
                    $error = "total amount not matched";
                }

                if ($error == "") {

                    $paymentMethodInfo = $this->subscription_model->getPaymentMethodInfo($this->input->post('payment_method'));
                    $data['payment_method_type_id'] = $paymentMethodInfo->id;
                    $data['gateway_transaction_id'] = $this->input->post('transaction_id');
                    $data['payment_date_time'] = date("Y-m-d H:i:s");

                    if ($this->input->post('transaction_status') == 'success') {
                        $data['payment_status'] = "success";
                    } else if ($this->input->post('transaction_status') == 'failed') {
                        $data['payment_status'] = "failure";
                    }
                    // for payment history table
                    $paymentHistory = array(
                        'order_id' => $orderInfo->reference_id,
                        'payment_method_type_id' => $paymentMethodInfo->id,
                        'extra_info' => 'Product Purchased by bKash',
                        'created_date_time' => date('Y-m-d H:i:s')
                    );
                    if ($this->global_model->update('subscription_order_list', $data, array('reference_id' => $this->input->post('merchant_reference_id')))) {
                        $this->global_model->insert('subscription_payment_history', $paymentHistory);
                    }
                    if ($this->deliverServiceToCustomer($this->input->post('product_id'), $this->input->post('user_id'), $this->input->post('payment_method'), $this->input->post('merchant_reference_id')) == TRUE) {
                        log_message('info', 'Product purchase successful');
                    } else {

                        //$this->sendMailToUser('Service delivery failed');
                        log_message('error', $this->router->fetch_class() . "->" . $this->router->fetch_method() . ": message:Service delivery failed" . " data:" . json_encode($data));
                    }
                } else {
                    //$this->sendMailToUser($error);
                    log_message('error', $this->router->fetch_class() . "->" . $this->router->fetch_method() . ": message:" . $error . " data:" . json_encode($this->input->post()));
                }
            } else {

                $error = "Form validation failed.";
                //$this->sendMailToUser($error);
                log_message('error', $this->router->fetch_class() . "->" . $this->router->fetch_method() . ": message:" . $error . " data:" . json_encode($this->input->post()));
            }
        }
    }

    private function deliverServiceToCustomer($productId, $userId, $paymentMethodName, $referenceId) {


        //$productInfoByPGate = $this->input->post('product_info');
        //$this->updateSubscriptionInformation();

        $productInfo = $this->subscription_model->get_data('subscription_product_list', array('id' => $productId));

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
            'created_datetime' => date('Y-m-d H:i:s')
        );

        if ($this->subscription_model->insert('subscription_product_purchase_list', $productPurchaseListData)) {
            $this->global_model->update('subscription_order_list', array('order_status' => 'completed'), array('reference_id' => $referenceId));
//            log_message('info', 'Product purchase successful');
            return TRUE;
        } else {
            return FALSE;
//            log_message('error', $this->router->fetch_class() . "->" . $this->router->fetch_method() . ": message:Product purchase failed" . " data:" . json_encode($productPurchaseListData));
        }
    }

    private function getwayInfo() {
        $result = $this->global_model->get_data('subscription_gateway_settings', array('gateway_id' => 1));
        return $result;
    }

}
