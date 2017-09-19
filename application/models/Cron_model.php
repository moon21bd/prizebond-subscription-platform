<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-02
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getAllDevicePushId() {
        $query = $this->db->get_where('device_info', array('device_status' => 'active'));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $devicePushIds[] = (string) $row->device_push_id;
            }
            return $devicePushIds;
        } else {
            return FALSE;
        }
    }

    public function getInvalidAccessTokensId() {
        $query = $this->db->get_where('user', array('access_token_expire_time <' => time()));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $id[] = $row->id;
            }
            return $id;
        } else {
            return FALSE;
        }
    }

    public function resetVerifyCounter($table) {

        $this->db->select('update_date_time,id');

        $query = $this->db->get($table);
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {
                $time = $row->update_date_time;
                $hourdiff = (strtotime(date('Y-m-d H:i:s')) - strtotime($time)) / 3600;
                if ($hourdiff > 24) {
                    $this->db->update($table, array('counter' => 0), array('id' => $row->id));
                }
            }
        } else {
            return FALSE;
        }
    }

    public function getDrawInfo() {
        $this->db->order_by('result_number', 'DESC');
        $this->db->limit(8);
        $query = $this->db->get('prizebond_result_info');
        $drawInfo = array();
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {

                $drawInfo[$row->result_number]['draw'] = $row->result_number;
                $drawInfo[$row->result_number]['series'] = $row->result_series;
                $drawInfo[$row->result_number]['prizebond_result_info_id'] = $row->id;

                $this->db->select('prize_position,bond_number');
                $query1 = $this->db->get_where('prizebond_result_bond_list', array('prizebond_result_info_id' => $row->id));

                foreach ($query1->result() as $row2) {
                    $drawInfo[$row->result_number]['bonds'][$row2->prize_position][] = "'" . $row2->bond_number . "'";
                }
            }

            return $drawInfo;
        } else {
            return array();
        }
    }

    public function getSingleDrawInfoForGcmPush() {
        $this->db->limit(1);
        $query = $this->db->get_where('prizebond_result_info', array('gcm_status' => 0));
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function getUserWinBondsInfo($bonds) {

        $query = $this->db->query("SELECT * FROM user_prizebond_list WHERE bond_number IN ($bonds)");

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getPrizeAmount($prizePosition, $drawNumber) {

        $query = $this->db->get_where('prizebond_result_info', array('result_number' => $drawNumber));
        if ($query->num_rows() > 0) {
            if ($prizePosition == '1st') {
                return $query->row()->first_prize_value_tk;
            } elseif ($prizePosition == '2nd') {
                return $query->row()->second_prize_value_tk;
            } elseif ($prizePosition == '3rd') {
                return $query->row()->third_prize_value_tk;
            } elseif ($prizePosition == '4th') {
                return $query->row()->fourth_prize_value_tk;
            } else {
                return $query->row()->fifth_prize_value_tk;
            }
        } else {
            return FALSE;
        }
    }

    public function getUserBondList() {
        $this->db->limit(50);
        $query = $this->db->get_where('user_prizebond_list', array('cron_status' => 'NO'));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $userBondsInfo['id'] = $row->id;
                $userBondsInfo['user_id'] = $row->user_id;
                $userBondsInfo['prize_position'] = $row->prize_position;
                $userBondsInfo['draw_number'] = $row->draw_number;
                // $userBondsInfo['user_devcie_uuid'] = $row->user_devcie_uuid;
                $userBondsInfo['bond_number'] = $row->bond_number;
                $userBondsInfo['bond_series'] = $row->bond_series;
            }
            return $resultBondList;
        } else {
            return array();
        }
    }

    public function winBondInfo($data) {
        $this->db->insert('user_prizebond_won_list', $data);
        return $this->db->insert_id();
    }

    public function updateUserBondInfo($updateData, $bondId) {
        $this->db->update('user_prizebond_list', $updateData, array('id' => $bondId));
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function updateTable($table = '', $data = array(), $clauses = array()) {
        if (count($clauses)) {
            $this->db->update($table, $data, $clauses);
        } else {
            $this->db->update($table, $data);
        }
    }

    public function getDevices($deviceType, $offset, $limit) {
        $query = $this->db->get_where('device_info', array('device_type' => $deviceType, 'device_status' => 'active'), $limit, $offset);
        return $query->result();
    }

    public function getAllDevicesForGcm($offset, $limit) {
        $query = $this->db->get_where('device_info', array('device_status' => 'active'), $limit, $offset);
        return $query->result();
    }

    public function getStatusToSendGCMPush() {

        $query = $this->db->query("SELECT * FROM prize_bond_draw_info WHERE push_status = '0' LIMIT 1");
        if ($query->num_rows() > 0) {
            return $query->row();
        }
    }

    public function getTotalDevices($deviceType) {
        $query = $this->db->query("SELECT id FROM device_info WHERE device_status = 'active' AND device_type = $deviceType");
        return $query->num_rows();
    }

    public function getDevicesForSendPush($userType, $limit, $offset) {

        /* select device push id for non subscribers
          SELECT device_info.`device_push_id`
          FROM device_info
          LEFT JOIN (
          SELECT user_id FROM subscription_product_purchase_list WHERE user_id
          NOT IN( SELECT DISTINCT user_id FROM `subscription_product_purchase_list` WHERE purchased_by = 'coupon' OR purchased_by = 'online payment' OR purchased_by = 'bKash' OR purchased_by = 'SureCash' OR purchased_by = 'COD' OR purchased_by = 'BRAC' OR purchased_by = 'Rocket' )
          ) AS nonSubscribers ON device_info.user_id = nonSubscribers.user_id GROUP BY device_info.user_id
         */
        /* select subscribers
          SELECT id FROM user LEFT JOIN (SELECT DISTINCT user_id FROM `subscription_product_purchase_list`
          WHERE purchased_by != 'sign up' AND valid_end_datetime >= '2017-05-29 13:47:23') AS subscribers
          ON user.id = subscribers.user_id
          WHERE (subscribers.user_id = user.id)
         */

        $currentDate = date("Y-m-d H:i:s");
        $pushIdArray = array();
        if ($userType == 1) { // subscribers
            $sql = "SELECT id FROM user LEFT JOIN (SELECT DISTINCT user_id FROM `subscription_product_purchase_list`
                                        WHERE purchased_by != 'sign up' AND valid_end_datetime >= '$currentDate') AS subscribers 
                                        ON user.id = subscribers.user_id
                                        WHERE (subscribers.user_id = user.id) LIMIT $limit OFFSET $offset";

            $query = $this->db->query("SELECT id FROM user LEFT JOIN (SELECT DISTINCT user_id FROM `subscription_product_purchase_list`
                                        WHERE purchased_by != 'sign up' AND valid_end_datetime >= '$currentDate') AS subscribers 
                                        ON user.id = subscribers.user_id
                                        WHERE (subscribers.user_id = user.id) LIMIT $limit OFFSET $offset");
            foreach ($query->result() as $value) {
                $query = $this->db->query("SELECT user.id,user.name,device_info.device_push_id AS push_id 
                                            FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user.id = $value->id 
                                            AND device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' 
                                            AND device_info.gcm_status = 'active'");
                if ($query->num_rows() > 0) {
                    $result[] = $query->row();
                }
            }
//            foreach ($result as $value) {
//                $pushIdArray[] = $value->push_id;
//            }
            return $result;
        } elseif ($userType == 2) { // non subscribers
            $query = $this->db->query("SELECT device_info.`device_push_id` AS push_id
                FROM device_info 
                LEFT JOIN (
                            SELECT user_id FROM subscription_product_purchase_list WHERE user_id 
                            NOT IN( SELECT DISTINCT user_id FROM `subscription_product_purchase_list` WHERE purchased_by = 'coupon' OR purchased_by = 'online payment' OR purchased_by = 'bKash' OR purchased_by = 'SureCash' OR purchased_by = 'COD' OR purchased_by = 'BRAC' OR purchased_by = 'Rocket' )
                          ) AS nonSubscribers ON device_info.user_id = nonSubscribers.user_id GROUP BY device_info.user_id LIMIT $limit OFFSET $offset");

            if ($query->num_rows() > 0) {
//                foreach ($query->result() as $value) {
//                    $pushIdArray[] = $value->device_push_id;
//                }
                return $query->result();
            }
        } elseif ($userType == 3) { // all users
            $query = $this->db->query("SELECT user.id,user.name,device_info.device_push_id AS push_id FROM user
                                        LEFT JOIN device_info ON device_info.user_id = user.id
                                        WHERE device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' 
                                        AND device_info.gcm_status = 'active'  LIMIT $limit OFFSET $offset");

            if ($query->num_rows() > 0) {
//                foreach ($query->result() as $value) {
//                    $pushIdArray[] = $value->push_id;
//                }
                return $query->result();
            }
        } elseif ($userType == 4) { //for developer
            $query = $this->db->query("SELECT user.id,user.name,user.user_type_id,device_info.device_push_id AS push_id
                                        FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user_type_id = 2
                                        AND device_info.device_push_id IS NOT NULL 
                                        AND device_info.device_status = 'active' 
                                        AND device_info.gcm_status = 'active'");

            if ($query->num_rows() > 0) {
//                foreach ($query->result() as $value) {
//                    $pushIdArray[] = $value->push_id;
//                }
                return $query->result();
            }
        }
        return $pushIdArray;
    }

    public function countDevicesForSendPush($userType) {
        if (!empty($userType)) {
            if ($userType == 1) {
                $query = $this->db->query("SELECT count(id) AS total FROM user LEFT JOIN (SELECT DISTINCT user_id FROM `subscription_product_purchase_list`
                                        WHERE purchased_by != 'sign up') AS subscribers 
                                        ON user.id = subscribers.user_id
                                        WHERE (subscribers.user_id = user.id)");
            } elseif ($userType == 2) {
                $query = $this->db->query("SELECT count(id) AS total,device_info.`device_push_id` 
                            FROM device_info 
                            LEFT JOIN (
                            SELECT user_id FROM subscription_product_purchase_list WHERE user_id 
                            NOT IN( SELECT DISTINCT user_id FROM `subscription_product_purchase_list` WHERE purchased_by = 'coupon' OR purchased_by = 'online payment' OR purchased_by = 'bKash' OR purchased_by = 'SureCash' OR purchased_by = 'COD' OR purchased_by = 'BRAC' OR purchased_by = 'Rocket' )
                          ) AS nonSubscribers ON device_info.user_id = nonSubscribers.user_id GROUP BY device_info.user_id ");
            } elseif ($userType == 3) {
                $query = $this->db->query("SELECT count(id) AS total FROM user");
            } elseif ($userType == 4) {
                $query = $this->db->query("SELECT count(id) AS total FROM user WHERE user_type_id = 2");
            }
            return $query->row()->total;
        }
        return;
    }

    public function getUnVerifiedUsers() {
        $this->db->select('id,verify_status,name')->from('user')->where('user_type_id !=', 2);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(5);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $result = array();
            foreach ($query->result() as $row) {
                $data = array();
                $data['id'] = $row->id;
                $data['name'] = $row->name;
                $data['verify_status'] = $row->verify_status;
                 
                if ( $data['verify_status'] == 'NO') {
                    $result[] = $data;
                }
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    

}
