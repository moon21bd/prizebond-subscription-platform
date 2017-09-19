<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-11
 */
defined('BASEPATH') OR exit('No direct script access allowed');
//require_once APPPATH . 'models/Main_model.php';

class Message_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function __destruct() {
        $this->db->close();
    }

    public function getNewsListByPagination($limit, $offset) {

        $this->db->order_by("id", "desc");
        $query = $this->db->get('messages', $limit, $offset);
        if ($query->num_rows()) {
            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getNotificationsData() {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('notifications', 5);
        return $query->result();
    }

    public function countRow() {

        $this->db->select('COUNT(id) AS total');
        $query = $this->db->get('messages');
        if ($query->num_rows()) {
            return $query->row()->total;
        } else {
            return FALSE;
        }
    }

}
