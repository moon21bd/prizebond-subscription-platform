<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }
    
    public function searchApp($params = array(), $count = false, $per_page = null, $offset = null) {

        $select = $this->db->select('*')->from('cbd_teams');
        if (empty($params)) {
            $this->db->where("type", 'int');
        }
        if ($params) {
            if (isset($params['type']) && !empty($params['type']))
                $this->db->where("type", $params['type']);
        }
        if ($per_page) {
            $select->limit($per_page, $offset);
        }

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    // save product
    public function saveProductList($data = array()) {
        $this->db->insert('subscription_product_list', $data);
        return $this->db->affected_rows();
    }

    // save attribute value
    public function saveAttributesValues($save_data) {
        $this->db->insert('subscription_product_attribute_values', $save_data);
        return $this->db->affected_rows();
    }

    public function updateAttribute($save_data, $attrId) {
        $this->db->update('subscription_product_attribute_values', $save_data, array('id' => $attrId));
        return $this->db->affected_rows();
    }

    public function saveAttributes($save_data) {
        $this->db->insert('subscription_products_attributes', $save_data);
        return $this->db->affected_rows();
    }

    public function isPerchased($id) {
        $this->db->select('*')->from('subscription_product_purchase_list')->where('product_id', $id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    //........... show data........
    public function getProductList($params = array(),$limit = 0, $offset = 0) {
        $this->db->order_by("ordering", "asc");
        $this->db->select('*')->from('subscription_product_list');
        if ($params) {
            if (isset($params['appType']) && !empty($params['appType']))
                $this->db->where("app_types_id", $params['appType']);
        }
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['ordering'] = $row->ordering;
                $result[$i]['app_type'] = $this->getAppTypeByAppTypeId($row->app_types_id);
                $result[$i]['product_cat'] = $this->getProductCatByProductCatId($row->product_category_id);
                $result[$i]['product_type'] = $this->getProductTypeByProductTypeId($row->product_type_id);
                $result[$i]['product_name'] = $this->getProductByProductId($row->id);
                $result[$i]['total_purchesed'] = $this->getTotalProductPurchesedByProductId($row->id);
                $result[$i]['status'] = $row->status;
                $result[$i]['created'] = $row->created;
                $result[$i]['modified'] = $row->modified;
                $i++;
            }
            return $result;
            
        } else {
            return FALSE;
        }
    }
    
    private function getTotalProductPurchesedByProductId($productId) {
        $query = $this->db->get_where('subscription_product_purchase_list', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        }
    }

    public function getApplicationName($limit = 0, $offset = 0) {
        $this->db->group_by('subscription_app_types.name');
        $query = $this->db->select('subscription_product_list.*,subscription_app_types.name')
                ->from('subscription_product_list')
                ->join('subscription_app_types', 'subscription_app_types.id =subscription_product_list.app_types_id');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            // return $query->result();

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['app_type'] = $this->getAppTypeByAppTypeId($row->app_types_id);
                $result[$i]['product_cat'] = $this->getProductCatByProductCatId($row->product_category_id);
                $result[$i]['product_type'] = $this->getProductTypeByProductTypeId($row->product_type_id);
                $result[$i]['total_app'] = $this->getTotalAppNameAndAppType($row->app_types_id);
                $result[$i]['total_active_app'] = $this->getTotalActiveAppNameAndAppType($row->app_types_id);
                $result[$i]['total_inactive_app'] = $this->getTotalInactiveAppNameAndAppType($row->app_types_id);
                $result[$i]['name'] = $row->name;
                $result[$i]['status'] = $row->status;
                $result[$i]['created'] = $row->created;
                $result[$i]['modified'] = $row->modified;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    private function getTotalAppNameAndAppType($appType) {
        $query = $this->db->get_where('subscription_product_list', array('app_types_id' => $appType));
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        }
    }

    private function getTotalActiveAppNameAndAppType($appType) {
        $query = $this->db->get_where('subscription_product_list', array('app_types_id' => $appType, 'status' => 1));
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        }
    }

    private function getTotalInactiveAppNameAndAppType($appType) {
        $query = $this->db->get_where('subscription_product_list', array('app_types_id' => $appType, 'status' => 0));
        if ($query->num_rows() > 0) {
            return $query->num_rows();
        }
    }

    public function getAppTypeByAppTypeId($typeId) {

        $query = $this->db->get_where('subscription_app_types', array('id' => $typeId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductByProductId($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
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

    private function getProductTypeByProductTypeId($proId) {
        $query = $this->db->get_where('subscription_product_types', array('id' => $proId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function getUnit() {
        $this->db->select('*')->from('subscription_product_list');
        $query = $this->db->get();
        $response = array('' => '-Select-');
        foreach ($query->result() as $aData) {
            $response[$aData->unit] = $aData->unit;
        }
        return $response;
    }

    // get single data....(edit)
    public function editProductList($id) {
        $this->db->select('*')->from('subscription_product_list')->where('id', $id);
        $query = $this->db->get();

        return $query->row_array();
        
    }

    public function getProductInfoById($id) {
        $query = $this->db->get_where('subscription_product_list', array('id' => $id));        
        $result = array();
        $result['id'] = $query->row_array()['id'];
        $result['app_type'] = $this->getAppTypeByAppTypeId($query->row_array()['app_types_id']);
        $result['product_cat'] = $this->getProductCatByProductCatId($query->row_array()['product_category_id']);
        $result['product_type'] = $this->getProductTypeByProductTypeId($query->row_array()['product_type_id']);
        $result['name'] = $query->row_array()['name'];
        $result['name_english'] = $query->row_array()['name_english'];
        $result['attributeName'] = $this->getProductAttributeValue($query->row_array()['id']);
        $result['created'] = $query->row_array()['created'];
        $result['modified'] = $query->row_array()['modified'];
        return $result;
    }

    public function getProductAttributeValue($productId) {

        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $query = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                $result[$query->row()->name] = $row->value;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    //---update data.....
    public function updateProductList($data = array(), $productId) {
        $this->db->update('subscription_product_list', $data, array('id' => $productId));
        return $this->db->affected_rows();
    }

    public function updateOrder($data = array(), $productId) {
        $this->db->update('subscription_product_list', $data, array('id' => $productId));
        return $this->db->affected_rows();
    }

    public function updateStatus($id, $status) {
        $this->db->update('subscription_product_list', array('status' => $status), array('id' => $id));
        return $this->db->affected_rows();
    }

    // delete single row
    Public function deleteProductList($id) {
        $this->db->delete('subscription_product_list', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function ajax_call($app_list_id) {
        $this->db->select('*')->from('subscription_product_list')
                ->where('id', $app_list_id);
        $query = $this->db->get();
        return $query->result();
    }

    //get number of rows
    public function numberOfRows($app_type, $app_name = NULL) {
        $this->db->select('subscription_product_list.*,subscription_app_types.name')
                ->from('subscription_product_list')
                ->join('subscription_app_types', 'subscription_app_types.id =subscription_product_list.app_types_id')
                ->where('subscription_app_types.name', $app_type);

        //$this->db->from('subscription_product_list');
        return $this->db->count_all_results();
    }

}
