<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Productattributes extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/productattributes_model');
        $this->load->model('subscription/coupon_model');
        $this->load->model('subscription/product_model');
        $this->load->model('subscription/productpurchase_model');
        $this->load->helper('global');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function add() {
        $data = array();
        $data['title'] = 'Add';
        $data['tab_active'] = 'attributes';

        //$data['getAppList'] = $this->productattributes_model->getAppName();
        if ($this->input->post('submit')) {

            $this->form_validation
                    ->set_rules('name', 'Product Attribute', 'trim|required')
                    ->set_rules('attribute_predefine_value', 'attribute predefine value', 'trim|strtolower');
            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['name'] = $this->input->post('name');
                $save_data['attribute_predefine_value'] = $this->input->post('attribute_predefine_value');
                $save_data['created'] = date('Y-m-d H:i:s');
                if ($this->productattributes_model->saveProductAttributes($save_data)) {
                    $this->session->set_flashdata('success_msg', 'Product Atribute Saved Successfully...');
                    redirect('subscription/productattributes/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/productattributes/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function manage($offset = 0) {
        $data = array();
        $data['title'] = 'Manage';
        // $data['tab_active'] = 'attributes';
        //pagination
        $uri_segment = 4;
        $per_page = 20;
        $total_rows = $this->productattributes_model->numberOfRows();
        generatePagging('/subscription/productattributes/manage', $total_rows, $per_page, $uri_segment, 4);

        $data['attributeList'] = $this->productattributes_model->getProductAttributes($per_page, $offset);

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        // $this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/productattributes/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    //    .... delete....
    public function delete($id) {

        if ($this->productattributes_model->deleteAttributeList($id)) {
            $this->session->set_flashdata('success_msg', 'Product Attribute Deleted Successfully...');
            redirect('subscription/productattributes/manage');
        }
    }

    public function edit($id) {
        $data = array();
        $data['title'] = ' Update';
        $data['tab_active'] = 'attributes';


        //Get Parent Category
        $data['getProList'] = $this->productpurchase_model->getProListAssociate();

        //view data
        $attributesList = $this->productattributes_model->getSingleProductAttributes($id);

        $data['id'] = $attributesList['id'];
        $data['name'] = $attributesList['name'];
        $data['attribute_predefine_value'] = $attributesList['attribute_predefine_value'];
        $data['created'] = $attributesList['created'];

        //app name edit for ajax
//        if ($attributesList->app_id) {
//            $product_list = $this->coupon_model->ajax_call_product($attributesList->app_id);
//
//            $product_list_option = array();
//            foreach ($product_list as $show) {
//                $product_list_option[] = array('id' => $show->id, 'name' => $show->name);
//            }
//            $data['product_list'] = $product_list_option;
//        }

        if ($this->input->post('submit')) {

            $this->form_validation
                    ->set_rules('name', 'Product Attribute', 'trim|required')
                    ->set_rules('attribute_predefine_value', 'attribute predefine value', 'trim|strtolower');

            if ($this->form_validation->run()) {

                $update_data = array();
                $update_data['name'] = $this->input->post('name');
                $update_data['attribute_predefine_value'] = $this->input->post('attribute_predefine_value');
                ;
                if ($this->productattributes_model->updateProductAttributes($update_data, $id)) {
                    $this->session->set_flashdata('success_msg', 'Data Successfully updated.');
                    redirect('subscription/productattributes/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/productattributes/edit', $data);
        $this->load->view('admin/footer', $data);
    }

}
