<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-29
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Device_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // get single book
    public function getSingleDevice($id) {
        $this->db->select('*')->from('device_info')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    //get number of rows
    public function numberOfDevice($device_info, $where = false) {
        if ($where) {
            $this->db->where($where);
        }
        $this->db->from($device_info);
        return $this->db->count_all_results();
    }

    public function pagination($limit, $offset,$device_uuid=0) {
        $this->db->order_by("id", "desc");
        if($device_uuid){
            $this->db->like('device_uuid', $device_uuid);
        }
        $query = $this->db->get('device_info', $limit, $offset);
        
        
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    //get daily installs
    public function countInstalledAppByAddedDate($addedDate, $deviceType) {
        $sql = "SELECT COUNT(id) as total FROM device_info WHERE DATE(added_date) ='$addedDate' AND device_type='$deviceType'";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }
    
}
