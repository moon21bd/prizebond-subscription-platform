<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-31
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once APPPATH . 'controllers/Main.php';

class Draw extends Main {

    function __construct() {
        parent::__construct();

        $this->load->model('admin_model');

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
    }

    public function index() {
        $data = array();
        $data['title'] = 'Draw Information Manage';

        $data['all_prize_bond_info'] = $this->admin_model->getPrizeBondDrawInfo();
        $this->load->view('admin/draw_info/manage', $data);
    }

    public function viewAPrizeBondDrawInfo($id) {
        $title = array();
        $title['title'] = 'View of Draw Information';
        $data = array();

        $data['single_prize_bond_info'] = $this->admin_model->getPrizeBondInfo($id);

        $first_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '1st');
        $second_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '2nd');
        $third_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '3rd');
        if (isset($third_prize_bond_number)) {
            foreach ($third_prize_bond_number as $third_data) {
                $third_winner_num[] = $third_data->bond_number;
            }
        }

        $fourth_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '4th');
        if (isset($fourth_prize_bond_number)) {
            foreach ($fourth_prize_bond_number as $fourth_data) {
                $fourth_winner_num[] = $fourth_data->bond_number;
            }
        }

        $fifth_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '5th');
        if (isset($fifth_prize_bond_number)) {
            foreach ($fifth_prize_bond_number as $fifth_data) {
                $fifth_winner_num[] = $fifth_data->bond_number;
            }
        }
        $data['first_prize_bond_number'] = $first_prize_bond_number[0]->bond_number;
        $data['second_prize_bond_number'] = $second_prize_bond_number[0]->bond_number;
        $data['third_prize_bond_number'] = implode(',', $third_winner_num);
        $data['fourth_prize_bond_number'] = implode(',', $fourth_winner_num);
        $data['fifth_prize_bond_number'] = implode(',', $fifth_winner_num);

        $this->load->view('admin/header', $title);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/draw_info/view', $data);
        $this->load->view('admin/footer', $data);
    }

    public function editPrizeBondDrawInfo($id) {

        $title = array();
        $title['title'] = 'Edit Draw Information';

        $data['single_prize_bond_info'] = $this->admin_model->getPrizeBondInfo($id);
        $first_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '1st');
        $second_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '2nd');
        $third_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '3rd');
        if (!empty($third_prize_bond_number)) {
            foreach ($third_prize_bond_number as $third_data) {
                $third_winner_num[] = $third_data->bond_number;
            }
        }

        $fourth_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '4th');
        if (!empty($fourth_prize_bond_number)) {
            foreach ($fourth_prize_bond_number as $fourth_data) {
                $fourth_winner_num[] = $fourth_data->bond_number;
            }
        }

        $fifth_prize_bond_number = $this->admin_model->getPrizeBondNumber($id, '5th');
        if (!empty($fifth_prize_bond_number)) {
            foreach ($fifth_prize_bond_number as $fifth_data) {
                $fifth_winner_num[] = $fifth_data->bond_number;
            }
        }



        $data['first_prize_bond_number'] = $first_prize_bond_number[0]->bond_number;
        $data['second_prize_bond_number'] = $second_prize_bond_number[0]->bond_number;
        $data['third_prize_bond_number'] = implode(',', $third_winner_num);
        $data['fourth_prize_bond_number'] = implode(',', $fourth_winner_num);
        $data['fifth_prize_bond_number'] = implode(' ', $fifth_winner_num);


        if ($this->input->post('update')) {

            $this->form_validation->set_rules('result_date', 'Result Date', 'trim|required');
            $this->form_validation->set_rules('result_series', 'Result Series', 'trim|required');
            $this->form_validation->set_rules('result_number', 'Result Number', 'trim|required');

            $this->form_validation->set_rules('bond_value', 'bond value', 'trim|required');
            $this->form_validation->set_rules('total_series_number', 'Total Series Number', 'trim|required');

            $this->form_validation->set_rules('first_prize_number', 'First Prize Number', 'trim|required');
            $this->form_validation->set_rules('second_prize_number', 'Second Prize Number', 'trim|required');
            $this->form_validation->set_rules('third_prize_number', 'Third Prize Number', 'trim|required');
            $this->form_validation->set_rules('fourth_prize_number', 'Fourth Prize Number', 'trim|required');
            $this->form_validation->set_rules('fifth_prize_number', 'Fifth Prize Number', 'trim|required');

            $this->form_validation->set_rules('total_first_prize_number', 'Total First Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_second_prize_number', 'Total Second Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_third_prize_number', 'Total Third Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_fourth_prize_number', 'Total Fourth Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_fifth_prize_number', 'Total Fifth Prize Number', 'trim|required');

            $this->form_validation->set_rules('total_prize_number', 'Total Prize Bond Number', 'trim|required');

            $this->form_validation->set_rules('first_prize_value_tk', 'First Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('second_prize_value_tk', 'Second Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('third_prize_value_tk', 'Third Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('fourth_prize_value_tk', 'Fourth Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('fifth_prize_value_tk', 'Fifth Prize Bond Money', 'trim|required');


            $updateData = array();
            $updateData['result_date'] = $this->input->post('result_date');
            $updateData['result_series'] = $result_series = $this->input->post('result_series');
            $updateData['result_number'] = $this->input->post('result_number');
            $updateData['bond_value'] = $this->input->post('bond_value');
            $updateData['total_series_number'] = $this->input->post('total_series_number');
            $updateData['first_prize_number'] = $this->input->post('first_prize_number');
            $updateData['second_prize_number'] = $this->input->post('second_prize_number');
            $updateData['third_prize_number'] = $this->input->post('third_prize_number');
            $updateData['fourth_prize_number'] = $this->input->post('fourth_prize_number');
            $updateData['fifth_prize_number'] = $this->input->post('fifth_prize_number');

            $updateData['total_first_prize_number'] = $this->input->post('total_first_prize_number');
            $updateData['total_second_prize_number'] = $this->input->post('total_second_prize_number');
            $updateData['total_third_prize_number'] = $this->input->post('total_third_prize_number');
            $updateData['total_fourth_prize_number'] = $this->input->post('total_fourth_prize_number');
            $updateData['total_fifth_prize_number'] = $this->input->post('total_fifth_prize_number');

            $updateData['total_prize_number'] = $this->input->post('total_prize_number');

            $updateData['first_prize_value_tk'] = $this->input->post('first_prize_value_tk');
            $updateData['second_prize_value_tk'] = $this->input->post('second_prize_value_tk');
            $updateData['third_prize_value_tk'] = $this->input->post('third_prize_value_tk');
            $updateData['fourth_prize_value_tk'] = $this->input->post('fourth_prize_value_tk');
            $updateData['fifth_prize_value_tk'] = $this->input->post('fifth_prize_value_tk');

            $first_prize_data = array();
            $first_prize_data['bond_number'] = $this->input->post('first_prize_bond_number');

            $second_prize_data = array();
            $second_prize_data['bond_number'] = $this->input->post('second_prize_bond_number');

            $third_prize_data = array();
            $fourth_prize_data = array();
            $fifth_prize_data = array();



            if ($this->form_validation->run()) {

                if ($this->admin_model->updatePrizeBondInfo($updateData, $id)) {
                    //updating series list table
                    if (strpos($result_series, ',') == TRUE) {
                        $explode = explode(',', $result_series);
                        foreach ($explode as $value) {
                            if (!($this->global_model->doesExist('series_list', array('name' => trim($value))))) {
                                $this->global_model->insert('series_list', array('name' => trim($value)));
                            }
                        }
                    }

                    $this->admin_model->updatePrizeBondNmber($id, '1st', $first_prize_data);
                    $this->admin_model->updatePrizeBondNmber($id, '2nd', $second_prize_data);


                    // Delete exsisting by prize bond id
                    $this->global_model->delete('prizebond_result_bond_list', array('prizebond_result_info_id' => $id, 'prize_position' => '3rd'));
                    $third_prize_num_array = explode(",", $this->input->post('third_prize_bond_number'));
                    foreach ($third_prize_num_array as $third_prize_num) {
                        $third_prize_data['prizebond_result_info_id'] = $id;
                        $third_prize_data['prize_position'] = '3rd';
                        $third_prize_data['bond_number'] = $third_prize_num;
                        $this->admin_model->saveThirdPrizeBondInfo($third_prize_data);
                    }

                    // Delete exsisting by prize bond id
                    $this->global_model->delete('prizebond_result_bond_list', array('prizebond_result_info_id' => $id, 'prize_position' => '4th'));
                    $fourth_prize_num_array = explode(",", $this->input->post('fourth_prize_bond_number'));
                    foreach ($fourth_prize_num_array as $fourth_prize_num) {
                        $fourth_prize_data['prizebond_result_info_id'] = $id;
                        $fourth_prize_data['prize_position'] = '4th';
                        $fourth_prize_data['bond_number'] = $fourth_prize_num;
                        $this->admin_model->saveFourthPrizeBondInfo($fourth_prize_data);
                    }

                    // Delete exsisting by prize bond id
                    $this->global_model->delete('prizebond_result_bond_list', array('prizebond_result_info_id' => $id, 'prize_position' => '5th'));
                    $fifth_prize_num_array = explode(" ", $this->input->post('fifth_prize_bond_number'));
                    foreach ($fifth_prize_num_array as $fifth_prize_num) {
                        $fifth_prize_data['prizebond_result_info_id'] = $id;
                        $fifth_prize_data['prize_position'] = '5th';
                        $fifth_prize_data['bond_number'] = $fifth_prize_num;
                        $this->admin_model->saveFifthPrizeBondInfo($fifth_prize_data);
                    }



                    $this->session->set_flashdata('success_msg', 'Your information has been successfully Updated.');
//                    redirect('admin/managePrizeBondDrawInfo');
                    redirect('admin/draw');
                }
            }
        }

        $this->load->view('admin/header', $title);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/draw_info/edit', $data);
        $this->load->view('admin/footer', $data);
    }

    public function deletePrizebondDrawInfo($id) {
        if ($this->global_model->delete('prizebond_result_info', array('id' => $id))) {
            $this->global_model->delete('prizebond_result_bond_list', array('prizebond_result_info_id' => $id));
            $this->session->set_flashdata('success_msg', 'Your information has been successfully Deleted.');
            redirect('admin/draw');
        }
    }

    public function addPrizeBondDrawInfo() {

        $title = array();
        $title['title'] = 'Add Draw Information';
        $first_prize_data = array();
        $second_prize_data = array();
        $third_prize_data = array();
        $fourth_prize_data = array();
        $fifth_prize_data = array();
        $third_prize_num_array = array();
        $fourth_prize_num_array = array();
        $fifth_prize_num_array = array();

        if ($this->input->post('add')) {

            $this->form_validation->set_rules('result_date', 'Result Date', 'trim|required');
            $this->form_validation->set_rules('result_series', 'Result Series', 'trim|required');
            $this->form_validation->set_rules('result_number', 'Result Number', 'trim|required');

            $this->form_validation->set_rules('bond_value', 'bond value', 'trim|required');
            $this->form_validation->set_rules('total_series_number', 'Total Series Number', 'trim|required');

            $this->form_validation->set_rules('first_prize_number', 'First Prize Number', 'trim|required');
            $this->form_validation->set_rules('second_prize_number', 'Second Prize Number', 'trim|required');
            $this->form_validation->set_rules('third_prize_number', 'Third Prize Number', 'trim|required');
            $this->form_validation->set_rules('fourth_prize_number', 'Fourth Prize Number', 'trim|required');
            $this->form_validation->set_rules('fifth_prize_number', 'Fifth Prize Number', 'trim|required');

            $this->form_validation->set_rules('total_first_prize_number', 'Total First Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_second_prize_number', 'Total Second Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_third_prize_number', 'Total Third Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_fourth_prize_number', 'Total Fourth Prize Number', 'trim|required');
            $this->form_validation->set_rules('total_fifth_prize_number', 'Total Fifth Prize Number', 'trim|required');

            $this->form_validation->set_rules('total_prize_number', 'Total Prize Bond Number', 'trim|required');

            $this->form_validation->set_rules('first_prize_value_tk', 'First Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('second_prize_value_tk', 'Second Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('third_prize_value_tk', 'Third Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('fourth_prize_value_tk', 'Fourth Prize Bond Money', 'trim|required');
            $this->form_validation->set_rules('fifth_prize_value_tk', 'Fifth Prize Bond Money', 'trim|required');



            $data['result_date'] = $this->input->post('result_date');
            $data['result_series'] = $result_series = $this->input->post('result_series');
            $data['result_number'] = $this->input->post('result_number');
            $data['bond_value'] = $this->input->post('bond_value');
            $data['total_series_number'] = $this->input->post('total_series_number');
            $data['first_prize_number'] = $this->input->post('first_prize_number');
            $data['second_prize_number'] = $this->input->post('second_prize_number');
            $data['third_prize_number'] = $this->input->post('third_prize_number');
            $data['fourth_prize_number'] = $this->input->post('fourth_prize_number');
            $data['fifth_prize_number'] = $this->input->post('fifth_prize_number');

            $data['total_first_prize_number'] = $this->input->post('total_first_prize_number');
            $data['total_second_prize_number'] = $this->input->post('total_second_prize_number');
            $data['total_third_prize_number'] = $this->input->post('total_third_prize_number');
            $data['total_fourth_prize_number'] = $this->input->post('total_fourth_prize_number');
            $data['total_fifth_prize_number'] = $this->input->post('total_fifth_prize_number');

            $data['total_prize_number'] = $this->input->post('total_prize_number');

            $data['first_prize_value_tk'] = $this->input->post('first_prize_value_tk');
            $data['second_prize_value_tk'] = $this->input->post('second_prize_value_tk');
            $data['third_prize_value_tk'] = $this->input->post('third_prize_value_tk');
            $data['fourth_prize_value_tk'] = $this->input->post('fourth_prize_value_tk');
            $data['fifth_prize_value_tk'] = $this->input->post('fifth_prize_value_tk');



            if ($this->form_validation->run() == TRUE) {

                if ($this->global_model->doesExist('prizebond_result_info', array('result_number' => $this->input->post('result_number'))) == FALSE) {
                    if ($insertedId = $this->admin_model->savePrizeBondInfo($data)) {

                        //updating series list table
                        if (strpos($result_series, ',') == TRUE) {
                            $resultSeriesArray = array_map("trim", explode(',', $result_series));

                            foreach ($resultSeriesArray as $series) {

                                $firstChar = mb_substr($series, 0, 1);
                                $secondChar = mb_substr($series, 1, 1);
                                $name = $firstChar . ' ' . $secondChar;

                                if (!($this->global_model->doesExist('series_list', array('name' => trim($name))))) {
                                    $this->global_model->insert('series_list', array('name' => trim($name)));
                                }
                            }
                        }

                        $first_prize_data['prizebond_result_info_id'] = $insertedId;
                        $first_prize_data['prize_position '] = '1st';
                        $first_prize_data['bond_number '] = $this->input->post('first_prize_bond_number');
                        $this->admin_model->saveFirstPrizeBondInfo($first_prize_data);

                        $second_prize_data['prizebond_result_info_id'] = $insertedId;
                        $second_prize_data['prize_position'] = '2nd';
                        $second_prize_data['bond_number'] = $this->input->post('second_prize_bond_number');
                        $this->admin_model->saveSecondPrizeBondInfo($second_prize_data);

                        $third_prize_num_array = explode(",", $this->input->post('third_prize_bond_number'));
                        foreach ($third_prize_num_array as $third_prize_num) {
                            $third_prize_data['prizebond_result_info_id'] = $insertedId;
                            $third_prize_data['prize_position'] = '3rd';
                            $third_prize_data['bond_number'] = $third_prize_num;
                            $this->admin_model->saveThirdPrizeBondInfo($third_prize_data);
                        }

                        $fourth_prize_num_array = explode(",", $this->input->post('fourth_prize_bond_number'));
                        foreach ($fourth_prize_num_array as $fourth_prize_num) {
                            $fourth_prize_data['prizebond_result_info_id'] = $insertedId;
                            $fourth_prize_data['prize_position'] = '4th';
                            $fourth_prize_data['bond_number'] = $fourth_prize_num;
                            $this->admin_model->saveFourthPrizeBondInfo($fourth_prize_data);
                        }

                        $fifth_prize_num_array = explode(" ", $this->input->post('fifth_prize_bond_number'));
                        foreach ($fifth_prize_num_array as $fifth_prize_num) {
                            $fifth_prize_data['prizebond_result_info_id'] = $insertedId;
                            $fifth_prize_data['prize_position'] = '5th';
                            $fifth_prize_data['bond_number'] = $fifth_prize_num;
                            $this->admin_model->saveFifthPrizeBondInfo($fifth_prize_data);
                        }

                        $this->session->set_flashdata('success_msg', 'Your information has been successfully save.');
                        //redirect('admin/draw/changeDrawStatus');
                        redirect('admin/draw');
                    }
                } else {
                    $this->session->set_flashdata('warning_msg', 'Your information is already exists in Database.');
                }
            } else {
                echo validation_errors();
            }
        }

        $this->load->view('admin/header', $title);
        $this->load->view('admin/navbar');
        $this->load->view('admin/sidebar');
        $this->load->view('admin/draw_info/add');
        $this->load->view('admin/footer');
    }

    public function checkExistingDrawInfo() {
        $data = array();
        $data['title'] = 'Check Draw Info';
        $inputData = array();
        $existingData = '';
        $data['draw_number'] = $this->admin_model->getDrawNumber();
        if ($this->input->post('submit')) {


            $this->form_validation->set_rules('first', 'first', 'trim');
            $this->form_validation->set_rules('third', 'third', 'trim');
            $this->form_validation->set_rules('fourth', 'fourth', 'trim');
            $this->form_validation->set_rules('fifth', 'fifth', 'trim');
            $this->form_validation->set_rules('draw_number', 'Draw Number', 'trim|required');
            if ($this->form_validation->run()) {
                $inputData = array();
                $inputData['first_second_bonds'] = $this->input->post('first');
                $inputData['third_bonds'] = $this->input->post('third');
                $inputData['fourth_bonds'] = $this->input->post('fourth');
                $inputData['fifth_bonds'] = $this->input->post('fifth');
                $total = explode(',', $this->input->post('fifth'));
                $trimmed_array = array_map('trim', $total);
                $inputData['total_fifth_number'] = count($total);
                $inputData['hash'] = md5(serialize($inputData));

                $existingData = $this->admin_model->getDrawExistingData($this->input->post('draw_number'));

                $total2 = explode(',', $existingData['fifth_bonds']);
                $inputData['existing_fifth_number'] = count($total2);

                $uncommon_bond = array_diff($trimmed_array, $total2);
                if (is_array($uncommon_bond)) {
                    $uncommon_bond = implode(',', $uncommon_bond);
                }
            }
        }
        $data['input'] = $inputData;
        $data['output'] = $existingData;

        if (!empty($uncommon_bond)) {
            $data['uncommon'] = $uncommon_bond;
        }


        $this->load->view('admin/check_draw_info', $data);
    }

    public function drawListAfterNewDrawAdded() {
        $data = array();
        $data['title'] = 'Draw Information Manage';

        $array = $this->admin_model->getLastNineDrawInfo();
        $data['all_prize_bond_info'][] = $drawInfo = end($array);

        $data['last_draw'] = $drawInfo->result_number;

        $data['totalUsers'] = $this->admin_model->getUserNumberForLastDraw($drawInfo->result_number);
        $data['totalWonUsers'] = $this->admin_model->getWonUserNumberForLastDraw($drawInfo->id);

        $this->load->view('admin/draw_info/draw_list_after_new_draw_added', $data);
    }

    public function changeDrawStatus($id, $status) {
        if ($this->db->update('prizebond_result_info', array('status' => $status), array('id' => $id))) {
            redirect('admin/draw/drawListAfterNewDrawAdded');
        }
    }

    public function removeDrawNumberTags($drawNumber) {

        $ids = $this->admin_model->getRemoveDrawInfoID($drawNumber);
        $updateArray = array(
            'prize_position' => NULL,
            'prize_amount' => NULL,
            'draw_number' => NULL,
        );
        foreach ($ids as $id) {
            $this->db->update('user_prizebond_list', $updateArray, array('id' => $id));
        }
        redirect('admin/draw/drawListAfterNewDrawAdded');
    }

    public function deleteWiningNumber($prizebondResultInfoId) {
        if ($this->db->delete('user_prizebond_won_list', array('prizebond_result_info_id' => $prizebondResultInfoId))) {
            redirect('admin/draw/drawListAfterNewDrawAdded');
        }
    }

}
