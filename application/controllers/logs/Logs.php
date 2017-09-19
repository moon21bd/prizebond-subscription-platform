<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/Main.php';

class Logs extends Main {

    public function __construct() {
        parent::__construct();
        if ($this->checkHost()) {
            $this->checkAuth();
        }
        $this->initDateRange();
        $this->load->model('logs/error_log_model');
        $this->load->helper('user_profile_info_helper');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
    }

    public function index() {
        $data = array();
        $data['title'] = 'User Acivity Logs';
        $data['tab_active'] = 'useractivitylog';

        $uri_segment = 5;
        $limit = 20;
        $userId = NULL;

        if ($this->uri->segment(5) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(5) ? $this->uri->segment(5) : 0;
        }


        $searchQuery = NULL;

        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');
        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;

        if ($this->input->get()) {
            $getData = array();
            foreach ($this->input->get() as $key => $value) {
                $getData[$key] = $value;
            }

            $this->form_validation->set_data($getData);

            if ($this->input->get('search') == 1) {
                $this->form_validation->set_rules('search_query', '', 'trim');
            }
            if ($this->form_validation->run() == TRUE) {
                $searchQuery = trim($this->input->get('search_query'));
            } else {
                echo validation_errors();
            }
        }

        $result = $this->error_log_model->getUsersActivityLogs($userId, $limit, $offset, $startDate, $endDate, $searchQuery);

        $data['usersLogList'] = $result['result'];

        $data['paymentMethod'] = '';
        $total_rows = $result['total'];;
        //$total_rows = $this->error_log_model->numberOfRows('user_activity_logs');


        generatePagging('/logs/logs/index/offset/', $total_rows, $limit, $uri_segment, 3);

       //echo $this->db->last_query();
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/user_activity_log', $data);
        $this->load->view('admin/footer', $data);
    }

    public function view($id) {
        $data = array();
        $data['userActivityLog'] = $this->error_log_model->viewUserActivityLog($id);
        $this->load->view('admin/logs/user_activity_log_view', $data);
    }

    private function initDateRange() {
        if (empty($this->session->userdata('start_date')) && (empty($this->session->userdata('end_date')))) {
            $this->session->set_userdata('start_date', date('Y-m-d', strtotime('-30 Days')));
            $this->session->set_userdata('end_date', date('Y-m-d'));
        }
    }

    public function suspiciousActivityByDeviceUUID() {

        /*
          SELECT `device_uuid`,user_id FROM device_info WHERE device_status = 'active' AND user_id != 0 GROUP BY `device_uuid` HAVING count(*) > 1
          15 total

          SELECT `device_uuid`,user_id FROM device_info WHERE user_id != 0 GROUP BY `device_uuid` HAVING count(*) > 1
          189 total
         */
        //echo "Following users used same device to register / login multiple accounts :<br>";
        $data = array();
        $result = array();
        $data['title'] = 'Suspicious Activity By Device UUID';
        $today = date("Y-m-d");
        $query = $this->db->query("SELECT `device_uuid`,user_id FROM device_info WHERE user_id != 0 GROUP BY `device_uuid` HAVING count(*) > 1");

        if ($query->num_rows()) {
            foreach ($query->result() as $row) {

                $query2 = $this->db->query("SELECT user_id FROM device_info WHERE user_id != 0 AND device_uuid = '$row->device_uuid'");
                foreach ($query2->result() as $row2) {

                    $query3 = $this->db->query("SELECT * FROM user WHERE id = '$row2->user_id'");
                    foreach ($query3->result() as $row3) {

                        if ($row3->user_type_id == 1) {
                            $type = 'customer';
                        } else {
                            $type = 'developer';
                        }

                        $totalSubscription = 0;
                        $query4 = $this->db->query("SELECT count(id) AS total_subscription FROM subscription_product_purchase_list WHERE product_id != '1' AND  user_id = '$row2->user_id' AND valid_end_datetime >= '$today'");
                        foreach ($query4->result() as $row4) {
                            $totalSubscription = $totalSubscription + $row4->total_subscription;
                        }

                        $result[$row->device_uuid][] = array(
                            'id' => $row3->id,
                            'user_id' => $row3->user_id,
                            'name' => $row3->name,
                            'email' => $row3->email,
                            'mobile_number' => $row3->mobile_number,
                            'type' => $type,
                            'status' => $row3->status,
                            'verified' => $row3->verify_status,
                            'total_valid_subscription' => $totalSubscription,
                        );
                    }
                }
            }
        }

        $data['suspiciousUsers'] = $result;

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/suspicious _activity', $data);
        $this->load->view('admin/footer', $data);
    }

    public function changeUserStatus($userId, $status) {

        if ($this->error_log_model->updateUserStatus($userId, $status)) {
            $this->session->set_flashdata('success_msg', 'Successfully updated');
            redirect('logs/logs/suspiciousActivityByDeviceUUID');
        } else {
            $this->session->set_flashdata('err_msg', 'Failed to update');
            redirect('logs/logs/suspiciousActivityByDeviceUUID');
        }
    }

    public function userFlagLogs() {
        $data = array();
        $data['title'] = 'Users Flag Log';
        $uri_segment = 5;
        $limit = 20;
        $offset = 0;
        $searchInfo = '';
        $userId = NULL;
        if ($this->uri->segment(5) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(5) ? $this->uri->segment(5) : 0;
        }
        if ($this->input->get('search') == 1) {
            if ($this->input->get('search_info')) {
                $searchInfo = $this->input->get('search_info');
            }
        }
        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;
        

        
        $result = $this->error_log_model->getUserFlagLogs($userId, $limit, $offset, $searchInfo);
        //echo $this->db->last_query();
        $total_rows = $result['totalRow'];
        generatePagging('/logs/logs/userFlagLogs/offset/', $total_rows, $limit, $uri_segment, 4);
        $data['userFlagLog'] = $result['result'];


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/user_flag_logs', $data);
        $this->load->view('admin/footer', $data);
    }
    
    public function smsHistory() {
        $data = array();
        $data['title'] = 'SMS History';
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');
        //pagination
        $uri_segment = 4;
        $per_page = 20;
        if ($this->uri->segment(4) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }
        $result = $this->error_log_model->getSmsResponseList($per_page, $offset, $startDate, $endDate);
         $data['smsList'] = $result['result'];
        generatePagging('/logs/logs/smsHistory/', $result['totalRow'], $per_page, $uri_segment, 2);

        
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/logs/sms_response', $data);
        $this->load->view('admin/footer', $data);
    }

}
