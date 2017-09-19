<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

class Global_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getUserInfoByCredential($credential, $password) {
        $this->db->where("(email = '$credential' OR mobile_number LIKE  '% $credential %')");
        $this->db->where('password', $password);
        $query = $this->db->get('user');
        if ($query->result()) {
            $row = $query->row_array();
            $data['id'] = $row['id'];
            $data['device_uuid'] = $row['device_uuid'];
            $data['previous_device_uuid'] = $row['previous_device_uuid'];
            $data['name'] = $row['name'];
            $data['email'] = $row['email'];
            $data['mobile_number'] = $row['mobile_number'];
            $data['verify_status'] = $row['verify_status'];

            if (!empty($row['image'])) {
                $data['image'] = base_url('images') . '/' . $row['image'];
            }
            return $data;
        } else {
            return false;
        }
    }

    public function getUserInfo($userId) {
        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->result()) {
            $row = $query->row_array();
            $data['user_id'] = $row['id'];
            $data['name'] = $row['name'];
            $data['email'] = $row['email'];
            $data['mobile_number'] = $row['mobile_number'];
            if (!empty($row['image'])) {
                $data['image'] = base_url('images') . '/' . $row['image'];
            }
            return $data;
        } else {
            return false;
        }
    }

    /**
     * @param $table
     * @param $data
     *
     * @return mixed
     */
    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
        
    }

    /**
     * @param $table
     * @param $where
     *
     * @return bool
     */
    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    /**
     * @param $table
     * @param $data
     * @param $where
     *
     * @return mixed
     */
    public function update($table, $data, $where) {
        $this->db->where($where);
        $this->db->update($table, $data);
        return TRUE;
    }

    public function updateProfile($table, $data, $where) {
        $this->db->where($where);
        $this->db->update($table, $data);
        $query = $this->db->get_where('user', $where);
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    /**
     * @param $table
     * @param $where
     * @return mixed
     */
    public function delete($table, $where) {
        return $this->db->delete($table, $where);
    }

    /**
     * @param $table
     *
     * @return bool
     */
    public function get($table, $where = false, $limit = false, $order_by = false) {
        $this->db->select('*')->from($table);

        if (!empty($where)) {
            $this->db->where($where);
        }

        if (!empty($limit)) {
            $this->db->limit($limit['limit'], $limit['start']);
        }

        if (!empty($order_by)) {
            $this->db->order_by($order_by['filed'], $order_by['order']);
        }

        $query = $this->db->get();

        //$this->db->last_query();

        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function getPrizeBondInfo() {

        $this->db->select('bond_number');
        $this->db->group_by('bond_number');
        $query = $this->db->get_where('sync_info', array('push_send' => 0));
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    /**
     * @param      $table
     * @param      $like
     * @param bool $where
     *
     * @return bool
     */
    public function get_like($table, $like, $where = false) {

        $this->db->select('*');
        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }
        $this->db->like($like);
        $query = $this->db->get();

        if ($query->num_rows()) {
            return $query->result();
        } else {
            return false;
        }
    }

    /**
     * @param      $table
     * @param      $join_table
     * @param      $jon_on
     * @param bool $where
     * @param bool $all
     *
     * @return bool
     */
    public function get_with_join($table, $join_table, $join_on, $where = false, $all = false) {
        $this->db->select('*')->from($table);

        $this->db->join($join_table, $join_table . '.' . $join_on . ' = ' . $table . '.' . $join_on);

        if (!empty($where)) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            if ($all) {
                return $query->result();
            } else {
                return $query->row_array();
            }
        } else {
            return false;
        }
    }

    /**
     * @param $table
     * @param $where
     *
     * @return bool
     */
    public function get_row($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($result = $query->result()) {
            return true;
        } else {
            return false;
        }
    }

    public function doesExist($table, $where) {
        $this->db->select('id');
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function count_row($table) {
        //$this->db->select('*');
        $this->db->select('id');
        $query = $this->db->get($table);
        return $query->num_rows();
    }

    public function countUserPrizbond($userId) {
        $this->db->select('id');
        $query = $this->db->get_where('user_prizebond_list', array('user_id' => $userId));
        return $query->num_rows();
    }

}
