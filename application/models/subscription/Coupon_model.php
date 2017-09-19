<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Coupon_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getAppName() {
        $this->db->select('*')->from('app_list');
        $this->db->where('status', 1);
        $query = $this->db->get();
        $response = array('' => '-- Select --');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }
    public function getSeriesName() {
        $this->db->select('*')->from('subscription_coupon_series');
        $query = $this->db->get();
        $response = array('' => '-- Select --');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    public function doesExists($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function saveCoupon($data = array()) {
        $this->db->insert('subscription_coupons', $data);
        return $this->db->affected_rows();
    }

    public function getCouponSeries($limit, $offset) {
        $this->db->order_by("id", "desc");
        //$this->db->select('*')->from('coupons');
        $query = $this->db->get('subscription_coupons', $limit, $offset);
        if ($query->num_rows() > 0) {
            // return $query->result();

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['series_id'] = $this->getSeriesNameBySeriesId($row->series_id);
                //$result[$i]['app_id'] = $this->getAppNameByAppId($row->app_id);
                $result[$i]['product_list'] = $this->getProductListByCId($row->product_id);
                $result[$i]['product_status'] = $this->getProductListStatusByCId($row->product_id);
                $result[$i]['name'] = $row->coupon;
                $result[$i]['status'] = $row->status;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['added_date'] = $this->time_elapsed_string($row->created_date_time);
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    //get number of rows
    public function numberOfCoupons($coupon_info, $where = false) {
        if ($where) {
            $this->db->where($where);
        }
        $this->db->from($coupon_info);
        return $this->db->count_all_results();
    }

    public function viewCouponSeries($id) {
        $query = $this->db->get_where('subscription_coupons', array('id' => $id));

        $result = array();

        foreach ($query->result() as $row) {
            $result['id'] = $query->row()->id;
            $result['series_id'] = $this->getSeriesNameBySeriesId($query->row()->series_id);
            $result['product_list_id'] = $this->getProductNameByProductId($query->row()->product_id);
            //$result['app_id'] = $this->getAppNameByAppId($query->row()->app_id);
            $result['name'] = $query->row()->coupon;
            $result['note'] = $query->row()->note;
            $result['status'] = $query->row()->status;
        }
        return $result;
    }

    private function getAppNameByAppId($appId) {

        $query = $this->db->get_where('app_list', array('id' => $appId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductListByCId($CId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $CId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductListStatusByCId($productId) {
        $this->db->select('status')->from('subscription_product_list');
        $this->db->where('id', $productId);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row()->status;
        } else {
            return FALSE;
        }
    }

    private function getSeriesNameBySeriesId($serisId) {

        $query = $this->db->get_where('subscription_coupon_series', array('id' => $serisId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductNameByProductId($productListId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productListId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function ajax_call($app_id) {
        $this->db->select('*')->from('subscription_coupon_series')
                ->where('app_id', $app_id);
        $query = $this->db->get();
        return $query->result();
    }

    public function ajax_call_product($app_id) {
        $this->db->select('*')->from('product_list')
                ->where('app_list_id', $app_id)
                ->where('status', 1);
        $query = $this->db->get();
        return $query->result();
    }

    // delete single row
    Public function deleteCouponList($id) {
        $this->db->delete('subscription_coupons', array('id' => $id));
        return $this->db->affected_rows();
    }

    // get single data....(edit)
    public function editCoupon($id) {
        $this->db->select('*')->from('subscription_coupons')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    //---update data.....
    public function updateCoupon($data = array(), $id) {
        $this->db->update('subscription_coupons', $data, array('id' => $id));
        return $this->db->affected_rows();
    }

    public function updateStatus($id, $status) {
        $this->db->update('subscription_coupons', array('status' => $status), array('id' => $id));
        return $this->db->affected_rows();
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
