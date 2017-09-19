<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Paymenthistory extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }

        $this->load->model('subscription/paymenthistory_model');
        $this->load->helper('global');
        //$this->load->model('admin/orderlist_model');
    }

    public function __destruct() {
        $this->db->close();
    }

//    public function add() {
//        $data = array();
//        $data['title'] = 'Add';
//        $data['tab_active'] = 'payment';
//
//        //Get Order List
//        $data['getOrderList'] = $this->paymenthistory_model->getOrderList();
//        $data['getPaymentType'] = $this->paymenthistory_model->getPaymentType();
//
//        if ($this->input->post('submit')) {
//            $this->form_validation
//                    ->set_rules('order_id', 'Order', 'trim|required')
//                    ->set_rules('payment_method_type_id', 'Payment Type', 'trim|required')
//                    ->set_rules('extra_info', 'Extra Information', 'trim|required');
//            if ($this->form_validation->run()) {
//                $save_data = array();
//                $save_data['order_id'] = $this->input->post('order_id');
//                $save_data['payment_method_type_id'] = $this->input->post('payment_method_type_id');
//                $save_data['extra_info'] = $this->input->post('extra_info');
//                $save_data['created_date_time'] = date('Y-m-d H:i:s');
//
//                if ($this->paymenthistory_model->savePaymentHistory($save_data)) {
//                    $this->session->set_flashdata('success_msg', 'Payment History Save Successfully.');
//                    redirect('subscription/paymenthistory/manage');
//                }
//            }
//        }
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/paymenthistory/add', $data);
//        $this->load->view('subscription/footer', $data);
//    }

    public function payments($offset = 0) {
        $data = array();
        $data['title'] = ' Manage';
        // $data['tab_active'] = 'payment';
        //pagination
        $uri_segment = 4;
        $per_page = 20;
        $total_rows = $this->paymenthistory_model->numberOfRows();
        generatePagging('/subscription/paymenthistory/payments', $total_rows, $per_page, $uri_segment, 4);

        $data['paymentInfo'] = $this->paymenthistory_model->managePaymentHistory($per_page, $offset);

        // Search 
        $created = array();
        $start_date = 0;
        $end_date = 0;
        $serchData = array();
        if ($this->input->get('search')) {
            $serchData['order_id'] = $this->input->get('order_id_search');
            $created['start_date'] = $this->input->get('start_date');
            $created['end_date'] = $this->input->get('end_date');
            $serchData['start_date'] = $start_date = date('Y-m-d', strtotime($created['start_date']));
            $serchData['end_date'] = $end_date = date('Y-m-d', strtotime($created['end_date']));


            $total_rows = $this->paymenthistory_model->numberOfRows($serchData);
            generatePagging('/subscription/paymenthistory/payments', $total_rows, $per_page, $uri_segment, 4);

            $data['paymentInfo'] = $this->paymenthistory_model->managePaymentHistory($per_page, $offset, $serchData);
        }


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        // $this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/paymenthistory/manage', $data);
        $this->load->view('subscription/footer', $data);
    }

//    public function edit($id) {
//        $data = array();
//        $data['title'] = 'Edit';
//        $data['tab_active'] = 'payment';
//
//        //.... view data..........
//        //Get Order List
//        $data['getOrderList'] = $this->paymenthistory_model->getOrderList();
//
//        //get selected payment type data
//        $data['getPaymentType'] = $this->paymenthistory_model->getPaymentType();
//
//        $aHistory = $this->paymenthistory_model->getSinglePayment($id);
//
//
//
//        $data['order_id'] = $aHistory->order_id;
//        $data['payment_method_type_id'] = $aHistory->payment_method_type_id;
//        $data['extra_info'] = $aHistory->extra_info;
////        $data['order_id'] = $aHistory['order_id'];
////        $data['payment_method_type_id'] = $aHistory['payment_method_type_id'];
////        $data['extra_info'] = $aHistory['extra_info'];
//
//        if ($this->input->post('update')) {
//
//            $this->form_validation
//                    ->set_rules('order_id', 'Order Id', 'trim|required')
//                    ->set_rules('payment_method_type_id', 'Payment Type', 'trim|required')
//                    ->set_rules('extra_info', 'Extra Information', 'trim|required');
//
//            $save_data = array();
//            $save_data['order_id'] = $this->input->post('order_id');
//            $save_data['payment_method_type_id'] = $this->input->post('payment_method_type_id');
//            $save_data['extra_info'] = $this->input->post('extra_info');
//            //$save_data['created_date_time'] = date('Y-m-d H:i:s');
//
//
//            if ($this->paymenthistory_model->updatePaymentHistory($save_data, $id)) {
//                $this->session->set_flashdata('success_msg', 'Update Successfully...');
//                redirect('subscription/paymenthistory/manage');
//            }
//        }
//
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/paymenthistory/edit', $data);
//        $this->load->view('subscription/footer', $data);
//    }
    //  .........view data..........
    public function view($id) {
        $data = array();
        $data['title'] = 'View';
        $data['tab_active'] = 'payment';

        $data['paymentHistoryInfo'] = $this->paymenthistory_model->viewPaymentHistory($id);


        $this->load->view('subscription/paymenthistory/view', $data);
    }

    //    .... delete....
    public function delete($id) {

        if ($this->paymenthistory_model->deletePaymentHistory($id)) {
            $this->session->set_flashdata('success_msg', 'Delete Successfully...');
            redirect('subscription/paymenthistory/manage');
        }
    }

    public function order_info($order_id) {
        $data = array();
        $data['title'] = 'View';
        $data['tab_active'] = 'payment';
        $data['orderListInfo'] = $this->paymenthistory_model->viewOrderList($order_id);
        $this->load->view('subscription/paymenthistory/order_detail', $data);
    }

}
