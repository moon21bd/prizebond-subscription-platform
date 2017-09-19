<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-05-17
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Message_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function getAllMessages($userId = 0, $limit = 0, $offset = 0, $ticketStatus = '', $serchInfo = '') {
        $this->db->select('*')->from('user_support_messages');

        if ($userId) {
            $this->db->where('sender_user_id', $userId);
        }
        if (!empty($ticketStatus)) {
            $this->db->where('ticket_status', $ticketStatus);
        } else {
            $this->db->where('ticket_status !=', 'closed');
        }
        if (!empty($serchInfo)) {
            $this->db->where("(ticket_status LIKE '%$serchInfo%'OR subject LIKE '%$serchInfo%'OR message_id LIKE '%$serchInfo%' OR sender_user_id LIKE '%$serchInfo%')");
        }
        $this->db->order_by('last_replied_date_time', 'DESC');
        $tempdb = clone $this->db;
        $finalResult['totalRow'] = $tempdb->count_all_results();

        $this->db->limit($limit, $offset);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $value) {
                $value->message = $this->getLastMessageOfASubject($value->message_id);
            }

            $finalResult['result'] = $query->result();
            return $finalResult;
        } else {
            return FALSE;
        }
    }

    public function getConversationOfAMessage($messageId) {
        $this->db->select('*')->from('user_support_conversations');
        $this->db->where('message_id', $messageId);
        $this->db->order_by('sent_date_time', 'ASC');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        }
    }

    private function getLastMessageOfASubject($messageID) {
        $this->db->select('message');
        $this->db->order_by('sent_date_time', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get_where('user_support_conversations', array('message_id' => $messageID));
        return $query->row()->message;
    }

    public function updateMsgStatus($messageId, $status) {
        $this->db->update('user_support_messages', $status, array('message_id' => $messageId));
        return $this->db->affected_rows();
    }

    public function updateMsgTableAfterReply($data, $messageId) {
        $this->db->update('user_support_messages', $data, array('message_id' => $messageId));
        return $this->db->affected_rows();
    }

    public function deleteMsgWithConversation($messageId) {
        $this->db->delete('user_support_messages', array('message_id' => $messageId));
        $this->db->delete('user_support_conversations', array('message_id' => $messageId));
        return $this->db->affected_rows();
    }

    public function getUsersName($search) {
        $this->db->select('id,name,email,mobile_number,image')->from('user');
        $this->db->like('name', $search);
        $this->db->or_like('email', $search);
        $this->db->or_like('mobile_number', $search);
        $this->db->or_like('image', $search);
        $query = $this->db->get();
        $data = array();
        foreach ($query->result_array() as $row) {
            $info = array();
            $info['user_id'] = $row['id'];
            $info['label'] = $row['name'];
            $info['value'] = $row['name'];
            $info['email'] = $row['email'];
            $info['mobile_number'] = $row['mobile_number'];
            $info['image'] = $row['image'];
            $data[] = $info;
        }
        //return json data
        return json_encode($data);
    }

    public function countSupportTickets() {
        $result['total'] = $this->db->count_all('user_support_messages');
        $result['open'] = $this->db->where('ticket_status', 'open')->from('user_support_messages')->count_all_results();
        $result['pending'] = $this->db->where('ticket_status', 'pending')->from('user_support_messages')->count_all_results();
        $result['solved'] = $this->db->where('ticket_status', 'solved')->from('user_support_messages')->count_all_results();
        $result['closed'] = $this->db->where('ticket_status', 'closed')->from('user_support_messages')->count_all_results();
        return $result;
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

    public function getUserInfoAutoComplete($search) {
        $this->db->select('*')->from('user');
        $this->db->like('name', $search);
        $this->db->or_like('mobile_number', $search);
        $this->db->or_like('email', $search);
        //$this->db->or_like('device_uuid', $search);
        $this->db->limit(10);
        $query = $this->db->get();
        $data = array();
        foreach ($query->result_array() as $row) {
            $info = array();
            $info['status'] = 'found';
            $info['user_id'] = $row['id'];
            $info['label'] = $row['name'];
            $info['value'] = $row['name'];
            $info['mobile_number'] = $row['mobile_number'];
            $info['email'] = $row['email'];
            $data[] = $info;
        }
        //return json data
        return json_encode($data);
    }

    public function getMessageId($where) {
        $this->db->select('message_id');
        $query = $this->db->get_where('user_support_messages', $where);
        if ($query->num_rows() > 0) {
            return $query->row()->message_id;
        } else {
            return FALSE;
        }
    }

    public function insert($table, $data = array()) {
        $this->db->insert($table, $data);
        return TRUE;
    }

}
