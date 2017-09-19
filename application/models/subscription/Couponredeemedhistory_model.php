<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Couponredeemedhistory_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function saveCouponRedeemedHistory($data = array()) {
        $this->db->insert('subscription_coupon_redeemed_history', $data);
        return $this->db->affected_rows();
    }

    public function getCouponsByCouponsId() {
        $this->db->select('*')->from('subscription_coupons');
        $query = $this->db->get();
        $response = array('' => 'Select Coupons');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->coupon;
        }
        return $response;
    }

    public function getOrderByOrderId() {
        $this->db->select('*')->from('subscription_order_list');
        $query = $this->db->get();
        $response = array('' => 'Select Order');
        foreach ($query->result() as $aData) {
            $response[$aData->order_id] = $aData->order_id;
        }
        return $response;
    }

    public function manageCouponRedeemedHistory() {
        $this->db->select('*')->from('subscription_coupon_redeemed_history');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getCouponRedeemedHistory($limit, $offset) {
         $this->db->order_by("id", "desc");
        $query = $this->db->get('subscription_coupon_redeemed_history', $limit, $offset);
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['coupon_code'] = $row->coupon_code;
                $result[$i]['order_id'] = $row->order_id;
                $result[$i]['user_id'] = $row->user_id;
                $result[$i]['user_name'] = $this->getUserNameByUserId($row->user_id);
                $result[$i]['user_email'] = $this->getUserEmailByUserId($row->user_id);
                $result[$i]['user_image'] = $this->getUserImageByUserId($row->user_id);
                $result[$i]['user_phone'] = $this->getUserPhoneByUserId($row->user_id);
                $result[$i]['product_id'] = $row->product_id;
                $result[$i]['product_info'] = $this->getProductNameByProductId($row->product_id);
                $result[$i]['added_date'] = $this->time_elapsed_string($row->created_date_time);
                $result[$i]['created_date_time'] = $row->created_date_time;
                $i++;
            }

            return $result;
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
        private function getUserEmailByUserId($userId) {

        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->num_rows() > 0) {
            return $query->row()->email;
        } else {
            return FALSE;
        }
    }
        private function getUserImageByUserId($userId) {

        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->num_rows() > 0) {
            return $query->row()->image;
        } else {
            return FALSE;
        }
    }
        private function getUserPhoneByUserId($userId) {

        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->num_rows() > 0) {
            return $query->row()->mobile_number;
        } else {
            return FALSE;
        }
    }

    public function getProductNameByProductId($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }
    public function getProductInfoByProductId($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            $result = array();
           foreach ($query->result() as $row) { 
               $result['id'] = $query->row()->id;
                $result['app_types'] = $this->getAppTypeByAppTypeId($query->row()->app_types_id);
                $result['product_category'] = $this->getProductCatagoryByProductCatagoryId($query->row()->product_category_id);
                $result['name'] = $query->row()->name;
                $result['ordering'] = $query->row()->ordering;
                $result['created'] = $query->row()->created;
           }
           return $result;
        } else {
            return FALSE;
        }
    }
    public function getAppTypeByAppTypeId($appTypeId) {

        $query = $this->db->get_where('subscription_app_types', array('id' => $appTypeId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }
    public function getProductCatagoryByProductCatagoryId($productCatagoryId) {

        $query = $this->db->get_where('subscription_product_categories', array('id' => $productCatagoryId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getCoupons($couponsId) {

        $query = $this->db->get_where('subscription_coupons', array('id' => $couponsId));
        if ($query->num_rows() > 0) {
            return $query->row()->coupon;
        } else {
            return FALSE;
        }
    }

    private function getOrder($orderId) {

        $query = $this->db->get_where('subscription_order_list', array('order_id' => $orderId));
        if ($query->num_rows() > 0) {
            return $query->row()->order_id;
        } else {
            return FALSE;
        }
    }

    //get single data....(edit)
    public function getSingleCouponRedeemedHistory($id) {
        $this->db->select('*')->from('subscription_coupon_redeemed_history')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function updateCouponRedeemedHistory($data = array(), $id) {
        return $this->db->update('subscription_coupon_redeemed_history', $data, array('id' => $id));
    }

    //delete single payment history
    Public function deleteCouponRedeemedHistory($id) {
        $this->db->delete('subscription_coupon_redeemed_history', array('id' => $id));
        return $this->db->affected_rows();
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
        $this->db->from('subscription_coupon_redeemed_history');
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
