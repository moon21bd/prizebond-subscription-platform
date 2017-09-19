<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Couponredeemedhistory extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        $this->load->model('subscription/couponredeemedhistory_model');
        $this->load->helper('global');
    }

    public function __destruct() {
        $this->db->close();
    }

//
//    public function add() {
//        $data = array();
//        $data['title'] = 'Add';
//        $data['tab_active'] = 'redeemed';
//        //Get Order List
//        $data['getCoupons'] = $this->couponredeemedhistory_model->getCouponsByCouponsId();
//        
//        $data['getOrder'] = $this->couponredeemedhistory_model->getOrderByOrderId();
//     
//
//        if ($this->input->post('submit')) {
//            $this->form_validation
//                    ->set_rules('coupon_id', 'Coupons', 'trim|required')
//                    ->set_rules('order_id', 'Order', 'trim|required');
//
//            if ($this->form_validation->run()) {
//                $save_data = array();
//                $save_data['coupon_id'] = $this->input->post('coupon_id');
//                $save_data['order_id'] = $this->input->post('order_id');
//                $save_data['created_date_time'] = date('Y-m-d H:i:s');
//
//
//                if ($this->couponredeemedhistory_model->saveCouponRedeemedHistory($save_data)) {
//                    $this->session->set_flashdata('success_msg', 'Coupon Redeemed History Save Successfully.');
//                    redirect('subscription/couponredeemedhistory/manage');
//                }
//            }
//        }
//        
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/couponredeemedhistory/add', $data);
//        $this->load->view('subscription/footer', $data);
//    }

    public function coupons($offset = 0) {
        $data = array();
        $data['title'] = ' Manage';
        //$data['tab_active'] = 'redeemed';
        //pagination
        $uri_segment = 4;
        $per_page = 10;
        $total_rows = $this->couponredeemedhistory_model->numberOfRows();

        generatePagging('subscription/couponredeemedhistory/coupons', $total_rows, $per_page, $uri_segment, 3);
        $data['getCouponRedeemed'] = $this->couponredeemedhistory_model->getCouponRedeemedHistory($per_page, $offset);

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/couponredeemedhistory/manage', $data);
        $this->load->view('admin/footer', $data);
    }

//    public function edit($id) {
//        $data = array();
//        $data['title'] = 'Edit';
//        $data['tab_active'] = 'redeemed';
//
//
//        //get selected payment type data
//        //$data['getCoupons'] = $this->couponredeemedhistory_model->getCouponRedeemedHistory();
//        $data['getCoupons'] = $this->couponredeemedhistory_model->getCouponsByCouponsId();
//        $data['getOrder'] = $this->couponredeemedhistory_model->getOrderByOrderId();
//
//        $aRedeemed = $this->couponredeemedhistory_model->getSingleCouponRedeemedHistory($id);
//     
//        //$data['id'] = $aHistory->id;
//        $data['coupon_id'] = $aRedeemed->coupon_id;
//        $data['order_id'] = $aRedeemed->order_id;
//
//        if ($this->input->post('update')) {
//
//            $this->form_validation
//                    ->set_rules('coupon_id', 'Coupons', 'trim|required')
//                    ->set_rules('order_id', 'Order', 'trim|required');
//
//            $save_data = array();
//            $save_data['coupon_id'] = $this->input->post('coupon_id');
//            $save_data['order_id'] = $this->input->post('order_id');
//            $save_data['created_date_time'] = date('Y-m-d H:i:s');
//
//
//            if ($this->couponredeemedhistory_model->updateCouponRedeemedHistory($save_data, $id)) {
//                $this->session->set_flashdata('success_msg', 'Update Successfully...');
//                redirect('subscription/couponredeemedhistory/manage');
//            }
//        }
//
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/couponredeemedhistory/edit', $data);
//        $this->load->view('subscription/footer', $data);
//    }
    //    .... delete....
    public function delete($id) {

        if ($this->couponredeemedhistory_model->deleteCouponRedeemedHistory($id)) {
            $this->session->set_flashdata('success_msg', 'Delete Successfully...');
            redirect('subscription/couponredeemedhistory/manage');
        }
    }

    public function product_info($product_id) {
        $data = array();
        $data['title'] = 'Edit';
        $data['tab_active'] = 'redeemed';
        $data['product_info'] = $this->couponredeemedhistory_model->getProductInfoByProductId($product_id);

        $this->load->view('subscription/couponredeemedhistory/product_info', $data);
    }

}
