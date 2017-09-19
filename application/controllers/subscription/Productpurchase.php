<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Productpurchase extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }
        if ($this->input->get('debug') == 1) {
            $this->output->enable_profiler(TRUE);
        }
        $this->load->model('subscription/productpurchase_model');
        $this->load->model('subscription/product_model');
        $this->load->model('subscription/coupon_model');
        $this->load->model('subscription/api_model');
        $this->load->model('subscription/global_model');
        $this->load->helper('global');
        $this->load->helper('user_profile_info_helper');
    }

    public function __destruct() {
        $this->db->close();
    }

//    public function add() {
//        $data = array();
//        $data['title'] = 'Add';
//
//        $data['tab_active'] = 'purchase';
//        $data['getProList'] = $this->productpurchase_model->getProListAssociate();
//
//        //$data['getAppList'] = $this->coupon_model->getAppName();
//
//        if ($this->input->post('submit')) {
//            $this->form_validation
//                    ->set_rules('user_id', 'User ID', 'trim|required')
//                    ->set_rules('product_list_id', 'Product Name', 'trim|required')
//                    ->set_rules('purchased_by', 'Purchased By', 'trim|required');
//
//            if ($this->form_validation->run()) {
//                $userId = $this->input->post('user_id');
//                $purchaseBy = $this->input->post('purchased_by');
//                $product_list_id = $this->input->post('product_list_id');
//                // Transaction Start
//                if ($purchaseBy == 'online paymemt') {
//                    $this->form_validation->set_rules('transaction_id', 'Transaction ID', 'trim|required')
//                            ->set_rules('reference', 'Reference', 'trim|required');
//                    if ($this->form_validation->run()) {
//                        $this->db->trans_start();
//                        $transaction_id = $this->input->post('transaction_id');
//                        $reference = $this->input->post('reference');
//                        $where = array(
//                            'trxId' => $transaction_id,
//                            'reference_no' => $reference
//                        );
//                        $paymentHistory = $this->global_model->get_data('subscription_payment_bkash_queue', $where);
//                        if ($paymentHistory) {
//                            $productInfo = $this->api_model->get_data('subscription_product_list', array('id' => $product_list_id));
//                            $productAttribueValue = $this->api_model->getProductAttributeAndValue($product_list_id);
//                            $insertId = $this->saveOrderedInfo($productAttribueValue, $product_list_id);
//                            $orderId = $this->api_model->get_data('subscription_order_list', array('id' => $insertId));
//                            $this->savePaymentHistoryInfo($orderId['order_id'], 2);
//                            $this->savePurchaseProductInfo($productAttribueValue, $productInfo, $purchaseBy);
//                        } else {
//                            $this->session->set_flashdata('error', 'Transaction id or reference not matched');
//                            //$data['error'] = 'Transantion id or reference not matched';
//                        }
//                    }
//                }
//                if ($purchaseBy == 'coupon') {
//                    $this->form_validation->set_rules('coupon_code', 'Coupon Code', 'trim|required');
//                    if ($this->form_validation->run()) {
//                        $this->db->trans_start();
//                        $coupon_code = $this->input->post('coupon_code');
//                        $couponArray = explode('-', $coupon_code);
//                        $couponSeriesId = $this->api_model->getCouponSeriesIdByAppIdAndCouponSeries($couponArray[0]);
//
//                        $result = $this->api_model->get_data('subscription_coupons', array('coupon' => $couponArray[1], 'series_id' => $couponSeriesId));
//                        if ($result['status'] == 'not used') {
//                            $userData = array(
//                                'user_id' => $this->input->post('user_id'),
//                                'coupon_code' => $couponArray[1]
//                            );
//
//                            $this->api_model->update('subscription_coupons', array('status' => 'used'), array('coupon' => $couponArray[1]));
//                            $productInfo = $this->api_model->get_data('subscription_product_list', array('id' => $result['product_list_id']));
//                            $productAttribueValue = $this->api_model->getProductAttributeAndValue($result['product_list_id']);
//                            $insertId = $this->saveOrderedInfo($productAttribueValue, $result['product_list_id']);
//                            $orderId = $this->api_model->get_data('subscription_order_list', array('id' => $insertId));
//
//                            $couponSeriesId = $this->api_model->getCouponSeriesIdByAppIdAndCouponSeries($couponArray[0]);
//                            $r = $this->api_model->get_data('subscription_coupons', array('coupon' => $couponArray[1], 'series_id' => $couponSeriesId));
//
//                            $couponRedeemedHistoryData = array(
//                                'coupon_id' => $r['id'],
//                                'order_id' => $orderId['order_id'], //not primary id
//                                'coupon_code' => $coupon_code,
//                                'user_id' => $userId,
//                                'product_id' => $result['product_list_id'],
//                                'created_date_time' => date('Y-m-d H:i:s')
//                            );
//                            $this->api_model->insert('subscription_coupon_redeemed_history', $couponRedeemedHistoryData);
//                            $this->savePaymentHistoryInfo($orderId['order_id'], 2);
//                            $this->savePurchaseProductInfo($productAttribueValue, $productInfo, $purchaseBy);
//                        } else {
//                            $this->session->set_flashdata('error', 'This coupon already used.');
//                            //$data['error'] = 'This coupon already used.';
//                        }
//                    }
//                }
//            }
//        }
//        //redirect('admin/productpurchase/manage');
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/productpurchase/add', $data);
//        $this->load->view('subscription/footer', $data);
//    }
//    private function saveOrderedInfo($productAttributeValue, $productId) {
//
//        $ordarListData = array(
//            'user_id' => $this->input->post('user_id'),
//            'order_id' => time(),
//            'order_id_prefix' => strtoupper(substr(date('D'), 0, 2)),
//            'product_id' => $productId,
//            'price' => !empty($productAttributeValue['price']) ? $productAttributeValue['price'] : 'N/A',
//            'payment_method_type_id' => 2,
//            'payment_status' => 'success',
//            'order_status' => 'completed',
//            'order_created_date_time' => date('Y-m-d H:i:s'),
//            'created_date_time' => date('Y-m-d H:i:s')
//        );
//        $this->api_model->insert('subscription_order_list', $ordarListData);
//        return $this->db->insert_id();
//    }
//
//    private function savePaymentHistoryInfo($order_id, $payment_method_type_id) {
//        $paymentHistoryData = array(
//            'order_id' => $order_id, //not primary id
//            'payment_method_type_id' => $payment_method_type_id,
//            'extra_info' => 'Product Purchase',
//            'created_date_time' => date('Y-m-d H:i:s')
//        );
//
//        $this->api_model->insert('subscription_payment_history', $paymentHistoryData);
//    }
//
//    private function savePurchaseProductInfo($productAttribueValue, $res, $paymentType) {
//
//        if (isset($productAttribueValue['unit']) && isset($productAttribueValue['validity(days)'])) {
//            if (!empty($productAttribueValue['unit']) && $productAttribueValue['unit'] == 'bond') {
//                $validity = $productAttribueValue['validity(days)'];
//                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
//            }
//        }
//
//        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
//            if ($productAttribueValue['unit'] == 'bond') {
//                $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
//            }
//        }
//
//        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
//            if ($productAttribueValue['unit'] == 'days') {
//                $validity = $productAttribueValue['unit_value'];
//                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
//            }
//        }
//
//        if (!isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
//            $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
//        }
//
//        $productPurchaseListData = array(
//            'user_id' => $this->input->post('user_id'),
//            'product_category_id' => $res['product_category_id'],
//            'product_type_id' => $res['product_type_id'],
//            'app_types_id' => $res['app_types_id'],
//            'product_id' => $res['id'],
//            'purchased_by' => $paymentType,
//            'valid_start_datetime' => date('Y-m-d H:i:s'),
//            'valid_end_datetime' => $validEndDateTime,
//            'status' => '1',
//            'created_datetime' => date('Y-m-d H:i:s')
//        );
//        $this->api_model->insert('subscription_product_purchase_list', $productPurchaseListData);
//
//        $this->db->trans_complete();
//        if ($this->db->trans_status() != FALSE) {
//            $this->session->set_flashdata('success_msg', 'Save successfull');
//            redirect('subscription/productpurchase/manage');
//        } else {
//            $this->session->set_flashdata('error_msg', 'Transaction not complite');
//            redirect('subscription/productpurchase/manage');
//        }
//    }
//
//    public function addOld() {
//        $data = array();
//        $data['title'] = 'Add';
//        $data['tab_active'] = 'purchase'; 
//
//        //get parent Category
////      $result = $this->productpurchase_model->getProductByProductId();
//
//        $data['getProList'] = $this->productpurchase_model->getProListAssociate();
//        $data['getAppList'] = $this->coupon_model->getAppName();
//
//        if ($this->input->post('submit')) {
//            $this->form_validation
//                    ->set_rules('user_id', 'User ID', 'trim|required')
//                    ->set_rules('app_id', 'App Name', 'trim|required')
//                    ->set_rules('product_list_id', 'Product Name', 'trim|required')
//                    ->set_rules('purchased_by', 'Purchased By', 'trim|required');
//
//            if ($this->form_validation->run()) {
//
//                $save_data = array();
//                $save_data['user_id'] = $this->input->post('user_id');
//                $save_data['app_list_id'] = $this->input->post('app_id');
//                $save_data['product_list_id'] = $this->input->post('product_list_id');
//
//                $result = $this->productpurchase_model->getProjectInofByProductId($this->input->post('product_list_id'));
//
//                // from different table
//                $save_data['product_category_id'] = $result->product_category_id;
//                $save_data['product_type_id'] = $result->product_type_id;
//                $save_data['app_list_id'] = $result->app_list_id;
//                $save_data['app_types_id'] = $result->app_types_id;
//
//                $save_data['purchased_by'] = $this->input->post('purchased_by');
//                $save_data['created_datetime'] = date('Y-m-d H:i:s');
//
//                if ($this->productpurchase_model->saveProductPurchase($save_data)) {
//                    $this->session->set_flashdata('success_msg', 'Product Purchase Save Successfully...');
//                    redirect('admin/productpurchase/manage');
//                }
//            }
//        }
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/productpurchase/add', $data);
//        $this->load->view('admin/footer', $data);
//    }
    //public function products($offset = 0, $user='', $userId = '') {
    public function products() {
        $data = array();
        $data['title'] = 'Products';
        //$data['tab_active'] = 'purchase';

        $userId = NULL;
        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;
        //pagination
        $uri_segment = 4;
        $per_page = 5;
        $total_rows = $this->productpurchase_model->numberOfRows($userId);
        $offset = 0;

        if ($this->uri->segment(4) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }
        generatePagging('/subscription/productpurchase/products/', $total_rows, $per_page, $uri_segment, 2);

        $data['productPurchase'] = $this->productpurchase_model->getProductPruchase($userId, $per_page, $offset);

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
//        if (!empty($userId)) {
//            $data['user_info'] = $this->productpurchase_model->getUserInfoByUserId($userId);
//            $this->load->view('subscription/productpurchase/manage_user_view', $data);
//        } else {
        $this->load->view('subscription/productpurchase/manage', $data);
        //}
        $this->load->view('admin/footer', $data);
    }

