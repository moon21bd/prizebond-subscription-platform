<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-11
 */

class Main_model extends CI_Model {

    // TBD Manage Login System Start
    var $managedb = NULL;

    // TBD Manage Login System End

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function __destruct() {
        $this->db->close();
    }

    public function insertNotificationData($data) {
        $this->db->insert('notifications', $data);
        return TRUE;
    }

    public function getFilteredData($newTitle) {
        //$filteredTitle = preg_replace('/\\\{1,}/', '', str_replace('n', '', $newTitle));
        $filteredTitle = preg_replace('/\\\{1,}/', '', strip_tags(nl2br($newTitle)));
        return $filteredTitle;
    }

    public function getNotificationsData() {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('notifications', 5);
        return $query->result();
    }






}
