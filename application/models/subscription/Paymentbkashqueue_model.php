<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Paymentbkashqueue_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
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

    public function getAllPaymentBkashQueueList($limit, $offset) {
        $this->db->order_by("id", "desc");
        $query = $this->db->get('subscription_payment_bkash_queue', $limit, $offset);
        if ($query->num_rows() > 0) {

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['trxId'] = $row->trxId;
                $result[$i]['customer_mobile_number'] = $row->customer_mobile_number;
                $result[$i]['reference_no'] = $row->reference_no;
                $result[$i]['counter_no'] = $row->counter_no;
                $result[$i]['payment_status'] = $row->payment_status;
                $result[$i]['created_date'] = $row->created_date;
                $result[$i]['last_tried_date_time'] = $row->last_tried_date_time;
                $result[$i]['user_id'] = $row->user_id;
                $result[$i]['user_name'] = $this->getUserNameByUserId($row->user_id);
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    private function getUserNameByUserId($proListId) {

        $query = $this->db->get_where('user_list', array('id' => $proListId));
        if ($query->num_rows() > 0) {
            return $query->row()->full_name;
        } else {
            return FALSE;
        }
    }

    public function numberOfRows() {
        $this->db->from('subscription_payment_bkash_queue');
        return $this->db->count_all_results();
    }

}
