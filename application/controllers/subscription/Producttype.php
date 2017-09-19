<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Producttype extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/producttype_model');
        $this->load->model('subscription/apptype_model');
        $this->load->model('subscription/productcategory_model');
        
    }

    public function __destruct() {
        $this->db->close();
    }

    public function add() {
        $data = array();
        $data['title'] = ' Add';
        $data['tab_active'] = 'product_type';
        //Get Parent Category
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();

        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('app_types_id', 'App Type', 'trim|required')
                    ->set_rules('name', 'Product Type ', 'trim|required|is_unique[subscription_product_types.name]');

            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['app_types_id'] = $this->input->post('app_types_id');
                $save_data['name'] = $this->input->post('name');
                $save_data['created'] = date('Y-m-d H:i:s');

                if ($this->producttype_model->saveProductType($save_data)) {
                    $this->session->set_flashdata('success_msg', 'Product Type Save Successfully...');
                    redirect('subscription/producttype/manage');
                }
            }
        }

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/producttype/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function manage() {
        $data = array();
        $data['title'] = 'Manage';
        //$data['tab_active'] = 'product_type';
        $data['productType'] = $this->producttype_model->getAppType();
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/producttype/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    public function edit($id) {
        $data = array();
        $data['title'] = ' Update';
        $data['tab_active'] = 'product_type';
        //Get Parent Category
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();
        //.... view data..........
        $aData = $this->producttype_model->editProductType($id);

        $data['id'] = $aData['id'];
        $data['app_types_id'] = $aData['app_types_id'];
        //$data['product_category_id'] = $aData->product_category_id;
        $data['name'] = $aData['name'];

        if ($this->input->post('update')) {
            $this->form_validation
                    ->set_rules('app_types_id', 'App Type', 'trim|required')
                    ->set_rules('name', 'Product Type ', 'trim|required|is_unique[product_types.name]');
            $save_data = array();
            $save_data['app_types_id'] = $this->input->post('app_types_id');
            //   $save_data['product_category_id'] = $this->input->post('product_category_id');
            $save_data['name'] = $this->input->post('name');
            $save_data['created'] = date('Y-m-d H:i:s');

            if ($this->producttype_model->updateProductType($save_data, $id)) {
                $this->session->set_flashdata('success_msg', 'Update Successfully...');
                redirect('subscription/producttype/manage');
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/producttype/edit', $data);
        $this->load->view('admin/footer', $data);
    }

    //    .... delete....
    public function delete($id) {
        if ($this->producttype_model->deleteProductType($id)) {
            $this->session->set_flashdata('success_msg', 'Delete Successfully...');
            redirect('subscription/producttype/manage');
        }
    }

}
