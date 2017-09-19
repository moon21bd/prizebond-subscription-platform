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
        $this->load->library('uuid');
    }

    public function getUserInfoByCredential($emailOrMobile, $password) {

        $this->db->where("(email = '$emailOrMobile' OR mobile_number LIKE  '%$emailOrMobile%')");
        $this->db->where('password', md5($password));
        $query = $this->db->get('user');
        if ($query->result()) {

            $row = $query->row();

            if ($row->user_id == NULL) {
                $user_id = $this->uuid->v4();
                $this->update('user', array('user_id' => $user_id), array('id' => $row->id));
                $row->user_id = $user_id;
            }

            if (!empty($row->image)) {
                $row->image = base_url('images') . '/' . $row->image;
            }

            $row->total_synced_bonds = (string) $this->countUserPrizbond($row->id);

            return $row;
        }
        return false;
    }

    public function getUserInfoByUserId($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('user');
        if ($query->result()) {
            $row = $query->row();

            if (!empty($row->image)) {
                $row->image = base_url('images') . '/' . $row->image;
            }

            $row->total_synced_bonds = (string) $this->countUserPrizbond($row->id);

            return $row;
        }
        return false;
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
        // echo $this->db->last_query();
        /* if ($this->db->affected_rows() > 0) {
          return TRUE;
          } else {
          return FALSE;
          }
         * 
         */
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

    public function getPushIdByDeviceUuid($deviceUuid) {
        $this->db->select('device_push_id');
        $query = $this->db->get_where('device_info', array('device_uuid' => $deviceUuid));
        if ($query->num_rows() > 0) {
            return $query->row()->device_push_id;
        } else {
            return FALSE;
        }
    }

}
