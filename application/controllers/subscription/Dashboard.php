<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Dashboard extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/dashboard_model');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        $data = array();
        $data['title'] = 'Dashboard';
        $data['tab_active'] = 'dashboard';

        $data['total_coupons'] = $totalCoupons = $this->dashboard_model->getTotalCoupons();
        $data['total_products'] = $totalProducts = $this->dashboard_model->getTotalProducts();
        $data['total_purchesed_products'] = $totalPurchesedProducts = $this->dashboard_model->getTotalPurchesedProducts();
        $data['total_coupon_payments'] = $totalCouponPayment = $this->dashboard_model->getTotalCouponPayment();
        $data['total_bkash_payments'] = $totalTotalBkashPayment = $this->dashboard_model->getTotalBkashPayment();
        $data['total_payment_received'] = $totalCouponPayment + $totalTotalBkashPayment;


        $this->load->view('subscription/dashboard_header', $data);
        $this->load->view('subscription/navbar', $data);
        $this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/dashboard', $data);
        $this->load->view('subscription/dashboard_footer', $data);
    }

}
