<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-04-26
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Useractivitylog_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUsersActivityLogs($userId = NULL, $limit, $offset = 0, $startDate = NULL, $endDate = NULL, $searchQuery = NULL) {
        $queryComponents = array();

        $sql = $sql2 = "SELECT user_activity_logs.*, user.id AS user_id,user.name FROM user_activity_logs";
        $sql .= " LEFT JOIN user ON user.id = user_activity_logs.user_id";

        if ($userId != NULL || ($startDate != NULL) || ($endDate != NULL)) {
            $sql .= " WHERE ";
        }
        if ($userId != NULL) {
            $queryComponents[] = "user.id = '$userId'";
        }
        if ($startDate != NULL && ($endDate != NULL) && ($userId != NULL)) {
            $queryComponents[] = "(date(user_activity_logs.created_date_time)>= '$startDate' AND date(user_activity_logs.created_date_time) <= '$endDate')";
        }

        if ($startDate != NULL && ($endDate != NULL)) {
            $queryComponents[] = "(date(user_activity_logs.created_date_time)>= '$startDate' AND date(user_activity_logs.created_date_time) <= '$endDate')";
        }

        if ($searchQuery != NULL) {
            $queryComponents[] = "user_activity_logs.code LIKE '%$searchQuery%'OR user.name LIKE '%$searchQuery%'OR user_activity_logs.device_uuid LIKE '%$searchQuery%'OR user_activity_logs.source_url LIKE '%$searchQuery%'";
        }
        
        if (count($queryComponents) > 0) {
            $sql .= implode(" AND ", $queryComponents);
        }

        $sql .= " ORDER BY user_activity_logs.created_date_time DESC";
        $totalSql = str_replace($sql2, "SELECT COUNT(user_activity_logs.id) AS total FROM user_activity_logs", $sql);
        $total = $this->db->query($totalSql)->row("total");

        if ((is_numeric($limit)) && (is_numeric($offset))) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $query = $this->db->query($sql);
        
//        echo $this->db->last_query();

        if ($query->num_rows() > 0) {
            return array('result' => $query->result(), 'total' => $total);
        } else {
            return FALSE;
        }
    }

    public function viewUserActivityLog($id) {
        $query = $this->db->get_where('user_activity_logs', array('id' => $id));

        $result = array();
        if ($query->num_rows() > 0) {
//            foreach ($query->result() as $row) {
//                $result['id'] = $query->row()->id;
//                $result['order_id'] = $query->row()->reference_id;
//                $result['user_id'] = $query->row()->user_id;
//                $result['product_id'] = $this->getProductNameByProductId($query->row()->product_id);
//                $result['payment_method_type_id'] = $this->getPaymentMethodByPaymentId($query->row()->payment_method_type_id);
//                $result['price'] = $query->row()->rate;
//                $result['order_status'] = $query->row()->order_status;
//                $result['payment_status'] = $query->row()->payment_status;
//                $result['order_created_date_time'] = $query->row()->order_created_date_time;
//                $result['created_date_time'] = $query->row()->payment_date_time;
//            }
            return $query->row();
        }
    }

}
