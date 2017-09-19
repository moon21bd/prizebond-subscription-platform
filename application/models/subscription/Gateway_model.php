<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-12
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Gateway_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function getGatewayName() {
        $this->db->select('*')->from('subscription_gateway_info');
        $query = $this->db->get();
        $response = array('' => '--select--');
        foreach ($query->result() as $aData) {
            $response[$aData->id] = $aData->gateway_name;
        }
        return $response;
    }

    public function saveGateway($data = array()) {
        $this->db->insert('subscription_gateway_info', $data);
        return $this->db->insert_id();
    }

    public function getGatewayInfo() {
        $query = $this->db->get('subscription_gateway_info');
        if ($query->num_rows() > 0) {
            // return $query->result();

            $result = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $result[$i]['id'] = $row->id;
                $result[$i]['gateway_name'] = $row->gateway_name;
                $result[$i]['gateway_fee'] = $row->gateway_fee;
                $result[$i]['currency_format'] = $row->currency_format;
                $result[$i]['created_date_time'] = $row->created_date_time;
                $result[$i]['updated_date_time'] = $row->updated_date_time;
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getAGatewayInfo($id) {
        $query = $this->db->get_where('subscription_gateway_info',array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    // delete single row
    Public function deleteGateway($id) {
        $this->db->delete('subscription_gateway_info', array('id' => $id));
        return $this->db->affected_rows();
    }
    
        //---update data.....
    public function updateGateway($data = array(), $id) {
        $this->db->update('subscription_gateway_info', $data, array('id' => $id));
        return $this->db->affected_rows();
    }

}
