<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-14
 */
defined('BASEPATH') OR exit('No direct script access allowed');
//require_once APPPATH . 'models/Main_model.php';

class Users_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }
    
    public function __destruct() {
        $this->db->close();
    }

    public function getAddedTotalUserByDate($date, $deviceType) {

        $sql = "SELECT count(*) AS device_count FROM device_info WHERE  DATE(added_date) ='" . $date . "' AND device_type='" . $deviceType . "'";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                return $row->device_count;
            }
        }
    }

    public function getTotalActiveUserByDate($date, $deviceType) {

        $sql = "SELECT count(*) AS device_count FROM device_activity_log WHERE update_date ='" . $date . "' AND device_type='" . $deviceType . "'";
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                return $row->device_count;
            }
        }
    }

    public function countUsersByDateRange($startDate, $endDate, $date, $device_type) {

        $sql = "SELECT count(*) AS device_count FROM device_activity_log WHERE update_date='" . $date . "' AND added_date >='" . $endDate . "' AND added_date <= '" . $startDate . "' AND device_type='" . $device_type . "'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {

                $total = $this->getTotalActiveUserByDate($date, $device_type);

                if (!$total == '0') {
                    $result = $row->device_count / $total * 100;
                    $result = sprintf("%.2f", $result);
                } else {
                    $result = 0;
                }

                return $row->device_count . '<br/>(' . $result . '%)';
            }
        }
    }

    public function countUsersBeforeOneYear($date, $preThreeSixtyFiveDays, $deviceType) {

        $sql = "SELECT count(*) AS device_count FROM device_activity_log WHERE update_date='" . $date . "' AND added_date <='" . $preThreeSixtyFiveDays . "'  AND device_type='" . $deviceType . "' ";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {

                $total = $this->getTotalActiveUserByDate($date, $deviceType);

                if (!$total == '0') {
                    $result = $row->device_count / $total * 100;
                    $result = sprintf("%.2f", $result);
                } else {
                    $result = 0;
                }
                return $row->device_count . '<br/>(' . $result . '%)';
            }
        }
    }

    public function getNotificationsData() {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('notifications', 5);
        return $query->result();
    }

}
