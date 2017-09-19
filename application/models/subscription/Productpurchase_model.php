<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Productpurchase_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    Public function getProductByProductId($id) {
        $query = $this->db->get_where('product_list', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function saveProductPurchase($data = array()) {
        $this->db->insert('subscription_product_purchase_list', $data);
        return $this->db->affected_rows();
    }

    public function getProjectInofByProductId($id) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function getProListAssociate() {
        $this->db->select('*')->from('subscription_product_list');
        $this->db->where('status', 1);
        $query = $this->db->get();
        $response = array('' => '-- Select --');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    //........... show data or ........
    public function getProductPruchase($userId = '', $limit = null, $offset = 0) {
        $this->db->order_by("id", "desc");

        if ($userId) {
            $this->db->select('*')->from('subscription_product_purchase_list')->where('user_id', $userId);
            $this->db->limit($limit, $offset);
        } else {
            $this->db->select('*')->from('subscription_product_purchase_list');
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['user_id'] = $row->user_id;
                $result[$i]['product_id'] = $row->product_id;
                $result[$i]['user_name'] = $this->getUserNameByUserId($row->user_id);
                $result[$i]['app_type'] = $this->getAppTypeByAppTypeId($row->app_types_id);
                $result[$i]['product_cat'] = $this->getProductCatByProductCatId($row->product_category_id);
                $result[$i]['product_type'] = $this->getProductTypeByProductTypeId($row->product_type_id);
                $result[$i]['product_name'] = $this->getProListByProListId($row->product_id);
                $result[$i]['purchased_by'] = $row->purchased_by;
                $result[$i]['start_datetime'] = $row->valid_start_datetime;
                $result[$i]['end_datetime'] = $row->valid_end_datetime;
                $result[$i]['created'] = $row->created_datetime;
                if ($row->status == 1) {
                    $result[$i]['status'] = 'Active';
                } else {
                    $result[$i]['status'] = 'Expired';
                }

                $result[$i]['added_date'] = $this->time_elapsed_string($row->created_datetime);
                $result[$i]['modified'] = $row->modified;
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

    private function getProductTypeByProductTypeId($proId) {

        $query = $this->db->get_where('subscription_product_types', array('id' => $proId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProListByProListId($proListId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $proListId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getUserNameByUserId($userId) {

        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getPriceByProListId($productId) {

        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $query = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                $result[$query->row()->name] = $row->value;
            }
            return $result[$query->row()->name] = $row->value;
        } else {
            return FALSE;
        }
    }

    public function getUserInfoByUserId($id) {
        $this->db->select('*')->from('user')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    // get single data....(edit)
    public function editProPurchase($id) {
        $this->db->select('*')->from('subscription_product_purchase_list')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    //---update data.....
    public function updateProPurchase($data = array(), $id) {
        $this->db->update('subscription_product_purchase_list', $data, array('id' => $id));
        return $this->db->affected_rows();
    }

    // delete single row
    Public function deleteProPurchase($id) {
        $this->db->delete('subscription_product_purchase_list', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function viewProductPurchase($id) {
        $query = $this->db->get_where('subscription_product_purchase_list', array('id' => $id));
        $result = array();
        $result['id'] = $query->row()->id;
        $result['user_id'] = $query->row()->user_id;
        $result['app_type'] = $this->getAppTypeByAppTypeId($query->row()->app_types_id);
        //$result['app_name'] = $this->getAppNameByAppNameId($query->row()->app_list_id);
        $result['product_cat'] = $this->getProductCatByProductCatId($query->row()->product_category_id);
        $result['product_type'] = $this->getProductTypeByProductTypeId($query->row()->product_type_id);
        $result['product_name'] = $this->getProListByProListId($query->row()->product_id);
        $result['price'] = $this->getPriceByProListId($query->row()->product_id);
        $result['purchased_by'] = $query->row()->purchased_by;
        $result['start_datetime'] = $query->row()->valid_start_datetime;
        $result['end_datetime'] = $query->row()->valid_end_datetime;
        return $result;
    }

    public function getPurchase() {
        $this->db->select('*')->from('product_purchase_list');
        $query = $this->db->get();
        $response = array('' => '-Select-');
        foreach ($query->result() as $aData) {
            $response[$aData->purchased_by] = $aData->purchased_by;
        }
        return $response;
    }

    //for sales report page
    public function getPurchaseMethod() {
        $this->db->select('*')->from('subscription_product_purchase_list')->where('purchased_by!=', 'sign up');
        $query = $this->db->get();
        foreach ($query->result() as $aData) {
            $response[$aData->purchased_by] = $aData->purchased_by;
        }
        return $response;
    }

    //for sales report page
    public function getProductList() {
        $this->db->select('*')->from('subscription_product_list');
        $this->db->where('status', 1);
        $this->db->where('id >', 1);
        $query = $this->db->get();
        $response = array();
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    public function numberOfRows($params = array()) {
        if ($params) {

            if (!empty($params['start_date'])) {
                $this->db->where('created_date >=', $params['start_date']);
            }

            if (!empty($params['end_date'])) {
                $this->db->where('created_date <=', $params['end_date']);
            }
            if (!empty($params['user_id'])) {
                $this->db->where('user_id', $params['user_id']);
            }
        }
        $this->db->from('subscription_product_purchase_list');
        return $this->db->count_all_results();
    }

    private function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full)
            $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

}
