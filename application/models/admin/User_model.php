<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getUserInfo($limit = 0, $offset = 0, $startDate, $endDate, $serchInfo = '', $serchArray = array()) {

        /*
          SELECT locations.id, title, name, hours.lobby
          FROM locations
          LEFT JOIN states ON states.id = locations.state_id
          LEFT JOIN (SELECT location_id, type_id AS lobby FROM location_hours
          WHERE type_id IS NOT NULL) AS hours ON locations.id = hours.location_id
          GROUP BY locations.id'; */

        $finalResult = array();

        if (!empty($serchArray)) {

            if (isset($serchArray['user_type_id']) || (isset($serchArray['status'])) || (isset($serchArray['flag_status']))) {
                if (!empty($startDate) && (!empty($endDate))) {
                    $this->db->where("(date(user.created_date_time)>= '$startDate' AND date(user.created_date_time) <= '$endDate')");
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

                if (!empty($serchInfo)) {
                    $this->db->where("(name LIKE '%$serchInfo%'OR email LIKE '%$serchInfo%'OR mobile_number LIKE '%$serchInfo%'OR user_id LIKE '%$serchInfo%'OR id LIKE '%$serchInfo%')");
                }

                $this->db->select('*')->from('user');
                $this->db->order_by("id", "DESC");
                $tempdb = clone $this->db;
                $finalResult['totalRow'] = $tempdb->count_all_results();
                $this->db->limit($limit, $offset);
                $query = $this->db->get();
            } else {

                $prefix1 = "SELECT COUNT(user.id) AS total";
                $prefix2 = "SELECT user.*";
                $suffix = "LIMIT $limit OFFSET $offset";


                //$searchValue = $serchInfo ? $serchInfo : '';

                if (isset($serchArray['highest_paid_subscribers'])) {
                    /*
                      $sql = "SELECT user.*, paid_users.highest_purchased FROM user
                      LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased
                      FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased
                      FROM subscription_order_list WHERE total_receivable_amount > 0
                      AND payment_status='success' GROUP BY user_id ) a GROUP by user_id
                      ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id
                      WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'
                      AND paid_users.highest_purchased is not NULL GROUP BY user.id
                      ORDER BY paid_users.highest_purchased DESC LIMIT $limit OFFSET $offset "; */

                    $sql_middle = ", paid_users.highest_purchased FROM user
                                LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased 
                                FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased
                                FROM subscription_order_list WHERE total_receivable_amount > 0 
                                AND payment_status='success' GROUP BY user_id ) a GROUP by user_id
                                ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id
                                WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'
                                AND paid_users.highest_purchased is not NULL";
                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY paid_users.highest_purchased DESC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } elseif (isset($serchArray['lowest_paid_subscribers'])) {
                    $sql_middle = ", paid_users.highest_purchased FROM user 
                        LEFT JOIN (SELECT user_id, MAX(total_purchased) AS highest_purchased 
                        FROM ( SELECT user_id, SUM(total_receivable_amount) AS total_purchased
                        FROM subscription_order_list WHERE total_receivable_amount > 0 
                        AND payment_status='success' GROUP BY user_id ) a GROUP by user_id
                        ORDER BY highest_purchased DESC) AS paid_users ON user.id = paid_users.user_id 
                        WHERE created_date_time > '$startDate' AND created_date_time < '$endDate'
                        AND paid_users.highest_purchased is not NULL";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY paid_users.highest_purchased ASC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;

                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } elseif (isset($serchArray['maximum_bonds_added'])) {
                    $sql_middle = ", bond_added.maximum_bond_added FROM user
                            LEFT JOIN (SELECT user_id, MAX(bond_added_by_user) AS maximum_bond_added 
                            FROM ( SELECT user_id, COUNT(id) AS bond_added_by_user FROM user_prizebond_list 
                            GROUP BY user_id ORDER BY bond_added_by_user DESC ) a GROUP by user_id ORDER BY 
                            maximum_bond_added DESC) AS bond_added ON user.id = bond_added.user_id 
                            WHERE created_date_time > '$startDate' AND created_date_time < '$endDate' 
                            AND bond_added.maximum_bond_added is not NULL";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY `bond_added`.`maximum_bond_added` DESC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } elseif (isset($serchArray['minimum_bonds_added'])) {
                    $sql_middle = ", bond_added.maximum_bond_added FROM user 
                            LEFT JOIN (SELECT user_id, MAX(bond_added_by_user) AS maximum_bond_added 
                            FROM ( SELECT user_id, COUNT(id) AS bond_added_by_user FROM user_prizebond_list 
                            GROUP BY user_id ORDER BY bond_added_by_user DESC ) a GROUP by user_id ORDER BY 
                            maximum_bond_added DESC) AS bond_added ON user.id = bond_added.user_id 
                            WHERE created_date_time > '$startDate' AND created_date_time < '$endDate' 
                            AND bond_added.maximum_bond_added is not NULL";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY `bond_added`.`maximum_bond_added` ASC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } elseif (isset($serchArray['maximum_bonds_overloaded'])) {

                    $sql_middle = ", overloaded_users.total_overloaded 
                                    FROM user 
                                    LEFT JOIN (SELECT subscription_product_purchase_list.user_id,
                                    bond_added_info.bond_added_by_user - SUM(attributes.bond_capacity) AS total_overloaded
                                    FROM subscription_product_purchase_list LEFT JOIN (SELECT user_id, COUNT(id) 
                                    AS bond_added_by_user FROM user_prizebond_list GROUP BY user_id ORDER BY bond_added_by_user DESC) 
                                    AS bond_added_info ON subscription_product_purchase_list.user_id = bond_added_info.user_id 
                                    LEFT JOIN (SELECT product_id, value AS bond_capacity FROM subscription_product_attribute_values 
                                    WHERE attribute_id = 2) AS attributes ON subscription_product_purchase_list.product_id = attributes.product_id 
                                    GROUP BY subscription_product_purchase_list.user_id ORDER BY total_overloaded DESC) 
                                    AS overloaded_users ON user.id = overloaded_users.user_id 
                                    WHERE user.created_date_time > '$startDate' AND user.created_date_time < '$endDate' 
                                    AND overloaded_users.total_overloaded > 0";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY overloaded_users.total_overloaded DESC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } elseif (isset($serchArray['minimum_bonds_overloaded'])) {

                    $sql_middle = ", overloaded_users.total_overloaded 
                        FROM user 
                        LEFT JOIN (SELECT subscription_product_purchase_list.user_id, bond_added_info.bond_added_by_user - SUM(attributes.bond_capacity)
                        AS total_overloaded FROM subscription_product_purchase_list
                        LEFT JOIN (SELECT user_id, COUNT(id) AS bond_added_by_user 
                        FROM user_prizebond_list GROUP BY user_id ORDER BY bond_added_by_user DESC) 
                        AS bond_added_info ON subscription_product_purchase_list.user_id = bond_added_info.user_id 
                        LEFT JOIN (SELECT product_id, value AS bond_capacity FROM subscription_product_attribute_values 
                        WHERE attribute_id = 2) AS attributes ON subscription_product_purchase_list.product_id = attributes.product_id 
                        GROUP BY subscription_product_purchase_list.user_id ORDER BY total_overloaded DESC) 
                        AS overloaded_users ON user.id = overloaded_users.user_id 
                        WHERE user.created_date_time > '$startDate' AND user.created_date_time < '$endDate' 
                        AND overloaded_users.total_overloaded > 0";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY overloaded_users.total_overloaded ASC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['highest_order_pending'])) {

                    $sql_middle = ",orders_pending.pending AS total_pending FROM user
                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS pending FROM subscription_order_list 
                    WHERE order_status='pending' GROUP BY user_id ORDER BY pending DESC) 
                    AS orders_pending ON orders_pending.user_id = user.id 
                    WHERE orders_pending.pending > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY orders_pending.pending DESC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['lowest_order_pending'])) {

                    $sql_middle = ",orders_pending.pending AS total_pending FROM user
                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS pending FROM subscription_order_list 
                    WHERE order_status='pending' GROUP BY user_id ORDER BY pending DESC) 
                    AS orders_pending ON orders_pending.user_id = user.id 
                    WHERE orders_pending.pending > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY orders_pending.pending ASC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['highest_order_failed'])) {

                    $sql_middle = ",orders_failed.failed AS total_failed FROM user
                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS failed FROM subscription_order_list 
                    WHERE order_status='failed' GROUP BY user_id ORDER BY failed DESC) 
                    AS orders_failed ON orders_failed.user_id = user.id 
                    WHERE orders_failed.failed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY orders_failed.failed DESC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['lowest_order_failed'])) {

                    $sql_middle = ",orders_failed.failed AS total_failed FROM user
                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS failed FROM subscription_order_list 
                    WHERE order_status='failed' GROUP BY user_id ORDER BY failed DESC) 
                    AS orders_failed ON orders_failed.user_id = user.id 
                    WHERE orders_failed.failed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY orders_failed.failed ASC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['highest_order_completed'])) {

                    $sql_middle = ",orders_completed.completed AS total_completed
                    FROM user
                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS completed FROM subscription_order_list 
                    WHERE order_status='completed' GROUP BY user_id ORDER BY completed DESC) 
                    AS orders_completed ON orders_completed.user_id = user.id
                    WHERE orders_completed.completed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY orders_completed.completed DESC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['lowest_order_completed'])) {

                    $sql_middle = ",orders_completed.completed AS total_completed FROM user
                    LEFT JOIN (SELECT user_id,COUNT(user_id) AS completed FROM subscription_order_list 
                    WHERE order_status='completed' GROUP BY user_id ORDER BY completed DESC) 
                    AS orders_completed ON orders_completed.user_id = user.id
                    WHERE orders_completed.completed > 0 AND user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY orders_completed.completed ASC ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                } else if (isset($serchArray['subscriber'])) {
                    $sql_middle = " FROM user
                        LEFT JOIN (SELECT user_id FROM `subscription_product_purchase_list`
                        WHERE `product_id` != 1 AND `status` = 1 AND `valid_end_datetime` > NOW() GROUP BY user_id) 
                        AS subscribers ON subscribers.user_id = user.id
                        WHERE user.created_date_time > '$startDate' AND user.created_date_time < '$endDate'";

                    if (!empty($serchInfo)) {
                        $sql_middle .=" AND (user.name LIKE '%$serchInfo%'OR user.email LIKE '%$serchInfo%'OR user.mobile_number LIKE '%$serchInfo%'OR user.user_id LIKE '%$serchInfo%'OR user.id LIKE '%$serchInfo%')";
                    }

                    $sql_middle .=" GROUP BY user.id ORDER BY user.id ";

                    $sql1 = $prefix1 . $sql_middle;
                    $sql2 = $prefix2 . $sql_middle . $suffix;
                    $queryRowCount = $this->db->query($sql1);
                    $finalResult['totalRow'] = $queryRowCount->num_rows();
                    $query = $this->db->query($sql2);
                }
            }
        } else {

            if (!empty($startDate) && (!empty($endDate))) {
                $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
            }

            if (!empty($serchInfo)) {
                $this->db->where("(name LIKE '%$serchInfo%'OR email LIKE '%$serchInfo%'OR mobile_number LIKE '%$serchInfo%'OR user_id LIKE '%$serchInfo%'OR id LIKE '%$serchInfo%')");
            }

            $this->db->select('*')->from('user');
            $this->db->order_by("id", "DESC");
            $tempdb = clone $this->db;
            $finalResult['totalRow'] = $tempdb->count_all_results();

            $this->db->limit($limit, $offset);
            $query = $this->db->get();
            //echo $this->db->last_query();
        }
        $this->session->set_userdata("user_query", $this->db->last_query());

        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['user_id'] = $row->user_id;
                $result[$i]['device_uuid'] = $row->device_uuid;
                $result[$i]['name'] = $row->name;
                $result[$i]['email'] = $row->email;
                $result[$i]['mobile_number'] = $row->mobile_number;
                $result[$i]['image'] = $row->image;
                $result[$i]['status'] = $row->status;
                $result[$i]['user_type'] = $row->user_type_id;
                $result[$i]['verify_status'] = $row->verify_status;
                $result[$i]['email_verify_status'] = $row->email_verify_status;
                $result[$i]['rang_of_add_prizebond'] = $prizebond_range = $this->getPrizebondRangeByUserId($row->id);
                $result[$i]['allready_added_prizebond'] = $prizebond_added = $this->getAllReadyAddedPrizebondByUserId($row->id);
                if ($prizebond_added > $prizebond_range) {
                    $result[$i]['overloaded_prizebond'] = $prizebond_added - $prizebond_range;
                } else {
                    $result[$i]['overloaded_prizebond'] = 0;
                }
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['created_date_human_eye_format'] = $this->time_elapsed_string($row->created_date_time);
                $result[$i]['updated_date_time'] = $row->updated_date_time;
                $result[$i]['updated_date_human_eye_format'] = $this->time_elapsed_string($row->updated_date_time);
                $result[$i]['totalPurchasedMoney'] = $this->getTotalPurchasedMoneyByUserId($row->id);
                $result[$i]['total_order'] = $this->getTotalOrder($row->id);
                $result[$i]['total_pending_order'] = $this->getTotalPendingOrder($row->id);
                $result[$i]['total_completed_order'] = $this->getTotalCompletedOrder($row->id);
                $result[$i]['total_failed_order'] = $this->getTotalFailedOrder($row->id);
                $result[$i]['totalCountofProduct'] = $this->getTotalCountOfProductPurchasedByUserId($row->id);
                $i++;
            }
            $finalResult['result'] = $result;

            return $finalResult;
        }
        return FALSE;
    }

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

    public function getBondsOfAUserByUserId($userId, $limit = 50, $offset = 0) {
        $select = $this->db->select('*')->from('user_prizebond_list')->where('user_id', $userId);
        $this->db->order_by("id", "DESC");

        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result();
    }

    public function getASingleUsersPrizebondListByUserId($userId, $limit = NULL, $offset = 0, $serchData = array()) {

        $select = $this->db->select('*')->from('user_prizebond_list')
                ->where('user_id', $userId);
        $this->db->order_by("id", "DESC");
        if ($serchData) {
            if (isset($serchData['search_info']) && !empty($serchData['search_info'])) {
                $search_info = $serchData['search_info'];
                $this->db->where("(bond_series LIKE '%$search_info%'OR bond_number LIKE '%$search_info%')");
            }
            $this->db->limit($limit, $offset);
            $query = $this->db->get();
        } elseif ($limit) {
            $this->db->limit($limit, $offset);
            $query = $this->db->get();
        }

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getUsersAssociatedDeviceInfo($userId = NULL, $limit, $offset = 0, $startDate = NULL, $endDate = NULL, $serchData = array()) {
        $this->db->group_by('device_uuid');
        $this->db->select('*');
        if (!empty($userId)) {
            $this->db->where('user_id', $userId);
        }
        if ($startDate != NULL && ($endDate != NULL)) {
            
        }
        $this->db->limit($limit, $offset);
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
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

    private function getAllReadyAddedPrizebondByUserId($userId) {
        $this->db->select('id, COUNT(id) as total')->where('user_id', $userId);
        $query = $this->db->get('user_prizebond_list');
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

    private function getTotalOrder($userId) {
        $this->db->select('id, COUNT(id) as total')
                ->where('user_id', $userId);
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function getTotalPendingOrder($userId) {
        $this->db->select('id, COUNT(id) as total')
                ->where('order_status', 'pending')
                ->where('user_id', $userId);
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function getTotalCompletedOrder($userId) {
        $this->db->select('id, COUNT(id) as total')
                ->where('order_status', 'completed')
                ->where('user_id', $userId);
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

    private function getTotalFailedOrder($userId) {
        $this->db->select('id, COUNT(id) as total')
                ->where('order_status', 'failed')
                ->where('user_id', $userId);
        $query = $this->db->get('subscription_order_list');

        if ($query->num_rows() > 0) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

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

}
