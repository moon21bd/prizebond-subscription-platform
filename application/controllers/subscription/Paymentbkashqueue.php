<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Paymentbkashqueue extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('subscription/paymentbkashqueue_model');
        $this->load->helper('global');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
    }

    public function __destruct() {
        $this->db->close();
    }

    public function manage($offset = 0) {
        $data = array();
        $data['title'] = 'Manage';
        $data['tab_active'] = 'payment_bkash_queue';

        //pagination
        $uri_segment = 4;
        $per_page = 20;
        $total_rows = $this->paymentbkashqueue_model->numberOfRows();
        $data['totalRow'] = $total_rows;
        generatePagging('/subscription/paymentbkashqueue/manage', $total_rows, $per_page, $uri_segment, 2);
        $data['paymentBkash'] = $this->paymentbkashqueue_model->getAllPaymentBkashQueueList($per_page, $offset);

        $this->load->view('subscription/header', $data);
        $this->load->view('subscription/navbar', $data);
        $this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/paymentbkashqueue/manage', $data);
        $this->load->view('subscription/footer', $data);
    }

}
