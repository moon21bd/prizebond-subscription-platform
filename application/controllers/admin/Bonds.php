<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once APPPATH . 'controllers/Main.php';

class Bonds extends Main {

    function __construct() {
        parent::__construct();

        $this->load->model('admin/bond_model');
        $this->load->helper('user_profile_info_helper');


        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if ($this->input->get('debug') == 1) {
            $this->output->enable_profiler(TRUE);
        }
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        $data = array();
        $data['title'] = 'Bond List';

        $userId = NULL;
        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');

        // Search 
        $searchData = NULL;
        $prizePosition = NULL;
        $draw = NULL;
        $seriesList = NULL;
        $userType = NULL;

        if ($this->input->get()) {
            $prizePosition = trim($this->input->get('prize_position'));
            $draw = trim($this->input->get('draw'));
            $seriesList = trim($this->input->get('seriesList'));
            $userType = trim($this->input->get('user_type'));

            if ($this->input->get('search')) {
                $searchData = trim($this->input->get('search_info'));
            }
        }
        //pagination
        $uri_segment = 4;
        $per_page = 10;
        $offset = 0;
        if ($this->uri->segment(4) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }

        $result = $this->bond_model->getPrizebondList($userId, $searchData, $per_page, $offset, $startDate, $endDate, $prizePosition, $draw, $seriesList, $userType);
        $data['users_prize_bond_list'] = $result['result'];
        $data['total_rows'] = $total_rows = $result['totalRow'];

        if (!empty($userId)) {
            generatePagging('/admin/bonds/index/' . $userId, $total_rows, $per_page, $uri_segment, 2);
        } else {
            generatePagging('/admin/bonds/index/', $total_rows, $per_page, $uri_segment, 2);
        }

        $data['drawList'] = $this->bond_model->getDrawList();
        $data['seriesList'] = $this->bond_model->getSeriesList();


        $this->load->view('admin/bonds/all_bonds', $data);
    }

    public function winnerList() {
        $data = array();
        $data['title'] = 'Bond Manage';
        $data['winners_info'] = $this->bond_model->getWinnersInfo();

        $this->load->view('admin/bonds/winner_list', $data);
    }

}
