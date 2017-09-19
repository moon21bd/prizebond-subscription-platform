<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'controllers/Main.php';

class Product extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('subscription/global_model');
        $this->load->model('subscription/product_model');
        $this->load->model('subscription/apptype_model');
        $this->load->model('subscription/productcategory_model');
        $this->load->model('subscription/producttype_model');
        $this->load->model('subscription/productattributes_model');
        $this->load->helper('global');
    }

    public function __destruct() {
        $this->db->close();
    }

    // Product add
    public function addProduct() {
        $data = array();
        $data['title'] = 'Add ';
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();
        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('app_types_id', 'App Type', 'trim|required')
                    ->set_rules('product_category_id', 'Category Name', 'trim|required')
                    ->set_rules('product_type_id', 'Product Type', 'trim|required')
                    ->set_rules('name', 'Name', 'trim|required')
                    ->set_rules('name_english', 'English Name', 'trim|required');

            if ($this->form_validation->run()) {

                $save_data = array();
                $save_data['app_types_id'] = $this->input->post('app_types_id');
                $save_data['product_category_id'] = $this->input->post('product_category_id');
                $save_data['product_type_id'] = $this->input->post('product_type_id');
                $save_data['name'] = $this->input->post('name');
                $save_data['name_english'] = $this->input->post('name_english');
                $save_data['created'] = date('Y-m-d H:i:s');

                if ($this->product_model->saveProductList($save_data)) {
                    $productId = $this->db->insert_id();
                    redirect('subscription/product/add/' . $productId);
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/product/add_product', $data);
        $this->load->view('admin/footer', $data);
    }

    public function add($productId) {

        $data = array();
        $data['title'] = 'Add ';
        $data['tab_active'] = 'product';
        $data['attributeList'] = $this->productattributes_model->getAtributeListByAppId();
        $data['product_name'] = $this->productattributes_model->getProductNameByProductListId($productId);

        $productInfo = $this->productattributes_model->get_data('subscription_product_list', array('id' => $productId));
        $appType = $this->productattributes_model->get_data('subscription_app_types', array('id' => $productInfo['app_types_id']));

        $catInfo = $this->productattributes_model->get_data('subscription_product_categories', array('id' => $productInfo['product_category_id']));
        $typeInfo = $this->productattributes_model->get_data('subscription_product_types', array('id' => $productInfo['product_type_id']));

        $data['product_cat'] = $catInfo['name'];
        $data['product_type'] = $typeInfo['name'];

        if ($this->input->post('submit')) {
            foreach ($this->input->post('attribute_value') as $key => $value) {
                if (!empty($value)) {
                    $sdata['product_id'] = $productId;
                    $sdata['attribute_id'] = $key;
                    $sdata['value'] = !empty($value) ? $value : 'N/A';
                    $sdata['created'] = date('Y-m-d H:i:s');
                    $this->product_model->saveAttributesValues($sdata);
                }
            }
            $this->session->set_flashdata('success_msg', 'Product Save Successfully...');
            redirect('subscription/product/manage');
        }

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/product/add', $data);
        $this->load->view('admin/footer', $data);
    }

    public function editAttribute($productId, $appId = 0, $offset = 0) {
        $appType = $this->session->userdata('appType');
        $offSet = $offset;
        $data = array();
        $data['title'] = ' edit';
        $data['tab_active'] = 'product';

        //get attribute and value by app id and product id
        $data['attributeList'] = $this->productattributes_model->getAtributeAndValueByAppIdAndProductId($productId);

        //get product name by product id
        $data['product_name'] = $this->productattributes_model->getProductNameByProductListId($productId);

        //Get Parent Category
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();
        $data['getProCat'] = $this->productcategory_model->getCatAssociate();
        $data['getProType'] = $this->producttype_model->getProAssociate();

        //.... view data..........
        $data['productInfo'] = $productInfo = $this->product_model->getProductInfoById($productId);
        $aProduct = $this->product_model->editProductList($productId);

        $data['id'] = $aProduct['id'];
        $data['app_types_id'] = $aProduct['app_types_id'];
        $data['product_category_id'] = $aProduct['product_category_id'];
        $data['product_type_id'] = $aProduct['product_type_id'];
        $data['name'] = $aProduct['name'];
        $data['name_english'] = $aProduct['name_english'];

        // update attribute
        if ($this->input->post('submit')) {

            $this->form_validation
                    ->set_rules('product_category_id', ' Category Name', 'trim|required')
                    ->set_rules('product_type_id', 'Product Type', 'trim|required')
                    ->set_rules('product_name', 'Product Name', 'trim|required')
                    ->set_rules('name_english', 'Product English Name', 'trim|required');


            if ($this->form_validation->run()) {
                $save_data = array();

                $save_data['product_category_id'] = $this->input->post('product_category_id');
                $save_data['product_type_id'] = $this->input->post('product_type_id');
                $save_data['name'] = $this->input->post('product_name');
                $save_data['name_english'] = $this->input->post('name_english');
                $save_data['created'] = date('Y-m-d H:i:s');

                $this->product_model->updateProductList($save_data, $productId);


                foreach ($this->input->post('attribute_value') as $attrId => $value) {
                    if (!empty($value)) {
                        $sdata['value'] = $value;
                        $this->product_model->updateAttribute($sdata, $attrId);
                    }
                }
                $this->session->set_flashdata('success_msg', 'Updated Successfully...');
                redirect('subscription/product/manage/');
//                if (!empty($offSet)) {
//                    redirect('subscription/product/all_application/' . $productInfo['app_type'] . '/' . $offSet);
//                } else {
//                    redirect('subscription/product/all_application/' . $productInfo['app_type']);
//                }
            }
        }


        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/product/edit_attribute', $data);
        $this->load->view('admin/footer', $data);
    }

    public function manage($offset = 0) {
        $data = array();
        $data['title'] = 'Manage';
        //$data['tab_active'] = 'product';
        $data['productList'] = $productList = $this->product_model->getProductList();
        $data['appType'] = '';

        //search app type
        $serchData = array();
        if ($this->input->get('search')) {
            $data['appType'] = ($this->input->get('appType'));
            $serchData['appType'] = $this->input->get('appType');
            $data['productList'] = $this->product_model->getProductList($serchData);
        }

        //ordering update
        if ($this->input->post('submit')) {
            $ids = $this->input->post('ids');
            $ordering = $this->input->post('ordering');
            if (is_array($ordering) && count($ordering) > 0) {
                foreach ($ordering as $key => $value) {
                    if (!empty($value)) {
                        $upate['ordering'] = $value;
                        $this->product_model->updateOrder($upate, $ids[$key]);
                    }
                }
            }
            $this->session->set_flashdata('success_msg', 'Updated Successfully...');
        }

        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        //$this->load->view('subscription/sidebar', $data);
        $this->load->view('subscription/product/manage', $data);
        $this->load->view('admin/footer', $data);
    }

    public function post_status($id, $status, $productType, $offset = 0) {

        $this->product_model->updateStatus($id, $status);
        if ($offset != 0) {
            redirect('subscription/product/manage/' . $productType . '/' . $offset);
        } else {
            redirect('subscription/product/manage/' . $productType);
        }
    }

    public function edit($id) {
        $data = array();
        $data['title'] = ' Update';
        $data['tab_active'] = 'product';

        //Get Parent Category
        $data['getAppTypes'] = $this->apptype_model->getAppAssociate();
        $data['getAppList'] = $this->applist_model->getAppAssociate();
        $data['getProCat'] = $this->productcategory_model->getCatAssociate();
        $data['getProType'] = $this->producttype_model->getProAssociate();


        //.... view data..........
        $aProduct = $this->product_model->editProductList($id);

        $data['id'] = $aProduct->id;
        $data['app_types_id'] = $aProduct->app_types_id;
        $data['app_list_id'] = $aProduct->app_list_id;
        $data['product_category_id'] = $aProduct->product_category_id;
        $data['product_type_id'] = $aProduct->product_type_id;
        $data['name'] = $aProduct->name;
        //app name edit for ajax
        if ($aProduct->app_types_id) {
            $app_list = $this->applist_model->ajax_call($aProduct->app_types_id);
            $app_list_option = array();
            foreach ($app_list as $show) {
                $app_list_option[] = array('id' => $show->id, 'name' => $show->name);
            }
            $data['app_list'] = $app_list_option;
        }


        //Category name edit for ajax
        if ($aProduct->app_list_id) {
            $product_categories = $this->productcategory_model->ajax_call($aProduct->app_list_id);

            $category_option = array();
            foreach ($product_categories as $cat) {
                $category_option[] = array('id' => $cat->id, 'name' => $cat->name);
            }
            $data['product_categories'] = $category_option;
        }


        //product type edit for ajax
        if ($aProduct->app_list_id) {
            $product_type = $this->producttype_model->ajax_call($aProduct->app_list_id);

            $product_type_option = array();
            foreach ($product_type as $cat) {
                $product_type_option[] = array('id' => $cat->id, 'name' => $cat->name);
            }
            $data['product_type'] = $product_type_option;
        }

        if ($this->input->post('update')) {

            $this->form_validation
                    ->set_rules('app_types_id', 'App Type', 'trim|required')
                    ->set_rules('app_list_id', 'App Name', 'trim|required')
                    ->set_rules('product_category_id', ' Category Name', 'trim|required')
                    ->set_rules('product_type_id', 'Product Type', 'trim|required')
                    ->set_rules('name', 'Product Name', 'trim|required');


            if ($this->form_validation->run()) {
                $save_data = array();
                $save_data['app_types_id'] = $this->input->post('app_types_id');
                $save_data['app_list_id'] = $this->input->post('app_list_id');
                $save_data['product_category_id'] = $this->input->post('product_category_id');
                $save_data['product_type_id'] = $this->input->post('product_type_id');
                $save_data['name'] = $this->input->post('name');
                $save_data['created'] = date('Y-m-d H:i:s');


                if ($this->product_model->updateProductList($save_data, $id)) {
                    $this->session->set_flashdata('success_msg', 'Product Update Successfully...');
                    redirect('subscription/product/manage');
                }
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('subscription/product/edit', $data);
        $this->load->view('admin/footer', $data);
    }

    //    .... delete....
    public function delete($id) {

        if ($this->product_model->deleteProductList($id)) {
            $this->session->set_flashdata('success_msg', 'Product Delete Successfully...');
            redirect('subscription/product/manage');
        }
    }

    public function view($id) {
        $data = array();
        $data['title'] = ' View';
        $data['tab_active'] = 'product';

        $data['productInfo'] = $this->product_model->getProductInfoById($id);

        $this->load->view('subscription/product/view', $data);
        ;
    }

//    public function manage() {
//        $data = array();
//        $data['title'] = 'Manage';
//        $data['tab_active'] = 'product';
//        $data['allAppName'] = $this->product_model->getApplicationName();
//
//        $this->load->view('subscription/header', $data);
//        $this->load->view('subscription/navbar', $data);
//        $this->load->view('subscription/sidebar', $data);
//        $this->load->view('subscription/product/all_application', $data);
//        $this->load->view('subscription/footer', $data);
//    }
}
