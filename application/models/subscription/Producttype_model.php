<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Producttype_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function saveProductType($data = array()) {
        $this->db->insert('subscription_product_types', $data);
        return $this->db->affected_rows();
    }

    //........... show data........
    public function getProductType() {
        $this->db->select('*')->from('subscription_product_types');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getAppType() {
        $this->db->select('*')->from('subscription_product_types');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['app_type'] = $this->getAppTypeByAppTypeId($row->app_types_id);
                $result[$i]['name'] = $row->name;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    private function getAppTypeByAppTypeId($typeId) {
        $query = $this->db->get_where('subscription_app_types', array('id' => $typeId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductCatByProductCatId($catId) {
        $query = $this->db->get_where('subscription_product_categories', array('id' => $catId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    // get single data....(edit)
    public function editProductType($id) {
        $this->db->select('*')->from('subscription_product_types')->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    //---update data.....
    public function updateProductType($data = array(), $id) {
        $this->db->update('subscription_product_types', $data, array('id' => $id));
        return $this->db->affected_rows();
    }

    // delete single row
    Public function deleteProductType($id) {
        $this->db->delete('subscription_product_types', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function getProAssociate() {
        $this->db->select('*')->from('subscription_product_types');
        $query = $this->db->get();
        $response = array('' => '--select--');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    public function ajax_call($app_type_id) {
        $this->db->select('*')->from('subscription_product_types')
                ->where('app_types_id', $app_type_id);
        $query = $this->db->get();
        return $query->result();
    }

}
