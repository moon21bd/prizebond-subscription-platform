<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-11
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('subscription/productcategory_model');
        $this->load->model('subscription/producttype_model');
    }

    public function load_name() {

        $app_type_id = $this->input->post('app_type_id');
        $product_categories = $this->productcategory_model->ajax_call($app_type_id);
        $product_type = $this->producttype_model->ajax_call($app_type_id);

        $category_option = '<option value="" selected="selected">--Select--</option>';
        foreach ($product_categories as $cat) {
            $category_option .= '<option value="' . $cat->id . '">' . $cat->name . '</option>';
        }


        $product_type_option = '<option value="" selected="selected">--Select--</option>';
        $comment = '';
        foreach ($product_type as $type) {
            if ($type->name == 'Subscription') {
                $comment = '(for a specific time priod)';
            } elseif ($type->name == 'Non Subscription') {
                $comment = '(for purchase ex: 25 years)';
            }
            $product_type_option .= '<option value="' . $type->id . '">' . $type->name . ' ' . $comment . '</option>';
        }

        $response = array();
        $response['category_list'] = $category_option;
        $response['type_list'] = $product_type_option;

        echo json_encode($response);
    }

}
