<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Productattributes_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function saveProductAttributes($data = array()) {
        $this->db->insert('subscription_products_attributes', $data);
        return $this->db->affected_rows();
    }

    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function getAtributeListByAppId() {
        $query = $this->db->get('subscription_products_attributes');
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['name'] = $row->name;
                $result[$i]['attribute_predefine_value'] = $row->attribute_predefine_value;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getAtributeAndValueByAppIdAndProductId($productId) {
        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['name'] = $this->getAttributeNameByAttributeId($row->attribute_id);
                $result[$i]['value'] = $row->value;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getAttributeNameByAttributeId($AttrId) {
        $query = $this->db->get_where('subscription_products_attributes', array('id' => $AttrId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    // get all data
    public function getProductAttributes($limit, $offset) {
        $this->db->order_by("id", "desc");
        $query = $this->db->get('subscription_products_attributes', $limit, $offset);
        if ($query->num_rows() > 0) {
            // return $query->result();

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['name'] = $row->name;
                $result[$i]['attribute_predefine_value'] = $row->attribute_predefine_value;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    // get product attribute
    public function geAttributeValue() {
        $this->db->select('*')->from('subscription_products_attributes');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            // return $query->result();

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['name'] = $row->name;
                $result[$i]['attribute_predefine_value'] = !empty($row->attribute_predefine_value) ? $row->attribute_predefine_value : '';
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getAppNameByAppListId($appId) {

        $query = $this->db->get_where('app_list', array('id' => $appId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function getProductCategoryNameByCategoryId($productCatId) {

        $query = $this->db->get_where('subscription_product_categories', array('id' => $productCatId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function getProductNameByProductListId($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    // delete single row
    Public function deleteAttributeList($id) {
        $this->db->delete('subscription_products_attributes', array('id' => $id));
        return $this->db->affected_rows();
    }

    //single product attribute
    public function getSingleProductAttributes($id) {
        $this->db->select('*')->from('subscription_products_attributes')->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function updateProductAttributes($data = array(), $id) {
        return $this->db->update('subscription_products_attributes', $data, array('id' => $id));
    }

    public function ajax_call($product_list_id) {
        $this->db->select('*')->from('subscription_products_attributes')
                ->where('id', $product_list_id);
        $query = $this->db->get();
        return $query->result();
    }

    public function numberOfRows($params = array()) {
        if ($params) {
            if (!empty($params['start_date'])) {
                $this->db->where('created_date >=', $params['start_date']);
            }

            if (!empty($params['end_date'])) {
                $this->db->where('created_date <=', $params['end_date']);
            }
            if (!empty($params['app_id'])) {
                $this->db->where('app_id', $params['app_id']);
            }
        }
        $this->db->from('subscription_products_attributes');
        return $this->db->count_all_results();
    }

}
