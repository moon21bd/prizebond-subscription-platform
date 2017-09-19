<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-12
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Gateway extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if ($this->input->get('debug') == 1) {
            $this->output->enable_profiler(TRUE);
        }

        $this->load->model('subscription/gateway_model');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        $data = array();
        $data['title'] = 'Manage Gateway Info';
        $data['tab_active'] = 'gateway';


        $data['getGatewayInfoArray'] = $this->gateway_model->getGatewayInfo();

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/gateway/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add Gateway Info';
        $data['tab_active'] = 'gateway';

        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('gateway_name', 'Gateway Name', 'trim|in_list[bKash,sureCash,BRAC,Rocket,PayPal,2Checkout,EBL]|required')
                    ->set_rules('gateway_fee', 'Gateway Fee', 'trim|required')
                    ->set_rules('currency_format', 'Currency Format', 'trim|in_list[en_US,bn_BD,it_IT,de_DE,en_GB]');
            if ($this->form_validation->run()) {
                $save_data = array();

                $save_data['gateway_name'] = $this->input->post('gateway_name');
                $save_data['gateway_fee'] = $this->input->post('gateway_fee');
                if ($this->input->post('currency_format')) {
                    $save_data['currency_format'] = $this->input->post('currency_format');
                }

                $save_data['created_date_time'] = date('Y-m-d H:i:s');


                if ($insert_id = $this->gateway_model->saveGateway($save_data)) {
                    $this->session->set_flashdata('success_msg', 'Gateway Info Saved Successfully...');
                    redirect('subscription/gateway');
                } else {
                    $this->session->set_flashdata('error_msg', 'Gateway Info Could not Saved Successfully...');
                    redirect('subscription/gateway/add');
                }
            }
        }

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/gateway/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function addGatewayAjax() {
        
        if ($this->input->post()) {
            // write the validation rule
            $this->form_validation
                    ->set_rules('gateway_name', 'Gateway Name', 'trim|in_list[bKash,sureCash,BRAC,Rocket,PayPal,2Checkout,EBL]|required')
                    ->set_rules('gateway_fee', 'Gateway Fee', 'trim|required')
                    ->set_rules('currency_format', 'Currency Format', 'trim|in_list[en_US,bn_BD,it_IT,de_DE,en_GB]|required');
            if ($this->form_validation->run()) {
                $save_data = array();

                $save_data['gateway_name'] = $this->input->post('gateway_name');
                $save_data['gateway_fee'] = $this->input->post('gateway_fee');
                if ($this->input->post('currency_format')) {
                    $save_data['currency_format'] = $this->input->post('currency_format');
                }
                $save_data['created_date_time'] = date('Y-m-d H:i:s');

                if ($insert_id = $this->gateway_model->saveGateway($save_data)) {
                    $response['status'] = 1;
                    $response['message'] = 'Gateway Info Added Successfully';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Gateway Info Could Not Added';
                }
            } else {
                $response['status'] = 0;
                $response['message'] = validation_errors();
            }
            echo json_encode($response);
        }
    }

    // .... delete....
    public function delete($id) {

        if ($this->gateway_model->deleteGateway($id)) {
            $this->session->set_flashdata('success_msg', 'Coupon Deleted Successfully...');
            redirect('subscription/gateway');
        }
    }

//    public function edit($id) {
//        $data = array();
//        $data['title'] = 'Edit Gateway Info';
//        $data['tab_active'] = 'gateway';
//
//        $data['getGatewayInfo'] = $getGatewayInfo = $this->gateway_model->getAGatewayInfo($id);
//
//        if ($this->input->post('update')) {
//            
//            
//            $this->form_validation
//                    ->set_rules('gateway_name', 'Gateway Name', 'trim|in_list[bKash,sureCash,BRAC,Rocket,PayPal,2Checkout,EBL]|required')
//                    ->set_rules('gateway_fee', 'Gateway Fee', 'trim|required')
//                    ->set_rules('currency_format', 'Currency Format', 'trim|in_list[en_US,bn_BD,it_IT,de_DE,en_GB]');
//            if ($this->form_validation->run()) {
//                $update_data = array();
//
//                $update_data['gateway_name'] = $this->input->post('gateway_name');
//                $update_data['gateway_fee'] = $this->input->post('gateway_fee');
//                $update_data['currency_format'] = $this->input->post('currency_format');
//
//                $update_data['updated_date_time'] = date('Y-m-d H:i:s');
//
//                if ($this->gateway_model->updateGateway($update_data, $id)) {
//                    $this->session->set_flashdata('success_msg', 'Gateway Info Updated Successfully...');
//                    redirect('subscription/gateway');
//                } else {
//                    $this->session->set_flashdata('error_msg', 'Gateway Info Could not Updated...');
//                    redirect('subscription/gateway/edit/' . $id);
//                }
//            }
//        }
//        $this->load->view('admin/header', $data);
//        $this->load->view('admin/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        $this->load->view('subscription/gateway/edit', $data);
//        $this->load->view('admin/footer', $data);
//    }
    public function edit($id) {
        $data = array();
        $data['title'] = 'Edit Gateway Info';
        $data['tab_active'] = 'gateway';

        $data['getGatewayInfo'] = $getGatewayInfo = $this->gateway_model->getAGatewayInfo($id);

        
        $this->load->view('subscription/gateway/edit_modal', $data);
    }
    
    public function editGatewayAjax(){
         if ($this->input->post()) {
            // write the validation rule
            $this->form_validation
                    ->set_rules('gateway_name', 'Gateway Name', 'trim|in_list[bKash,sureCash,BRAC,Rocket,PayPal,2Checkout,EBL]|required')
                    ->set_rules('gateway_fee', 'Gateway Fee', 'trim|required')
                    ->set_rules('currency_format', 'Currency Format', 'trim|in_list[en_US,bn_BD,it_IT,de_DE,en_GB]|required');
            if ($this->form_validation->run()) {
                $update_data = array();

                //$update_data['gateway_id'] = $this->input->post('gateway_id');
                $update_data['gateway_name'] = $this->input->post('gateway_name');
                $update_data['gateway_fee'] = $this->input->post('gateway_fee');
                $update_data['currency_format'] = $this->input->post('currency_format');
                $update_data['updated_date_time'] = date('Y-m-d H:i:s');
                

                if ($this->gateway_model->updateGateway($update_data, $this->input->post('gateway_id'))) {
                    $response['status'] = 1;
                    $response['message'] = 'Gateway Info Updated Successfully';
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Gateway Info Could Not Updated';
                }
            } else {
                $response['status'] = 0;
                $response['message'] = validation_errors();
            }
            echo json_encode($response);
        }
    }

}
