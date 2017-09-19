<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Error_log extends Main {

    public function __construct() {
        parent::__construct();
        $this->load->model('logs/error_log_model');
        $this->load->model('logs/Message_model');
        $this->load->helper('global');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
    }

    public function errorLogList($offset = 0) {

        $data = array();
        $data['title'] = 'Error Log List';

        $uriSegment = 3;
        $perPage = 20;
        $totalRows = $this->error_log_model->totalNumberOfRows();
        generatePagging('logs/error_log/errorLogList', $totalRows, $perPage, $uriSegment, 3);
        $data['getAllErrorLog'] = $this->error_log_model->errorLogList($perPage, $offset);

        //$data['notificationList'] = $this->Message_model->getNotificationsData();
//        $data['layout'] = $this->load->view('logs/error_log_list', $data, TRUE);
//        $this->load->view('template', $data);
         $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/error_log_list', $data);
        $this->load->view('admin/footer', $data);
    }

}
