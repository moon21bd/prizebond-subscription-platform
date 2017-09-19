<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Coupon extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        $this->load->model('subscription/coupon_model');
        $this->load->model('subscription/productpurchase_model');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add';
        //$data['tab_active'] = 'coupon';
        //$data['getAppList'] = $this->coupon_model->getAppName();
        $data['getCouponSeriesList'] = $this->coupon_model->getSeriesName();
        $data['getProList'] = $this->productpurchase_model->getProListAssociate();

        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('series_id', 'Series Name', 'trim|required')
                    ->set_rules('coupon_quantity', 'Coupon', 'trim|required')
                    ->set_rules('note', 'Coupon Note', 'trim')
                    ->set_rules('product_list_id', 'Product', 'trim|required');

            if ($this->form_validation->run()) {

                $save_data = array();
                for ($i = 0; $i < $this->input->post('coupon_quantity'); $i++) {

                    $save_data['series_id'] = $this->input->post('series_id');
                    $save_data['product_id'] = $this->input->post('product_list_id');
                    $save_data['note'] = $this->input->post('note');
                    $save_data['created_date_time'] = date('Y-m-d H:i:s');

                    //for coupon code genarate
                    $coupon_code = $this->random_string(4);
                    $match = $this->coupon_model->doesExists('subscription_coupons', array('coupon' => $coupon_code));
                    if ($match != TRUE) {
                        $save_data['coupon'] = $coupon_code;
                        $this->coupon_model->saveCoupon($save_data);
                    }
                }

                $this->session->set_flashdata('success_msg', 'Coupon Saved Successfully...');
                redirect('subscription/coupon/manage');
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/coupon/add', $data);
        $this->load->view('admin/footer', $data);
    }

    private function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        $coupon = strtoupper($key);

        return $coupon;
    }

    public function manage($offset = 0) {
        $data = array();
        $data['title'] = 'Manage';
        //$data['tab_active'] = 'coupon';

        $this->load->library('pagination');

        $config = array();
        $config['base_url'] = base_url() . 'subscription/coupon/manage/';
        $config['total_rows'] = $this->coupon_model->numberOfCoupons('subscription_coupons');
        $config['per_page'] = 20;
        $config['num_links'] = 2;
        $config['query_string_segment'] = 'page';

        /* This Application Must Be Used With BootStrap 3 *  */
        $config['full_tag_open'] = "<ul class='pagination'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tagl_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";


        // end of file Pagination.php 

        $this->pagination->initialize($config);

        $data['couponList'] = $this->coupon_model->getCouponSeries($config['per_page'], $offset);

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/coupon/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    public function post_status($id, $status) {
        $a = urldecode($status);
        $this->coupon_model->updateStatus($id, $a);
        redirect('subscription/coupon/manage');
    }

    // .... delete....
    public function delete($id) {

        if ($this->coupon_model->deleteCouponList($id)) {
            $this->session->set_flashdata('success_msg', 'Coupon Delete Successfully...');
            redirect('subscription/coupon/manage');
        }
    }

    public function view($id) {
        $data = array();
        $data['title'] = ' View';
        $data['tab_active'] = 'coupon';

        $data['couponInfo'] = $this->coupon_model->viewCouponSeries($id);
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/coupon/view', $data);
//        $this->load->view('subscription/footer', $data);
    }

    public function edit($id) {
        $data = array();
        $data['title'] = ' Update';
        $data['tab_active'] = 'product';

        $data['getAppList'] = $this->coupon_model->getAppName();

        //.... view data..........
        $aCoupon = $this->coupon_model->editCoupon($id);

        $data['id'] = $aCoupon->id;
        $data['series_id'] = $aCoupon->series_id;
        $data['app_id'] = $aCoupon->app_id;
        $data['coupon'] = $aCoupon->coupon;
        $data['note'] = $aCoupon->note;
        $data['status'] = $aCoupon->status;

        //app name edit for ajax
        if ($aCoupon->app_id) {
            $series_list = $this->coupon_model->ajax_call($aCoupon->app_id);
            $series_list_option = array();
            foreach ($series_list as $show) {
                $series_list_option[] = array('id' => $show->id, 'name' => $show->name);
            }
            $data['series_list'] = $series_list_option;
        }

        if ($this->input->post('submit')) {

            $this->form_validation
                    ->set_rules('app_id', 'App', 'trim|required')
                    ->set_rules('series_id', 'Series', 'trim|required')
                    ->set_rules('coupon', ' Coupon', 'trim|required|max_length[4]|alpha_numeric|strtoupper');


            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['app_id'] = $this->input->post('app_id');
                $save_data['series_id'] = $this->input->post('series_id');
                $save_data['coupon'] = $this->input->post('coupon');
                $save_data['note'] = $this->input->post('note');
                $save_data['status'] = $this->input->post('status');
                $save_data['created_date_time'] = date('Y-m-d H:i:s');



                if ($this->coupon_model->updateCoupon($save_data, $id)) {
                    $this->session->set_flashdata('success_msg', 'Coupon Update Successfully...');
                    redirect('subscription/coupon/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/coupon/edit', $data);
        $this->load->view('admin/footer', $data);
    }

    public function load_app() {
        $app_id = $this->input->post('app_name');
        $series_list = $this->coupon_model->ajax_call($app_id);
        $product_list = $this->coupon_model->ajax_call_product($app_id);



        $series_list_option = '<option value="" selected="selected">--Select--</option>';
        foreach ($series_list as $show) {
            $series_list_option .= '<option value="' . $show->id . '">' . $show->name . '</option>';
        }

        $product_list_option = '<option value="" selected="selected">--Select--</option>';
        foreach ($product_list as $show) {
            $product_list_option .= '<option value="' . $show->id . '">' . $show->name . '</option>';
        }

        $response = array();
        $response['series_list'] = $series_list_option;
        $response['product_list'] = $product_list_option;

        echo json_encode($response);
    }

}
