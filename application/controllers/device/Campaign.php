<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . 'controllers/Main.php';

class Campaign extends Main {

    function __construct() {
        parent::__construct();
        $this->load->model('admin/campaign_model');
        $this->load->helper('user_profile_info_helper');
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }

        //Check User Auth
        if ($this->checkHost()) {
            $this->checkAuth();
        }
    }

    public function index() {

        // load the view file
        $data['title'] = 'Device List';
        $data['error'] = '';
        $data['offset'] = $offset = 0;
        $uri_segment = 4;
        if ($this->uri->segment(4) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }

        $where = array();
        //$data['start_date'] = $startDate = $this->input->get('start_date') ? $this->input->get('start_date') : date('Y-m-d');
        //$data['end_date'] = $endDate = $this->input->get('end_date') ? $this->input->get('end_date') : date('Y-m-d');
        $data['utm_campaign'] = $campaign = $this->input->get('utm_campaign') ? $this->input->get('utm_campaign') : '';
        $data['device_status'] = $device_status = $this->input->get('device_status') ? $this->input->get('device_status') : '';
        $data['utm_source'] = $campaign_source = $this->input->get('utm_source') ? $this->input->get('utm_source') : '';
        $data['campaign_medium'] = $campaign_medium = $this->input->get('utm_medium') ? $this->input->get('utm_medium') : '';
        $data['campaign_term'] = $campaign_term = $this->input->get('utm_term') ? $this->input->get('utm_term') : '';
        $data['campaign_content'] = $campaign_content = $this->input->get('utm_content') ? $this->input->get('utm_content') : '';

        $where['DATE( added_date ) >='] = $this->session->userdata('start_date');
        $where['DATE( added_date ) <='] = $this->session->userdata('end_date');
        if ($campaign) {
            $where['utm_campaign'] = $campaign;
        }
        if ($campaign_source) {
            $where['utm_source'] = $campaign_source;
        }
        if ($campaign_medium) {
            $where['utm_medium'] = $campaign_medium;
        }
        if ($campaign_term) {
            $where['utm_term'] = $campaign_term;
        }
        if ($campaign_content) {
            $where['utm_content'] = $campaign_content;
        }
        if ($device_status) {
            $where['device_status'] = $device_status;
        }

        $data['totalRow'] = $totalRow = $this->campaign_model->countTotalDevice('device_info', $where);
        $perPage = 20;

        //$data['pagination'] = createPagination(base_url('device/campaign/index'), $totalRow, $perPage, 3);
        $data['pagination'] = generatePagging('device/campaign/index', $totalRow, $perPage, $uri_segment, 4);
        $data['campaigns'] = $this->campaign_model->getCampaigns();


        $data['devices'] = $this->campaign_model->getDevices($perPage, $offset, $where);

        if ($device_status) {
            $where['device_status'] = $device_status;
        } else {
            $where['device_status'] = 'active';
        }

        $data['totalActiveRow'] = $totalRow = $this->campaign_model->countTotalDevice('device_info', $where);


        //get camp info
        $data['campaignSource'] = $this->campaign_model->getCampaignSource();
        $data['campaignMedium'] = $this->campaign_model->getCampaignMedium();
        $data['campaignTerm'] = $this->campaign_model->getCampaignTerm();
        $data['campaignContent'] = $this->campaign_model->getCampaignContent();

        $data['notificationList'] = '';
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/devicelist/campaign_device', $data);
        $this->load->view('admin/footer', $data);
    }

}
