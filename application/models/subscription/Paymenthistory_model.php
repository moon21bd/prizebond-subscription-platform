<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Paymenthistory_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function savePaymentHistory($data = array()) {
        $this->db->insert('subscription_payment_history', $data);
        return $this->db->affected_rows();
    }

    public function viewOrderList($order_id) {
        $query = $this->db->get_where('subscription_order_list', array('reference_id' => $order_id));

        $result = array();

        foreach ($query->result() as $row) {
            $result['id'] = $query->row()->id;
            $result['order_id'] = $query->row()->reference_id;
            $result['user_id'] = $query->row()->user_id;
            $result['product_id'] = $this->getProductNameByProductId($query->row()->product_id);
            $result['payment_method_type_id'] = $this->getPaymentMethodByPaymentId($query->row()->payment_method_type_id);
            $result['price'] = $query->row()->rate;
            $result['order_status'] = $query->row()->order_status;
            $result['payment_status'] = $query->row()->payment_status;
            $result['order_created_date_time'] = $query->row()->order_created_date_time;
            $result['created_date_time'] = $query->row()->payment_date_time;
        }
        return $result;
    }

    private function getProductNameByProductId($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getPaymentMethodByPaymentId($paymentId) {

        $query = $this->db->get_where('subscription_payment_method_types', array('id' => $paymentId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function managePaymentHistory($limit, $offset, $params = array()) {
        $select = $this->db->select('*')->from('subscription_payment_history');
        if ($params) {
            if (isset($params['start_date']) && !empty($params['start_date']) && isset($params['end_date']) && !empty($params['end_date']))
                $this->db->where(array('date(created_date_time)>=' => $params['start_date'], 'date(created_date_time) <=' => $params['end_date']));
            
            if (isset($params['order_id']) && !empty($params['order_id']))
                $this->db->like("order_id", $params['order_id']);
        }
        if ($limit) {
            $select->limit($limit, $offset);
        }
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['payment_method_type_id'] = $this->getPaymentMethodPaymentMethodId($row->payment_method_type_id);
                $result[$i]['order_id'] = $row->order_id;
                $result[$i]['extra_info'] = $row->extra_info;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['date_time'] = $this->time_elapsed_string($row->created_date_time);
                $i++;
            }

            return $result;
        } else {
            return FALSE;
        }
    }

    private function getPaymentMethodPaymentMethodId($methodId) {

        $query = $this->db->get_where('subscription_payment_method_types', array('id' => $methodId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    public function getSinglePayment($id) {

        $this->db->select('*')->from('subscription_payment_history')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function getPaymentType() {
        $this->db->select('*')->from('subscription_payment_method_types');
        $query = $this->db->get();
        $response = array('' => '-Select-');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    public function getOrderList() {
        $this->db->select('*')->from('subscription_order_list');
        $query = $this->db->get();
        $response = array('' => '-Select-');
        foreach ($query->result() as $aData) {
            $response[$aData->order_id] = $aData->order_id;
        }
        return $response;
    }

    public function updatePaymentHistory($data = array(), $id) {
        return $this->db->update('subscription_payment_history', $data, array('id' => $id));
        //return $this->db->affected_rows();
    }

    //delete single payment history
    Public function deletePaymentHistory($id) {
        $this->db->delete('subscription_payment_history', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function numberOfRows($params = array()) {
        if ($params) {

            if (!empty($params['start_date'])) {
                $this->db->where('created_date_time >=', $params['start_date']);
            }

            if (!empty($params['end_date'])) {
                $this->db->where('created_date_time <=', $params['end_date']);
            }
            if (!empty($params['order_id'])) {
                $this->db->like('order_id', $params['order_id']);
            }
        }
        $this->db->from('subscription_payment_history');
        return $this->db->count_all_results();
    }

    public function viewPaymentHistory($id) {
        $query = $this->db->get_where('subscription_payment_history', array('id' => $id));
        $result = array();
        $result['id'] = $query->row()->id;
        $result['order_id'] = $query->row()->order_id;
        $result['payment_method'] = $this->getProListByProListId($query->row()->payment_method_type_id);
        $result['extra_info'] = $query->row()->extra_info;
        $result['created_date_time'] = $query->row()->created_date_time;
        return $result;
    }

    private function getProListByProListId($paymentTypeId) {

        $query = $this->db->get_where('subscription_payment_method_types', array('id' => $paymentTypeId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
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
