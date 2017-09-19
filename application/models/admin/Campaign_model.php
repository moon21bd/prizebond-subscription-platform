<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

//defined('BASEPATH') OR exit('No direct script access allowed');
//
//require_once APPPATH . 'models/Main_model.php';

class Campaign_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    public function __destruct() {
        $this->db->close();
    }

    public function countTotalDevice($table, $where = false) {
        $this->db->from($table);
        // $this->db->where("utm_campaign !=", "");
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->count_all_results();
    }

    public function getCampaigns() {
        $this->db->select('utm_campaign')
                ->where("utm_campaign !=", "");
        $this->db->group_by('utm_campaign');
        $this->db->order_by('utm_campaign', 'ASC');
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    public function getCampaignSource($campaign = false) {
        $this->db->select('utm_source');
        if ($campaign) {
            $this->db->where("utm_campaign", $campaign);
        }

        //$this->db->where("utm_campaign !=", "");

        $this->db->group_by('utm_source');
        $this->db->order_by('utm_source', 'ASC');
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    public function getCampaignMedium($source = false) {
        $this->db->select('utm_medium');
        if ($source) {
            $this->db->where("utm_source", $source);
        }

        //$this->db->where("utm_campaign !=", "");

        $this->db->group_by('utm_medium');
        $this->db->order_by('utm_medium', 'ASC');
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    public function getCampaignTerm($medium = false) {
        $this->db->select('utm_term');
        if ($medium) {
            $this->db->where("utm_medium", $medium);
        }

        //$this->db->where("utm_campaign !=", "");

        $this->db->group_by('utm_term');
        $this->db->order_by('utm_term', 'ASC');
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    public function getCampaignContent($term = false) {
        $this->db->select('utm_content');
        if ($term) {
            $this->db->where("utm_term", $term);
        }
        //$this->db->where("utm_campaign !=", "");
        $this->db->group_by('utm_content');
        $this->db->order_by('utm_content', 'ASC');
        $query = $this->db->get('device_info');
        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    public function getDevices($limit, $offset, $where = false) {

        $this->db->order_by("added_date", "desc");
        //$this->db->where("utm_campaign !=", "");
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get('device_info', $limit, $offset);
        if ($query->num_rows() > 0) {

            return $query->result();
        } else {
            return FALSE;
        }
    }

}
