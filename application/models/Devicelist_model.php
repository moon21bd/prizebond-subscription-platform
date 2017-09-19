<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');

//require_once APPPATH . 'models/Main_model.php';

class Devicelist_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function __destruct() {
        $this->db->close();
    }

    public function getDeveloperDevicesInfo($id) {
        $query = $this->db->get_where('developer_devices', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row();
        }
    }

    public function updateDeveloperInfo($data, $id) {
        $this->db->update('developer_devices', $data, array('id' => $id));
        return TRUE;
    }

    public function getDevicsStatistics() {

        $this->db->select('app_version_name');
        $this->db->select('device_type');
        $this->db->group_by('app_version_name');
        $this->db->order_by('app_version_name', 'ASC');
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {
                $data[] = $this->getActiceDeviceByVersion(array('app_version_name' => $row->app_version_name, 'date(created_date_time) =' => date('Y-m-d')));
            }
        }

        return $data;
    }

    private function getActiceDeviceByVersion($where = array()) {
        $query = $this->db->get_where('device_info', $where);
        if ($query->num_rows()) {
            return $query->num_rows();
        } else {
            return FALSE;
        }
    }

    public function getNewsListByPagination($userId = NULL, $limit, $offset = 0, $startDate = NULL, $endDate = NULL, $searchQuery = NULL, $deviceStatus = NULL, $gcmStatus = NULL) {

        //  $this->db->select('id, news_title,source_newspaper_name,date_time');
        $this->db->order_by("id", "desc");
        $this->db->select('*')->from('device_info');
        if ($userId) {
            $this->db->where('user_id', $userId);
        }
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(added_date)>= '$startDate' AND date(added_date) <= '$endDate')");
        }
        if ($searchQuery) {
            $this->db->where("(device_uuid LIKE '%$searchQuery%'OR app_version_code LIKE '%$searchQuery%')");
        }
        if ($deviceStatus) {
            $this->db->where('device_status', $deviceStatus);
        }
        if ($gcmStatus) {
            $this->db->where('gcm_status', $gcmStatus);
        }

        $tempdb = clone $this->db;
        $finalResult['totalRow'] = $tempdb->count_all_results();

        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        //echo $this->db->last_query();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {

                $result[] = $row;
            }

            $finalResult['result'] = $result;
            return $finalResult;
        } else {
            return FALSE;
        }
    }

    public function getSearchData($searchData) {

        $str = "SELECT * FROM device_info WHERE device_uuid LIKE '%$searchData%' ORDER BY id DESC";

        $query = $this->db->query($str);

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function countRow() {

        $query = $this->db->get('device_info');
        if ($query->num_rows()) {
            return $query->num_rows();
        } else {
            return FALSE;
        }
    }

    public function countTotalDevice($table) {
        $sql = "SELECT COUNT(id) as total FROM $table";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getNotificationsData() {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('notifications', 5);
        return $query->result();
    }

    public function getDeviceInfoById($deviceId) {
        $query = $this->db->get_where('device_info', array('id' => $deviceId));
        if ($query->num_rows()) {
            $result = $query->result();
            return $result[0];
        } else {
            return FALSE;
        }
    }

    public function getDeviceInfoByUUID($deviceUUID) {
        $query = $this->db->get_where('device_info', array('device_uuid' => $deviceUUID));
        if ($query->num_rows()) {
            $result = $query->result();

            return $result[0];
        } else {
            return FALSE;
        }
    }

    public function addToDeveloperList($developerData) {
        $this->db->insert('developer_devices', $developerData);
        return TRUE;
    }

    public function getDeveloperDevices() {
        $query = $this->db->get('developer_devices');
        return $query->result();
    }

    public function deleteDeveloperDeviceById($developerId) {
        $this->db->delete('developer_devices', array('id' => $developerId));
        return TRUE;
    }

    public function countInstalledAppByAddedDate($addedDate, $deviceType) {
        $sql = "SELECT COUNT(id) as total FROM device_info WHERE DATE(added_date) ='$addedDate' AND device_type='$deviceType'";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function countActiveAppByAddedDate($addedDate, $deviceType) {
        $sql = "SELECT COUNT(id) as total FROM device_info WHERE DATE(update_date) ='$addedDate' AND device_type='$deviceType'";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getActiveDevice($deviceType) {
        $this->db->select('COUNT(id) as total');
        $query = $this->db->get_where('device_info', array('device_type' => $deviceType));
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalActivePushDevice($deviceType) {
        $this->db->select('COUNT(id) as total');
        $query = $this->db->get_where('device_info', array('device_type' => $deviceType, 'device_status' => 'active'));
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalManualPushOffDevice($deviceType) {
        $this->db->select('COUNT(id) as total');
        $query = $this->db->get_where('device_info', array('device_type' => $deviceType, 'device_status' => 'active', 'do_not_send_push_till !=' => '0000-00-00 00:00:00'));
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getVersionInfo() {
        $this->db->select('app_version_name,app_version_code');
        $this->db->group_by('app_version_code');
//        $this->db->order_by('app_version_code', 'DESC');
        $query = $this->db->get('device_info');
        if ($query->num_rows()) {
            $data = array();
            $finalArray = array();
            foreach ($query->result() as $row) {
                if ($row->app_version_name !== 'n/a') {
                    $data['varsion'] = $row->app_version_name;
                    $data['varsion_code'] = $row->app_version_code;
                    $data['android'] = $this->_countDeviceByAppVersionCode($row->app_version_code, 2);
                    $data['iOS'] = $this->_countDeviceByAppVersionCode($row->app_version_code, 1);
                    $data['installTodayAndroid'] = $this->countDevice('device_info', array('app_version_code' => $row->app_version_code, 'device_type ' => 2, 'date(added_date)=' => date('Y-m-d')));
                    $data['installTodayiOS'] = $this->countDevice('device_info', array('app_version_code' => $row->app_version_code, 'device_type ' => 1, 'date(added_date)=' => date('Y-m-d')));
                    $data['todayAndroidActive'] = $this->countDevice('device_info', array('app_version_code' => $row->app_version_code, 'device_type ' => 2, 'date(added_date)=' => date('Y-m-d'), 'device_status' => 'active'));
                    $data['todayiOSActive'] = $this->countDevice('device_info', array('app_version_code' => $row->app_version_code, 'device_type ' => 1, 'date(added_date)=' => date('Y-m-d'), 'device_status' => 'active'));
                    $finalArray[] = $data;
                }
            }
            return $finalArray;
        } else {
            return FALSE;
        }
    }

    private function countDevice($table, $where = FALSE) {
        $this->db->select('COUNT(id) as total')->from($table);
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get();
        return $query->row()->total;
    }

    private function _countDeviceByAppVersionCode($versionCode, $deviceType) {
        $this->db->select('COUNT(id) as total');
        $query = $this->db->get_where('device_info', array('app_version_code' => $versionCode, 'device_type ' => $deviceType));
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function _countDeviceByAppVersionName($versionName, $deviceType) {
        $this->db->select('COUNT(id) as total');
        $query = $this->db->get_where('device_info', array('app_version_name' => $versionName, 'device_type ' => $deviceType));
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function countDeviceByUnknownAppVersionName($deviceType) {
        $this->db->select('COUNT(id) as total');
        $query = $this->db->get_where('device_info', array('app_version_name' => 'n/a', 'device_type ' => $deviceType));
        if ($query->num_rows()) {
            return $query->row()->total;
        }
        return FALSE;
    }

    public function getUserUsesStatistics($uuid, $where = false) {

        // $sql = "SELECT news_id, device_uuid FROM push_tabbed_tracking WHERE `device_uuid` = '" . $uuid . "') UNION ( SELECT news_id, device_uuid FROM user_redirect_to_newspaper_history WHERE `device_uuid` = '" . $uuid . "')";
        //$sql = "(SELECT news_id, device_uuid, created_date_time FROM push_tabbed_tracking WHERE `device_uuid` = '" . $uuid . "') UNION (SELECT news_id, device_uuid, created_date_time FROM user_redirect_to_newspaper_history WHERE `device_uuid` = '" . $uuid . "')";
        if ($where) {
            $sql = "SELECT news_id, device_uuid, created_date_time FROM push_tabbed_tracking WHERE `device_uuid` = '" . $uuid . "' AND DATE(created_date_time) >='" . $where['startDate'] . "' AND DATE(created_date_time) <= '" . $where['endDate'] . "'";
        } else {
            $sql = "SELECT news_id, device_uuid, created_date_time FROM push_tabbed_tracking WHERE `device_uuid` = '" . $uuid . "'";
        }

        //echo $sql;

        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return $query->result();
        }
        return FALSE;
    }

    public function getUserNewsDetails($uuid, $where = false) {

        if ($where) {
            $sql = "SELECT news_id, device_uuid, created_date_time FROM user_redirect_to_newspaper_history WHERE `device_uuid` = '" . $uuid . "' AND DATE(created_date_time) >='" . $where['startDate'] . "' AND DATE(created_date_time) <= '" . $where['endDate'] . "' AND `sent_to_web` = 'NO'";
        } else {
            $sql = "SELECT news_id, device_uuid, created_date_time FROM user_redirect_to_newspaper_history WHERE `device_uuid` = '" . $uuid . "' AND `sent_to_web` = 'NO'";
        }
        //echo $sql;

        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return $query->result();
        }
        return FALSE;
    }

    public function getUserNewspaperRedirect($uuid, $where = false) {

        if ($where) {
            $sql = "SELECT news_id, device_uuid, created_date_time FROM user_redirect_to_newspaper_history WHERE `device_uuid` = '" . $uuid . "' AND DATE(created_date_time) >='" . $where['startDate'] . "' AND DATE(created_date_time) <= '" . $where['endDate'] . "' AND `sent_to_web` = 'YES'";
        } else {
            $sql = "SELECT news_id, device_uuid, created_date_time FROM user_redirect_to_newspaper_history WHERE `device_uuid` = '" . $uuid . "' AND `sent_to_web` = 'YES'";
        }
        //echo $sql;

        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return $query->result();
        }
        return FALSE;
    }

    public function getTopPushDevice($startDate, $endDate, $limit = false) {

        $sql = "SELECT tapped.device_uuid, org.message, org.created_date_time, org.source_newspaper_name, count(tapped.news_id) as tap FROM `push_messge_list` as org join push_tabbed_tracking as tapped on org.news_id=tapped.news_id where (date(org.created_date_time)>='" . $startDate . "' and date(org.created_date_time)<='" . $endDate . "') group by tapped.device_uuid order by tap desc ";
        if ($limit) {
            $sql .= " LIMIT " . $limit['start'] . " , " . $limit['item'];
        }
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {
                $query1 = $this->db->get_where('device_info', array('device_uuid' => $row->device_uuid));
                if ($query1->num_rows()) {
                    $row1 = $query1->row();
                    $row1->tap = $row->tap;
                    $data[] = $row1;
                }
            }

            return $data;
        }
        return FALSE;
    }

    public function getTopPushDeviceTotal($startDate, $endDate) {

        $sql = "SELECT tapped.device_uuid FROM `push_messge_list` as org join push_tabbed_tracking as tapped on org.news_id=tapped.news_id where (date(org.created_date_time)>='" . $startDate . "' and date(org.created_date_time)<='" . $endDate . "') group by tapped.device_uuid";
        $query = $this->db->query($sql);
        return $query->num_rows();
    }

    public function getTotalPushSent($startDate, $endDate) {

        $sql = "SELECT count(org.news_id) as total  FROM `push_messge_list` as org where (date(org.created_date_time)>='" . $startDate . "' and date(org.created_date_time)<='" . $endDate . "')";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result['total'];
        } else {
            return 0;
        }
    }

    public function getTotalPushTapped($startDate, $endDate) {

        $sql = "SELECT SUM( tap) AS total FROM (SELECT count(tapped.news_id) as tap FROM `push_messge_list` as org join push_tabbed_tracking as tapped on org.news_id=tapped.news_id where (date(org.created_date_time)>='" . $startDate . "' and date(org.created_date_time)<='" . $endDate . "') group by tapped.device_uuid order by tap desc) AS A";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return $result['total'];
        } else {
            return 0;
        }
    }

    public function getPushTappedNews($uuid, $offset, $limit) {
        $data = array();
        $sql = "SELECT * FROM push_tabbed_tracking WHERE  device_uuid ='" . $uuid . "' ORDER BY created_date_time DESC LIMIT $offset, $limit";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $query1 = $this->db->get_where('news_listing', array('id' => $row->news_id));
                if ($query1->num_rows()) {
                    $data[] = $query1->row();
                }
            }
            return $data;
        }
        return FALSE;
    }

}
