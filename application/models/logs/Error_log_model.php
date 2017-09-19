<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Error_log_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function errorLogList($limit, $offset) {

        $db = $this->session->userdata('_currentProjectDB');
        $ex = explode('_', $db);
        $this->db->order_by("id", "desc");
        $this->db->where('country', $ex[0]);
        $query = $this->db->get('error_logs', $limit, $offset);
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['log_type'] = $row->log_type;
                $result[$i]['country'] = $row->country;
                $result[$i]['newspaper_id'] = $row->newspaper_id;
                $result[$i]['message'] = $row->message;
                $result[$i]['added_date'] = $row->added_date;
                $i++;
            }

            return $result;
        } else {
            return FALSE;
        }
    }

    public function getNewpaperNameById($newspaperId) {
        $query = $this->db->get_where('newspapers', array('id' => $newspaperId));
        return $query->row()->name;
    }

    public function totalNumberOfRows() {
        $this->db->from('error_logs');
        return $this->db->count_all_results();
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
            $queryComponents[] = "(user_activity_logs.code LIKE '%$searchQuery%'OR user.name LIKE '%$searchQuery%'OR user_activity_logs.device_uuid LIKE '%$searchQuery%'OR user_activity_logs.source_url LIKE '%$searchQuery%')";
        }

        if (count($queryComponents) > 0) {
            $sql .= implode(" AND ", $queryComponents);
        }

        //$sql .= " ORDER BY user_activity_logs.created_date_time DESC";
        $sql .= " ORDER BY user_activity_logs.id DESC";

//        $totalSql = str_replace($sql2, "SELECT COUNT(user_activity_logs.id) AS total FROM user_activity_logs", $sql);
        $totalSql = "SELECT COUNT(user_activity_logs.id) AS total FROM user_activity_logs WHERE (date(user_activity_logs.created_date_time)>= '$startDate' AND date(user_activity_logs.created_date_time) <= '$endDate')";

        $total = $this->db->query($totalSql)->row("total");


        if ((is_numeric($limit)) && (is_numeric($offset))) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return array('result' => $query->result(), 'total' => $total);
        } else {
            return FALSE;
        }
    }

    public function getFlagLogsOfAUserByUserId($userId, $limit = NULL, $offset = 0) {
        $select = $this->db->select('*')->from('user_flag_logs')->where('user_id', $userId);
        $this->db->order_by("id", "DESC");

        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result();
    }

    public function viewUserActivityLog($id) {
        $query = $this->db->get_where('user_activity_logs', array('id' => $id));

        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    public function updateUserStatus($userId, $status) {
        $this->db->update('user', array('status' => $status), array('id' => $userId));
        return $this->db->affected_rows();
    }

    public function getUserFlagLogs($userId = NULL, $limit, $offset, $searchInfo = '') {
        $finalResult = array();
        $this->db->select('*')->from('user_flag_logs');
        if (!empty($userId)) {
            $this->db->where('user_id', $userId);
        }
        if (!empty($searchInfo)) {
            $this->db->where("(device_uuid '%$searchInfo%'OR message LIKE '%$searchInfo%')");
        }
        $tempdb = clone $this->db;
        $finalResult['totalRow'] = $tempdb->count_all_results();

        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $finalResult['result'] = $query->result();
            return $finalResult;
        }
        return FALSE;
    }

    public function getSmsResponseList($limit = 0, $offset = 0, $startDate, $endDate) {
        $finalResult = array();
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $this->db->select('*')->from('sms_response');
        $this->db->order_by("id", "DESC");
        $tempdb = clone $this->db;
        $finalResult['totalRow'] = $tempdb->count_all_results();

        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {

                $result[$i]['id'] = $row->id;
                $result[$i]['response_message'] = $row->response_message;
                $result[$i]['recipient_mobile_number'] = $row->recipient_mobile_number;
                $result[$i]['message'] = $row->message;
                $result[$i]['sender_mask'] = $row->sender_mask;
                $result[$i]['response_type'] = $row->response_type;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $i++;
            }
            $finalResult['result'] = $result;

            return $finalResult;
        }
    }

}
