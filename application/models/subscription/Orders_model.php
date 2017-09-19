<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-05
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Orders_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getAppName() {
        $this->db->select('*')->from('app_list');
        $query = $this->db->get();
        $response = array('' => '--select--');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    public function getProductName() {
        $this->db->select('*')->from('subscription_product_list');
        $query = $this->db->get();
        $response = array('' => '--select--');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

    public function getPaymentMethod() {
        $this->db->select('*')->from('subscription_payment_method_types');
        $query = $this->db->get();
        $response = array('' => 'Payment Method Any');
        foreach ($query->result() as $aData) {
            $response[$aData->name] = 'Payment Method - ' . $aData->name;
        }
        return $response;
    }

    public function saveOrderList($data = array()) {
        $this->db->insert('subscription_order_list', $data);
        return $this->db->insert_id();
    }

    public function getLastId($data = array()) {
        $this->db->insert('subscription_order_list', $data);
        $last_id = $this->db->insert_id();
        return $last_id;
    }

    public function getPrizeBondSeriesName() {
        $this->db->select('*')->from('series_list');
        $query = $this->db->get();
        $response = array('' => '-- Series --');
        foreach ($query->result() as $aData) {
            $response[$aData->name] = $aData->name;
        }
        return $response;
    }

    public function getOrderList($userId = NULL, $limit, $offset = 0, $startDate = NULL, $endDate = NULL, $searchQuery = NULL, $paymentName = NULL, $paymentStatus = NULL, $orderStatus = NULL, $product = NULL, $orderBy = NULL, $order = NULL) {
       
        $queryComponents = array();
        $sql = $sql2 = "SELECT subscription_order_list.*, user.id AS user_id,user.name,user.user_id AS user_externel_id,subscription_payment_method_types.id AS payment_method_id ,subscription_payment_method_types.name AS payment_method_name FROM subscription_order_list";
        $sql .= " LEFT JOIN user ON user.id = subscription_order_list.user_id";
        $sql .= " LEFT JOIN subscription_payment_method_types ON subscription_payment_method_types.id = subscription_order_list.payment_method_type_id";

        if ($userId != NULL || ($startDate != NULL) || ($endDate != NULL)) {
            $sql .= " WHERE ";
        }
        if ($userId != NULL) {
            $queryComponents[] = "user.id = '$userId'";
        }
        if ($startDate != NULL && ($endDate != NULL) && ($userId != NULL)) {
            $queryComponents[] = "(date(subscription_order_list.order_created_date_time)>= '$startDate' AND date(subscription_order_list.order_created_date_time) <= '$endDate')";
        }

        if ($startDate != NULL && ($endDate != NULL)) {
            $queryComponents[] = "(date(subscription_order_list.order_created_date_time)>= '$startDate' AND date(subscription_order_list.order_created_date_time) <= '$endDate')";
        }

        if ($searchQuery != NULL) {
            $queryComponents[] = "(subscription_order_list.reference_id LIKE '%$searchQuery%'OR user.name LIKE '%$searchQuery%'OR subscription_payment_method_types.name LIKE '%$searchQuery%')";
        }
        if ($paymentName != NULL) {
            $queryComponents[] = "subscription_payment_method_types.name LIKE '%$paymentName%'";
        }
        if ($paymentStatus != NULL) {
            $queryComponents[] = "subscription_order_list.payment_status ='$paymentStatus'";
        }
        if ($orderStatus != NULL) {
            $queryComponents[] = "subscription_order_list.order_status ='$orderStatus'";
        }
        if ($product != NULL) {
            if ($product == 'prizebond') {
                $queryComponents[] = "subscription_order_list.product_id ='26'";
            } else {
                $queryComponents[] = "subscription_order_list.product_id !='26'";
            }
        }

        if (count($queryComponents) > 0) {
            $sql .= implode(" AND ", $queryComponents);
        }
       
        if (!empty($orderBy)) {
            $sql .= " ORDER BY $orderBy $order";
        } else {
            $sql .= " ORDER BY order_created_date_time DESC";
        }

        $totalSql = str_replace($sql2, "SELECT COUNT(subscription_order_list.id) AS total FROM subscription_order_list", $sql);
        $total = $this->db->query($totalSql)->row("total");

        if ((is_numeric($limit)) && (is_numeric($offset))) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $query = $this->db->query($sql);

        $this->session->set_userdata("order_query", $this->db->last_query());
        
        if ($query->num_rows() > 0) {

            $result = array();
            $i = 0;
            $totalAmount = 0;
            foreach ($query->result() as $row) {

                $result[$i]['id'] = $row->id;
                $result[$i]['order_id'] = $row->reference_id;
                $result[$i]['user_id'] = $row->user_id;
                $result[$i]['user_externel_id'] = $row->user_externel_id;
                $result[$i]['product_id'] = $row->product_id;
//                $result[$i]['user_name'] = $this->getUserNameByUserId($row->user_id);
                $result[$i]['user_name'] = $row->name;
                $result[$i]['product_name'] = $this->getProductNameByProductId($row->product_id);
                $result[$i]['payment_method_type_name'] = $this->getPaymentMethodByPaymentId($row->payment_method_type_id);
                $result[$i]['payment_note'] = $this->getPaymentNoteByOrderId($row->reference_id);
                $result[$i]['payment_method_type_id'] = $row->payment_method_type_id;
                $result[$i]['price'] = $row->rate;
                $result[$i]['quantity'] = $row->quantity;
                $result[$i]['total_receivable_amount'] = $row->total_receivable_amount;
                $totalAmount = $totalAmount + $row->total_receivable_amount;
                //$totalAmount = $totalAmount + $row->rate;
                $result[$i]['discount'] = $row->discount;
                $result[$i]['shipping_cost'] = $row->shipping_cost;
                $result[$i]['order_status'] = $row->order_status;
                $result[$i]['payment_status'] = $row->payment_status;
                $result[$i]['prizebond'] = $row->prizebond;
                $result[$i]['delivery_id'] = $row->delivery_id;
                $result[$i]['gateway_id'] = $row->gateway_transaction_id;
                $result[$i]['order_created_date_time'] = $row->order_created_date_time;
                $result[$i]['payment_date_time'] = $row->payment_date_time;
                $result[$i]['order_date'] = $this->time_elapsed_string($row->order_created_date_time, TRUE);
                if ($row->payment_date_time != '0000-00-00 00:00:00') {
                    $result[$i]['payment_date'] = $this->time_elapsed_string($row->payment_date_time, TRUE);
                } else {
                    $result[$i]['payment_date'] = '';
                }
                $i++;
            }

            return array('result' => $result, 'total' => $total, 'totalAmount' => $totalAmount);
        } else {
            return FALSE;
        }
    }

    public function getTotalSalesAmount($startDate, $endDate) {
        $this->db->select_sum('rate')
                ->where('payment_status', 'success')
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    public function getTotalBkashSalesAmount($startDate, $endDate) {
        $this->db->select_sum('rate')
                ->where('payment_status', 'success')
                ->where('payment_method_type_id', 2)
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    public function getTotalBkashSales($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')
                ->where('payment_status', 'success')
                ->where('payment_method_type_id', 2)
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalBracSalesAmount($startDate, $endDate) {
        $this->db->select_sum('rate')
                ->where('payment_status', 'success')
                ->where('payment_method_type_id', 6)
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    public function getTotalBracSales($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')
                ->where('payment_status', 'success')
                ->where('payment_method_type_id', 6)
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalCodSalesAmount($startDate, $endDate) {
        $this->db->select_sum('rate')
                ->where('payment_status', 'success')
                ->where('payment_method_type_id', 7)
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    public function getTotalCodSales($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')
                ->where('payment_status', 'success')
                ->where('payment_method_type_id', 7)
                ->where('order_status', 'completed');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(order_created_date_time)>= '$startDate' AND date(order_created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('subscription_order_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
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

    private function getAppNameByAppId($appId) {

        $query = $this->db->get_where('app_list', array('id' => $appId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getProductNameByProductId($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        } else {
            return FALSE;
        }
    }

    private function getPaymentNoteByOrderId($orderId) {

        $query = $this->db->get_where('subscription_payment_history', array('order_id' => $orderId));
        if ($query->num_rows() > 0) {
            return $query->row()->extra_info;
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

    // delete single row
    Public function deleteOrderList($id) {
        $this->db->delete('subscription_order_list', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function viewOrderList($id) {
        $query = $this->db->get_where('subscription_order_list', array('id' => $id));

        $result = array();

        foreach ($query->result() as $row) {
            $result['id'] = $query->row()->id;
            $result['order_id'] = $query->row()->reference_id;
            $result['user_id'] = $query->row()->user_id;
            $result['product_id'] = $this->getProductNameByProductId($query->row()->product_id);
            //$result['app_id'] = $this->getAppNameByAppId($query->row()->app_id);
            $result['payment_method_type_id'] = $this->getPaymentMethodByPaymentId($query->row()->payment_method_type_id);
            $result['price'] = $query->row()->rate;
            $result['order_status'] = $query->row()->order_status;
            $result['payment_status'] = $query->row()->payment_status;
            $result['order_created_date_time'] = $query->row()->order_created_date_time;
            $result['created_date_time'] = $query->row()->payment_date_time;
        }
        return $result;
    }

    public function getOrderInfoById($id) {
        $query = $this->db->get_where('subscription_order_list', array('id' => $id));

        $result = array();

        if ($query->result()) {
            $query->row()->order_id = $query->row()->reference_id;
            $query->row()->product_name = $this->getProductNameByProductId($query->row()->product_id);
            $query->row()->payment_method_type_name = $this->getPaymentMethodByPaymentId($query->row()->payment_method_type_id);
            $query->row()->price = $query->row()->rate;
            return $query->row();
        }
        return FALSE;
    }

    // get single data....(edit)
    public function editOrderList($id) {
        $this->db->select('*')->from('subscription_order_list')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function getUserInfoByUserId($id) {
        $this->db->select('*')->from('user')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function updateOrderList($data = array(), $id) {
        return $this->db->update('subscription_order_list', $data, array('id' => $id));
//        return $this->db->affected_rows();
    }

    // count number of rows for pagination...........
    public function numberOfRows($table) {

        $this->db->from($table);
        return $this->db->count_all_results();
    }

    public function userDetails($id) {
        $query = $this->db->get_where('user', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row();
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
            'y' => 'Y',
            'm' => 'M',
            'w' => 'W',
            'd' => 'D',
            'h' => 'h',
            'i' => 'm',
            's' => 's',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . '' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!$full)
            $string = array_slice($string, 0, 1);
        return $string ? implode(' ', $string) . ' ago' : 'just now';
    }

    public function updateCODStatus($id, $status) {
        $this->db->update('subscription_order_list', array('order_status' => $status), array('id' => $id));
        return $this->db->affected_rows();
    }

    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row();
        } else {
            return false;
        }
    }

}
