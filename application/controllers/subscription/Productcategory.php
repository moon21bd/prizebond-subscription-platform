<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Productcategory extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/productcategory_model');
        $this->load->model('subscription/apptype_model');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add';
        $data['tab_active'] = 'category';
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();

        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('app_types_id', 'App Type', 'trim|required')
                    ->set_rules('name', 'Category Name', 'trim|required|is_unique[subscription_product_categories.name]');
            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['app_types_id'] = $this->input->post('app_types_id');
                $save_data['name'] = $this->input->post('name');
                $save_data['created'] = date('Y-m-d H:i:s');
                if ($this->productcategory_model->saveProductCat($save_data)) {
                    $this->session->set_flashdata('success_msg', 'Category Name Save Successfully...');
                    redirect('subscription/productcategory/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/productcategory/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function manage() {
        $data = array();
        $data['title'] = ' Manage';
        //$data['tab_active'] = 'category';
        $data['productCat'] = $this->productcategory_model->getProductCategory();
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/productcategory/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    public function edit($id) {
        $data = array();
        $data['title'] = 'Update';
        $data['tab_active'] = 'category';
        //Get Parent Category
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();
        //.... view data..........
        $aCat = $this->productcategory_model->editProductCat($id);
        $data['id'] = $aCat['id'];
        $data['app_types_id'] = $aCat['app_types_id'];
        $data['name'] = $aCat['name'];

        if ($this->input->post('update')) {
            $this->form_validation
                    ->set_rules('app_types_id', 'App Type', 'trim|required')
                    ->set_rules('name', 'Category Name', 'trim|required|is_unique[product_categories.name]');

            $save_data = array();
            $save_data['app_types_id'] = $this->input->post('app_types_id');
            $save_data['name'] = $this->input->post('name');
            $save_data['created'] = date('Y-m-d H:i:s');

            if ($this->productcategory_model->updateProductCat($save_data, $id)) {
                $this->session->set_flashdata('success_msg', 'Update Successfully...');
                redirect('subscription/productcategory/manage');
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/productcategory/edit', $data);
        $this->load->view('admin/footer', $data);
    }

    //    .... delete....
    public function delete($id) {
        if ($this->productcategory_model->deleteProductCat($id)) {
            $this->session->set_flashdata('success_msg', 'Deleted Successfully...');
            redirect('subscription/productcategory/manage');
        }
    }

}
