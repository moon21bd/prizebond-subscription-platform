<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

class Bond_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getPrizebondList($userId, $searchData, $limit = NULL, $offset = 0, $startDate = NULL, $endDate = NULL, $prizePosition = NULL, $draw = NULL, $seriesList = NULL, $userType = NULL) {

        if ($userType && ($userType == 'subscriber')) {

            $countRow = "SELECT COUNT(user_prizebond_list.id) as total FROM 
                                user_prizebond_list LEFT JOIN (SELECT DISTINCT user_id FROM `subscription_product_purchase_list`
                                WHERE purchased_by != 'sign up') AS subscribers 
                                ON user_prizebond_list.user_id = subscribers.user_id 
                                WHERE (subscribers.user_id = user_prizebond_list.user_id)
                                AND (date(user_prizebond_list.created_date_time) >= '$startDate' 
                                AND date(user_prizebond_list.created_date_time) <= '$endDate')
                                ORDER BY user_prizebond_list.id DESC";

            $sql = "SELECT * FROM user_prizebond_list LEFT JOIN (SELECT DISTINCT user_id FROM `subscription_product_purchase_list`
                        WHERE purchased_by != 'sign up') AS subscribers 
                        ON user_prizebond_list.user_id = subscribers.user_id 
                        WHERE (subscribers.user_id = user_prizebond_list.user_id)
                        AND (date(user_prizebond_list.created_date_time) >= '$startDate' 
                        AND date(user_prizebond_list.created_date_time) <= '$endDate')
                        ORDER BY user_prizebond_list.id DESC LIMIT $limit OFFSET $offset";


            $queryRowCount = $this->db->query($countRow);
            $finalResult['totalRow'] = $queryRowCount->row()->total;

            $query = $this->db->query($sql);
        } else {

            $finalResult = array();
            $select = $this->db->select('*')->from('user_prizebond_list');
            $this->db->order_by("id", "DESC");
            if ($userId) {
                $this->db->where('user_id', $userId);
            }
            if (!empty($startDate) && (!empty($endDate))) {
                $this->db->where("(date(created_date_time)>= '$startDate' AND date(created_date_time) <= '$endDate')");
            }
            if ($searchData) {
                $this->db->where("(user_devcie_uuid LIKE '%$searchData%'OR bond_number LIKE '%$searchData%'OR user_id LIKE'%$searchData%')");
            }
            if ($prizePosition) {
                $this->db->where('prize_position', $prizePosition);
            }
            if ($draw) {
                $this->db->where('draw_number', $draw);
            }
            if ($seriesList) {
                $this->db->where('bond_series', $seriesList);
            }

            $tempdb = clone $this->db;
            $finalResult['totalRow'] = $tempdb->count_all_results();

            $this->db->limit($limit, $offset);
            $query = $this->db->get();

            //echo $this->db->last_query();
        }

//        echo $this->db->last_query();
        if ($query->num_rows() > 0) {
            $finalResult['result'] = $query->result();
            return $finalResult;
        } else {
            return FALSE;
        }
    }

    //winner list

    public function getWinnersInfo() {
        $this->db->select('prizebond_result_info_id')->from('user_prizebond_won_list');
        $this->db->order_by("prizebond_result_info_id", "DESC");
        $this->db->group_by("prizebond_result_info_id");
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['draw'] = $this->getDrawNumberByDrawId($row->prizebond_result_info_id);
                $result[$i]['draw_info'] = $this->getWinnersInfoByDrawId($row->prizebond_result_info_id);
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getDrawList() {
        $this->db->select('id,result_number')->from('prizebond_result_info');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(8);
        $query = $this->db->get();
        $response = array();
        foreach ($query->result() as $aData) {
            $response[$aData->result_number] = $aData->result_number;
        }
        return $response;
    }

    public function getSeriesList() {
        $this->db->select('*')->from('series_list');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get();
        $response = array();
        foreach ($query->result() as $aData) {
            $response[$aData->name] = $aData->name;
        }
        return $response;
    }

    private function getDrawNumberByDrawId($drawId) {
        $this->db->select('result_number');
        $query = $this->db->get_where('prizebond_result_info', array('id' => $drawId));
        if ($query->num_rows() > 0) {
            return $query->row()->result_number;
        } else {
            return FALSE;
        }
    }

    private function getWinnersInfoByDrawId($drawId) {
        $this->db->select('*')->from('user_prizebond_won_list')
                ->where('prizebond_result_info_id', $drawId);
        $query = $this->db->get();
        $this->db->order_by("id", "DESC");

        if ($query->num_rows() > 0) {
            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {

                $result[$i]['bond_number'] = $row->bond_number;
                $result[$i]['bond_series'] = $row->bond_series;
                $result[$i]['user_id'] = $row->user_id;
                $result[$i]['prize_position'] = $row->prize_position;
                $result[$i]['prize_amount'] = $row->prize_amount;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

}
