<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-03-22
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Maintenance_model extends CI_Model {
    
    function __construct() {
        parent::__construct();
    }
    
    public function getDrawDetailInfoById($id)
    {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $id));
        
        if( ! $query)
        {
            $error = $this->db->error();
        }
        
        if($query->num_rows())
        {
            $resultInfo = $query->row();
            
            $query = $this->db->get_where('prizebond_result_bond_list', array('prizebond_result_info_id' => $resultInfo->id));
            if($query->num_rows())
            {
                $resultInfo->bonds = $query->result_array();
            }
            
            return $resultInfo;
        }
        
    }
    
    
    public function getMatchedUserBondListWithDrawResultNumber($drawNumber) {
        $query = $this->db->get_where('user_prizebond_list', array('draw_number' => $drawNumber));
        if($query->num_rows())
        {
            return $query->result();
        }
    }
    
    public function removeDrawResultInfoFromUserBonds($bondId) {
        
        $data = array(
            'prize_position' => NULL,
            'draw_number' => NULL,
            'prize_amount' => NULL
        );

        $this->db->where('id', $bondId);
        $this->db->update('user_prizebond_list', $data);
        
        
        $this->db->delete('user_prizebond_won_list', array('user_prizebond_id' => $bondId));
    }
    
    public function applyDrawResultToUserBonds($bondNumber, $bondSeries, $drawNumber, $prizePosition, $prizeAmount, $drawResultId) {
        $query = $this->db->query("SELECT id,user_id,bond_number,bond_series FROM user_prizebond_list WHERE bond_number = '".$bondNumber."' AND bond_series IN ('$bondSeries')");
        if($query->num_rows())
        {
            $totalAffectedRows = 0;
            foreach ($query->result() as $userBondInfo)
            {
                $data = array(
                    'prize_position' => $prizePosition,
                    'draw_number' => $drawNumber,
                    'prize_amount' => $prizeAmount
                );
                $this->db->update('user_prizebond_list', $data, array('id' => $userBondInfo->id));
                
                $totalAffectedRows = $totalAffectedRows + $this->db->affected_rows();
                
                $data = array(
                    'user_id' => $userBondInfo->user_id,
                    'user_prizebond_id' => $userBondInfo->id,
                    'bond_number' => $userBondInfo->bond_number,
                    'bond_series' => $userBondInfo->bond_series,
                    'prizebond_result_info_id' => $drawResultId,
                    'prize_position' => $prizePosition,
                    'prize_amount' => $prizeAmount,
                    'created_date_time' => date('Y-m-d H:i:s')
                );
                $this->db->insert('user_prizebond_won_list', $data);
                
                $totalAffectedRows = $totalAffectedRows + $this->db->affected_rows();
            }
            
            return $totalAffectedRows;
        }
    }
    
    
    
}