//
//    public function edit($id) {
//        $data = array();
//        $data['title'] = 'Update';
//        $data['tab_active'] = 'purchase';
//
//        //Get Parent Category
//        $data['getProList'] = $this->productpurchase_model->getProListAssociate();
//
//        //for get app list
//        $data['getAppList'] = $this->coupon_model->getAppName();
//        //for get purchased By
//        $data['getPurchasedBy'] = $this->productpurchase_model->getPurchase();
//
//        $aProduct = $this->productpurchase_model->editProPurchase($id);
//
//
//        $data['id'] = $aProduct->id;
//        $data['user_id'] = $aProduct->user_id;
//        $data['app_list_id'] = $aProduct->app_list_id;
//        $data['product_list_id'] = $aProduct->product_id;
//        $data['purchased_by'] = $aProduct->purchased_by;
//
//
//        //app name edit for ajax
//        if ($aProduct->app_list_id) {
//            $product_list = $this->coupon_model->ajax_call_product($aProduct->app_list_id);
//
//            $product_list_option = array();
//            foreach ($product_list as $show) {
//                $product_list_option[] = array('id' => $show->id, 'name' => $show->name);
//            }
//            $data['product_list'] = $product_list_option;
//        }
//
//
//        if ($this->input->post('update')) {
//
//            $this->form_validation
//                    ->set_rules('user_id', 'User ID', 'trim|required')
//                    ->set_rules('product_list_id', 'Product List', 'trim|required')
//                    ->set_rules('purchased_by', 'Purchased By', 'trim|required');
//
//            if ($this->form_validation->run()) {
//
//                $save_data = array();
//                $save_data['user_id'] = $this->input->post('user_id');
//
//                $result = $this->productpurchase_model->getProjectInofByProductId($this->input->post('product_list_id'));
//                // from different table
//                $save_data['product_category_id'] = $result->product_category_id;
//                $save_data['product_type_id'] = $result->product_type_id;
//                $save_data['app_list_id'] = $result->app_list_id;
//                $save_data['app_types_id'] = $result->app_types_id;
//                $save_data['product_id'] = $this->input->post('product_list_id');
//                $save_data['purchased_by'] = $this->input->post('purchased_by');
//                $save_data['created_datetime'] = date('Y-m-d H:i:s');
//
//                if ($this->productpurchase_model->updateProPurchase($save_data, $id)) {
//                    $this->session->set_flashdata('success_msg', 'Update Successfully...');
//                    redirect('admin/productpurchase/manage');
//                }
//            }
//        }
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('admin/productpurchase/edit', $data);
//        $this->load->view('admin/footer', $data);
//    }
    //  .........view data by modal..........
    public function view($id) {
        $data = array();
        $data['title'] = 'View';
        $data['tab_active'] = 'purchase';

        $data['productPurchaseInfo'] = $this->productpurchase_model->viewProductPurchase($id);

//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/productpurchase/view', $data);
//        $this->load->view('subscription/footer', $data);
    }

    //    .... delete....
    public function delete($id) {

        if ($this->productpurchase_model->deleteProPurchase($id)) {
            $this->session->set_flashdata('success_msg', 'Delete Successfully...');
            redirect('admin/productpurchase/manage');
        }
    }

}
