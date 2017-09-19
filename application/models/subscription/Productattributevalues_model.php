<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Productattributevalues_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function saveAttributesValues($save_data) {
        $this->db->insert('product_attribute_values', $save_data);
        return $this->db->affected_rows();
    }

    public function updateAttributesValues($data = array(), $id) {
        return $this->db->update('product_attribute_values', $data, array('id' => $id));
    }

    public function getProductsAttributes() {
        $this->db->distinct();
        //$query =$this->db->get('products_attributes');
        $this->db->select('*')->from('products_attributes');
        $query = $this->db->get();
        $response = array('' => 'Select');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }
    
    //single Payment History
    public function getSingleAttributeValues($id) {
        $this->db->select('*')->from('product_attribute_values')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function getAttributeValues() {
        $this->db->select('*')->from('product_attribute_values');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['attributes_id'] = $this->getProductsAttributesById($row->attribute_id);
                $result[$i]['value'] = $row->value;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    private function getProductsAttributesById($attId) {

        $query = $this->db->get_where('products_attributes', array('id' => $attId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    // delete single row
    Public function deleteProductsAttributes($id) {
        $this->db->delete('product_attribute_values', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function viewOrderList($id) {
        $query = $this->db->get_where('order_list', array('id' => $id));

        $result = array();

        foreach ($query->result() as $row) {
            $result['id'] = $query->row()->id;
            $result['order_id'] = $query->row()->order_id;
            $result['user_id'] = $query->row()->user_id;
            $result['product_id'] = $this->getProductNameByProductId($query->row()->product_id);
            $result['app_id'] = $this->getAppNameByAppId($query->row()->app_id);
            $result['payment_method_type_id'] = $this->getPaymentMethodByPaymentId($query->row()->payment_method_type_id);
            $result['price'] = $query->row()->price;
            $result['order_status'] = $query->row()->order_status;
            $result['payment_status'] = $query->row()->payment_status;
            $result['order_created_date_time'] = $query->row()->order_created_date_time;
            $result['created_date_time'] = $query->row()->created_date_time;
        }
        return $result;
    }

    
    

}
