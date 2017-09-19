<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-18
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        $this->db->close();
    }

    //save draw year
    public function saveResultYear($saveData) {
        $this->db->insert('result_year', $saveData);
        return $this->db->affected_rows();
    }

    //get all year
    public function selectAllYear() {

        $this->db->select('*')->from('result_year');
        $this->db->order_by("year", "DESC");
        $query = $this->db->get();
        return $query->result();
    }

    //get single drawyear
    public function singleYear($id) {
        $this->db->select('*')->from('result_year')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    //update drawyear
    public function updateYear($editData = array(), $yearID) {
        return $this->db->update('result_year', $editData, array('id' => $yearID));
    }

    //delete drawYear
    public function deleteYear($yearID) {
        $this->db->delete('result_year', array('id' => $yearID));
        return $this->db->affected_rows();
    }

    public function savePrizeBondInfo($data) {
        $this->db->insert('prizebond_result_info', $data);
        return $this->db->insert_id();
    }

    public function saveFirstPrizeBondInfo($data) {
        $this->db->insert('prizebond_result_bond_list', $data);
        return $this->db->affected_rows();
    }

    public function saveSecondPrizeBondInfo($data) {
        $this->db->insert('prizebond_result_bond_list', $data);
        return $this->db->affected_rows();
    }

    public function saveThirdPrizeBondInfo($data) {
        $this->db->insert('prizebond_result_bond_list', $data);
        return $this->db->affected_rows();
    }

    public function saveFourthPrizeBondInfo($data) {
        $this->db->insert('prizebond_result_bond_list', $data);
        return $this->db->affected_rows();
    }

    public function saveFifthPrizeBondInfo($data) {
        $this->db->insert('prizebond_result_bond_list', $data);
        return $this->db->affected_rows();
    }

    public function getPrizeBondDrawInfo() {
        $this->db->order_by("result_number", "DESC");
        $query = $this->db->get('prizebond_result_info');
        return $query->result();
    }

    public function getExistingData($id) {

        $query = $this->db->get_where('prize_bond_draw_info', array('draw_num' => $id));
        $bond_id = $query->row()->id;
        $bond_series = $query->row()->list_of_series;

        $query = $this->db->get_where('first_prize_data', array('prize_bond_id' => $bond_id));
        $winner_bond_num = $query->row()->winner_bond_num;
        $query = $this->db->get_where('second_prize_data', array('prize_bond_id' => $bond_id));
        $winner_bond_num1 = $query->row()->winner_bond_num;
        $resutl['first_second_bonds'] = $winner_bond_num . ',' . $winner_bond_num1;

        $query = $this->db->get_where('third_prize_data', array('prize_bond_id' => $bond_id));
        foreach ($query->result() as $row) {
            $third[] = $row->winner_bond_num;
        }
        $third = array_reverse($third);
        $resutl['third_bonds'] = implode(',', $third);


        $query = $this->db->get_where('fourth_prize_data', array('prize_bond_id' => $bond_id));
        foreach ($query->result() as $row) {
            $fourth[] = $row->winner_bond_num;
        }
        $fourth = array_reverse($fourth);
        $resutl['fourth_bonds'] = implode(',', $fourth);

        $query = $this->db->get_where('fifth_prize_data', array('prize_bond_id' => $bond_id));
        foreach ($query->result() as $row) {
            $fifth[] = $row->winner_bond_num;
        }

        $trimmed_array = array_map('trim', $fifth);
        $fifth = array_reverse($trimmed_array);
        //$resutl['fifth_bonds'] = implode(' ', $trimmed_array);
        $resutl['fifth_bonds'] = implode(' ', $fifth);
        $resutl['total_fifth_number'] = count($fifth);
        $resutl['hash'] = md5(serialize($resutl));
        $resutl['bond_series'] = $bond_series;
        return $resutl;
    }

    public function getDrawExistingData($id) {
        $data = array();

        $first_prize_bond = $this->getPrizeBondNumber($id, '1st');


        $second_prize_bond = $this->getPrizeBondNumber($id, '2nd');
        $first_prize_bond_number = '';
        if ($first_prize_bond[0]) {
            $first_prize_bond_number = $first_prize_bond[0]->bond_number;
        }
        $second_prize_bond_number = '';
        if ($second_prize_bond[0]) {
            $second_prize_bond_number = $second_prize_bond[0]->bond_number;
        }

        if ($first_prize_bond_number != '' && $second_prize_bond_number != '') {
            $data['first_second_bonds'] = $first_prize_bond_number . ',' . $second_prize_bond_number;
        }

        $third_winners_num = array();
        $third_prize_bond = $this->getPrizeBondNumber($id, '3rd');
        if (!empty($third_prize_bond)) {
            foreach ($third_prize_bond as $third_data) {
                $third_winners_num[] = $third_data->bond_number;
            }
        }
        $data['third_bonds'] = implode(',', $third_winners_num);
        $fourth_winners_num = array();
        $fourth_prize_bond = $this->getPrizeBondNumber($id, '4th');
        if (!empty($fourth_prize_bond)) {
            foreach ($fourth_prize_bond as $fourth_data) {
                $fourth_winners_num[] = $fourth_data->bond_number;
            }
        }
        $data['fourth_bonds'] = implode(',', $fourth_winners_num);
        $fifth_winners_num = array();
        $fifth_prize_bond = $this->getPrizeBondNumber($id, '5th');
        if (!empty($fifth_prize_bond)) {
            foreach ($fifth_prize_bond as $fifth_data) {
                $fifth_winners_num[] = $fifth_data->bond_number;
            }
        }
        $trimmed_array = array_map('trim', $fifth_winners_num);
        $fifth_winners_num = array_reverse($trimmed_array);
        $data['fifth_bonds'] = implode(',', $fifth_winners_num);
        $data['total_fifth_number'] = count($fifth_winners_num);
        $data['hash'] = md5(serialize($data));
        //$data['bond_series'] = $bond_series;
        return $data;
    }

    public function getDrawNumber() {
        $this->db->order_by("result_number", "DESC");
        $this->db->select('*')->from('prizebond_result_info');
        $query = $this->db->get();
        $response = array('' => '--Select--');
        foreach ($query->result() as $draw) {
            $response[$draw->id] = $draw->result_number;
        }
        return $response;
    }

    public function getSeries() {
        $this->db->select('name')->from('series_list');
        $query = $this->db->get();

        if ($query->num_rows()) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getLastPrizebondId() {
        $query = $this->db->order_by('id', 'desc')->get('prize_bond_draw_info', 1);
        return $query->row_array();
    }

    public function getAPrizeBondDrawInfo($id) {
        $query = $this->db->get_where('prize_bond_draw_info', array('id' => $id));
        return $query->row();
    }

    public function getAFirstPrizeData($id) {
        $query = $this->db->get_where('first_prize_data', array('prize_bond_id' => $id));
        return $query->row();
    }

    public function getPrizeBondInfo($id) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $id));
        return $query->row();
    }

    public function getPrizeBondNumber($id, $position) {
        $query = $this->db->get_where('prizebond_result_bond_list', array('prizebond_result_info_id' => $id, 'prize_position' => $position));
        if ($query->result()) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function getASecondPrizeData($id) {
        $query = $this->db->get_where('second_prize_data', array('prize_bond_id' => $id));
        return $query->row();
    }

    public function getAThirdPrizeData($id) {
        $query = $this->db->order_by('id', 'ASC')->get_where('third_prize_data', array('prize_bond_id' => $id));
        return $query->result();
    }

    public function getSingleThirdPrize($id) {
        $this->db->select('*')->from('third_prize_data')->where('prize_bond_id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function getAFourthPrizeData($id) {
        $query = $this->db->order_by('id', 'ASC')->get_where('fourth_prize_data', array('prize_bond_id' => $id));
        return $query->result();
    }

    public function getSingleFourthPrize($id) {
        $this->db->select('*')->from('fourth_prize_data')->where('prize_bond_id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function getAFifthPrizeData($id) {
        $query = $this->db->order_by('id', 'ASC')->get_where('fifth_prize_data', array('prize_bond_id' => $id));
        return $query->result();
    }

    public function getSingleFifthPrize($id) {
        $this->db->select('*')->from('fifth_prize_data')->where('prize_bond_id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function updatePrizeBondInfo($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('prizebond_result_info', $data);
    }

    public function updatePrizeBondNmber($id, $prize_position, $data) {
        $this->db->where('prizebond_result_info_id', $id)
                ->where('prize_position', $prize_position);

        return $this->db->update('prizebond_result_bond_list', $data);
    }

    public function updateFirstPrizeBondInfo($data, $id) {
        $this->db->where('prizebond_result_info_id', $id);
        //->where('prize_position','1st');

        return $this->db->update('prizebond_result_bond_list', $data);
    }

    public function updateSecondPrizeBondInfo($data, $id) {
        $this->db->where('prizebond_result_info_id', $id);
        //->where('prize_position','2nd');

        return $this->db->update('prizebond_result_bond_list', $data);
    }

    public function updateThirdPrizeBondInfo($data, $id) {
        $this->db->where('prizebond_result_info_id', $id);
        //->where('prize_position','3rd');
        return $this->db->update('prizebond_result_bond_list', $data);
    }

    public function updateFourthPrizeBondInfo($data, $id) {
        $this->db->where('prizebond_result_info_id', $id);
        //->where('prize_position','4th');
        return $this->db->update('prizebond_result_bond_list', $data);
    }

    public function updateFifthPrizeBondInfo($data, $id) {
        $this->db->where('prizebond_result_info_id', $id);
        //->where('prize_position','5th');
        return $this->db->update('prizebond_result_bond_list', $data);
    }

    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function getDeviceInfo() {

        $data = array();
        $offsetQuery = $this->db->order_by('id', 'desc')->get('offset', 1);
        $offsetResult = $offsetQuery->row_array();
        $offset = $offsetResult['last_offset'];
        $query = $this->db->get('user_device_info', 10, $offset);
        if ($query) {
            $offset = $offset + 20;
            $data['last_offset'] = $offset;
            $this->db->insert('offset', $data);
        }
        return $query->result();
    }

    public function resetPushStatus() {
        $this->db->update('user_device_info', array('push_send' => 0));
    }

    public function getSendPushData() {

        $query = $this->db->order_by('id', 'desc')->get('tbl_push_send', 1);
        return $query->row_array();
    }

    public function saveDrawInfoTablePushStatus($id) {
        $this->db->where('id', $id);
        return $this->db->update('prize_bond_draw_info', array('push_status' => 1));
    }

    public function savePushStatus($id) {
        $this->db->where('id', $id);
        return $this->db->update('user_device_info', array('push_send' => 1));
    }

    public function getDeviceInfoForSendPush() {
        $query = $this->db->select('*')
                ->from('user_device_info')
                ->where('push_send', 0)
                ->where('status', 1)
                ->limit(2);
        return $this->db->get()->result();
    }

    public function getLatestPrizebondDraw() {
        $query = $this->db->select('*')
                ->from('prize_bond_draw_info')
                ->where('push_status', 0)
                ->limit(1)
                ->order_by('id', "desc");
        if ($result = $this->db->get()->row()) {
            return $result;
        } else {
            return false;
        }
    }

    public function numberOfRows($table, $params = array()) {
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
        $this->db->from($table);
        return $this->db->count_all_results();
    }

    // moved in user_model 
    // 
//    public function getUserInfo($limit = 0, $offset = 0, $startDate, $endDate, $serchInfo = '', $serchArray = array()) {
//
//        /*
//          SELECT locations.id, title, name, hours.lobby
//          FROM locations
//          LEFT JOIN states ON states.id = locations.state_id
//          LEFT JOIN (SELECT location_id, type_id AS lobby FROM location_hours
//          WHERE type_id IS NOT NULL) AS hours ON locations.id = hours.location_id
//          GROUP BY locations.id'; */
//
//        $finalResult = array();
//
//        if (!empty($serchArray)) {
//
//            if (isset($serchArray['user_type_id']) || (isset($serchArray['status'])) || (isset($serchArray['flag_status']))) {
//                if (!empty($startDate) && (!empty($endDate))) {
//                    $this->db->where("(date(user.created_date_time)>= '$startDate' AND date(user.created_date_time) <= '$endDate')");
//                }
//
//                if (isset($serchArray['user_type_id']) && !empty($serchArray['user_type_id'])) {
//                    $this->db->where('user_type_id', $serchArray['user_type_id']);
//                }
//
//                if (isset($serchArray['status'])) {
//                    $this->db->where("status", $serchArray['status']);
//                }
//                if (isset($serchArray['flag_status']) && !empty($serchArray['flag_status'])) {
//                    $this->db->where("flag_status", $serchArray['flag_status']);
//                }
//
//                $this->db->select('*')->from('user');
//                $this->db->order_by("id", "DESC");
//                $tempdb = clone $this->db;
//                $finalResult['totalRow'] = $tempdb->count_all_results();
//                $this->db->limit($limit, $offset);
//                $query = $this->db->get();
//            } else {
//
//                $prefix1 = "SELECT COUNT(user.id) AS total";
//                $prefix2 = "SELECT user.*";
//                $suffix = "LIMIT $limit OFFSET $offset";
//
//                if (isset($serchArray['highest_paid_subscribers'])) {
//                    /*
//                      $sql = "SELECT user.*, paid_users.highest_purchased FROM user
//                      LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased
//                      FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased
//                      FROM subscription_order_list WHERE total_receivable_amount > 0
//                      AND payment_status='success' GROUP BY user_id ) a GROUP by user_id
//                      ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id
//                      WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'
//                      AND paid_users.highest_purchased is not NULL GROUP BY user.id
//                      ORDER BY paid_users.highest_purchased DESC LIMIT $limit OFFSET $offset "; */
//
//                    $sql_middle = ", paid_users.highest_purchased FROM user
//                                LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased 
//                                FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased
//                                FROM subscription_order_list WHERE total_receivable_amount > 0 
//                                AND payment_status='success' GROUP BY user_id ) a GROUP by user_id
//                                ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id
//                                WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'
//                                AND paid_users.highest_purchased is not NULL GROUP BY user.id
//                                ORDER BY paid_users.highest_purchased DESC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } elseif (isset($serchArray['lowest_paid_subscribers'])) {
//                    $sql_middle = ", paid_users.highest_purchased FROM user 
//                        LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased 
//                        FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased
//                        FROM subscription_order_list WHERE total_receivable_amount > 0 
//                        AND payment_status='success' GROUP BY user_id ) a GROUP by user_id
//                        ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id 
//                        WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'
//                        AND paid_users.highest_purchased is not NULL GROUP BY user.id
//                        ORDER BY paid_users.highest_purchased ASC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } elseif (isset($serchArray['maximum_bonds_added'])) {
//                    $sql_middle = ", bond_added.maximum_bond_added FROM user "
//                            . "LEFT JOIN (SELECT user_id, MAX(bond_added_by_user) AS maximum_bond_added "
//                            . "FROM ( SELECT user_id, COUNT(id) AS bond_added_by_user FROM user_prizebond_list "
//                            . "GROUP BY user_id ORDER BY bond_added_by_user DESC ) a GROUP by user_id ORDER BY "
//                            . "maximum_bond_added DESC) AS bond_added ON user.id = bond_added.user_id "
//                            . "WHERE created_date_time > '$startDate' AND created_date_time < '$endDate' "
//                            . "AND bond_added.maximum_bond_added is not NULL GROUP BY user.id "
//                            . "ORDER BY `bond_added`.`maximum_bond_added` DESC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } elseif (isset($serchArray['minimum_bonds_added'])) {
//                    $sql_middle = ", bond_added.maximum_bond_added FROM user "
//                            . "LEFT JOIN (SELECT user_id, MAX(bond_added_by_user) AS maximum_bond_added "
//                            . "FROM ( SELECT user_id, COUNT(id) AS bond_added_by_user FROM user_prizebond_list "
//                            . "GROUP BY user_id ORDER BY bond_added_by_user DESC ) a GROUP by user_id ORDER BY "
//                            . "maximum_bond_added DESC) AS bond_added ON user.id = bond_added.user_id "
//                            . "WHERE created_date_time > '$startDate' AND created_date_time < '$endDate' "
//                            . "AND bond_added.maximum_bond_added is not NULL GROUP BY user.id "
//                            . "ORDER BY `bond_added`.`maximum_bond_added` ASC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } elseif (isset($serchArray['maximum_bonds_overloaded'])) {
//
//                    $sql_middle = ", overloaded_users.total_overloaded 
//                                    FROM user 
//                                    LEFT JOIN (SELECT subscription_product_purchase_list.user_id,
//                                    bond_added_info.bond_added_by_user - SUM(attributes.bond_capacity) AS total_overloaded
//                                    FROM subscription_product_purchase_list LEFT JOIN (SELECT user_id, COUNT(id) 
//                                    AS bond_added_by_user FROM user_prizebond_list GROUP BY user_id ORDER BY bond_added_by_user DESC) 
//                                    AS bond_added_info ON subscription_product_purchase_list.user_id = bond_added_info.user_id 
//                                    LEFT JOIN (SELECT product_id, value AS bond_capacity FROM subscription_product_attribute_values 
//                                    WHERE attribute_id = 2) AS attributes ON subscription_product_purchase_list.product_id = attributes.product_id 
//                                    GROUP BY subscription_product_purchase_list.user_id ORDER BY total_overloaded DESC) 
//                                    AS overloaded_users ON user.id = overloaded_users.user_id 
//                                    WHERE user.created_date_time > '$startDate' AND user.created_date_time < '$endDate' 
//                                    AND overloaded_users.total_overloaded > 0
//                                    GROUP BY user.id ORDER BY overloaded_users.total_overloaded DESC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } elseif (isset($serchArray['minimum_bonds_overloaded'])) {
//
//                    $sql_middle = ", overloaded_users.total_overloaded 
//                        FROM user 
//                        LEFT JOIN (SELECT subscription_product_purchase_list.user_id, bond_added_info.bond_added_by_user - SUM(attributes.bond_capacity)
//                        AS total_overloaded FROM subscription_product_purchase_list
//                        LEFT JOIN (SELECT user_id, COUNT(id) AS bond_added_by_user 
//                        FROM user_prizebond_list GROUP BY user_id ORDER BY bond_added_by_user DESC) 
//                        AS bond_added_info ON subscription_product_purchase_list.user_id = bond_added_info.user_id 
//                        LEFT JOIN (SELECT product_id, value AS bond_capacity FROM subscription_product_attribute_values 
//                        WHERE attribute_id = 2) AS attributes ON subscription_product_purchase_list.product_id = attributes.product_id 
//                        GROUP BY subscription_product_purchase_list.user_id ORDER BY total_overloaded DESC) 
//                        AS overloaded_users ON user.id = overloaded_users.user_id 
//                        WHERE user.created_date_time > '$startDate' AND user.created_date_time < '$endDate' 
//                        AND overloaded_users.total_overloaded > 0
//                        GROUP BY user.id ORDER BY overloaded_users.total_overloaded ASC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['highest_order_pending'])) {
//
//                    $sql_middle = ",orders_pending.pending AS total_pending FROM user
//                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS pending FROM subscription_order_list 
//                    WHERE order_status='pending' GROUP BY user_id ORDER BY pending DESC) 
//                    AS orders_pending ON orders_pending.user_id = user.id 
//                    WHERE orders_pending.pending > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                    GROUP BY user.id ORDER BY orders_pending.pending DESC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['lowest_order_pending'])) {
//
//                    $sql_middle = ",orders_pending.pending AS total_pending FROM user
//                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS pending FROM subscription_order_list 
//                    WHERE order_status='pending' GROUP BY user_id ORDER BY pending DESC) 
//                    AS orders_pending ON orders_pending.user_id = user.id 
//                    WHERE orders_pending.pending > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                    GROUP BY user.id ORDER BY orders_pending.pending ASC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['highest_order_failed'])) {
//
//                    $sql_middle = ",orders_failed.failed AS total_failed FROM user
//                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS failed FROM subscription_order_list 
//                    WHERE order_status='failed' GROUP BY user_id ORDER BY failed DESC) 
//                    AS orders_failed ON orders_failed.user_id = user.id 
//                    WHERE orders_failed.failed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                    GROUP BY user.id ORDER BY orders_failed.failed DESC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['lowest_order_failed'])) {
//
//                    $sql_middle = ",orders_failed.failed AS total_failed FROM user
//                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS failed FROM subscription_order_list 
//                    WHERE order_status='failed' GROUP BY user_id ORDER BY failed DESC) 
//                    AS orders_failed ON orders_failed.user_id = user.id 
//                    WHERE orders_failed.failed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                    GROUP BY user.id ORDER BY orders_failed.failed ASC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['highest_order_completed'])) {
//
//                    $sql_middle = ",orders_completed.completed AS total_completed
//                    FROM user
//                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS completed FROM subscription_order_list 
//                    WHERE order_status='completed' GROUP BY user_id ORDER BY completed DESC) 
//                    AS orders_completed ON orders_completed.user_id = user.id
//                    WHERE orders_completed.completed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                    GROUP BY user.id ORDER BY orders_completed.completed DESC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['lowest_order_completed'])) {
//
//                    $sql_middle = ",orders_completed.completed AS total_completed FROM user
//                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS completed FROM subscription_order_list 
//                    WHERE order_status='completed' GROUP BY user_id ORDER BY completed DESC) 
//                    AS orders_completed ON orders_completed.user_id = user.id
//                    WHERE orders_completed.completed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                    GROUP BY user.id ORDER BY orders_completed.completed ASC ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                } else if (isset($serchArray['subscriber'])) {
//                    $sql_middle = " FROM user
//                        LEFT JOIN (SELECT user_id FROM `subscription_product_purchase_list`
//                        WHERE `product_id` != 1 AND `status` = 1 AND `valid_end_datetime` > NOW() GROUP BY user_id) 
//                        AS subscribers ON subscribers.user_id = user.id
//                        WHERE user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'
//                        GROUP BY user.id ORDER BY user.id ";
//
//                    $sql1 = $prefix1 . $sql_middle;
//                    $sql2 = $prefix2 . $sql_middle . $suffix;
//                    $queryRowCount = $this->db->query($sql1);
//                    $finalResult['totalRow'] = $queryRowCount->num_rows();
//                    $query = $this->db->query($sql2);
//                }
//            }
//        } else {
//
//            if (!empty($startDate) && (!empty($endDate))) {
//                $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
//            }
//
//            if (!empty($serchInfo)) {
//                $this->db->where("(name LIKE '%$serchInfo%'OR email LIKE '%$serchInfo%'OR mobile_number LIKE '%$serchInfo%'OR user_id LIKE '%$serchInfo%'OR id LIKE '%$serchInfo%')");
//            }
//
//            $this->db->select('*')->from('user');
//            $this->db->order_by("id", "DESC");
//            $tempdb = clone $this->db;
//            $finalResult['totalRow'] = $tempdb->count_all_results();
//
//            $this->db->limit($limit, $offset);
//            $query = $this->db->get();
//            //echo $this->db->last_query();
//        }
//
//        $this->session->set_userdata("user_query", $this->db->last_query());
//
//        if ($query->num_rows() > 0) {
//            $result = array();
//            $i = 0;
//            foreach ($query->result() as $row) {
//                $result[$i]['id'] = $row->id;
//                $result[$i]['user_id'] = $row->user_id;
//                $result[$i]['device_uuid'] = $row->device_uuid;
//                $result[$i]['name'] = $row->name;
//                $result[$i]['email'] = $row->email;
//                $result[$i]['mobile_number'] = $row->mobile_number;
//                $result[$i]['image'] = $row->image;
//                $result[$i]['status'] = $row->status;
//                $result[$i]['user_type'] = $row->user_type_id;
//                $result[$i]['verify_status'] = $row->verify_status;
//                $result[$i]['email_verify_status'] = $row->email_verify_status;
//                $result[$i]['rang_of_add_prizebond'] = $prizebond_range = $this->getPrizebondRangeByUserId($row->id);
//                $result[$i]['allready_added_prizebond'] = $prizebond_added = $this->getAllReadyAddedPrizebondByUserId($row->id);
//                if ($prizebond_added > $prizebond_range) {
//                    $result[$i]['overloaded_prizebond'] = $prizebond_added - $prizebond_range;
//                } else {
//                    $result[$i]['overloaded_prizebond'] = 0;
//                }
//                $result[$i]['created_date_time'] = $row->created_date_time;
//                $result[$i]['created_date_human_eye_format'] = $this->time_elapsed_string($row->created_date_time);
//                $result[$i]['updated_date_time'] = $row->updated_date_time;
//                $result[$i]['updated_date_human_eye_format'] = $this->time_elapsed_string($row->updated_date_time);
//                $result[$i]['totalPurchasedMoney'] = $this->getTotalPurchasedMoneyByUserId($row->id);
//                $result[$i]['total_order'] = $this->getTotalOrder($row->id);
//                $result[$i]['total_pending_order'] = $this->getTotalPendingOrder($row->id);
//                $result[$i]['total_completed_order'] = $this->getTotalCompletedOrder($row->id);
//                $result[$i]['total_failed_order'] = $this->getTotalFailedOrder($row->id);
//                $result[$i]['totalCountofProduct'] = $this->getTotalCountOfProductPurchasedByUserId($row->id);
//                $i++;
//            }
//            $finalResult['result'] = $result;
//
//            return $finalResult;
//        }
//        return FALSE;
//    }

    /*
      public function countRowsForUsers($startDate = NULL, $endDate = NULL, $serchInfo = '', $serchArray = array()) {

      if (!empty($serchArray)) {
      if (isset($serchArray['user_type_id']) || (isset($serchArray['status'])) || (isset($serchArray['flag_status']))) {
      if (!empty($startDate) && (!empty($endDate))) {
      $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
      }
      if (!empty($startDate) && (!empty($endDate))) {
      $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
      }

      if (isset($serchArray['user_type_id']) && !empty($serchArray['user_type_id'])) {
      $this->db->where('user_type_id', $serchArray['user_type_id']);
      }
      if (isset($serchArray['status'])) {
      $this->db->where("status", $serchArray['status']);
      }
      if (isset($serchArray['flag_status']) && !empty($serchArray['flag_status'])) {
      $this->db->where("flag_status", $serchArray['flag_status']);
      }

      $this->db->from('user');
      } elseif (isset($serchArray['highest_paid_subscribers'])) {
      $sql = "SELECT user.*, paid_users.highest_purchased FROM user "
      . "LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased "
      . "FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased"
      . " FROM subscription_order_list WHERE total_receivable_amount > 0 "
      . "AND payment_status='success' GROUP BY user_id ) a GROUP by user_id"
      . " ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id "
      . "WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'"
      . " AND paid_users.highest_purchased is not NULL GROUP BY user.id"
      . " ORDER BY paid_users.highest_purchased DESC";
      $query = $this->db->query($sql);
      return $query->num_rows();
      } elseif (isset($serchArray['lowest_paid_subscribers'])) {
      $sql = "SELECT user.*, paid_users.highest_purchased FROM user "
      . "LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased "
      . "FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased"
      . " FROM subscription_order_list WHERE total_receivable_amount > 0 "
      . "AND payment_status='success' GROUP BY user_id ) a GROUP by user_id"
      . " ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id "
      . "WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'"
      . " AND paid_users.highest_purchased is not NULL GROUP BY user.id"
      . " ORDER BY paid_users.highest_purchased DESC";
      $query = $this->db->query($sql);
      return $query->num_rows();
      } elseif (isset($serchArray['maximum_bonds_added'])) {
      $sql = "SELECT user.*, bond_added.maximum_bond_added FROM user "
      . "LEFT JOIN (SELECT user_id, MAX(bond_added_by_user) AS maximum_bond_added "
      . "FROM ( SELECT user_id, COUNT(id) AS bond_added_by_user FROM user_prizebond_list "
      . "GROUP BY user_id ORDER BY bond_added_by_user DESC ) a GROUP by user_id ORDER BY "
      . "maximum_bond_added DESC) AS bond_added ON user.id = bond_added.user_id "
      . "WHERE created_date_time > '$startDate' AND created_date_time < '$endDate' "
      . "AND bond_added.maximum_bond_added is not NULL GROUP BY user.id "
      . "ORDER BY `bond_added`.`maximum_bond_added` DESC ";
      $query = $this->db->query($sql);
      return $query->num_rows();
      } elseif (isset($serchArray['minimum_bonds_added'])) {
      $sql = "SELECT user.*, bond_added.maximum_bond_added FROM user "
      . "LEFT JOIN (SELECT user_id, MAX(bond_added_by_user) AS maximum_bond_added "
      . "FROM ( SELECT user_id, COUNT(id) AS bond_added_by_user FROM user_prizebond_list "
      . "GROUP BY user_id ORDER BY bond_added_by_user DESC ) a GROUP by user_id ORDER BY "
      . "maximum_bond_added DESC) AS bond_added ON user.id = bond_added.user_id "
      . "WHERE created_date_time > '$startDate' AND created_date_time < '$endDate' "
      . "AND bond_added.maximum_bond_added is not NULL GROUP BY user.id "
      . "ORDER BY `bond_added`.`maximum_bond_added` ASC ";
      $query = $this->db->query($sql);
      return $query->num_rows();
      }
      } else {

      if (!empty($startDate) && (!empty($endDate))) {
      $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
      }
      if (!empty($serchInfo)) {
      $this->db->where("(name LIKE '%$serchInfo%'OR email LIKE '%$serchInfo%'OR mobile_number LIKE '%$serchInfo%'OR user_id LIKE '%$serchInfo%'OR id LIKE '%$serchInfo%')");
      }
      $this->db->from('user');
      }

      $this->db->count_all_results();
      //echo $this->db->last_query();

      return $this->db->count_all_results();
      } */

    public function getAUserInfoById($userId) {
        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->num_rows() > 0) {
            $row = $query->row();

            $row->join_date = $this->time_elapsed_string($row->created_date_time);
            $row->no_of_associated_device = $this->getNumberOfAssociatedDevicesByUserId($userId);

            $device_uuid = $row->device_uuid;
            if (strpos($device_uuid, 'web') !== false) {
                $row->last_login_from = 'Web';
            } else {
                $row->last_login_from = 'Mobile';
            }
            $row->total_purchased_amount = $this->getTotalPurchasedMoneyByUserId($userId);

            $row->total_purchased_product = $this->getTotalCountOfProductPurchasedByUserId($userId);
            $row->total_expired_product = $this->getTotalCountOfExpiredProductsPurchasedByUserId($userId);
            $row->rang_of_add_prizebond = $this->getPrizebondRangeByUserId($userId);
            $row->allready_added_prizebond = $this->getAllReadyAddedPrizebondByUserId($userId);
            $row->overload = $row->allready_added_prizebond - $row->rang_of_add_prizebond;
            if ($row->overload < 0) {
                $row->overload = 0;
            } else {
                $row->overload = $row->overload;
            }
            return $row;
        } else {
            return FALSE;
        }
    }

//    public function getASingleUsersAssociatedDeviceInfoByUserId($userId = '') {
//        $this->db->group_by('device_uuid');
//        $this->db->select('*')->where('user_id', $userId);
//        $query = $this->db->get('device_info');
//        if ($query->num_rows() > 0) {
//            return $query->result();
//        } else {
//            return FALSE;
//        }
//    }
//    public function getUsersAssociatedDeviceInfo($userId = NULL, $limit, $offset = 0, $startDate = NULL, $endDate = NULL, $serchData = array()) {
//        $this->db->group_by('device_uuid');
//        $this->db->select('*');
//        if (!empty($userId)) {
//            $this->db->where('user_id', $userId);
//        }
//        if ($startDate != NULL && ($endDate != NULL)) {
//            
//        }
//        $this->db->limit($limit, $offset);
//        $query = $this->db->get('device_info');
//        if ($query->num_rows() > 0) {
//            return $query->result();
//        } else {
//            return FALSE;
//        }
//    }

    public function GenerateSalesCountByPaymentMethodType($startDate, $endDate) {
        $sql = "SELECT purchased_by,COUNT(id) AS total FROM subscription_product_purchase_list WHERE purchased_by!='sign up' AND date(created_datetime) >= '$startDate' AND date(created_datetime) <= '$endDate' GROUP BY purchased_by ORDER BY purchased_by DESC";

        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getUserManagementPanelData() {
        $data = array();
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');
        $data['total_users'] = $this->getTotalUsers($startDate, $endDate);
        //$data['total_active_users'] = $this->getTotalActiveUsers($startDate, $endDate);
        $data['total_inactive_users'] = $this->getTotalInactiveUsers($startDate, $endDate);
        $data['total_desabled_users'] = $this->getTotalDisabledUsers($startDate, $endDate);
        $data['total_suspended_users'] = $this->getTotalSuspendedUsers($startDate, $endDate);
        $data['total_verified_users'] = $this->getTotalVerifiedUsers($startDate, $endDate);
        $data['total_android_users'] = $this->getTotalAndroidUsers($startDate, $endDate);
        $data['total_iOS_users'] = $this->getTotaliOsUsers($startDate, $endDate);
        $data['total_draws'] = $this->getTotalDraws($startDate, $endDate);
        $data['total_prizebond'] = $this->getTotalPrizeBond($startDate, $endDate);
        $data['total_winners'] = $this->getTotalWinners($startDate, $endDate);
        $data['total_flaged_users'] = $this->getTotalFlagedUsers($startDate, $endDate);
        $data['total_subscribed_users'] = $this->getTotalSubscribedUsers($startDate, $endDate);
        $data['total_sales_amount'] = $this->getTotalSalesAmount($startDate, $endDate);
        $data['total_order'] = $this->getTotalSales($startDate, $endDate);

        return $data;
    }

//    public function getTotalActiveUsers($startDate, $endDate) {
//        $this->db->select('id, COUNT(id) as total')->where('status', '1');
//        if (!empty($startDate) && (!empty($endDate))) {
//            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
//        }
//        $query = $this->db->get('user');
//        if ($query->num_rows() > 0) {
//            return $query->row()->total;
//        } else {
//            return FALSE;
//        }
//    }

    public function getTotalInactiveUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('status', '0');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalDisabledUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('status', '2');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalSuspendedUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('status', '3');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalVerifiedUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('verify_status', 'YES');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalFlagedUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('flag_status', 'suspicious');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalSubscribedUsers($startDate, $endDate) {

//        $sql = "SELECT COUNT(user.id) AS total FROM user
//                        LEFT JOIN (SELECT user_id FROM `subscription_product_purchase_list`
//                        WHERE `product_id` != 1 AND `status` = 1 AND `valid_end_datetime` > NOW() GROUP BY user_id) 
//                        AS subscribers ON subscribers.user_id = user.id
//                        WHERE (date(user.created_date_time) > '$startDate' AND date(user.created_date_time) < '$endDate')
//                        GROUP BY user.id ORDER BY user.id";
        //$sql = "SELECT id FROM subscription_product_purchase_list WHERE `product_id` != 1 AND (`created_datetime` >= '$startDate' AND `created_datetime` <= '$endDate') AND `status` = 1 AND `valid_end_datetime` > NOW() GROUP BY user_id";
        $sql = "SELECT user_id FROM subscription_order_list WHERE product_id != 1 AND (date(payment_date_time) >= '$startDate' AND date(payment_date_time) <= '$endDate') AND payment_status = 'success' AND order_status = 'completed' GROUP BY user_id";
        $query = $this->db->query($sql);
        //echo $this->db->last_query();
        return $query->num_rows();
    }

    //Dashboard
    public function getTotalUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalAndroidUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('device_type', '2')->where('device_status', 'active');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(added_date)>= '$startDate' AND date(added_date) <= '$endDate')");
        }
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotaliOsUsers($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')->where('device_type', '1')->where('device_status', 'active');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(added_date)>= '$startDate' AND date(added_date) <= '$endDate')");
        }
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalDraws($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('prizebond_result_info');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalPrizeBond($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user_prizebond_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalWinners($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total');
        if (!empty($startDate) && (!empty($endDate))) {
            $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
        }
        $query = $this->db->get('user_prizebond_won_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getTotalSalesAmount($startDate, $endDate) {
        $this->db->select_sum('rate')
                ->where('payment_status', 'success');
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

    public function getUserType() {
        $this->db->select('*')->from('user_types');
        $query = $this->db->get();
        $response = array('' => 'User Type - Any');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = ucfirst($aData->name);
        }
        return $response;
    }

    public function getAppVersionCodes() {
        $this->db->select('app_version_code')->from('device_info')->order_by('app_version_code', 'DESC')
                ->group_by('app_version_code');
        $query = $this->db->get();
        //echo $this->db->last_query();

        foreach ($query->result() as $key => $versionCode) {
            $response[] = $versionCode;
        }

        return $response;
    }

    public function getPaymentMethods() {
        $this->db->select('name')->from('subscription_payment_method_types')->where('name!=', 'sign up')->order_by('id', 'DESC');
        $query = $this->db->get();
        //echo $this->db->last_query();

        foreach ($query->result() as $paymentMethod) {
            $response[] = $paymentMethod;
        }

        return $response;
    }

    public function getDevicesBrands() {
        $this->db->select('device_brand')->from('device_info')
                ->where('device_brand!=', '')
                ->group_by('device_brand');
        $query = $this->db->get();
        //echo $this->db->last_query();

        foreach ($query->result() as $deviceBrand) {
            $response[] = $deviceBrand;
        }

        return $response;
    }

    private function getTotalSales($startDate, $endDate) {
        $this->db->select('id, COUNT(id) as total')
                ->where('payment_status', 'success')->where('product_id !=', 1);
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

    private function getPrizebondRangeByUserId($userId) {
        $this->load->model('api_model');
        $userPurchaseStatus = $this->api_model->getAUserParchasedSummaryInfo($userId);
        if (!empty($userPurchaseStatus)) {
            $result = $userPurchaseStatus['total_user_purchased_prizebond'];
            return $result;
        } else {
            return FALSE;
        }
    }

    private function getTotalPurchasedMoneyByUserId($userId) {
        $this->db->select_sum('rate')
                ->where('payment_status', 'success')
                ->where('user_id', $userId);
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->rate;
        } else {
            return FALSE;
        }
    }

    //using in user_model
//    private function getTotalOrder($userId) {
//        $this->db->select('id, COUNT(id) as total')
//                ->where('user_id', $userId);
//        $query = $this->db->get('subscription_order_list');
//
//        if ($query->num_rows() > 0) {
//            return $query->row()->total;
//        } else {
//            return FALSE;
//        }
//    }
//
//    private function getTotalPendingOrder($userId) {
//        $this->db->select('id, COUNT(id) as total')
//                ->where('order_status', 'pending')
//                ->where('user_id', $userId);
//        $query = $this->db->get('subscription_order_list');
//
//        if ($query->num_rows() > 0) {
//            return $query->row()->total;
//        } else {
//            return FALSE;
//        }
//    }
//
//    private function getTotalCompletedOrder($userId) {
//        $this->db->select('id, COUNT(id) as total')
//                ->where('order_status', 'completed')
//                ->where('user_id', $userId);
//        $query = $this->db->get('subscription_order_list');
//
//        if ($query->num_rows() > 0) {
//            return $query->row()->total;
//        } else {
//            return FALSE;
//        }
//    }
//
//    private function getTotalFailedOrder($userId) {
//        $this->db->select('id, COUNT(id) as total')
//                ->where('order_status', 'failed')
//                ->where('user_id', $userId);
//        $query = $this->db->get('subscription_order_list');
//
//        if ($query->num_rows() > 0) {
//            return $query->row()->total;
//        } else {
//            return FALSE;
//        }
//    }

    private function getTotalCountOfProductPurchasedByUserId($userId) {
        $this->db->select('id, COUNT(id) as total')->where('user_id', $userId);
        $query = $this->db->get('subscription_product_purchase_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function getTotalCountOfExpiredProductsPurchasedByUserId($userId) {
        $this->db->select('id, COUNT(id) as total')->where('user_id', $userId)->where('valid_end_datetime<', date('Y-m-d H:i:s'));
        $query = $this->db->get('subscription_product_purchase_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function getAllReadyAddedPrizebondByUserId($userId) {
        $this->db->select('id, COUNT(id) as total')->where('user_id', $userId);
        $query = $this->db->get('user_prizebond_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function getNumberOfAssociatedDevicesByUserId($userId) {
        $this->db->group_by('device_uuid');
        $this->db->select('device_uuid')->where('user_id', $userId);
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return count($query->result());
        } else {
            return FALSE;
        }
    }

    /*
     * create by a teammate
     */

    public function getLastNineDrawInfo() {
        $this->db->order_by("result_number", "DESC");
        $this->db->limit(9);
        $query = $this->db->get('prizebond_result_info');
        return $query->result();
    }

    public function getUserNumberForLastDraw($drawNumber) {
        $this->db->select('id, COUNT(id) as total')->where('draw_number', $drawNumber);
        $query = $this->db->get('user_prizebond_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getWonUserNumberForLastDraw($drawNumber) {
        $this->db->select('id, COUNT(id) as total')->where('prizebond_result_info_id', $drawNumber);
        $query = $this->db->get('user_prizebond_won_list');
        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    public function getRemoveDrawInfoID($drawNumber) {
        $this->db->select('id');
        $query = $this->db->get_where('user_prizebond_list', array('draw_number' => $drawNumber));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $value) {
                $result[] = $value->id;
            }
            return $result;
        }
        return false;
    }

}
