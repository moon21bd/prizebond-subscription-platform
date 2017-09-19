<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Couponseries_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function saveCouponSeries($data = array()) {
        $this->db->insert('subscription_coupon_series', $data);
        return $this->db->affected_rows();
    }
    public function getCouponSeries() {
        $this->db->select('*')->from('subscription_coupon_series');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            // return $query->result();

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['name'] = $row->name;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }
    
}