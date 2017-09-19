<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-11
 */

class Auth_model extends CI_Model {

    var $managedb = NULL;

    public function __construct() {
        parent::__construct();
        $CI = &get_instance();
        $this->managedb = $CI->load->database('manage', TRUE);
    }

    public function __destruct() {
        $this->db->close();
    }

    public function getUserInfoByEmail($email) {
        $query = $this->managedb->get_where('user_list', array('email' => $email));
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function getUserPermissions($userId, $projectId) {

        $data = array();
        $query = $this->managedb->get_where('user_permissions', array('user_id' => $userId, 'project_id' => $projectId));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $query2 = $this->managedb->get_where('project_access_list', array('id' => $row->project_access_id));
                if ($query2->num_rows() > 0) {
                    $row = $query2->row();
                    $data[] = $row->controller_name;
                }
            }
            return implode(",", $data);
        }
        return FALSE;
    }

    public function getProjectDefaultControllerName($userId, $projectId) {
        $query = $this->managedb->get_where('user_permissions', array('user_id' => $userId, 'project_id' => $projectId));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                if ($row->default_route == 1) {

                    $query2 = $this->managedb->get_where('project_access_list', array('id' => $row->project_access_id));
                    if ($query2->num_rows() > 0) {
                        $result = $query2->row();
                        return $result->controller_name;
                    }
                }
            }
        }
        return '';
    }

    public function getProjectURL($project_id) {

        $query = $this->managedb->get_where('project_list', array('id' => $project_id));
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->url;
        } else {
            return FALSE;
        }
    }

    public function getProjectAccessController($projectId, $projectAccessId) {
        $query = $this->managedb->get_where('project_access_list', array('id' => $projectAccessId, 'project_id' => $projectId));
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->controller_name;
        } else {
            return FALSE;
        }
    }

    public function getUserRoleById($userId) {

        $query = $this->managedb->get_where('user_list', array('id' => $userId));
        if ($query->num_rows()) {
            $result = $query->row();
            $query2 = $this->managedb->get_where('user_types', array('id' => $result->user_type_id));
            if ($query2->num_rows()) {
                $result2 = $query2->row();
                return $result2->name;
            }
        } else {
            return FALSE;
        }
    }

    public function getUserName($userId) {
        $query = $this->managedb->get_where('user_list', array('id' => $userId));
        if ($query->num_rows()) {
            $result = $query->row();
            return $result->full_name;
        } else {
            return FALSE;
        }
    }

}
