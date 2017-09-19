<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . 'controllers/Main.php';

class Message extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        $this->load->model('message_model');
        $this->load->model('admin_model');
        $this->load->model('admin/user_model');
        $this->load->helper('user_profile_info_helper');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        redirect('admin/message/messageList');
    }

    public function messageList() {

        $data = array();
        $title = array();
        $title['title'] = 'Message List';
        $data['ticketCountByStatus'] = $this->message_model->countSupportTickets();

        $userId = (!empty($this->input->get("user_id"))) ? $this->input->get("user_id") : NULL;
        $ticketStatus = NULL;
        $serchInfo = NULL;
        $per_page = 20;
        $offset = 0;


        $uri_segment = 4;
        $offset = 0;
        if ($this->uri->segment(4) === FALSE) {
            $offset = 0;
        } else {
            $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }


        if ($this->input->get('ticket_status')) {
            $ticketStatus = trim($this->input->get('ticket_status'));
        }
        if ($this->input->get('search_info')) {
            $serchInfo = trim($this->input->get('search_info'));
        }

        $result = $this->message_model->getAllMessages($userId, $per_page, $offset, $ticketStatus, $serchInfo);
        $data['allMessage'] = $result['result'];
        $total_rows = $result['totalRow'];

        generatePagging('admin/message/messageList/', $total_rows, $per_page, $uri_segment, 4);


        $this->load->view('admin/header', $title);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/message/message_list', $data);
        $this->load->view('admin/footer', $data);
    }

    public function conversation($messageId, $senderUserId) {
        $data = array();
        $title = array();
        $title['title'] = 'Conversation';

        $data['senderUserId'] = $senderUserId;

        $data['messageInfo'] = $this->admin_model->get_data('user_support_messages', array('message_id' => $messageId));
        $data['users_personal_info'] = $this->user_model->getAUserInfoById($senderUserId);
        $data['messageId'] = $messageId;

        $status['status'] = 'read';
        $this->message_model->updateMsgStatus($messageId, $status);
        $data['allConversation'] = $this->message_model->getConversationOfAMessage($messageId);

        if ($this->input->post('submitReply')) {
            $this->form_validation->set_rules('message', 'Message', 'trim|required');

            $sessionUserId = $this->session->userdata('userId');
            $sessionUserName = $this->session->userdata('userName');
            $sessionUserRole = $this->session->userdata('userRole');

            if ($_SERVER['HTTP_HOST'] == 'localhost') {
                $sessionUserId = 111;
                $sessionUserName = 'localhost';
                $sessionUserRole = 'None';
            }

            if ($this->form_validation->run()) {
                $replyData = array();
                $replyData['message_id'] = $this->input->post('message_id');
                $replyData['message'] = $this->input->post('message');
                $replyData['sender_id'] = $sessionUserId;
                $replyData['sender_name'] = $sessionUserName;
                $replyData['sender_role'] = $sessionUserRole;
                $replyData['sent_by'] = 'authority';
                $replyData['receiver_id'] = $senderUserId;

                if (!$this->message_model->doesExist('user_support_conversations', array('message_id' => $messageId, 'sent_by' => 'authority'))) {
                    $replyMessageData['ticket_status'] = 'pending';
                }

                $replyMessageData['last_replied_by'] = 'authority';
                $replyMessageData['last_replied_date_time'] = date('Y-m-d H:i:s');
                $replyMessageData['status'] = 'unread';

                $this->message_model->updateMsgTableAfterReply($replyMessageData, $messageId);

                if ($this->db->insert('user_support_conversations', $replyData)) {
                    $this->session->set_flashdata('success_msg', 'Message replied successfully.');
                    redirect('admin/message/messageList');
                }
            }
        }
        $this->load->view('admin/header', $title);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/message/conversation', $data);
        $this->load->view('admin/footer', $data);
    }

    public function deleteMsg($messageId) {
        if ($this->message_model->deleteMsgWithConversation($messageId)) {
            $this->session->set_flashdata('success_msg', 'Message deleted successfully');
            redirect('admin/message/messageList');
        }
    }

    public function deleteConversation($conversationId, $messageId, $senderUserId) {
        if ($this->db->delete('user_support_conversations', array('id' => $conversationId))) {
            $this->session->set_flashdata('success_msg', 'Conversation deleted successfully');
            redirect('admin/message/conversation/' . $messageId . '/' . $senderUserId);
        }
    }

    public function getUserInfo() {
        $search = $this->input->get('term');
        $resp = $this->message_model->getUsersName($search);
        json_decode($resp);
        echo $resp;
    }

    public function updateTicketStatus($id) {
        if ($this->db->update('user_support_messages', array('ticket_status' => 'closed'), array('id' => $id))) {
            $this->session->set_flashdata('success_msg', 'Ticket is closed successfully');
            redirect('admin/message/messageList');
        }
        return FALSE;
    }

    public function sendMessageToUsers() {
        $data = array();
        $data['title'] = 'Send Message';


        if ($this->input->post('submit')) {
            $this->form_validation->set_rules('user_id', 'User Name', 'trim|required');
            $this->form_validation->set_rules('subject', 'Subject', 'trim|required');
            $this->form_validation->set_rules('message', 'Message', 'trim|required');

            if ($this->form_validation->run()) {


                $messageData = array(
                    'message_id' => uniqid(),
                    'sender_user_id' => trim($this->input->post('user_id')),
                    'subject' => trim($this->input->post('subject')),
                    'last_replied_by' => 'user',
                    'last_replied_date_time' => date('Y-m-d H:i:s')
                );

                $conversationData = array(
                    'message_id' => $messageData['message_id'],
                    'message' => trim($this->input->post('message')),
                    'sent_by' => 'user',
                    'receiver_id' => trim($this->input->post('user_id'))
                );
                if ($this->existingCheckForUsersMessage(trim($this->input->post('user_id')), trim($this->input->post('subject')), trim($this->input->post('message'))) == FALSE) {
                    if ($this->message_model->insert('user_support_conversations', $conversationData) && $this->message_model->insert('user_support_messages', $messageData)) {
                        $this->session->set_flashdata('success_msg', 'Ticket saved successfully');
                        redirect('admin/message/messageList');
                    } else {
                        $this->session->set_flashdata('err_msg', 'Failed to save ticket');
                        redirect('admin/message/sendMessageToUsers');
                    }
                } else {
                    $this->session->set_flashdata('err_msg', 'This ticket already exists.');
                    redirect('admin/message/sendMessageToUsers');
                }
            } else {
                $this->session->set_flashdata('err_msg', validation_errors());
                redirect('admin/message/sendMessageToUsers');
            }
        }
        $this->load->view('admin/header', $data);
        $this->load->view('admin/navbar', $data);
        $this->load->view('admin/sidebar', $data);
        $this->load->view('admin/message/create_ticket', $data);
        $this->load->view('admin/footer', $data);
    }

    public function getUserInfo_ajax() {
        
        $search = $this->input->get('term');
        $resp = $this->message_model->getUserInfoAutoComplete($search);
        json_decode($resp);
        echo $resp;
    }

    private function existingCheckForUsersMessage($userId, $subject, $message) {

        if ($this->message_model->doesExist('user_support_messages', array('sender_user_id' => $userId, 'subject' => $subject))) {
            $messageId = $this->message_model->getMessageId(array('sender_user_id' => $userId, 'subject' => $subject));

            if ($this->message_model->doesExist('user_support_conversations', array('message_id' => $messageId, 'message' => $message))) {
                //echo 'exist';
                return TRUE;
            } else {
                //echo 'not exist';
                return FALSE;
            }
        } else {
            //echo 'not exist';
            return FALSE;
        }
    }

}

?>