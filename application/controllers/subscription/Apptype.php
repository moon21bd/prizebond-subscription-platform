<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Apptype extends Main {

    public function __construct() {
        parent::__construct();
        
        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/apptype_model');
        $this->load->library('form_validation');
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add';
        $data['tab_active'] = 'add';
        

        if ($this->input->post('submit')) {
           
            $this->form_validation->set_rules('name', 'App Type', 'trim|required|is_unique[subscription_app_types.name]');
            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['name'] = $this->input->post('name');
                $save_data['created'] = date('Y-m-d H:i:s');

                if ($this->apptype_model->saveAppType($save_data)) {
                    $this->session->set_flashdata('success_msg', 'App Type Save Successfully...');
                    redirect('subscription/apptype/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/apptype/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function manage() {
        $data = array();
        $data['title'] = 'Manage';
        //$data['tab_active'] = 'manage';
        $data['appType'] = $this->apptype_model->getAppType();
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/apptype/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    public function edit($id) {
        $data = array();
        $data['title'] = ' Update';
        $data['tab_active'] = 'manage';
        //.... view data..........
        $aData = $this->apptype_model->editAppType($id);
        $data['name'] = $aData->name;
        if ($this->input->post('update')) {
            $this->form_validation
                    ->set_rules('name', 'App Type', 'trim|required|is_unique[app_types.name]');

            $save_data = array();
            $save_data['name'] = $this->input->post('name');
            $save_data['created'] = date('Y-m-d H:i:s');

            if ($this->apptype_model->updateAppType($save_data, $id)) {
                $this->session->set_flashdata('success_msg', 'Update Successfully...');
                redirect('subscription/apptype/manage');
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/apptype/edit', $data);
        $this->load->view('admin/footer', $data);
    }

    //    .... delete....
    public function delete($id) {
        if ($this->apptype_model->deleteAppType($id)) {
            $this->session->set_flashdata('success_msg', 'Delete Successfully...');
            redirect('subscription/apptype/manage');
        }
    }

}
