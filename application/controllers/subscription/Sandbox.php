<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-05-16
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Sandbox extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }

        $this->load->model('subscription/dashboard_model');
    }

    public function __destruct() {
        $this->db->close();
    }

//    public function index() {
//        $data = array();
//        $data['title'] = 'Sandbox';
//        //$data['tab_active'] = 'Sandbox';
//
//        $data['sand_box_info'] = $totalCoupons = $this->dashboard_model->sanboxData();
//
//        $this->load->view('subscription/dashboard_header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('admin/sidebar', $data);
//        //$this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/sandbox', $data);
//        $this->load->view('subscription/dashboard_footer', $data);
//    }
//
//    public function sandbox_mood_control($id, $status) {
//        if ($this->dashboard_model->updateSandboxMood($id, $status)) {
//            redirect('subscription/sandbox');
//        }
//    }

}
