<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-06-04
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once APPPATH . 'controllers/Main.php';

class Users extends Main {

    function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }

        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }
        
        $this->load->model('admin/user_model');
        $this->load->model('subscription/Orders_model');
        $this->load->model('subscription/productpurchase_model');
        $this->load->model('logs/error_log_model');
        $this->load->model('message_model');
        $this->load->model('admin_model');

        $this->load->helper('user_profile_info_helper');
    }

    public function __destruct() {
        $this->db->close();
    }

    public function index() {
        redirect('admin/users/manage');
    }

    public function manage() {

        $data = array();
        $data['title'] = 'Users';
        //pagination
        $uri_segment = 4;
        $data['offset'] = $offset = 0;
        if ($this->uri->segment(4) === FALSE) {
            $data['offset'] = $offset = 0;
        } else {
            $data['offset'] = $offset = $this->uri->segment(4) ? $this->uri->segment(4) : 0;
        }
        $data['limit'] = $per_page = 20;
        $serchInfo = '';
        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');

        $serchArray = array();

        if ($this->input->get()) {

            if ($this->input->get('search_info')) {
                $serchInfo = $this->input->get('search_info');
            }

            if ($this->input->get('user_search')) {
                $searchOption = $this->input->get('user_search');

                if ($searchOption == 'user_type_id_1' || ($searchOption == 'user_type_id_2') || ($searchOption == 'user_type_id_3')) {
                    $searchOption = explode('_', $searchOption);
                    $serchArray['user_type_id'] = $searchOption[3];
                } elseif ($searchOption == 'status_0' || ($searchOption == 'status_1') || ($searchOption == 'status_2') || ($searchOption == 'status_3')) {
                    $searchOption = explode('_', $searchOption);
                    $serchArray['status'] = $searchOption[1];
                } elseif ($searchOption == 'flag_status_suspicious') {
                    $searchOption = explode('_', $searchOption);
                    $serchArray['flag_status'] = $searchOption[2];
                } elseif ($searchOption == 'subscriber') {
                    $serchArray['subscriber'] = $searchOption;
                } elseif ($searchOption == 'highest_paid_subscribers') {
                    $serchArray['highest_paid_subscribers'] = $searchOption;
                } elseif ($searchOption == 'lowest_paid_subscribers') {
                    $serchArray['lowest_paid_subscribers'] = $searchOption;
                } elseif ($searchOption == 'maximum_bonds_added') {
                    $serchArray['maximum_bonds_added'] = $searchOption;
                } elseif ($searchOption == 'minimum_bonds_added') {
                    $serchArray['minimum_bonds_added'] = $searchOption;
                } elseif ($searchOption == 'maximum_bonds_overloaded') {
                    $serchArray['maximum_bonds_overloaded'] = $searchOption;
                } elseif ($searchOption == 'minimum_bonds_overloaded') {
                    $serchArray['minimum_bonds_overloaded'] = $searchOption;
                } elseif ($searchOption == 'highest_order_pending') {
                    $serchArray['highest_order_pending'] = $searchOption;
                } elseif ($searchOption == 'lowest_order_pending') {
                    $serchArray['lowest_order_pending'] = $searchOption;
                } elseif ($searchOption == 'highest_order_failed') {
                    $serchArray['highest_order_failed'] = $searchOption;
                } elseif ($searchOption == 'lowest_order_failed') {
                    $serchArray['lowest_order_failed'] = $searchOption;
                } elseif ($searchOption == 'highest_order_completed') {
                    $serchArray['highest_order_completed'] = $searchOption;
                } elseif ($searchOption == 'lowest_order_completed') {
                    $serchArray['lowest_order_completed'] = $searchOption;
                }
            }
        }


        if ($this->session->userdata('userRole') == 'CRM Officer') {
            if ($this->input->get()) {
                $result = $this->user_model->getUserInfo($per_page, $offset, $startDate, $endDate, $serchInfo, $serchArray);
                $data['users_info'] = $result['result'];
                $data['total_rows'] = $total_rows = $result['totalRow'];
            } else {
                $data['users_info'] = '';
                $data['total_rows'] = $total_rows = 0;
            }
        } else {
            $result = $this->user_model->getUserInfo($per_page, $offset, $startDate, $endDate, $serchInfo, $serchArray);
            $data['users_info'] = $result['result'];
            $data['total_rows'] = $total_rows = $result['totalRow'];
        }

        generatePagging('admin/users/manage/', $total_rows, $per_page, $uri_segment, 4);

        $this->load->view('admin/users/manage', $data);
    }

    public function profile($parameter, $userId) {
        $data = array();
        $data['title'] = 'User Profile : ' . showUserName($userId);
        $data['user_id'] = $userId;
        $data['users_personal_info'] = $this->user_model->getAUserInfoById($userId);
        $data['users_prize_bond_list'] = $this->user_model->getBondsOfAUserByUserId($userId, 50, 0);
        $data['users_associated_devices'] = $this->user_model->getUsersAssociatedDeviceInfo($userId, 10);
        $data['users_order'] = $this->Orders_model->getOrderList($userId, 10);
        $data['users_purchased_product'] = $this->productpurchase_model->getProductPruchase($userId, 10);
        $data['users_activity_log'] = $this->error_log_model->getUsersActivityLogs($userId, 5);
        $data['users_flag_log'] = $this->error_log_model->getFlagLogsOfAUserByUserId($userId, 10, 0);
        $usersMessages = $this->message_model->getAllMessages($userId, 5);
        $data['users_messages'] = $usersMessages['result'];

        $this->load->view('admin/users/profile', $data);
    }

    public function sendMsgToUser() {
        if ($this->input->post('submit')) {
            $this->form_validation
                    ->set_rules('user_id', 'User ID', 'trim|required')
                    ->set_rules('msg_type', 'Message Type', 'trim')
                    ->set_rules('subject', 'Subject', 'trim')
                    ->set_rules('message', 'Message', 'trim|required');

            // check the validation
            $userId = $this->input->post('user_id');
            if ($this->form_validation->run()) {
                $userId = $this->input->post('user_id');
                $msgType = $this->input->post('msg_type');
                $subject = $this->input->post('subject');
                $message = $this->input->post('message');

                if ($this->sendUserMsgInfo($userId, $msgType, $subject, $message)) {
                    redirect('admin/users/profile/userId/' . $userId);
                }
            } else {
                $this->session->set_flashdata('err_msg', validation_errors());
                redirect('admin/users/profile/userId/' . $userId);
            }
        }
    }

    private function sendUserMsgInfo($userId, $msgType, $subject, $message) {
        $userInfo = $this->Orders_model->get_data('user', array('id' => $userId));

        if (!empty($userInfo)) {
            if ($msgType == 'email') {
                //send email
                $this->processForSendEmail($userInfo, $subject, $message, $userId);
            } elseif ($msgType == 'sms') {
                $this->sendMsgToUserMobileDirect($userInfo, $message, $userId);
            } else {

                $emailSendStatus = $this->sendEmailToAUser($userInfo->email, $userInfo->name, $subject, $message, $userId);
                $smsSendStatus = $this->sendMsgToUserMobile($userInfo, $message, $userId);

                if ($emailSendStatus == TRUE && ($smsSendStatus == TRUE)) {
                    $pushMsg = "You have a new message from Prizebond Team.<br>Check your mobile or email.";
                    if ($this->sendPushNotificationToUser($userId, $pushMsg)) {
                        $this->session->set_flashdata('success_msg', 'Email, SMS and Push notification send.');
                        redirect('admin/users/profile/userId/' . $userId);
                    } else {
                        $this->session->set_flashdata('err_msg', 'Email, SMS send but failed to send Push notification.');
                        redirect('admin/users/profile/userId/' . $userId);
                    }
                } elseif ($emailSendStatus == TRUE && ($smsSendStatus == FALSE)) {
                    $pushMsg = "You have a new message from Prizebond Team.<br>Check your email.";
                    if ($this->sendPushNotificationToUser($userId, $pushMsg)) {
                        $this->session->set_flashdata('success_msg', 'Email and Push notification send.');
                        redirect('admin/users/profile/userId/' . $userId);
                    } else {
                        $this->session->set_flashdata('err_msg', 'Email send but failed to send Push notification.');
                        redirect('admin/users/profile/userId/' . $userId);
                    }
                } elseif ($emailSendStatus == FALSE && ($smsSendStatus == TRUE)) {
                    $pushMsg = "You have a new message from Prizebond Team.<br>Check your mobile.";
                    if ($this->sendPushNotificationToUser($userId, $pushMsg)) {
                        $this->session->set_flashdata('success_msg', 'SMS and Push notification send.');
                        redirect('admin/users/profile/userId/' . $userId);
                    } else {
                        $this->session->set_flashdata('err_msg', 'SMS send but failed to send Push notification.');
                        redirect('admin/users/profile/userId/' . $userId);
                    }
                } else {
                    $this->session->set_flashdata('err_msg', 'Failed to send email and sms.');
                    redirect('admin/users/profile/userId/' . $userId);
                }
            }
        }
    }

    private function processForSendEmail($userInfo, $subject, $message, $userId) {
        $customerName = $userInfo->name;
        $customerEmail = $userInfo->email;
        $customerMobileNumber = $userInfo->mobile_number;

        $senderEmail = 'no-reply@prizebond-checker.com';
        $senderName = 'PrizeBond';
        $receiverEmail = $userInfo->email;
        $receiverName = $userInfo->name;

        if ($this->sendMailUsingSMTP($userInfo->email, $subject, $message)) {
            $pushMsg = "You have a new email from Prizebond Team.<br>Check your email.";
            if ($this->sendPushNotificationToUser($userId, $pushMsg)) {
                $this->session->set_flashdata('success_msg', 'Email and Push notification send.');
                redirect('admin/users/profile/userId/' . $userId);
            } else {
                $this->session->set_flashdata('success_msg', "Send email successfully. <br>But failed to sent Push notification send.");
                redirect('admin/users/profile/userId/' . $userId);
            }
        } else {
            $this->session->set_flashdata('err_msg', 'Failed to sent email');
            redirect('admin/users/profile/userId/' . $userId);
        }
    }
    
    private function sendMsgToUserMobileDirect($userInfo, $message, $userId) {

        $params = array(
            'recipient_mobile_number' => $userInfo->mobile_number,
            'message' => $message,
            'sender_mask' => NULL
        );
        $this->load->library('smsrouter', $params);
        $response = $this->smsrouter->send();

        if ($response) {
            if ($this->sendPushNotificationToUser($userId, $pushMsg)) {
                $this->session->set_flashdata('success_msg', 'Email and Push notification send.');
                redirect('admin/users/profile/userId/' . $userId);
            } else {
                $this->session->set_flashdata('success_msg', "Send sms successfully. <br>But failed to sent  Push notification send.");
                redirect('admin/users/profile/userId/' . $userId);
            }
        }
        $this->session->set_flashdata('success_msg', "Failed to send message.");
        redirect('admin/users/profile/userId/' . $userId);
    }

    private function sendEmailToAUser($receiverEmail, $receiverName, $subject, $message, $userId) {
        $senderEmail = 'no-reply@prizebond-checker.com';
        $senderName = 'PrizeBond';

        $data = array($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message);
        $this->load->library('email');
        $this->email->from($senderEmail, $senderName);
        $this->email->to($receiverEmail);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return TRUE;
        }
        return FALSE;
    }

    private function processMsgForUserMobile($userInfo, $message, $userId) {

        if ($this->processMsgForUserMobile($userInfo, $message, $userId)) {
            $pushMsg = "You have a new message from Prizebond Team.<br>Check your mobile.";
            if ($this->sendPushNotificationToUser($userId, $pushMsg)) {
                $this->session->set_flashdata('success_msg', 'Email and Push notification send.');
                redirect('admin/users/profile/userId/' . $userId);
            } else {
                $this->session->set_flashdata('success_msg', "Send sms successfully. <br>But failed to sent  Push notification send.");
                redirect('admin/users/profile/userId/' . $userId);
            }
        }
        return FALSE;
    }

    private function sendMsgToUserMobile($userInfo, $message, $userId) {

        $params = array(
            'recipient_mobile_number' => $userInfo->mobile_number,
            'message' => $message,
            'sender_mask' => NULL
        );
        $this->load->library('smsrouter', $params);
        $response = $this->smsrouter->send();

        if ($response) {
            return TRUE;
        }
        return FALSE;
    }

    private function sendPushNotificationToUser($userId, $message) {
        $this->load->library('gcm');

        $userDeviceInfo = $this->Orders_model->get_data('device_info', array('user_id' => $userId, 'device_status' => 'active'));

        if (!empty($userDeviceInfo)) {
            $this->gcm->clearRecepients();
            $registrationIds = array();
            $registrationIds[] = $userDeviceInfo->device_push_id;

            $this->gcm->setRecepients($registrationIds);
            $payloadData = array(
                'prizebond' => 'Custom content'
            );

            $this->gcm->setData($payloadData);
            $this->gcm->setMessage($message);
            $this->gcm->setGroup(md5($message));
            $this->gcm->send();
            $gcmStatus = $this->gcm->status;
            if ($gcmStatus['error'] != 0) {
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }

}
