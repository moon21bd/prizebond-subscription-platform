<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Apptype_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function saveAppType($data = array()) {
        $this->db->insert('subscription_app_types', $data);
        return $this->db->affected_rows();
    }

    //........... show data........
    public function getAppType() {
        $this->db->select('*')->from('subscription_app_types');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
        return $query->result();
        } else {
            return FALSE;
        }
    }

    // get single data....(edit)
    public function editAppType($id) {
        $this->db->select('*')->from('subscription_app_types')->where('id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    //---update data.....
    public function updateAppType($data = array(), $id) {
        $this->db->update('subscription_app_types', $data, array('id' => $id));
        return $this->db->affected_rows();
    }

    // delete single row
    Public function deleteAppType($id) {
        $this->db->delete('subscription_app_types', array('id' => $id));
        return $this->db->affected_rows();
    }

    public function getAppAssociate() {
        $this->db->select('*')->from('subscription_app_types');
        $query = $this->db->get();
        $response = array('' => '--select--');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->name;
        }
        return $response;
    }

}
