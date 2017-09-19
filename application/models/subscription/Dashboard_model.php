<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

class Dashboard_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getTotalCoupons() {
        $this->db->select('id, COUNT(id) as total');
        $query = $this->db->get('subscription_coupons');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalProducts() {
        $this->db->select('id, COUNT(id) as total');
        $query = $this->db->get('subscription_product_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalPurchesedProducts() {
        $this->db->select('id, COUNT(id) as total');
        $query = $this->db->get('subscription_product_purchase_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalCouponPayment() {
        $this->db->select_sum('rate')
                ->where('payment_method_type_id', 1)
                ->where('payment_status', 'success');
        $query = $this->db->get('subscription_order_list');
        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    public function getTotalBkashPayment() {
        $this->db->select_sum('rate')
                ->where('payment_method_type_id', 2)
                ->where('payment_status', 'success');
        $query = $this->db->get('subscription_order_list');
        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    public function sanboxData() {
        $this->db->order_by('id', 'DESC');
        $this->db->select('subscription_gateway_settings.*,subscription_gateways.name')->from('subscription_gateway_settings');
        $this->db->join('subscription_gateways', 'subscription_gateways.id = subscription_gateway_settings.gateway_id', 'left');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->result();
            return $result;
        } else {
            return FALSE;
        }
    }

    public function updateSandboxMood($id, $sandboxMood) {
        $this->db->update('subscription_gateway_settings', array('sandbox_mode' => $sandboxMood), array('id' => $id));
        return $this->db->affected_rows();
    }

}
