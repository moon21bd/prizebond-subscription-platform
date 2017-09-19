<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Subscriptions extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/subscription_model');
        $this->load->model('subscription/global_model');
        $this->load->helper('form');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        $data = array();
        $userId = $this->input->get('userId');
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
        $data['productList'] = $this->subscription_model->couponListByUserId($userId);
        $sessionData = array(
            'userId' => $userId
        );
        $this->session->set_userdata($sessionData);
        $this->load->view('subscription/coupon_redeem_historry', $data);
    }

    public function payment($price, $productId) {

        $data = array();
        $sessionData = array(
            'price' => $price,
            'product_id' => $productId
        );

        $data['product_id'] = $productId;
        $data['price'] = $price;
        $this->session->set_userdata($sessionData);
        $this->load->view('subscription/payment_option_list', $data);
    }

    public function termOfService() {
        $data = array();
        $this->load->view('subscription/term_of_service', $data);
    }

    public function dbbl() {
        $this->load->view('subscription/dbbl_form');
    }

    public function sureCash() {
        $this->load->view('subscription/surecash_form');
    }

    public function saveSubscriptionInformation($price, $productId) {

        $ordarListData = array(
            'user_id' => $this->session->userdata('userId'),
            'order_id' => time(),
            'order_id_prefix' => strtoupper(substr(date('D'), 0, 2)),
            //'product_id' => $this->session->userdata('product_id'),
            //'price' => $this->session->userdata('price'),
            //'payment_method_type_id' => $this->session->userdata('payment_method_type_id'),
            'product_id' => $productId,
            'price' => $price,
            'payment_method_type_id' => 0,
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'order_created_date_time' => date('Y-m-d H:i:s'),
            'created_date_time' => date('Y-m-d H:i:s')
        );
        $this->global_model->insert('subscription_order_list', $ordarListData);
        echo 'done';
        exit;
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

    public function savePaymentInfo($bKashInfo) {
        $this->global_model->insert('payment_bkash_queue', $bKashInfo);
        return TRUE;
    }

}
