<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-04-13
 */

class Forgotpassword_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function doesExit($table, $where) {
        $this->db->select('id');
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function updateForgotCounter($table, $where) {
        $userId = $where['user_id'];
        $dataTime = date('Y-m-d H:i:s');
        $sql = "UPDATE $table SET counter =  counter + 1 , update_date_time = '$dataTime' WHERE user_id = '$userId'";
        $this->db->query($sql);
        return TRUE;
    }

    public function checkVerifyCodeSendCounter($table, $where = array()) {

        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            $endDate = $row->update_date_time;
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            if ($hourdiff > 24) {
                $this->db->delete($table, array('id' => $row->id));
                return TRUE;
            } else {
                if ($row->counter >= 3 && $hourdiff < 24) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            }
        } else {
            return TRUE;
        }
    }

    public function update($table, $data, $where) {
        $this->db->where($where);
        $this->db->update($table, $data);
        return TRUE;
    }

    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function getUserInfo($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function getUserInfoByMobile($table, $mobile) {

        $this->db->select('id,email,mobile_number');
        $this->db->like('mobile_number', $mobile);
        $query = $this->db->get($table);

        if ($query->result()) {
            $data['id'] = $query->row()->id;
            $data['mobile_number'] = $query->row()->mobile_number;
            $data['email'] = $query->row()->email;
            return $data;
        } else {
            return false;
        }
    }

    public function insert($table, $data = array()) {
        $this->db->insert($table, $data);
        return TRUE;
    }

    public function delete($table, $where) {
        $this->db->delete($table, $where);
        return TRUE;
    }

}

?>
