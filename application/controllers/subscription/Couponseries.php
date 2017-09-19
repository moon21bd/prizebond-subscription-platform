<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Couponseries extends Main {

    public function __construct() {
        parent::__construct();
        
        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/couponseries_model');
    }
    public function __destruct() {
        $this->db->close();
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add';
        $data['tab_active'] = 'series';

        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('name', 'Coupon Series', 'trim|required');
            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['name'] = $this->input->post('name');

                
                if ($this->couponseries_model->saveCouponSeries($save_data)) {
                    $this->session->set_flashdata('success_msg', 'Coupon Series Saved Successfully...');
                    redirect('subscription/couponseries/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/couponseries/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function manage() {
        $data = array();
        $data['title'] = 'Manage';
        //$data['tab_active'] = 'series';

        $data['couponseriesList'] = $this->couponseries_model->getCouponSeries();

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/couponseries/manage', $data);
        $this->load->view('admin/footer', $data);
    }

}
