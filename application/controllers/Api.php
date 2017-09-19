<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-09-19
 */

defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . 'controllers/Prizebond.php';
require APPPATH . '/libraries/REST_Controller.php';

/*
 * for future implementation
 * 
 * 100 => 'Continue',
  101 => 'Switching Protocols',
  200 => 'OK',
  201 => 'Created',
  202 => 'Accepted',
  203 => 'Non-Authoritative Information',
  204 => 'No Content',
  205 => 'Reset Content',
  206 => 'Partial Content',
  300 => 'Multiple Choices',
  301 => 'Moved Permanently',
  302 => 'Found',
  303 => 'See Other',
  304 => 'Not Modified',
  305 => 'Use Proxy',
  306 => '(Unused)',
  307 => 'Temporary Redirect',
  400 => 'Bad Request',
  401 => 'Unauthorized',
  402 => 'Payment Required',
  403 => 'Forbidden',
  404 => 'Not Found',
  405 => 'Method Not Allowed',
  406 => 'Not Acceptable',
  407 => 'Proxy Authentication Required',
  408 => 'Request Timeout',
  409 => 'Conflict',
  410 => 'Gone',
  411 => 'Length Required',
  412 => 'Precondition Failed',
  413 => 'Request Entity Too Large',
  414 => 'Request-URI Too Long',
  415 => 'Unsupported Media Type',
  416 => 'Requested Range Not Satisfiable',
  417 => 'Expectation Failed',
  426 => 'Upgrade required'
  500 => 'Internal Server Error',
  501 => 'Not Implemented',
  502 => 'Bad Gateway',
  503 => 'Service Unavailable',
  504 => 'Gateway Timeout',
  505 => 'HTTP Version Not Supported');
 * */

class Api extends REST_Controller {

    public $PATH;
    public $api_key;

    const USER_TYPE_CUSTOMER = 'customer';
    const USER_TYPE_DEVELOPER = 'developer';
    const USER_TYPE_CRM = 'crm';

    function __construct() {
        parent::__construct();
        $this->PATH = './images/';
        $this->load->model('api_model');
        $this->load->model('global_model');
        $this->load->model('subscription/subscription_model');
        $this->load->helper('string');
        //$this->load->library('verify'); commented by a teammate
        $this->load->library('uuid');
        $this->load->library('bitbirds');
        $this->load->library('muthofun');

        $this->api_key = $this->post('api_key') ? $this->post('api_key') : '';
    }

    public function updateAPIKey_post() {
        $updateData = array(
            'update_api_key' => $this->input->post('update_api_key')
        );
        return $this->api_model->updateAPIKey($updateData);
    }

    public function checkEmail_post() {

        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
        if ($this->form_validation->run() == TRUE) {
            $this->verifyAPIKey($this->api_key);
            if (!$this->global_model->doesExist('user', array('email' => $this->post('email')))) {

                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                $this->response($response, 200);
            } else {

                $response['response']['error'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['error']; //'Email address already exists';
                $response['response']['code'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>  User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
              $this->response($response, 200); */
        }
    }

    public function checkMobileNumber_post() {

        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        //$this->form_validation->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|required|min_length[2]|max_length[2]');
        $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[11]|max_length[14]');

        /* if ($this->post('country_iso') == 'BD') {
          $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[11]|max_length[14]');
          } else {
          $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[8]|max_length[20]');
          } */

        if ($this->form_validation->run() == TRUE) {
            $this->verifyAPIKey($this->api_key);

            /* if ($this->post('mobile_number')) {
              // verify mobile numbers with dialing code
              if ($validNumberStatus = $this->validateMobileNumber($this->post('country_iso'), $this->post('mobile_number'))) {
              // 1 = matched , 2 = not matched, 3 = invalid country iso
              if ($validNumberStatus == 2) {
              $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['error'];
              $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
              $this->response($response);
              } else if ($validNumberStatus == 3) {
              $response['response']['error'] = $this->config->item('INVALID_COUNTRY_ISO_CODE_01')['error'];
              $response['response']['code'] = $this->config->item('INVALID_COUNTRY_ISO_CODE_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
              $this->response($response, 200);
              }
              }
              } */

            if (!$this->global_model->doesExist('user', array('mobile_number' => $this->post('mobile_number')))) {

                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate (authentication)
    public function updateUserMobile_post() {

        $this->form_validation->set_rules('verification_code', 'verification code', 'trim|required');
        $this->form_validation->set_rules('update_mobile_number', 'update mobile number', 'trim|required|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        //$this->form_validation->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|required|min_length[2]|max_length[2]');

        /* if ($this->post('country_iso') == 'BD') {
          $this->form_validation->set_rules('update_mobile_number', 'update mobile number', 'trim|required|min_length[11]|max_length[14]');
          } else {
          $this->form_validation->set_rules('update_mobile_number', 'update mobile number', 'trim|required|min_length[8]|max_length[20]');
          } */

        if ($this->form_validation->run() == TRUE) {


            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                //$userInfo = $this->api_model->get_data('user', array('access_token' => $this->post('access_token')));

                $this->db->where('access_token', $this->post('access_token'));
                $query = $this->db->get('user');
                $userInfo = $query->row();


                if ($this->global_model->doesExist('user', array('id !=' => $userId, 'mobile_number' => $this->post('update_mobile_number')))) {
                    // mobile number already exists show error
                    $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                    $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());

                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Mobile number already exists');
                    $this->response($response, 200);
                } else {
                    // mobile number is eligable to update
                    if ($userInfo->verify_code == $this->post('verification_code')) {

                        if ($this->global_model->update('user', array('mobile_number' => $this->post('update_mobile_number'), 'update_mobile_verification_code' => NULL), array('id' => $userId))) {

                            $purchaseInfo = $this->api_model->getAUserParchasedSummaryInfo($userInfo->id);
                            $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userInfo->id);
                            $message = $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                            $code = $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];

                            $winInfo = $this->userWinInfoByUserId($userInfo->id);


                            //generate access token
                            $userInfo->access_token = $this->generateAccessTokenById($userInfo->id);
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Mobile number updated successfully');


                            $this->sendLoginSuccessResponse($userInfo, $purchaseInfo, $subscriptionInfo, $winInfo, $message, $code);


                            // show success message
//                            $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                            $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//
//                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Mobile number updated successfully');
//                            $this->response($response, 200);
                        } else {
                            // show db error
                            $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                            $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB error occoured');
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                            $this->response($response, 200);
                        }
                    } else {
                        // failed error
                        $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Verification code mot matched');
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->response($response, 200);
                    }
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {

            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate (authentication)
    public function updateUserEmail_post() {

        $this->form_validation->set_rules('verification_code', 'verification code', 'trim|required');
        $this->form_validation->set_rules('update_email', 'update email', 'trim|required|valid_email');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                $userInfo = $this->api_model->get_data('user', array('access_token' => $this->post('access_token')));
                if ($this->global_model->doesExist('user', array('id !=' => $userId, 'email' => $this->post('update_email')))) {
                    // email address already exists show error
                    $response['response']['error'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['error'];
                    $response['response']['code'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Email address already exists');
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                    $this->response($response, 200);
                } else {
                    // email address is eligable to update
                    if ($userInfo['verify_code'] == $this->post('verification_code')) {
                        if ($this->global_model->update('user', array('email' => $this->post('update_email'), 'verify_code' => NULL, 'email_verify_status' => 'YES'), array('id' => $userId))) {
                            // show success message
                            $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                            $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Email address updated successfully');
                            $this->response($response, 200);
                        } else {
                            // show db error
                            $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                            $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB error occoured');
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                            $this->response($response, 200);
                        }
                    } else {
                        // failed error
                        $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Verification code not matched');
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                        $this->response($response, 200);
                    }
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    public function userRegistration_post() {

        $webRequrest = $this->detectValidWebRequest();

        if ($webRequrest != TRUE) {
            $this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required|min_length[12]');
        }

        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('name', 'full name', 'trim|required');
        $this->form_validation->set_rules('email', 'email', 'trim|valid_email|required');
        $this->form_validation->set_rules('password', 'password', 'required|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('retype_password', 'password confirmation', 'required|matches[password]');

        if ($this->form_validation->run() == TRUE) {

            $this->verifyAPIKey($this->api_key);


            if ($webRequrest != TRUE) { // this from mobile device
                $userData['device_uuid'] = $this->post('device_uuid');
            } else { // this is from web
                $userData['device_uuid'] = 'web';
            }

            $userData['name'] = $this->post('name');
            $userData['email'] = $this->post('email');
            $userData['password'] = md5($this->post('password'));
            $userData['user_id'] = $this->uuid->v4();
            $userData['created_date_time'] = date('Y-m-d H:i:s');

            if ($this->global_model->doesExist('user', array('email' => $userData['email'])) != TRUE) {

                $this->db->trans_start();

                if ($webRequrest != TRUE) { // this from mobile device
                    $userId = 0; // do not change value
                    $deviceInfo = $this->api_model->getDeviceInfo($this->post('device_uuid'), $userId);

                    if (!$deviceInfo) {

                        $response['response']['error'] = $this->config->item('USER_REGISTRATION_FAILED_04')['error'];
                        $response['response']['code'] = $this->config->item('USER_REGISTRATION_FAILED_04')['code'];
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Device info not found for device_uuid : ' . $this->post('device_uuid') . ' userId: ' . $userId . ' during registration process');
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' -> [device info not found ] userData :' . json_encode($this->post()) . '</br>Source -> ' . current_url());
                        $this->response($response);
                    }

                    if ($userId = $this->global_model->insert('user', $userData)) {

                        $this->db->update('device_info', array('user_id' => $userId), array('device_uuid' => $userData['device_uuid'], 'user_id' => 0));
                        $this->api_model->allocateFreeProductToUserAfterSuccessfulRegistration($userId);

                        $this->db->trans_complete();
                        if ($this->db->trans_status() === FALSE) {

                            $response['response']['error'] = $this->config->item('USER_REGISTRATION_FAILED_03')['error'];
                            $response['response']['code'] = $this->config->item('USER_REGISTRATION_FAILED_03')['code'];
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' -> data :' . json_encode($userData) . '</br>Source -> ' . current_url());
                            $this->response($response);
                        } else {
                            $this->determineSuspiciousActivity($this->post('device_uuid'), $userId);
                            $this->logUserActivity($userId, NULL, NULL, 'User registration successfull from mobile');
                            $responseParam['user_id'] = (string) $userId;
                            $this->sendMobileVerificationRequiredResponse($responseParam);
                        }
                    } else { // db insert failed
                        $response['response']['error'] = $this->config->item('USER_REGISTRATION_FAILED_03')['error'];
                        $response['response']['code'] = $this->config->item('USER_REGISTRATION_FAILED_03')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' -> data :' . json_encode($userData) . '</br>Source -> ' . current_url());
                        $this->response($response);
                    }
                } else { // this is from web
                    if ($userId = $this->global_model->insert('user', $userData)) {

                        // update user table set device_uuid = web-userid
                        $this->api_model->update('user', array('device_uuid' => 'web-' . $userId), array('id' => $userId));

                        $this->api_model->allocateFreeProductToUserAfterSuccessfulRegistration($userId);

                        $this->db->trans_complete();
                        if ($this->db->trans_status() === FALSE) {

                            $response['response']['error'] = $this->config->item('USER_REGISTRATION_FAILED_03')['error'];
                            $response['response']['code'] = $this->config->item('USER_REGISTRATION_FAILED_03')['code'];
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' -> data :' . json_encode($userData) . '</br>Source -> ' . current_url());
                            $this->response($response);
                        } else {
                            $this->logUserActivity($userId, NULL, NULL, 'User registration successfull from web');
                            $responseParam['user_id'] = (string) $userId;
                            $this->sendMobileVerificationRequiredResponse($responseParam);
                        }
                    } else { // db insert failed
                        $response['response']['error'] = $this->config->item('USER_REGISTRATION_FAILED_03')['error'];
                        $response['response']['code'] = $this->config->item('USER_REGISTRATION_FAILED_03')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br> -> data :' . json_encode($userData) . '</br>Source -> ' . current_url());
                        $this->response($response);
                    }
                }
            } else {

                $response['response']['error'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['error']; //'Email address already exists';
                $response['response']['code'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));

                $this->response($response);
            }
        } else { // user registration validation failed
            $response['response']['error'] = $this->config->item('USER_REGISTRATION_FAILED_02')['error'];
            $response['response']['code'] = $this->config->item('USER_REGISTRATION_FAILED_02')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));

            $this->response($response);
        }
    }

    public function userLogin_post() {

        $webRequrest = $this->detectValidWebRequest();

        if ($webRequrest != TRUE) { // this is from mobile device
            $this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required|min_length[12]');
        }

        $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('email', 'email', 'trim|valid_email');
        $this->form_validation->set_rules('password', 'password', 'required|min_length[6]|max_length[20]');
//        $this->form_validation->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|required|min_length[2]|max_length[2]');

        /* if ($this->post('country_iso') == 'BD') {
          $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[11]|max_length[14]');
          } else {
          $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[8]|max_length[20]');
          } */

        if ($this->form_validation->run() == TRUE) {

            $this->verifyAPIKey($this->api_key);

            /* if ($this->post('mobile_number')) {
              // verify mobile numbers with dialing code
              $this->verifyMobileNumberWithDialingCode($this->post('mobile_number'), $this->post('country_iso'));
              } */

            $email = $this->post('email');
            $mobile_number = $this->post('mobile_number');

            if ($email) {
                $emailOrMobile = $email;
            } elseif ($mobile_number) {
                $emailOrMobile = $mobile_number;
            } else {
                $response['response']['error'] = $this->config->item('EMAIL_OR_MOBILE_REQUIRED_01')['error'];
                $response['response']['code'] = $this->config->item('EMAIL_OR_MOBILE_REQUIRED_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }

            if ($userInfo = $this->global_model->getUserInfoByCredential($emailOrMobile, $this->post('password'))) {

                $responseParam = array(
                    'user_id' => (string) $userInfo->id
                );

                if ($userInfo->status != 1) {

                    $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_03')['error'];
                    $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_03')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' user_status != 1 </br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    // pramas : userId, code , message, internal message
                    $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'User Account not active');
                    $this->response($response);
                } else if ($userInfo->verify_status == 'YES' && $userInfo->status == 1) {

                    if ($webRequrest != TRUE) { // this is from mobile device
                        $query = $this->db->get_where('device_info', array('user_id' => $userInfo->id, 'device_uuid' => $this->post('device_uuid')));
                        $deviceInfo = $query->row();

                        if (!$deviceInfo) { // user current device not found
                            $this->logUserActivity($userInfo->id, NULL, NULL, 'Device info not found while login');
                            $this->sendMobileVerificationRequiredResponse($responseParam);

                            // code disabled instructed by email. Sent by a teammate vai 26 April
//                            $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_03')['error'];
//                            $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_03')['code'];
//                            $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'Device info not found');
//                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' device info not found </br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                            $this->response($response);
                        } else if ($deviceInfo->device_status != 'active') { // user current device not active
                            $this->logUserActivity($userInfo->id, NULL, NULL, 'device status not active');
                            $this->sendMobileVerificationRequiredResponse($responseParam);
                        } else if ($userInfo->device_uuid != $this->post('device_uuid')) { // user logged in from another device
                            $this->sendMobileVerificationRequiredResponse($responseParam);
                        } else {
                            // user logged in from same device as did before
                            $data = array(
                                'device_status' => 'active',
                                'update_date' => date('Y-m-d H:i:s')
                            );
                            $this->global_model->update('device_info', $data, array('device_uuid' => $userInfo->device_uuid, 'user_id' => $userInfo->id));

                            // Tag internal test account
                            if (!filter_var($emailOrMobile, FILTER_VALIDATE_EMAIL) === false) {
                                // valid email

                                if (strpos($emailOrMobile, '@example.com') !== false) {
                                    // this is an internal test account, so tag it to developer account
                                    if ($userInfo->user_type_id == 1) {
                                        $data2 = array(
                                            'user_type_id' => '2'
                                        );
                                        $this->global_model->update('user', $data2, array('id' => $userInfo->id));
                                    }
                                }
                            }



                            // End Tag internal test account


                            $purchaseInfo = $this->api_model->getAUserParchasedSummaryInfo($userInfo->id);
                            $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userInfo->id);
                            $winInfo = $this->userWinInfoByUserId($userInfo->id);

                            //generate access token
                            $userInfo->access_token = $this->generateAccessTokenById($userInfo->id);

                            $message = $this->config->item('USER_LOGIN_SUCCESS_01')['success'];
                            $code = $this->config->item('USER_LOGIN_SUCCESS_01')['code'];

                            // pramas : userId, code , message, internal message
                            $this->logUserActivity($userInfo->id, $code, $message, 'Login success from mobile');

                            $this->sendLoginSuccessResponse($userInfo, $purchaseInfo, $subscriptionInfo, $winInfo, $message, $code);
                        }
                    } else { // this is from web
                        $purchaseInfo = $this->api_model->getAUserParchasedSummaryInfo($userInfo->id);
                        $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userInfo->id);
                        $winInfo = $this->userWinInfoByUserId($userInfo->id);

                        //generate access token
                        $userInfo->access_token = $this->generateAccessTokenById($userInfo->id);

                        $message = $this->config->item('USER_LOGIN_SUCCESS_01')['success'];
                        $code = $this->config->item('USER_LOGIN_SUCCESS_01')['code'];

                        // pramas : userId, code , message, internal message
                        $this->logUserActivity($userInfo->id, $code, $message, 'Login success from web');

                        $this->sendLoginSuccessResponse($userInfo, $purchaseInfo, $subscriptionInfo, $winInfo, $message, $code);
                    }
                } else { // User Verification required
                    // pramas : userId, code , message, internal message
                    $this->logUserActivity($userInfo->id, NULL, NULL, 'device verification required');
                    $this->sendMobileVerificationRequiredResponse($responseParam);
                }
            } else { // User credential missmached
                $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_02')['error'];
                $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response);
            }
        } else { // user validation failed
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate & (authentication)
    public function userLogout_post() {

        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                $data = array(
                    'access_token' => NULL,
                    'access_token_expire_time' => NULL
                );

                if ($this->db->update('user', $data, array('id' => $userId))) {
                    $response['response']['info'] = $this->config->item('USER_LOGIN_INFO_01')['info'];
                    $response['response']['code'] = $this->config->item('USER_LOGIN_INFO_01')['code'];
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('UPDATE_FAILED_01')['error'];
                    $response['response']['code'] = $this->config->item('UPDATE_FAILED_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB error occoured, unable to log out user');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {

            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response); */
        }
    }

    //created by a teammate & (authentication)
    public function searchBond_post() {
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        $this->form_validation->set_rules('order_by', 'Order By', 'trim|required|in_list[by_bond_number,by_added_date]');
        $this->form_validation->set_rules('bond_number', 'Bond number', 'required|trim|is_natural|min_length[3]|max_length[8]');

        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                if ($this->post('order_by') == 'by_bond_number') {
                    $order_by = 'bond_number';
                } elseif ($this->post('order_by') == 'by_added_date') {
                    $order_by = 'created_date_time';
                }


                $bond_number = $this->post('bond_number');
                $searchResult = $this->api_model->searchBondNumberByUserIdAndBondNumber($order_by, $bond_number, $userId);
                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                $winInfo = $this->userWinInfoByUserId($userId);
                if (!empty($searchResult)) {
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['total'] = count($searchResult);
                    $response['response']['data'] = $searchResult;
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_info'] = $winInfo;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'search result found : ');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'no prizebond found : ');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {

            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response); */
        }
    }

    //created by a teammate & (authentication)
    public function getPrizeBonds_post() {
        $this->form_validation->set_rules('offset', 'offset', 'trim|required|is_natural');
        $this->form_validation->set_rules('order_by', 'Order By', 'trim|required|in_list[by_bond_number,by_added_date]');
        $this->form_validation->set_rules('order_type', 'Order Type', 'trim|in_list[ascending,descending]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {

            if ($this->post('order_by') == 'by_bond_number') {
                $order_by = 'bond_number';
            } elseif ($this->post('order_by') == 'by_added_date') {
                $order_by = 'created_date_time';
            }

            $order_type = 'DESC';
            if ($this->post('order_type') == 'ascending') {
                $order_type = 'ASC';
            } elseif ($this->post('order_type') == 'descending') {
                $order_type = 'DESC';
            }


            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $offset = $this->post('offset');
                $limit = 50;

                $getPrizeBondList = $this->api_model->getBondsByUserId($limit, $offset, $order_by, $order_type, $userId);
                $totalBond = $this->api_model->countUserBondNumber($userId);
                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                $winInfo = $this->userWinInfoByUserId($userId);


                if (!empty($getPrizeBondList)) {
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['total'] = $totalBond;
                    $response['response']['data'] = $getPrizeBondList;
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_info'] = $winInfo;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'prizebond found ');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'no prizebond found');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate & (authentication)
    public function didIWin_post() {
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                $allDrawInfo = $this->api_model->getListOfDrawNumbers();
                $allDrawInfo = implode(', ', $allDrawInfo);
                $allDrawInfo = $this->convertEnglishNumberIntoBanglaNumber($allDrawInfo);
                $date = $this->convertEnglishDateTimeToBanglaDateTime();
                $message = 'আপনার সবগুলো প্রাইজবন্ড ' . $allDrawInfo . ' তম প্রাইজবন্ড ড্র ফলাফলের সাথে সর্বশেষ ' . $date . ' মিলিয়ে দেখা হয়েছে।';

                $winnerSearchResults = $this->api_model->getWinnerInfoByUserId($userId);

                $finalOutput = array();
                if (!empty($winnerSearchResults)) {

                    $currentPosition = $previousPosition = get_prize_position($winnerSearchResults[0]['prize_position']);

                    $finalOutput = array();
                    $prizeList = array();
                    foreach ($winnerSearchResults as $result) {

                        $currentPosition = get_prize_position($result['prize_position']);
                        if ($currentPosition != $previousPosition) {
                            $finalOutput[$previousPosition] = $prizeList;
                            $prizeList = array();
                            $previousPosition = get_prize_position($result['prize_position']);
                        }
                        $result['prizebond_result_number'] = $this->convertEnglishNumberIntoBanglaNumber($result['prizebond_result_number']);

                        //unset($result['prize_position']);
                        $prizeList[] = $result;
                    }

                    $finalOutput[$previousPosition] = $prizeList;
                }

                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                $winInfo = $this->userWinInfoByUserId($userId);

                if (!empty($subscriptionInfo)) {

                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['message'] = $message;
                    if (!empty($winnerSearchResults)) {
                        $response['response']['winning_data'] = $finalOutput;
                    } else {
                        $response['response']['winning_data'] = '';
                    }

                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_info'] = $winInfo;



                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], ' user checked his/her bonds with result ');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], ' user checked his/her bonds with result ');
                    $this->response($response, 200);
                }
            } else {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br> User Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());

              $this->response($response, 200); */
        }
    }

    //created by a teammate (authentication)
    public function changePassword_post() {
        $this->form_validation->set_rules('old_password', 'old password', 'required|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('new_password', 'new password', 'required|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('retype_password', 'retype password', 'required|matches[new_password]|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {
            $oldPassword = md5($this->post('old_password'));
            if (!$this->global_model->doesExist('user', array('password' => $oldPassword, 'access_token' => $this->post('access_token')))) {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $updateData['password'] = md5($this->post('new_password'));

                if ($this->global_model->update('user', $updateData, array('id' => $userId))) {
                    $updateAccessToken = array(
                        'access_token' => NULL,
                        'access_token_expire_time' => NULL
                    );
                    $this->global_model->update('user', $updateAccessToken, array('id' => $userId));

                    $response['response']['info'] = $this->config->item('USER_LOGIN_INFO_02')['info'];
                    $response['response']['code'] = $this->config->item('USER_LOGIN_INFO_02')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['info'], ' user has changed password succesfully');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('UPDATE_FAILED_01')['error'];
                    $response['response']['code'] = $this->config->item('UPDATE_FAILED_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB error occoured. Failed to change password');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {

            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());

              $this->response($response, 200); */
        }
    }

    //created by a teammate (authentication)
    public function updateProfile_post() {

        $this->form_validation->set_rules('name', 'full name', 'trim|min_length[3]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                $userData['name'] = $this->post('name');

                if (!empty($_FILES["image"]["name"])) {
                    $allowed = array('jpeg', 'png', 'jpg');
                    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

                    if (!in_array($ext, $allowed)) {
                        $response['response']['error'] = $this->config->item('INVALID_IMAGE_01')['error'];
                        $response['response']['code'] = $this->config->item('INVALID_IMAGE_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error']);
                        $this->response($response, 200);
                        exit;
                    }
                }

                if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != '') {

                    $this->deleteUserOldImage($userId);
                    $image_name = time();
                    if (!file_exists($this->PATH)) {
                        mkdir($this->PATH, 0777, true);
                    }
                    $this->load->library('file_processing');

                    $userData['image'] = $this->file_processing->image_upload('image', $this->PATH, 'size[400,400]', 'jpg|jpeg|png', $image_name);
                }

                if ($this->global_model->update('user', $userData, array('id' => $userId))) {
                    $userInfo = $this->global_model->getUserInfo($userId);
                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                    $winInfo = $this->userWinInfoByUserId($userId);
                    $hash = md5(serialize($userInfo));
                    $response['response']['success'] = $this->config->item('UPDATE_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('UPDATE_SUCCESS_01')['code'];
                    $response['response']['user_info'] = $userInfo;
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_bond'] = $winInfo;
                    $response['response']['hash'] = $hash;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'User profile updated');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('UPDATE_FAILED_01')['error'];
                    $response['response']['code'] = $this->config->item('UPDATE_FAILED_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB error occoured. Failed to update user profile');
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate (authentication)
    public function sendVerificationCodeForUpdateEmailOrMobileNumber_post() {

        $this->form_validation->set_rules('update_email', 'update email', 'trim|valid_email');
        $this->form_validation->set_rules('update_mobile_number', 'update mobile number', 'trim|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        $this->form_validation->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|min_length[2]|max_length[2]');

        /* if ($this->post('country_iso') == 'BD') {
          $this->form_validation->set_rules('update_mobile_number', 'update mobile number', 'trim|required|min_length[11]|max_length[14]');
          } else {
          $this->form_validation->set_rules('update_mobile_number', 'update mobile number', 'trim|required|min_length[8]|max_length[20]');
          } */

        if ($this->form_validation->run() == TRUE) {

            $mobileNumber = $this->post('update_mobile_number');
            $email = $this->post('update_email');

            if (empty($mobileNumber) && empty($email)) {

                // show error
                $response['response']['error'] = $this->config->item('COMMON_ERROR_01')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            } elseif ($mobileNumber && (empty($this->post('country_iso')))) {

                // show error
                $response['response']['error'] = $this->config->item('COMMON_ERROR_01')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }


            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {


                if ($mobileNumber) {

                    // verify mobile numbers with dialing code
                    //$this->verifyMobileNumberWithDialingCode($mobileNumber, $this->post('country_iso'));
                    // mobile number already used by someone or loggedin user
                    if ($this->global_model->doesExist('user', array('mobile_number' => $mobileNumber, 'verify_status' => 'YES'))) {

                        $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'mobile number already exists and verified by our system');
                        $this->response($response, 200);
                    } else {
                        if ($this->sendVerificationCodeByMobileNumber($mobileNumber, $userId)) {
                            $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                            $winInfo = $this->userWinInfoByUserId($userId);
                            $response['response']['success'] = $this->config->item('VARIFICATION_SUCCESS_02')['success'];
                            $response['response']['code'] = $this->config->item('VARIFICATION_SUCCESS_02')['code'];
                            $response['response']['subscription'] = $subscriptionInfo;
                            $response['response']['win_info'] = $winInfo;
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user need to verify device before updating mobile number');
                            $this->response($response, 200);
                        } else {
                            $response['response']['error'] = $this->config->item('USER_VERIFICATION_03')['error'];
                            $response['response']['code'] = $this->config->item('USER_VERIFICATION_03')['code'];
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . 'user_id->' . json_encode($userId));
                            $this->response($response, 200);
                        }
                    }
                } else if ($email) {

                    // email address already used by someone or by loggedin user
                    if ($this->global_model->doesExist('user', array('email' => $email, 'verify_status' => 'YES'))) {

                        $response['response']['error'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('EMAIL_VALIDATION_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->response($response, 200);
                    } else {

                        $userInfo = $this->api_model->get_data('user', array('access_token' => $this->post('access_token')));

                        $senderEmail = 'no-reply@prizebond-checker.com';
                        $senderName = 'PrizeBond';
                        $receiverEmail = $email;
//                        $receiverEmail = $userInfo['email'];
                        $receiverName = $userInfo['name'];
                        $subject = 'Your Verification Code';

                        $verificationCode = $this->generateVerificationCode();
                        $message = "Dear " . $receiverName . ",<br/>";
                        $message .= "<strong>Your prizebond-checker.com Account Verification Code is:</strong> " . $verificationCode . "<br/></br> Prizebond Checker Team";


                        if ($this->sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message)) {
                            // email sent successfully
                            if ($this->api_model->update('user', array('verify_code' => $verificationCode), array('id' => $userInfo['id']))) {
                                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                                $winInfo = $this->userWinInfoByUserId($userId);
                                $response['response']['success'] = $this->config->item('VARIFICATION_SUCCESS_02')['success'];
                                $response['response']['code'] = $this->config->item('VARIFICATION_SUCCESS_02')['code'];
                                $response['response']['subscription'] = $subscriptionInfo;
                                $response['response']['win_info'] = $winInfo;
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user need to retrieve verification code from existing email before updating email address');
                                $this->response($response, 200);
                            } else {
                                $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                                $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'DB error occourd during email verification');
                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                                $this->response($response, 200);
                            }
                        } else {
                            $data = array($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message);
                            // email sent failed
                            $response['response']['error'] = $this->config->item('USER_VERIFICATION_03')['error'];
                            $response['response']['code'] = $this->config->item('USER_VERIFICATION_03')['code'];
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Failed to sent email -> data: ' . json_encode($data));
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . 'data->' . json_encode($data));
                            $this->response($response, 200);
                        }
                    }
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_01')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    public function sendVerificationCode_post() {

        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('login_email', 'login email', 'trim|valid_email');
//        $this->form_validation->set_rules('login_mobile_number', 'login mobile number', 'trim|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('password', 'password', 'required|min_length[6]');
//        $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|required|min_length[2]|max_length[2]');

        if ($this->post('country_iso') == 'BD') {
            $this->form_validation->set_rules('login_mobile_number', 'login mobile number', 'trim|min_length[11]|max_length[14]');
            $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[11]|max_length[14]');
        } else {
            $this->form_validation->set_rules('login_mobile_number', 'login mobile number', 'trim|min_length[8]|max_length[20]');
            $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[8]|max_length[20]');
        }

        if ($this->form_validation->run() == TRUE) {

            $this->verifyAPIKey($this->api_key);

            if (substr_count($this->post('mobile_number'), '+') > 1) {
                $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response);
            } else if (substr_count($this->post('login_mobile_number'), '+') > 1) {
                $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response);
            }

            // case 1 : user register / login with email + password
            // case 2 : user login with email / mobile + password

            if ($this->post('mobile_number')) {
                // verify mobile numbers with dialing code
                $this->verifyMobileNumberWithDialingCode($this->post('mobile_number'), $this->post('country_iso'));
            }

            if ($this->post('login_mobile_number')) {
                // verify mobile numbers with dialing code
                $this->verifyMobileNumberWithDialingCode($this->post('login_mobile_number'), $this->post('country_iso'));
            }

            if ($this->post('login_email')) {

                $userInfo = $this->api_model->getUserInfoByEmailAndPassword($this->post('login_email'), $this->post('password'));

                if ($userInfo) {
                    $mobileNumber = $this->post('mobile_number');
                    if ($userInfo->mobile_number == $mobileNumber) {
                        $this->sendUserVerificationCode($userInfo->id, $mobileNumber);
                    } else if ($this->global_model->doesExist('user', array('id !=' => $userInfo->id, 'mobile_number' => $mobileNumber, 'verify_status' => 'YES'))) {

                        $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'mobile number already tagged and verified by other user');
                        $this->response($response);
                    } else {
                        $this->sendUserVerificationCode($userInfo->id, $mobileNumber);
                    }
                } else {
                    $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                    $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '<br>User Data -> ' . json_encode($this->post()));
                    $this->response($response, 200);
                }


                /*
                  $mobileNumber = $this->post('mobile_number');
                  if ($userInfo->mobile_number == $mobileNumber) {
                  $this->sendUserVerificationCode($userInfo->id, $mobileNumber);
                  } else {
                  if ($this->global_model->doesExist('user', array('id !=' => $userInfo->id, 'mobile_number' => $mobileNumber, 'verify_status' => 'YES'))) {

                  $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                  $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                  $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                  $this->response($response);
                  } else {
                  $this->sendUserVerificationCode($userInfo->id, $mobileNumber);
                  }
                  } */
            } elseif ($this->post('login_mobile_number')) {

                $userInfo = $this->api_model->getUserInfoByMobileNumberAndPassword($this->post('login_mobile_number'), $this->post('password'));
                if ($userInfo) {
                    if ($userInfo->mobile_number == $this->post('login_mobile_number')) {
                        $this->sendUserVerificationCode($userInfo->id, $this->post('login_mobile_number'));
                    } else if ($this->global_model->doesExist('user', array('id !=' => $userInfo->id, 'mobile_number' => $this->post('login_mobile_number'), 'verify_status' => 'YES'))) {

                        $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'mobile number already tagged and verified by other user -> data: ' . json_encode($this->post()));
                        $this->response($response);
                    } else {
                        $this->sendUserVerificationCode($userInfo->id, $this->post('login_mobile_number'));
                    }
                } else {
                    $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                    $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '<br>User Data -> ' . json_encode($this->post()));
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_01')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    public function verifyMobileNumber_post() {

        $webRequrest = $this->detectValidWebRequest();

        if ($webRequrest != TRUE) { // this is from mobile device
            $this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required|min_length[12]');
        }

        $this->form_validation->set_rules('api_key', 'api  key', 'trim|required');
        //$this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required|min_length[12]');
        $this->form_validation->set_rules('login_email', 'email', 'trim|valid_email');
//        $this->form_validation->set_rules('login_mobile_number', 'login mobile number', 'trim|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('password', 'password', 'required|min_length[6]|max_length[20]');
        $this->form_validation->set_rules('verification_code', 'verification code', 'trim|required|min_length[2]');
//        $this->form_validation->set_rules('mobile_number', 'verify mobile number', 'trim|required|min_length[11]|max_length[14]');
        $this->form_validation->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|required|min_length[2]|max_length[2]');

        if ($this->post('country_iso') == 'BD') {
            //$this->form_validation->set_rules('login_mobile_number', 'login mobile number', 'trim|required|min_length[11]|max_length[14]');
            $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[11]|max_length[14]');
        } else {
            $this->form_validation->set_rules('login_mobile_number', 'login mobile number', 'trim|required|min_length[8]|max_length[20]');
            $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required|min_length[8]|max_length[20]');
        }

        if ($this->form_validation->run() == TRUE) {

            $this->verifyAPIKey($this->api_key);

            if ($this->post('mobile_number')) {
                // verify mobile numbers with dialing code
                $this->verifyMobileNumberWithDialingCode($this->post('mobile_number'), $this->post('country_iso'));
            }

            if ($this->post('login_mobile_number')) {
                // verify mobile numbers with dialing code
                $this->verifyMobileNumberWithDialingCode($this->post('login_mobile_number'), $this->post('country_iso'));
            }


            $verificationCode = $this->post('verification_code');
            $mobileNumber = $this->post('mobile_number');
            $userInfo = FALSE;

            if ($this->post('login_email')) {
                $userInfo = $this->api_model->getUserInfoByEmailAndPassword($this->post('login_email'), $this->post('password'));
            } elseif ($this->post('login_mobile_number')) {
                $userInfo = $this->api_model->getUserInfoByMobileNumberAndPassword($this->post('login_mobile_number'), $this->post('password'));
            }

            if ($userInfo == FALSE) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '<br>User Data -> ' . json_encode($this->post()));
                $this->response($response, 200);
            }

            if ($userInfo->verify_code == $verificationCode) {

                $this->db->trans_start();

                if ($this->post('login_email')) {

                    // mobile number does not belong to others
                    if (!$this->global_model->doesExist('user', array('id !=' => $userInfo->id, 'mobile_number' => $mobileNumber, 'verify_status' => 'YES'))) {

                        if ($webRequrest != TRUE) { // this is from mobile device
                            //for fetch dialing code and country iso
                            $countryInfo = $this->getCountryInfo(strtoupper($this->post('country_iso')));
                            $countryISO = $countryInfo['code'];
                            $countryDialingCode = $countryInfo['d_code'];

                            $data = array(
                                'verify_status' => 'YES',
                                'mobile_number' => $mobileNumber,
                                'verify_code' => '',
                                'country_iso' => $countryISO ? $countryISO : '',
                                'country_dialing_code' => $countryDialingCode ? $countryDialingCode : '',
                                'device_uuid' => $this->post('device_uuid')
                            );
                        } else { // this is from web
                            $data = array(
                                'verify_status' => 'YES',
                                'mobile_number' => $mobileNumber,
                                'verify_code' => '',
                                'device_uuid' => 'web-' . $userInfo->id
                            );
                        }
                        $this->db->update('user', $data, array('id' => $userInfo->id));
                    } else {
                        // mobile number belong to someone else
                        $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                        $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'mobile number already tagged and verified by other user');
                        $this->response($response, 200);
                    }
                } else if ($this->post('login_mobile_number')) {

                    // mobile number does not belong to others
                    if (!$this->global_model->doesExist('user', array('id !=' => $userInfo->id, 'mobile_number' => $this->post('login_mobile_number'), 'verify_status' => 'YES'))) {

                        if ($webRequrest != TRUE) { // this is from mobile device
                            $data = array(
                                'verify_status' => 'YES',
                                'verify_code' => '',
                                'device_uuid' => $this->post('device_uuid')
                            );

                            $this->db->update('device_info', array('device_status' => 'active'), array('device_uuid' => $this->post('device_uuid'), 'user_id' => $userInfo->id));
                        } else {
                            $data = array(
                                'verify_status' => 'YES',
                                'verify_code' => '',
                                'device_uuid' => 'web-' . $userInfo->id
                            );
                        }
                        $this->db->update('user', $data, array('id' => $userInfo->id));
                    } else {
                        // mobile number belong to someone else
                        $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'mobile number already tagged and verified by other user');
                        $this->response($response, 200);
                    }
                }

                if ($webRequrest != TRUE) { // this is from mobile device
                    // associate new device_uuid with $userInfo->id in device_info table
                    $this->db->update('device_info', array('user_id' => $userInfo->id, 'device_status' => 'active'), array('device_uuid' => $this->post('device_uuid'), 'user_id' => $userInfo->id));

                    // set device_status = inactive where user_id = $userInfo->id AND device_uuid != $this->post('device_uuid') in device_info table
                    $this->db->update('device_info', array('device_status' => 'inactive'), array('user_id' => $userInfo->id, 'device_uuid !=' => $this->post('device_uuid')));
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                    $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'Data: ' . json_encode($this->post()) . '</br>Source -> ' . current_url());
                    $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
                    $this->response($response, 200);
                } else {
                    $purchaseInfo = $this->api_model->getAUserParchasedSummaryInfo($userInfo->id);
                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userInfo->id);
                    $message = $this->config->item('VARIFICATION_SUCCESS_01')['info'];
                    $code = $this->config->item('VARIFICATION_SUCCESS_01')['code'];
                    $winInfo = $this->userWinInfoByUserId($userInfo->id);


                    //generate access token
                    $userInfo->access_token = $this->generateAccessTokenById($userInfo->id);

                    $this->sendLoginSuccessResponse($userInfo, $purchaseInfo, $subscriptionInfo, $winInfo, $message, $code);
                }
            } else {
                // invalid verification code

                $response['response']['error'] = $this->config->item('INVALID_VARIFICATION_CODE_01')['error'];
                $response['response']['code'] = $this->config->item('INVALID_VARIFICATION_CODE_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->logUserActivity($userInfo->id, $response['response']['code'], $response['response']['error'], 'invalid verification code');
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());

              $this->response($response); */
        }
    }

    public function getSeriesList_post() { // app is accessing this api at the very begining, without login
        $this->form_validation->set_rules('api_key', 'api  key', 'trim|required');
        if ($this->form_validation->run() == TRUE) {
            $this->verifyAPIKey($this->api_key);
            $seriesList = $this->getSeriesList();
            if (!empty($seriesList)) {
                $hash = md5(serialize($seriesList));
                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                $response['response']['data'] = $seriesList;
                $response['response']['hash'] = $hash;
                $this->response($response, 200);
            } else {

                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate (authentication)
    public function savePrizebondNumbers_post() {

        $response = array();
        $this->form_validation->set_rules('bond_series', 'bond series', 'trim|required|min_length[2]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        $prizebond_numbers = $this->post('prizebond_numbers');

        if (!empty($prizebond_numbers) && is_array($prizebond_numbers)) {
            foreach ($prizebond_numbers as $id => $value) {
                $this->form_validation->set_rules('prizebond_numbers[' . $id . ']', 'bond number', 'trim|required|min_length[7]|max_length[7]');
            }
        } else {
            $this->form_validation->set_rules('prizebond_numbers', 'prizeond numbers', 'trim|required|min_length[7]|max_length[7]');
        }

        if ($this->form_validation->run() == TRUE) {

            $this->db->trans_start();

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $purchaseInfo = $this->api_model->getAUserParchasedSummaryInfo($userId);
                $userExistingBondNumber = $this->api_model->countUserBondNumber($userId);
                $userExistingBondNumber += count($this->post('prizebond_numbers'));

                if (!$purchaseInfo) {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                } else if ($purchaseInfo['total_user_purchased_prizebond'] < $userExistingBondNumber) {

                    $response['response']['error'] = $this->config->item('PRIZEBOND_STORE_CAPACITY_FAILED_01')['error'];
                    $response['response']['code'] = $this->config->item('PRIZEBOND_STORE_CAPACITY_FAILED_01')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                    $this->response($response, 200);
                    $this->response($output);
                }

                $prizebondArray = $this->post('prizebond_numbers');
                $convertedBondNumberArray = array();

                if (!empty($prizebondArray) && (is_array($prizebondArray))) {
                    foreach ($prizebondArray as $prizebond) {

                        if (mb_strlen($prizebond) != strlen($prizebond)) { // Bangla
                            $convertedBondNumber = $this->convertBanglaNumberIntoEnglishNumber($prizebond);
                            if (empty($convertedBondNumber)) {
                                $response['response']['error'] = $this->config->item('COMMON_ERROR_06')['error'];
                                $response['response']['code'] = $this->config->item('COMMON_ERROR_06')['code'];
                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Prizebond number language issue.');
                                $this->response($response, 200);
                            } else {
                                $convertedBondNumberArray[] = $convertedBondNumber;
                            }
                        } else {
                            $convertedBondNumberArray[] = $prizebond;
                        }
                    }
                } else {// if single bond
                    $prizebond = $this->post('prizebond_numbers');
                    if (mb_strlen($prizebond) != strlen($prizebond)) { // Bangla
                        $convertedBondNumber = $this->convertBanglaNumberIntoEnglishNumber($prizebond);
                        if (empty($convertedBondNumber)) {
                            $response['response']['error'] = $this->config->item('COMMON_ERROR_06')['error'];
                            $response['response']['code'] = $this->config->item('COMMON_ERROR_06')['code'];
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Prizebond number language issue.');
                            $this->response($response, 200);
                        } else {
                            $convertedBondNumberArray[] = $convertedBondNumber;
                        }
                    } else {
                        $convertedBondNumberArray[] = $prizebond;
                    }
                }

                if (!empty($convertedBondNumberArray) && (is_array($convertedBondNumberArray))) {
                    foreach ($convertedBondNumberArray as $prizebond) {
                        if (!is_numeric($prizebond) || (strlen($prizebond) != 7)) {
                            $response['response']['error'] = $this->config->item('COMMON_ERROR_06')['error'];
                            $response['response']['code'] = $this->config->item('COMMON_ERROR_06')['code'];
                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Prizebond number is incorrect.');
                            $this->response($response, 200);
                        } else {
                            // Continue
                        }
                    }
                }

                if (count(array_unique($convertedBondNumberArray)) < count($convertedBondNumberArray)) {
                    // Array has duplicates
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_07')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_07')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Duplicate prizebond number found.');
                    $this->response($response, 200);
                } else {
                    // Array does not have duplicates
                }

                if ($bondInfo = $this->savePrizeBondNumbers($userId, $this->post('device_uuid'), $convertedBondNumberArray, $this->post('bond_series'))) {

                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                    $winInfo = $this->userWinInfoByUserId($userId);
                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'Data: ' . json_encode($this->post()) . ' UserId : ' . $userId . '</br>Source -> ' . current_url());
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
                        $this->response($response, 200);
                    } else {
                        if (!empty($bondInfo['bond_info'])) {
                            $this->sendPushNotificationToWinner($bondInfo['bond_info'], $userId);

                            $response['response']['duplicate_numbers'] = !empty($bondInfo['duplicate_numbers']) ? $bondInfo['duplicate_numbers'] : '';
                            $response['response']['success'] = $this->config->item('COMMON_SUCCESS_02')['success'];

                            if (!empty($response['response']['duplicate_numbers'])) {
                                if (strpos($this->post('bond_series'), ',')) {
                                    $convertedBondNumber = $this->convertEnglishNumberIntoBanglaNumber($bondInfo['duplicate_numbers']);
                                    $response['response']['success'] = $response['response']['success'] . ' কিন্তু ' . $convertedBondNumber . ' নাম্বার আগে থেকে ছিল।';
                                } else {
                                    $convertedBondNumber = $this->convertEnglishNumberIntoBanglaNumber($bondInfo['duplicate_numbers']);
                                    $response['response']['success'] = $response['response']['success'] . ' কিন্তু ' . $this->post('bond_series') . ' সিরিজে  ' . $convertedBondNumber . ' নাম্বার আগে থেকে ছিল।';
                                }
                            }

                            $response['response']['code'] = $this->config->item('COMMON_SUCCESS_02')['code'];
                            $response['response']['data'] = $bondInfo['bond_info'];


                            $response['response']['subscription'] = $subscriptionInfo;
                            $response['response']['win_info'] = $winInfo;

                            if (!empty($response['response']['duplicate_numbers'])) {
                                $duplicateNumbers = json_encode($response['response']['duplicate_numbers']);
                            } else {
                                $duplicateNumbers = '';
                            }


                            $this->logActivity('info', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['success'] . ' Duplicate Bonds : ' . $duplicateNumbers . 'Data: ' . json_encode($this->post()) . ' UserId : ' . $userId . '</br>Source -> ' . current_url());
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'User saved prizebond successfully.');

                            $this->response($response, 200);
                        } else {

                            $response['response']['duplicate_numbers'] = !empty($bondInfo['duplicate_numbers']) ? $bondInfo['duplicate_numbers'] : '';
                            if (!empty($response['response']['duplicate_numbers'])) {
                                $duplicateNumbers = json_encode($response['response']['duplicate_numbers']);
                                $response['response']['error'] = $this->config->item('COMMON_ERROR_07')['error'];
                                $response['response']['code'] = $this->config->item('COMMON_ERROR_07')['code'];
                            } else {
                                $duplicateNumbers = '';
                                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                            }

                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' Duplicate Bonds : ' . $duplicateNumbers . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Failed to save bond.');

                            $this->response($response, 200);
                        }
                    }
                } else {

                    $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->response($response, 200);
                }
            } else {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
        }
    }

    //created by a teammate (authentication)
    public function deletePrizebondNumber_post() {
        $response = array();
        $this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required|min_length[12]');
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
        $bond_series = $this->post('bond_series');
        if (!empty($bond_series)) {
            foreach ($bond_series as $id => $value) {
                $this->form_validation->set_rules('bond_series[' . $id . ']', 'bond series', 'trim|required|min_length[2]');
            }
        }

        $prizebond_numbers = $this->post('prizebond_number');
        if (!empty($prizebond_numbers)) {
            foreach ($prizebond_numbers as $id => $value) {
                $this->form_validation->set_rules('prizebond_number[' . $id . ']', 'bond number', 'trim|required|min_length[7]|max_length[7]');
            }
        }

        if ($this->form_validation->run() == TRUE) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                if ($this->deletePrizeBondNumbers($this->post(), $userId)) {

                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                    $winInfo = $this->userWinInfoByUserId($userId);
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_03')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_03')['code'];
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_bond'] = $winInfo;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user deleted bond(s) successfully');
                    $this->response($response, 200);
                } else {

                    $response['response']['error'] = $this->config->item('COMMON_ERROR_03')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_03')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'user bond deletion failed');
                    $this->response($response, 200);
                }
            } else {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
              $this->response($response, 200); */
        }
    }

    public function getListOfDraw_post() { // app will access this api without login
        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        if ($this->form_validation->run() == TRUE) {
            $this->verifyAPIKey($this->api_key);
            $drawList = $this->getListOfDraw();
            if (!empty($drawList)) {
                $hash = md5(serialize($drawList));
                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                $response['response']['data'] = $drawList;
                $response['response']['hash'] = $hash;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
              $this->response($response, 200); */
        }
    }

    public function getResultOfADraw_post() { // app will access this api without login
        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('draw_id', 'draw id', 'trim|required|is_natural_no_zero');

        if ($this->form_validation->run() == TRUE) {
            $this->verifyAPIKey($this->api_key);
            $drawList = $this->getResultOfADraw($this->post('draw_id'));
            if (!empty($drawList)) {
                $hash = md5(serialize($drawList));
                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                $response['response']['data'] = $drawList;
                $response['response']['hash'] = $hash;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    public function saveDeviceInfo_post() {
        //mail('web-team@example.com', 'saveDeviceInfo_post', json_encode($this->post()));
        $this->form_validation
                ->set_rules('api_key', 'API Key', 'trim|required')
                ->set_rules('user_id', 'User Id', 'trim|alpha_dash')
                ->set_rules('device_uuid', 'Device uuid', 'trim|required|min_length[12]')
                ->set_rules('device_density', 'Device Density', 'trim|required|in_list[mdpi,hdpi,xhdpi,xxhdpi,xxxhdpi,ldpi]')
                ->set_rules('device_width', 'Device Width', 'trim|required|numeric')
                ->set_rules('device_height', 'Device Height', 'trim|required|numeric')
                ->set_rules('device_token', 'Device Token', 'trim|required|min_length[4]')
                ->set_rules('device_type', 'Device Type', 'trim|required|integer|min_length[1]|max_length[1]')
                ->set_rules('app_version_name', 'App Version Name', 'trim|required|min_length[1]')
                ->set_rules('app_version_code', 'App Version Code', 'trim|required|min_length[1]')
                ->set_rules('device_imei', 'Device imei', 'trim')
                ->set_rules('utm_campaign', 'Utm Campaign', 'trim')
                ->set_rules('utm_content', 'Utm Content', 'trim')
                ->set_rules('utm_source', 'Utm Source', 'trim')
                ->set_rules('utm_medium', 'Utm Medium', 'trim')
                ->set_rules('utm_term', 'Utm Term', 'trim')
                ->set_rules('device_manufacturer', 'device manufacturer', 'trim')
                ->set_rules('device_brand', 'device brand', 'trim')
                ->set_rules('device_product', 'device product', 'trim')
                ->set_rules('device_model', 'device model', 'trim')
                ->set_rules('device_os_version', 'device os version', 'trim')
                ->set_rules('device_sdk_version', 'device api version', 'trim');


        if ($this->form_validation->run() == TRUE) {

            $this->verifyAPIKey($this->api_key);
            $postData = array();

            /*
              foreach ($this->post() as $key => $value){
              $postData[$key] = $value;
              } */

            $postData['device_uuid'] = $this->post('device_uuid');
            $postData['device_density'] = $this->post('device_density');
            $postData['device_width'] = $this->post('device_width');
            $postData['device_height'] = $this->post('device_height');
            $postData['device_push_id'] = $this->post('device_token');
            $postData['device_type'] = $this->post('device_type');
            $postData['app_version_name'] = $this->post('app_version_name');
            $postData['app_version_code'] = $this->post('app_version_code');
            $postData['device_imei'] = $this->post('device_imei');
            $postData['utm_campaign'] = $this->post('utm_campaign');
            $postData['utm_source'] = $this->post('utm_source');
            $postData['utm_medium'] = $this->post('utm_medium');
            $postData['utm_term'] = $this->post('utm_term');
            $postData['utm_content'] = $this->post('utm_content');

            $postData['device_manufacturer'] = !empty($this->post('device_manufacturer')) ? $this->post('device_manufacturer') : NULL;
            $postData['device_brand'] = !empty($this->post('device_brand')) ? $this->post('device_brand') : NULL;
            $postData['device_product'] = !empty($this->post('device_product')) ? $this->post('device_product') : NULL;
            $postData['device_model'] = !empty($this->post('device_model')) ? $this->post('device_model') : NULL;
            $postData['device_os_version'] = !empty($this->post('device_os_version')) ? $this->post('device_os_version') : NULL;
            $postData['device_api_version'] = !empty($this->post('device_sdk_version')) ? $this->post('device_sdk_version') : NULL;

            $userId = !empty($this->post('user_id')) ? $this->post('user_id') : 0;
            $postData['user_id'] = $userId;


            if (is_string($userId) && (strlen($userId) > 1)) {

                if ($userId = $this->getInternalUserIdByExternalUserId($userId)) {
                    $postData['user_id'] = $userId;
                } else {
                    $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                    $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>data ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
                    $this->logUserActivity($postData['user_id'], $response['response']['code'], $response['response']['error'], 'UserId: ' . $this->post('user_id') . ' not found in db');
                    $this->response($response, 200);
                }
            } else {
                $postData['user_id'] = $userId;
            }


            if (($this->api_model->updateDeviceInfo($postData) == TRUE) && ($this->api_model->updateDeviceActivityLog($postData) == TRUE)) {

                if ($postData['user_id'] != 0) {
                    
                    //53778 = a teammate , 19 = support-team@example.com , 129 = admin@example.com
                    if($postData['user_id'] == '53778' || ($postData['user_id'] == '19') || ($postData['user_id'] == '129') ){
                        $response['response']['offer'] = "YES"; //YES / NO
                    }
                    else{
                        $response['response']['offer'] = "NO"; //YES / NO
                    }
                    
                    $response['response']['bond_purchase'] = "YES"; //YES / NO
                    $response['response']['bond_purchase_text'] = "প্রাইজ বন্ড কিনুন";
                }

                if ($postData['app_version_code'] < 70) {

                    $this->sendGCMNotification(array($postData['device_push_id']), "Dear valued user, Please download latest PrizeBond app from Google Play Store.");
                    if ($postData['user_id'] != 0) {
                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                        $this->logUserActivity($postData['user_id'], $response['response']['code'], $response['response']['success'], 'Push message has sent to update app');
                    }
                }

                $response['response']['success'] = $this->config->item('UPDATE_SUCCESS_01')['success'];
                $response['response']['code'] = $this->config->item('UPDATE_SUCCESS_01')['code'];
                if ($postData['user_id'] != 0) {
                    $this->logUserActivity($postData['user_id'], $response['response']['code'], $response['response']['success'], 'Device info updated for UserId: ' . $postData['user_id']);
                }

                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('UPDATE_FAILED_01')['error'];
                $response['response']['code'] = $this->config->item('UPDATE_FAILED_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));

                if ($postData['user_id'] != 0) {
                    $this->logUserActivity($postData['user_id'], $response['response']['code'], $response['response']['error'], 'Device info faield to update for UserId: ' . $postData['user_id']);
                }

                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
        }
    }

    public function getAppDetails_post() {

        $this->form_validation->set_rules('device_density', 'Device density', 'trim');
        if ($this->form_validation->run() == TRUE) {
            $device_density = $this->post('device_density');
            if ($existingAppInformation = $this->api_model->getAppDetails($device_density)) {
                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                $response['response']['data'] = $existingAppInformation;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate - web request
//    public function getAUserPurchasedStatus_post() {
//
//        $this->detectValidWebRequest();
//
//        $this->form_validation
//                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
//
//        if ($this->form_validation->run() == TRUE) {
//            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
//
//                $userPurchaseStatus = $this->api_model->getAUserParchasedSummaryInfo($userId);
//                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
//                $winInfo = $this->userWinInfoByUserId($userId);
//                if ($userPurchaseStatus) {
//                    $hash = md5(serialize($userPurchaseStatus));
//                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//                    $response['response']['purchase_info'] = $userPurchaseStatus;
//                    $response['response']['win_bond'] = $winInfo;
//                    $response['response']['subscription'] = $subscriptionInfo;
//                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'data found');
//                    $this->response($response, 200);
//                } else {
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'data not found');
//                    $this->response($response, 200);
//                }
//            } else {
//                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
//                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
//                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                $this->response($response, 200);
//            }
//        } else {
//            $this->sendFormVelidationErrorResponse();
//            /*
//              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//              $response['response']['error'] = $error;
//              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//              $this->response($response, 200); */
//        }
//    }
    //created by a teammate - web request
//    public function getOrderInfo_post() {
//
//        $this->detectValidWebRequest();
//
//        $this->form_validation->set_rules('order_id', 'Order id', 'trim|required|min_length[13]|alpha_numeric');
//
//        if ($this->form_validation->run() == TRUE) {
//
//            $where = array(
//                'reference_id' => $this->post('order_id')
//            );
//
//            $orderInfo = $this->api_model->get_data('subscription_order_list', $where);
//
//            if (is_array($orderInfo)) {
//                $userInfo = $this->api_model->get_data('user', array('id' => $orderInfo['user_id']));
//
//                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//                $response['response']['order_info'] = $orderInfo;
//                $response['response']['user_info'] = array(
//                    "user_id" => $userInfo["user_id"],
//                    "name" => $userInfo["name"],
//                    "email" => $userInfo["email"],
//                    "mobile_number" => $userInfo["mobile_number"]
//                );
//                $this->logUserActivity($userInfo["id"], $response['response']['code'], $response['response']['success'], 'data not found');
//                $this->response($response, 200);
//            } else {
//                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                $this->logUserActivity($orderInfo['user_id'], $response['response']['code'], $response['response']['error'], 'data not found');
//                $this->response($response, 200);
//            }
//        } else {
//            $this->sendFormVelidationErrorResponse();
//            /*
//              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//              $response['response']['error'] = $error;
//              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//              $this->response($response, 200); */
//        }
//    }
    //created by a teammate : - web request
//    public function createAnOrder_post() {
//
//        $this->detectValidWebRequest();
//
//        $this->form_validation
//                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
//                ->set_rules('product_id', 'Product id', 'trim|required|is_natural_no_zero')
//                ->set_rules('quantity', 'quantity', 'trim|required|greater_than_equal_to[1]')
//                ->set_rules('shipping_cost', 'Shipping cost', 'trim|required')
//                ->set_rules('discount', 'Discount', 'trim|required');
//
//        if ($this->form_validation->run() == TRUE) {
//
//            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
//
//                $productId = $this->post('product_id');
//                $productInfo = $this->subscription_model->getProductInfoByProductId($productId);
//                $orderId = strtoupper(substr(date('D'), 0, 2)) . uniqid();
//                $quantity = $this->post('quantity');
//                $shippingCost = $this->post('shipping_cost');
//                $discount = $this->post('discount');
//                $gatewayInfo = $this->getwayInfo();
//                $totalReceivableAmount = (($quantity * $productInfo['price']) + $shippingCost) - $discount;
//
//                $orderInfo = array(
//                    'user_id' => $userId,
//                    'product_id' => $productId,
//                    'reference_id' => $orderId,
//                    'product_description' => $productInfo['product_name'],
//                    'currency' => 'BDT',
//                    'quantity' => $quantity,
//                    'rate' => $productInfo['price'],
//                    'payment_method_type_id' => 0,
//                    'shipping_cost' => $shippingCost,
//                    'discount' => $discount,
//                    'total_receivable_amount' => $totalReceivableAmount,
//                    'order_created_date_time' => date('Y-m-d H:i:s')
//                );
//                if ($gatewayInfo['sandbox_mode'] == 'on') {
//                    $orderInfo['sandbox'] = 1;
//                } else {
//                    $orderInfo['sandbox'] = 0;
//                }
//
//                if ($this->api_model->insert('subscription_order_list', $orderInfo)) {
//                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
//                    $winInfo = $this->userWinInfoByUserId($userId);
//                    $orderInfo['user_id'] = $this->getExternalUserIdByInternalUserId($orderInfo['user_id']);
//
//                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//                    $response['response']['order_info'] = $orderInfo;
//                    $response['response']['subscription'] = $subscriptionInfo;
//                    $response['response']['win_info'] = $winInfo;
//                    $this->logActivity('info', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['success'] . '</br>Source -> ' . current_url() . '</br>Order Data ->' . json_encode($orderInfo) . '</br>Post Data ->' . json_encode($this->post()));
//                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'order created');
//                    $this->response($response, 200);
//                } else {
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'order could not created');
//                    $this->response($response, 200);
//                }
//            } else {
//                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
//                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
//                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                $this->response($response, 200);
//            }
//        } else {
//            $this->sendFormVelidationErrorResponse();
//            /*
//              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//              $response['response']['error'] = $error;
//              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//              $this->response($response, 200); */
//        }
//    }
    //created by a teammate : will use it from web
//    public function activateProductSubscription_post() {
//
//        $this->detectValidWebRequest();
//
//        if ($this->post()) {
//
//            // FOR DEBUG
//            if ($this->post('transaction_status') == 'success') {
//                $this->sendEmailToDeveloper('1 new sale ' . date("Y-m-d"), json_encode($this->post()));
//            } else {
//                $this->sendEmailToDeveloper('1 new hit ' . date("Y-m-d"), json_encode($this->post()));
//            }
//
//
//            $response = array();
//            $error = "";
//            $paymentMethod = $this->api_model->getAllPaymentMethod();
//            $paymentMethod = implode(',', $paymentMethod);
//            $this->form_validation
//                    ->set_rules('merchant_reference_id', 'merchant reference id', 'trim|required|min_length[15]')
//                    ->set_rules('user_id', 'user id', 'trim|required|min_length[10]|alpha_dash')
//                    ->set_rules('product_id', 'product id', 'trim|required|is_natural_no_zero')
//                    ->set_rules('quantity', 'quantity', 'trim|required|greater_than_equal_to[1]')
//                    ->set_rules('shipping_cost', 'Shipping cost', 'trim|required|greater_than_equal_to[0]')
//                    ->set_rules('discount', 'Discount', 'trim|required|greater_than_equal_to[0]')
//                    ->set_rules('total_received_amount', 'total received amount', 'trim|required|greater_than[0]')
//                    ->set_rules('shipping_address', 'Shipping Address', 'trim')
//                    ->set_rules('transaction_id', 'transaction id', 'trim|required|min_length[5]')
//                    ->set_rules('payment_method', 'payment method', 'trim|required|in_list[' . $paymentMethod . ']')
//                    ->set_rules('transaction_status', 'transaction status', 'trim|required|in_list[success,failed,cancel]')
//                    ->set_rules('payment_date', 'payment date format ex : date("Y-m-d H:i:s")', 'trim')
//                    ->set_rules('note', 'Note', 'trim|required|min_length[5]');
//
//
//            if ($this->form_validation->run() == TRUE) {
//
//                $merchantReferenceId = $this->post('merchant_reference_id');
//                $userId = $this->getInternalUserIdByExternalUserId($this->post('user_id'));
//                $productId = $this->post('product_id');
//                $totalReceivedAmount = $this->post('total_received_amount');
//                $transactionId = $this->post('transaction_id');
//                $paymentMethod = $this->post('payment_method');
//                $paymentDateFromGateway = $this->post('payment_date');
//                $transactionStatus = $this->post('transaction_status');
//                $note = $this->post('note');
//
//                $this->db->trans_start();
//
//
//                $orderInfo = $this->subscription_model->getOrderInfo($merchantReferenceId);
//                if (empty($orderInfo)) {
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'data not found');
//                    $this->response($response, 200);
//                } elseif ($userId == FALSE) {
//                    $error = "user id not found";
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                    $this->response($response, 200);
//                } elseif (($orderInfo->order_status == 'pending') && ($orderInfo->payment_status == 'pending')) {
//
//                    if ($orderInfo->user_id != $userId) {
//                        $error = "user id not matched";
//                        $response['response']['error'] = $error;
//                        $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'user_id of orderInfo not matched with current user id');
//                        $this->response($response, 200);
//                    } elseif ($orderInfo->product_id != $productId) {
//
//                        $error = "product id not matched";
//                        $response['response']['error'] = $error;
//                        $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
//                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'product id not matched');
//                        $this->response($response, 200);
//                    } elseif (($paymentMethod != 'COD') && $orderInfo->total_receivable_amount != $totalReceivedAmount) {
//
//                        $error = "total amount not matched";
//                        $response['response']['error'] = $error;
//                        $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
//                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'total amount not matched');
//                        $this->response($response, 200);
//                    }
//
//                    if ($error == "") {
//
//                        $paymentMethodInfo = $this->subscription_model->getPaymentMethodInfo($paymentMethod);
//
//                        if ($paymentMethod == 'COD') {
//
//                            $totalReceivableAmount = (($this->post('quantity') * $orderInfo->rate) + $this->post('shipping_cost')) - $this->post('discount');
//
//                            if ($this->post('total_received_amount') == $totalReceivableAmount) {
//                                $data = array(
//                                    'quantity' => $this->post('quantity'),
//                                    'shipping_cost' => $this->post('shipping_cost'),
//                                    'discount' => $this->post('discount'),
//                                    'shipping_address' => $this->post('shipping_address'),
//                                    'total_receivable_amount' => $this->post('total_received_amount')
//                                );
//                                $data['payment_method_type_id'] = $paymentMethodInfo->id;
//
//                                if ($transactionStatus == 'success') {
//                                    $data['order_status'] = 'waiting_for_confirmed';
//                                } else if ($transactionStatus == 'failed') {
//                                    $data['order_status'] = 'failed';
//                                } else if ($transactionStatus == 'cancel') {
//                                    $data['order_status'] = 'cancel';
//                                }
//
//
//
//                                $this->global_model->update('subscription_order_list', $data, array('reference_id' => $orderInfo->reference_id));
//
//                                $this->db->trans_complete();
//
//                                if ($this->db->trans_status() == TRUE) {
//
//                                    if ($transactionStatus == 'success') {
//                                        $this->sendMesageToSalesTeam($this->post(), $orderInfo);
//                                    }
//
//
//                                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//                                    $this->logActivity('info', ": COD : subscription order completed</br>Source -> " . current_url());
//                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'COD : subscription order completed');
//                                    $this->response($response, 200);
//                                } else {
//
//                                    $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
//                                    $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
//                                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
//                                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
//                                    $this->response($response, 200);
//                                }
//                            } else {
//                                $error = "COD total amount not matched";
//                                $response['response']['error'] = $error;
//                                $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
//                                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'COD amount not matched');
//                                $this->response($response, 200);
//                            }
//                        } else if ($paymentMethod != 'COD') {
//
//                            if (!empty($paymentDateFromGateway)) {
//                                $paymentDate = date('Y-m-d H:i:s', strtotime($paymentDateFromGateway));
//                            } else {
//                                $paymentDate = date("Y-m-d H:i:s");
//                            }
//
//                            $data['payment_method_type_id'] = $paymentMethodInfo->id;
//                            $data['gateway_transaction_id'] = $transactionId;
//
//
//                            if ($transactionStatus == 'success') {
//                                $data['payment_status'] = "success";
//                                $data['payment_date_time'] = $paymentDate;
//                            } else if ($transactionStatus == 'failed') {
//                                $data['payment_status'] = "failure";
//                                $data['order_status'] = 'failed';
//                            } else if ($transactionStatus == 'cancel') {
//                                $data['payment_status'] = "failure";
//                                $data['order_status'] = 'cancel';
//                            }
//
//
//                            if ($this->global_model->update('subscription_order_list', $data, array('reference_id' => $merchantReferenceId))) {
//
//                                if ($transactionStatus == 'success') {
//
//                                    // for payment history table
//                                    $paymentHistory = array(
//                                        'order_id' => $orderInfo->reference_id,
//                                        'payment_method_type_id' => $paymentMethodInfo->id,
//                                        'extra_info' => $note,
//                                        'created_date_time' => $paymentDate
//                                    );
//                                    $this->global_model->insert('subscription_payment_history', $paymentHistory);
//
//                                    if ($this->deliverServiceToCustomer($productId, $userId, $paymentMethod, $merchantReferenceId) == TRUE) {
//
//                                        $this->db->trans_complete();
//
//                                        if ($this->db->trans_status() == TRUE) {
//
//                                            $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                                            $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//                                            $this->logActivity('info', ": Subscription purchased successfully</br>Source -> " . current_url());
//                                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'Subscription purchased successfully');
//                                            $this->response($response, 200);
//                                        } else {
//
//                                            $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
//                                            $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
//                                            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data: " . json_encode($this->post()) . '</br>Source -> ' . current_url());
//                                            $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed');
//                                            $this->response($response, 200);
//                                        }
//                                    } else {
//
//                                        $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
//                                        $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
//
//                                        //Service delivery failed;
//                                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": Service delivery failed" . "</br> data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
//                                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'Service delivery failed');
//                                        $this->response($response, 200);
//                                    }
//                                } else if ($transactionStatus == 'failed') {
//
//                                    $this->db->trans_complete();
//
//                                    if ($this->db->trans_status() == TRUE) {
//
//                                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
//                                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
//                                        $this->logActivity('info', ": transaction status : failed, No service delivered </br>Source -> " . current_url());
//                                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'transaction status : failed, received from gateway. No service delivered');
//                                        $this->response($response, 200);
//                                    } else {
//
//                                        $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'] . ' Transaction rollback';
//                                        $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
//                                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": DB Transaction failed" . " </br>data: " . json_encode($this->post()) . '</br>Source -> ' . current_url());
//                                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'DB transaction failed. Service delivery failed');
//                                        $this->response($response, 200);
//                                    }
//                                }
//                            }
//                        }
//                    } else {
//
//                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ": message:" . $error . "</br> data:" . json_encode($this->post()) . '</br>Source -> ' . current_url());
//                    }
//                } else {
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_04')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_04')['code'];
//                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . " </br>data:" . json_encode($orderInfo) . '</br>Source -> ' . current_url() . '</br> User Data ->' . json_encode($this->post()));
//                    $this->response($response, 200);
//                }
//            } else {
//                $this->sendFormVelidationErrorResponse();
//                /*
//                  $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//                  $response['response']['error'] = $error;
//                  $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//                  $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//                  $this->response($response, 200); */
//            }
//        }
//    }
    //created by a teammate
    public function addNewSupportMessage_post() {

        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('subject', 'Subject', 'trim|required|min_length[5]|max_length[256]')
                ->set_rules('message', 'Message', 'trim|required|min_length[5]');

        if ($this->form_validation->run() == TRUE) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                $messageData = array(
                    'message_id' => uniqid(),
                    'sender_user_id' => $userId,
                    'subject' => $this->post('subject'),
                    'last_replied_by' => 'user',
                    'last_replied_date_time' => date('Y-m-d H:i:s')
                );

                $conversationData = array(
                    'message_id' => $messageData['message_id'],
                    'message' => $this->post('message'),
                    'sent_by' => 'user',
                    'receiver_id' => $userId
                );

                if ($this->existingCheckForUsersMessage($userId, $this->post('subject'), $this->post('message')) == FALSE) {

                    if ($this->api_model->insert('user_support_conversations', $conversationData) && $this->api_model->insert('user_support_messages', $messageData)) {

                        $message = "New support request from customer.";
                        $this->sendPushNotificationToDevelopers($message);

                        $winInfo = $this->userWinInfoByUserId($userId);
                        $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                        $response['response']['subscription'] = $subscriptionInfo;
                        $response['response']['win_info'] = $winInfo;
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user send a message to support');
                        $this->response($response, 200);
                    } else {
                        $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->response($response, 200);
                    }
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], ' user tried to send same message more than one time. ');
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));

                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate
    public function replySupportMessage_post() {
        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('message_id', 'message_id', 'trim|required|alpha_numeric')
                ->set_rules('message', 'Message', 'trim|required|min_length[5]');

        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                if ($this->api_model->doesExist('user_support_conversations', array('message_id' => $this->post('message_id')))) {
                    $replyConversationData = array(
                        'message_id' => $this->post('message_id'),
                        'message' => $this->post('message'),
                        'sent_by' => 'user',
                        'receiver_id' => $userId
                    );
                    $replyMessageData = array(
                        'last_replied_by' => 'user',
                        'last_replied_date_time' => date('Y-m-d H:i:s'),
                        'status' => 'unread'
                    );

                    if ($this->api_model->insert('user_support_conversations', $replyConversationData)) {

                        $this->api_model->update('user_support_messages', $replyMessageData, array('message_id' => $this->post('message_id')));

                        $message = "1 message replied by customer.";
                        $this->sendPushNotificationToDevelopers($message);


                        $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                        $winInfo = $this->userWinInfoByUserId($userId);
                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                        $response['response']['subscription'] = $subscriptionInfo;
                        $response['response']['win_bond'] = $winInfo;
                        $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user replyed of a messsage');
                        $this->response($response, 200);
                    } else {

                        $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                        $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->response($response, 200);
                    }
                } else {

                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate
    public function getSupportMessageList_post() {

        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('offset', 'offset', 'trim|required|is_natural');

        if ($this->form_validation->run() == TRUE) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $offset = $this->post('offset');
                $limit = 20;
                $messageArray = $this->api_model->getAllSupportMessages($limit, $offset, $userId);
                if (!empty($messageArray) && is_array($messageArray)) {
                    $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                    $winInfo = $this->userWinInfoByUserId($userId);
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['data'] = $messageArray;
                    $response['response']['total'] = count($messageArray);
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_bond'] = $winInfo;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user viewing support message list');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate
    public function getSupportConversationList_post() {

        $this->form_validation
                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
                ->set_rules('offset', 'offset', 'trim|is_natural')
                ->set_rules('message_id', 'Message id', 'trim|required|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $offset = $this->post('offset') ? $this->post('offset') : 0;
                $limit = 20;
                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                $winInfo = $this->userWinInfoByUserId($userId);

                $supportConversationListArray = $this->api_model->getAllSupportConversations($limit, $offset, $this->post('message_id'));

                if (count($supportConversationListArray)) {
                    //update query will run for read message
                    $this->api_model->update('user_support_messages', array('status' => 'read'), array('message_id' => $this->post('message_id')));
                }

                if (!empty($supportConversationListArray) && is_array($supportConversationListArray)) {
                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
                    $response['response']['data'] = $supportConversationListArray;
                    $response['response']['total'] = count($supportConversationListArray);
                    $response['response']['subscription'] = $subscriptionInfo;
                    $response['response']['win_info'] = $winInfo;
                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user viewing conversation list');
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate : will use by web
//    public function getUserSubscriptionList_post() {
//        $this->form_validation
//                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric')
//                ->set_rules('offset', 'offset', 'trim|is_natural');
//        if ($this->form_validation->run() == TRUE) {
//            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
//
//                $offset = $this->post('offset');
//                $offset = !empty($offset) ? $offset : 0;
//
//                $subscription = $this->subscription_model->subscriptionListByUserId($userId);
//
//
//                if (is_array($subscription)) {
//                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
//                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
//                    $response['response']['data'] = $subscription;
//                    $this->logUserActivity($userId, $response['response']['code'], $response['response']['success'], 'user viewing subscription list');
//                    $this->response($response, 200);
//                } else {
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                    $this->response($response, 200);
//                }
//            } else {
//                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
//                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
//                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                $this->response($response, 200);
//            }
//        } else {
//            $this->sendFormVelidationErrorResponse();
//            /*
//              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//              $response['response']['error'] = $error;
//              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//              $this->response($response, 200); */
//        }
//    }
    //created by a teammate : will use by web
    public function forgotPasswordSendVerificationCode_post() {
        $this->form_validation
                ->set_rules('api_key', 'Api Key', 'trim|required')
                ->set_rules('email', 'email', 'trim|valid_email')
                ->set_rules('mobile_number', 'mobile number', 'trim|min_length[11]|max_length[14]');
        //->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|min_length[2]|max_length[2]');

        /*  if ($this->post('country_iso') == 'BD') {
          $this->form_validation->set_rules('mobile_number', ' mobile number', 'trim|required|min_length[11]|max_length[14]');
          } else {
          $this->form_validation->set_rules('mobile_number', ' mobile number', 'trim|required|min_length[8]|max_length[20]');
          } */

        if ($this->form_validation->run() == TRUE) {

            $this->verifyAPIKey($this->api_key);
            $email = $this->post('email');
            $mobileNumber = $this->post('mobile_number');

            if (empty($mobileNumber) && (empty($email))) {

                // show error
                $response['response']['error'] = $this->config->item('EMAIL_OR_MOBILE_REQUIRED_01')['error'];
                $response['response']['code'] = $this->config->item('EMAIL_OR_MOBILE_REQUIRED_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                //$this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'user did not enter mobile number or email address');
                $this->response($response, 200);
            }

            /* if ($mobileNumber) {
              // verify mobile numbers with dialing code
              $this->verifyMobileNumberWithDialingCode($mobileNumber, $this->post('country_iso'));
              } */


            $userInfo = FALSE;
            if ($email) {
                $userInfo = $this->global_model->get_data('user', array('email' => $email, 'status' => '1', 'verify_status' => 'YES'));
            } elseif ($mobileNumber) {
                $userInfo = $this->global_model->get_data('user', array('mobile_number' => $mobileNumber, 'status' => '1', 'verify_status' => 'YES'));
            } else {
                $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                //$this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'user info not found');
                $this->response($response, 200);
            }


            if ($userInfo != FALSE && is_array($userInfo)) {
                if ($userInfo['verify_status'] == 'YES' && (!empty($email))) {

                    /*
                      $senderEmail = 'no-reply@prizebond-checker.com';
                      $senderName = 'PrizeBond';
                      $receiverEmail = $userInfo['email'];
                      $receiverName = $userInfo['name'];
                      $subject = 'Your password recovery code';

                      $verificationCode = $this->generateVerificationCode();
                      $message = "Dear " . $receiverName . ",<br/>";
                      $message .= "<strong>Your prizebond-checker.com Account Verification Code is:</strong> " . $verificationCode . "<br/></br> Prizebond Checker Team";

                      if ($this->sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message)) {
                      $this->api_model->update('user', array('verify_code' => $verificationCode), array('id' => $userInfo['id']));
                      } */

                    if ($this->sendVerificationCodeByMobileNumber($userInfo['mobile_number'], $userInfo['id'])) {

                        $response['response']['success'] = $this->config->item('VARIFICATION_SUCCESS_02')['success'];
                        $response['response']['code'] = $this->config->item('VARIFICATION_SUCCESS_02')['code'];
                        $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['success'], 'successfully send verification code to mobile number');
                        $this->response($response, 200);
                    } else {

                        $response['response']['error'] = $this->config->item('USER_VERIFICATION_03')['error'];
                        $response['response']['code'] = $this->config->item('USER_VERIFICATION_03')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'Data ->' . json_encode($userInfo) . '</br>Source -> ' . current_url() . '</br> User Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['error'], 'unable to send verification code to mobile number');
                        $this->response($response, 200);
                    }
                } else if ($userInfo['verify_status'] == 'YES' && (!empty($mobileNumber))) {

                    if ($this->sendVerificationCodeByMobileNumber($userInfo['mobile_number'], $userInfo['id'])) {

                        $response['response']['success'] = $this->config->item('VARIFICATION_SUCCESS_02')['success'];
                        $response['response']['code'] = $this->config->item('VARIFICATION_SUCCESS_02')['code'];
                        $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['success'], 'successfully send verification code to mobile number');
                        $this->response($response, 200);
                    } else {

                        $response['response']['error'] = $this->config->item('USER_VERIFICATION_03')['error'];
                        $response['response']['code'] = $this->config->item('USER_VERIFICATION_03')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'Data ->' . json_encode($userInfo) . '</br>Source -> ' . current_url() . '</br> User Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['error'], 'unable to send verification code to mobile number');
                        $this->response($response, 200);
                    }
                } else {
                    // show error mesage wih post data
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br> data -> ' . json_encode($this->post()) . '</br>Source -> ' . current_url());
                    $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['error'], 'user not verified');
                    $this->response($response, 200);
                }
            } else {
                //error unauthorize access
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else { // user validation failed
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());
              $this->response($response, 200); */
        }
    }

    //created by a teammate : will use by web
    public function forgotPasswordVerifyCodeAndResetPassword_post() {
        $this->form_validation
                ->set_rules('api_key', 'Api Key', 'trim|required')
                ->set_rules('email', 'email', 'trim|valid_email')
                ->set_rules('mobile_number', 'mobile number', 'trim|min_length[11]|max_length[14]')
//                ->set_rules('country_iso', 'ISO 3166-1 alpha-2 country code', 'trim|required|min_length[2]|max_length[2]')
                ->set_rules('password', 'password', 'required|min_length[6]|max_length[20]')
                ->set_rules('retype_password', 'retype password', 'required|min_length[6]|max_length[20]')
                ->set_rules('verification_code', 'Verification', 'trim|required');

        /*  if ($this->post('country_iso') == 'BD') {
          $this->form_validation->set_rules('mobile_number', ' mobile number', 'trim|required|min_length[11]|max_length[14]');
          } else {
          $this->form_validation->set_rules('mobile_number', ' mobile number', 'trim|required|min_length[8]|max_length[20]');
          } */

        if ($this->form_validation->run() == TRUE) {
            $this->verifyAPIKey($this->api_key);

            /*  if ($this->post('mobile_number')) {
              // verify mobile numbers with dialing code
              $this->verifyMobileNumberWithDialingCode($this->post('mobile_number'), $this->post('country_iso'));
              } */

            $email = $this->post('email');
            $mobile_number = $this->post('mobile_number');
            $userInfo = FALSE;
            if ($email) {
                $userInfo = $this->global_model->get_data('user', array('email' => $email));
            } elseif ($mobile_number) {
                $userInfo = $this->global_model->get_data('user', array('mobile_number' => $mobile_number));
            } else {
                $response['response']['error'] = $this->config->item('EMAIL_OR_MOBILE_REQUIRED_01')['error'];
                $response['response']['code'] = $this->config->item('EMAIL_OR_MOBILE_REQUIRED_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'user did not enter email  or mobile number');
                $this->response($response, 200);
            }

            if ($userInfo != FALSE && is_array($userInfo)) {

                if ($userInfo['verify_code'] == $this->post('verification_code')) {
                    $data = array(
                        'password' => md5($this->post('password'))
                    );
                    //update user table where id = $userInfo['id]
                    if ($this->api_model->update('user', $data, array('id' => $userInfo['id']))) {
                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_04')['success'];
                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_04')['code'];
                        $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['success'], 'user has changed password successfully');
                        $this->response($response, 200);
                    } else {
                        $response['response']['error'] = $this->config->item('DB_ERROR_01')['error'];
                        $response['response']['code'] = $this->config->item('DB_ERROR_01')['code'];
                        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                        $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['error'], 'DB error occoured during password change');
                        $this->response($response, 200);
                    }
                } else {
                    $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
                    $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
                    $this->logUserActivity($userInfo['id'], $response['response']['code'], $response['response']['error'], 'verification code not matched');
                    $this->response($response, 200);
                }
            } else {
                // error unautrize access
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else { // user validation failed
            $this->sendFormVelidationErrorResponse();
            /*
              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
              $response['response']['error'] = $error;
              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' userData ->' . json_encode($this->post()) . '</br>Source -> ' . current_url());

              $this->response($response, 200); */
        }
    }

    //created by a teammate : will use by web
//    public function getSubscriptionItems_post() {
//        $this->form_validation->set_rules('api_key', 'api  key', 'trim|required');
//        if ($this->form_validation->run() == TRUE) {
//            $this->verifyAPIKey($this->api_key);
//
//            $items = $this->subscription_model->getSubscriptionList();
//            if (!empty($items)) {
//                $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
//                $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
//                $response['response']['data'] = $items;
//                $this->response($response, 200);
//            } else {
//                $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                $this->response($response, 200);
//            }
//        } else {
//            $this->sendFormVelidationErrorResponse();
//            /*
//              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//              $response['response']['error'] = $error;
//              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//              $this->response($response, 200); */
//        }
//    }
    //created by a teammate : will use by web
//    public function getUserCouponFailedCount_post() {
//        $this->form_validation
//                ->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
//
//        if ($this->form_validation->run() == TRUE) {
//
//            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
//
//                $info = $this->api_model->getUserCouponFailedCount($userId);
//
//                if (!empty($info)) {
//                    $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
//                    $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
//                    $response['response']['data'] = array('counter' => $info['counter'], 'update_date' => $info['update_date_time']);
//                    $this->response($response, 200);
//                } else {
//                    $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                    $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                    $this->response($response, 200);
//                }
//            } else {
//                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
//                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
//                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                $this->response($response, 200);
//            }
//        } else {
//            $this->sendFormVelidationErrorResponse();
//            /*
//              $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//              $response['response']['error'] = $error;
//              $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//              $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//              $this->response($response, 200); */
//        }
//    }
//    public function getOrderHistory_post() {
//
//        if ($this->post()) {
//
//            $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');
//            $this->form_validation->set_rules('offset', 'offset', 'trim|required|is_natural');
//
//            if ($this->form_validation->run() == TRUE) {
//
//                if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
//
//                    $offset = $this->post('offset');
//                    $limit = 50;
//                    $orderHistoryList = $this->admin_model->getOrderHistoryByUserId($limit, $offset, $userId);
//
//                    if (!empty($orderHistoryList)) {
//                        $response['response']['success'] = $this->config->item('COMMON_SUCCESS_01')['success'];
//                        $response['response']['code'] = $this->config->item('COMMON_SUCCESS_01')['code'];
//                        $response['response']['data'] = $orderHistoryList;
//                        $this->response($response, 200);
//                    } else {
//                        $response['response']['error'] = $this->config->item('COMMON_ERROR_02')['error'];
//                        $response['response']['code'] = $this->config->item('COMMON_ERROR_02')['code'];
//                        $this->response($response, 200);
//                    }
//                } else {
//                    $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
//                    $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
//                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
//                    $this->response($response, 200);
//                }
//            } else {
//
//                $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
//                $response['response']['error'] = $error;
//                $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
//                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
//                $this->response($response, 200);
//            }
//        }
//    }

    public function sendTestSMS_get() {
        $mobile = $this->get("mobile");

        if ($mobile) {
            $senderText = $this->config->item('sms_sender_text');
            $smsText = "This is a test message, Date : " . date("Y-m-d h:i:s A");
            $gsm = $mobile;

            if ($this->sendSMSToCustomer($gsm, $smsText) == TRUE) {
                //$this->api_model->updateMobileVerificationCode($verifyCode, $userId);
                echo "success";
            } else {
                return FALSE;
            }

            /*
              $SMSResponse = $this->bitbirds->sendSMS($senderText, $smsText, $gsm);
              if ($SMSResponse > 0) {
              $this->api_model->updateMobileVerificationCode($verifyCode, $userId);
              echo "success";
              } else {
              echo "failed response code : " . $SMSResponse;
              } */
        }
    }

    public function getUserInfoByAccessToken_post() {
        $this->detectValidWebRequest();

        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {

            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {

                $userInfo = $this->global_model->getUserInfoByUserId($userId);

                $purchaseInfo = $this->api_model->getAUserParchasedSummaryInfo($userId);
                $subscriptionInfo = $this->api_model->getAUserSubscriptionInfo($userId);
                $winInfo = $this->userWinInfoByUserId($userId);

                $message = $this->config->item('COMMON_SUCCESS_01')['success'];
                $code = $this->config->item('COMMON_SUCCESS_01')['code'];

                // pramas : userId, code , message, internal message
                $this->logUserActivity($userId, $code, $message, 'Login success from web');
                $this->sendLoginSuccessResponse($userInfo, $purchaseInfo, $subscriptionInfo, $winInfo, $message, $code);
            } else {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br>User Input Data ->' . json_encode($this->post()));
                $this->response($response, 200);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
        }
    }

    public function webPing_post() {
        $data = array();
        $this->form_validation->set_rules('access_token', 'Access token', 'trim|required|min_length[13]|alpha_numeric');

        if ($this->form_validation->run() == TRUE) {
            if ($userId = $this->userAuthCheckByAccessToken($this->post('access_token'))) {
                $response['response']['type'] = 'success';
                $this->response($response);
            }
        } else {
            $this->sendFormVelidationErrorResponse();
        }
    }

    private function existingCheckForUsersMessage($userId, $subject, $message) {

        if ($this->api_model->doesExist('user_support_messages', array('sender_user_id' => $userId, 'subject' => $subject))) {
            $messageId = $this->api_model->getMessageId(array('sender_user_id' => $userId, 'subject' => $subject));

            if ($this->api_model->doesExist('user_support_conversations', array('message_id' => $messageId, 'message' => $message))) {
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

    private function sendSMSToCustomer($mobileNumber, $message) {


        $params = array(
            'recipient_mobile_number' => $mobileNumber,
            'message' => $message,
            'sender_mask' => NULL
        );
        $this->load->library('smsrouter', $params);
        $response = $this->smsrouter->send();


        if (!empty($response) && is_array($response)) {

            if (!empty($response['messageid'])) {
                $messageID = $response['messageid'] ? $response['messageid'] : 0;
            } else {
                $messageID = 0;
            }



            $data = array(
                'recipient_mobile_number' => $mobileNumber,
                'message' => $message,
                'sender_mask' => '',
                'response_type' => $response['type'],
                'response_code' => $response['code'],
                'response_messageid' => $messageID,
                'response_message' => $response['message'],
                'created_date_time' => date('Y-m-d H:i:s')
            );

            $this->db->insert('sms_response', $data);
        }

        return TRUE;


        /*
          $allowedBitBirds = array(
          '+88017',
          '+88019',
          '+88016',
          '+88018',
          '+88015',
          '+88011'
          );


          $allowedMuthofun = array(
          '+88019',
          '+88016',
          '+88018',
          '+88015',
          '+88011',
          );



          $prefix = substr($mobileNumber, 0, 6);

          if (in_array($prefix, $allowedBitBirds)) {
          // call bitbirds code

          $SMSResponse = $this->bitbirds->sendSMS('', $message, $mobileNumber);
          if ($SMSResponse > 0) {

          $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

          return TRUE;
          } else {
          // call muthofun code
          $response = $this->muthofun->sendSMS($mobileNumber, $message);

          if ($response['type'] == 'success') {
          $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());

          return TRUE;
          }

          $this->logActivity('error', 'SMS Sending Failed to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());

          return FALSE;
          }
          } else if (in_array($prefix, $allowedMuthofun)) {
          // call muthofun code
          $response = $this->muthofun->sendSMS($mobileNumber, $message);
          if ($response['type'] == 'success') {
          $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());

          return TRUE;
          } else {
          $SMSResponse = $this->bitbirds->sendSMS('', $message, $mobileNumber);
          if ($SMSResponse > 0) {
          $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

          return TRUE;
          }
          $this->logActivity('error', 'SMS Sending Failed to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

          return FALSE;
          }
          } else {

          $response = $this->muthofun->sendSMS($mobileNumber, $message);
          if ($response['type'] == 'success') {
          $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Muthofun </br>Source -> ' . current_url());
          return TRUE;
          } else {
          $SMSResponse = $this->bitbirds->sendSMS('', $message, $mobileNumber);
          if ($SMSResponse > 0) {
          $this->logActivity('info', 'SMS sent to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

          return TRUE;
          }
          $this->logActivity('error', 'SMS Sending Failed to : ' . $mobileNumber . '</br>SMS sent by : Bit Birds </br>Source -> ' . current_url());

          return FALSE;
          }
          } */
    }

    private function sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message) {
        $data = array($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message);
        $this->load->library('email');
        $this->email->from($senderEmail, $senderName);
        $this->email->to($receiverEmail);
        $this->email->subject($subject);
        //$mailer = "Dear " . $receiverName . ",<br/>";
        //$mailer .= "<strong>Your prizebond-checker.com Account Verification Code is:</strong> " . $verificationCode . "<br/></br> Prizebond Checker Team";
        $this->email->message($message);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return TRUE;
        } else {
            $response['response']['error'] = $this->config->item('COMMON_ERROR_05')['error'];
            $response['response']['code'] = $this->config->item('COMMON_ERROR_05')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br> Data ->' . json_encode($data));
            $this->response($response, 200);
        }
    }

    private function sendEmailToDeveloper($subject, $message) {
        $this->load->library('email');
        $this->email->from('noreply@prizebond-checker.com', 'Prizebond');
        $this->email->to('backend-team@example.com,web-team@example.com');
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->send();
        return TRUE;
    }

    private function deleteUserOldImage($userId) {
        $userInfo = $this->global_model->get_data('user', array('id' => $userId));
        if (!empty($userInfo['image'])) {
            $path = $this->PATH . $userInfo['image'];
            unlink($path);
        }
        return TRUE;
    }

    private function sendPushNotification($deviceUUID, $message, $payloadData = array()) {
        $device_push_id = $this->getPushId($deviceUUID);
        $this->load->library('gcm');
        $this->gcm->clearRecepients();
        $registrationIds = array();
        $registrationIds[] = $device_push_id;
        $this->gcm->setRecepients($registrationIds);
        if (count($payloadData) > 0) {
            $this->gcm->setData($payloadData);
        }
        $this->gcm->setMessage($message);
        $this->gcm->setGroup(md5($message));
        $this->gcm->send();
        $gcmStatus = $this->gcm->status;
        if ($gcmStatus['error'] != 0) {
            log_message('error', "GCM : " . $gcmStatus['message']);
            return FALSE;
        }
        return TRUE;
    }

    protected function sendLoginSuccessResponse($userInfo, $purchaseInfo, $subscriptionInfo, $winInfo, $message, $code) {


        if (count($purchaseInfo) > 0) {
            $userInfo->total_purchased_prizebond = (string) $purchaseInfo['total_user_purchased_prizebond'];
            $userInfo->advertisement = $purchaseInfo['advertisement'];
        } else {
            $userInfo->total_purchased_prizebond = "0";
            $userInfo->advertisement = 'ON';
        }

        unset($userInfo->id);
        unset($userInfo->previous_device_uuid);
        unset($userInfo->update_email_verification_code);
        unset($userInfo->update_mobile_verification_code);
        unset($userInfo->password);
        unset($userInfo->access_token_expire_time);
        unset($userInfo->created_date_time);
        unset($userInfo->updated_date_time);
        unset($userInfo->device_uuid);
        unset($userInfo->verify_code);
        unset($userInfo->status);
        unset($userInfo->verify_status);

        $hash = md5(serialize($userInfo));
        $response['response']['success'] = $message;
        $response['response']['code'] = $code;

        $response['response']['user_info'] = $userInfo;
        $response['response']['subscription'] = $subscriptionInfo;
        $response['response']['win_info'] = $winInfo;
        $response['response']['hash'] = $hash;
        $this->response($response, 200);
    }

    protected function sendMobileVerificationRequiredResponse($response = array()) {

        $response['response']['error'] = $this->config->item('USER_VERIFICATION_01')['error'];
        $response['response']['code'] = $this->config->item('USER_VERIFICATION_01')['code'];

        $userId = !empty($response['user_id']) ? $response['user_id'] : 0;

        unset($response['user_id']);

        // pramas : userId, code , message, internal message
        $this->logUserActivity($userId, $response['response']['code'], $response['response']['error'], 'User required to verify mobile / device');

        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br> User data ->' . json_encode($response) . '</br>Source -> ' . current_url());
        $this->response($response, 200);
    }

    private function sendEmailVerificationRequiredResponse($response = array()) {

        $response['response']['error'] = $this->config->item('USER_VERIFICATION_02')['error'];
        $response['response']['code'] = $this->config->item('USER_VERIFICATION_02')['code'];
        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br> User data ->' . json_encode($response) . '</br>Source -> ' . current_url());
        $this->response($response, 200);
    }

    private function deleteUserOldUploadPrizbondImage($userId, $oldPrizebondNumber, $oldSeries) {

        $where = array('user_id' => $userId, 'bond_number' => $oldPrizebondNumber, 'bond_series' => $oldSeries,);
        $userInfo = $this->global_model->get_data('user_prizebond_list', $where);
        if (!empty($userInfo['bond_image_name'])) {
            $path = $this->PATH . $userId . '/' . $userInfo['bond_image_name'];
            unlink($path);
        }
        return TRUE;
    }

    protected function userAuthCheck($emailOrMobile, $password, $accessToken = '') {

        if (!empty($accessToken)) {
            $userInfo = $this->api_model->get_data('user', array('access_token' => $accessToken));

            if (empty($userInfo)) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->response($response);
            }
            $expireTime = $userInfo['access_token_expire_time'];
            $currentTime = time();

            if ($userInfo['verify_status'] == 'NO') {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->response($response);
            } else if ($userInfo['status'] != 1) {
                $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_03')['error'];
                $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_03')['code'];
                $this->response($response);
            } elseif ($expireTime < $currentTime) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->response($response);
            }
            return $userInfo['id'];
        } elseif ($userInfo = $this->api_model->getUserInfoByEmailOrMobileNumberAndPassword($emailOrMobile, $password)) {

            if ($userInfo->verify_status == 'NO') {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->response($response);
            } else if ($userInfo->status != 1) {
                $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_03')['error'];
                $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_03')['code'];
                $this->response($response);
            }

            return $userInfo->id;
        } else {
            $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
            $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
            $this->response($response);
            exit;
        }
    }

    //created by a teammate
    private function userAuthCheckByAccessToken($accessToken) {

        if (!empty($accessToken)) {
            $userInfo = $this->api_model->get_data('user', array('access_token' => $accessToken));

            if (empty($userInfo)) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->response($response);
            }
            $expireTime = $userInfo['access_token_expire_time'];
            $currentTime = time();

            if ($userInfo['verify_status'] == 'NO') {

                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_01')['code'];
                $this->response($response);
            } else if ($userInfo['status'] != 1) {
                $response['response']['error'] = $this->config->item('USER_LOGIN_FAILED_03')['error'];
                $response['response']['code'] = $this->config->item('USER_LOGIN_FAILED_03')['code'];
                $this->response($response);
            } elseif ($expireTime < $currentTime) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->response($response);
            } elseif ($expireTime >= $currentTime) {

                $tokenInformation = array(
                    'access_token_expire_time' => strtotime('+5 minutes')
                );
                $this->api_model->update('user', $tokenInformation, array('id' => $userInfo['id']));
            }
            return $userInfo['id'];
        } else {
            $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
            $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
            $this->response($response);
            exit;
        }
    }

    protected function userWinInfoByUserId($userId) {
        if (!empty($userId)) {
            $userWinInfo = $this->api_model->getAUsersWinInfo($userId);

            $winInfo = new stdClass();
            if (!empty($userWinInfo)) {
                $winInfo->total_bonds = count($userWinInfo);
            } else {
                $winInfo->total_bonds = 0;
            }
            return $winInfo;
        } else {
            $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
            $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
            $this->response($response);
            exit;
        }
    }

    /*
      private function sendVerificationCodeByEmail($email, $userId) {

      $verifyCode = $this->generateVerificationCode();
      $this->load->library('email');
      $this->email->set_mailtype("html");
      $this->email->from('noreply@example.com', 'Prizebond Team');
      $this->email->to($email);
      $this->email->subject('Your PrizeBond App Verification Code');
      $message = 'Dear User, <br/>  Please copy and enter this verification code <b>' . $verifyCode . '</b> in your Prizebond App for further process. <br/><br/> For any query, please contact with our support team<br/>Prizebond Team';

      $this->email->message($message);
      if ($this->email->send()) {
      $this->api_model->updateEmailVerificationCode($verifyCode, $userId);
      return TRUE;
      } else {
      return FALSE;
      }
      } */

    private function generateVerificationCode() {
        return random_string('numeric', 4);
    }

    private function sendVerificationCodeByMobileNumber($mobile_number, $userId) {

        $verifyCode = $this->generateVerificationCode();
        $smsText = $this->config->item('sms_text') . $verifyCode;
        //$senderText = $this->config->item('sms_sender_text');
        //$gsm = $mobile_number;

        if ($this->sendSMSToCustomer($mobile_number, $smsText) == TRUE) {
            $this->api_model->updateMobileVerificationCode($verifyCode, $userId);
            return TRUE;
        } else {
            return FALSE;
        }



        /*
          $SMSResponse = $this->bitbirds->sendSMS($senderText, $smsText, $gsm);
          if ($SMSResponse > 0) {
          $this->api_model->updateMobileVerificationCode($verifyCode, $userId);
          return TRUE;
          } else {
          return FALSE;
          } */
    }

    private function sendUserVerificationCode($userId, $mobileNumber) {

        $userVerifyCounterInfo = $this->api_model->getVerifyCodeSentCounterForAUser($userId);
        if ($userVerifyCounterInfo['counter'] == 3) {

            $response['response']['error'] = $this->config->item('USER_VERIFICATION_02')['error'];
            $response['response']['code'] = $this->config->item('USER_VERIFICATION_02')['code'];
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error']);
            $this->response($response);
        } else {

            $verifyCode = $this->generateVerificationCode();
            $senderText = $this->config->item('sms_sender_text');
            $smsText = $this->config->item('sms_text') . $verifyCode;

            if ($this->sendSMSToCustomer($mobileNumber, $smsText) == TRUE) {

                $this->db->trans_start();

                $this->api_model->updateUserVerificationCode($verifyCode, $userId, $mobileNumber);

                $verifyCounterData = array(
                    'counter' => $userVerifyCounterInfo['counter'] + 1,
                );

                if ($this->api_model->updateUserVerifyCounter($userId, $verifyCounterData)) {
                    $this->db->trans_complete();
                    $response['response']['success'] = $this->config->item('VARIFICATION_SUCCESS_02')['success'];
                    $response['response']['code'] = $this->config->item('VARIFICATION_SUCCESS_02')['code'];
                    $this->response($response, 200);
                } else {

                    $response['response']['error'] = $this->config->item('USER_VERIFICATION_03')['error'];
                    $response['response']['code'] = $this->config->item('USER_VERIFICATION_03')['code'];
                    $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'user_id->' . json_encode($userId) . '</br>Source -> ' . current_url());
                    $this->response($response);
                }
            } else {
                $response['response']['error'] = $this->config->item('USER_VERIFICATION_03')['error'];
                $response['response']['code'] = $this->config->item('USER_VERIFICATION_03')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'user_id->' . json_encode($userId) . '</br>Source -> ' . current_url());
                $this->response($response);
            }
        }
    }

    private function convertEnglishleterToBanglaleter($leter) {

        switch ($leter) {
            case "ka_":
                return 'ক';
                break;
            case "kh_":
                return 'খ';
                break;
            case "ga_":
                return 'গ';
                break;
            case "ga_":
                return 'গ';
                break;
            case "gha_":
                return 'ঘ';
                break;
            case "uma_":
                return 'ঙ';
                break;
            case "ch_":
                return 'চ';
                break;
            case "chh_":
                return 'ছ';
                break;
            case "ja_":
                return 'জ';
                break;
            case "jh_":
                return 'ঝ';
                break;
            case "eo_":
                return 'ঞ';
                break;
            case "tt_":
                return 'ট';
                break;
            case "tt_":
                return 'ট';
                break;
            case "th_":
                return 'ঠ';
                break;
            case "dd_":
                return 'ড';
                break;
            case "dh_":
                return 'ঢ';
                break;
            case "nn_":
                return 'ণ';
                break;
            case "t_":
                return 'ত';
                break;
            case "tth_":
                return 'থ';
                break;
            case "d_":
                return 'দ';
                break;
            case "ddh_":
                return 'ধ';
                break;
            case "n_":
                return 'ন';
                break;
            case "pa_":
                return 'প';
                break;
            case "fa_":
                return 'ফ';
                break;
            case "ba_":
                return 'ব';
                break;
            case "va_":
                return 'ভ';
                break;
            case "ma_":
                return 'ম';
                break;
            case "jj_":
                return 'য';
                break;

            case "ra_":
                return 'র';
                break;

            case "la_":
                return 'ল';
                break;

            case "ssh_":
                return 'শ';
                break;

            case "ssh_":
                return 'শ';
                break;

            case "shh_":
                return 'ষ';
                break;

            case "sh_":
                return 'স';
                break;

            case "ha_":
                return 'হ';
                break;
        }
    }

    //created by a teammate
    private function logActivity($type, $message) {

        if ($this->config->item('LOG_ACTIVITY') == TRUE) {
            if ($type == 'error') {
                log_message('error', $message);
            } elseif ($type == 'debug') {
                log_message('debug', $message);
            } else {
                log_message('info', $message);
            }
        }
    }

    //created by a teammate
    private function logUserActivity($userId = 0, $code = NULL, $message = NULL, $internalMessage = NULL) {

        $deviceUUID = !empty($this->input->post('device_uuid')) ? $this->input->post('device_uuid') : NULL;
        $userData = !empty($this->input->post()) ? $this->input->post() : NULL;
        $data = array(
            'user_id' => $userId,
            'device_uuid' => $deviceUUID,
            'code' => $code,
            'message' => $message,
            'internal_message' => $internalMessage,
            'user_data' => json_encode($userData),
            'source_url' => current_url()
        );
        $this->db->insert('user_activity_logs', $data);
    }

    //created by a teammate
    private function getCountryInfo($countryISO) {
        $countries = array();
        $countries[] = array("code" => "AF", "name" => "Afghanistan", "d_code" => "+93");
        $countries[] = array("code" => "AL", "name" => "Albania", "d_code" => "+355");
        $countries[] = array("code" => "DZ", "name" => "Algeria", "d_code" => "+213");
        $countries[] = array("code" => "AS", "name" => "American Samoa", "d_code" => "+1");
        $countries[] = array("code" => "AD", "name" => "Andorra", "d_code" => "+376");
        $countries[] = array("code" => "AO", "name" => "Angola", "d_code" => "+244");
        $countries[] = array("code" => "AI", "name" => "Anguilla", "d_code" => "+1");
        $countries[] = array("code" => "AG", "name" => "Antigua", "d_code" => "+1");
        $countries[] = array("code" => "AR", "name" => "Argentina", "d_code" => "+54");
        $countries[] = array("code" => "AM", "name" => "Armenia", "d_code" => "+374");
        $countries[] = array("code" => "AW", "name" => "Aruba", "d_code" => "+297");
        $countries[] = array("code" => "AU", "name" => "Australia", "d_code" => "+61");
        $countries[] = array("code" => "AT", "name" => "Austria", "d_code" => "+43");
        $countries[] = array("code" => "AZ", "name" => "Azerbaijan", "d_code" => "+994");
        $countries[] = array("code" => "BH", "name" => "Bahrain", "d_code" => "+973");
        $countries[] = array("code" => "BD", "name" => "Bangladesh", "d_code" => "+880");
        $countries[] = array("code" => "BB", "name" => "Barbados", "d_code" => "+1");
        $countries[] = array("code" => "BY", "name" => "Belarus", "d_code" => "+375");
        $countries[] = array("code" => "BE", "name" => "Belgium", "d_code" => "+32");
        $countries[] = array("code" => "BZ", "name" => "Belize", "d_code" => "+501");
        $countries[] = array("code" => "BJ", "name" => "Benin", "d_code" => "+229");
        $countries[] = array("code" => "BM", "name" => "Bermuda", "d_code" => "+1");
        $countries[] = array("code" => "BT", "name" => "Bhutan", "d_code" => "+975");
        $countries[] = array("code" => "BO", "name" => "Bolivia", "d_code" => "+591");
        $countries[] = array("code" => "BA", "name" => "Bosnia and Herzegovina", "d_code" => "+387");
        $countries[] = array("code" => "BW", "name" => "Botswana", "d_code" => "+267");
        $countries[] = array("code" => "BR", "name" => "Brazil", "d_code" => "+55");
        $countries[] = array("code" => "IO", "name" => "British Indian Ocean Territory", "d_code" => "+246");
        $countries[] = array("code" => "VG", "name" => "British Virgin Islands", "d_code" => "+1");
        $countries[] = array("code" => "BN", "name" => "Brunei", "d_code" => "+673");
        $countries[] = array("code" => "BG", "name" => "Bulgaria", "d_code" => "+359");
        $countries[] = array("code" => "BF", "name" => "Burkina Faso", "d_code" => "+226");
        $countries[] = array("code" => "MM", "name" => "Burma Myanmar", "d_code" => "+95");
        $countries[] = array("code" => "BI", "name" => "Burundi", "d_code" => "+257");
        $countries[] = array("code" => "KH", "name" => "Cambodia", "d_code" => "+855");
        $countries[] = array("code" => "CM", "name" => "Cameroon", "d_code" => "+237");
        $countries[] = array("code" => "CA", "name" => "Canada", "d_code" => "+1");
        $countries[] = array("code" => "CV", "name" => "Cape Verde", "d_code" => "+238");
        $countries[] = array("code" => "KY", "name" => "Cayman Islands", "d_code" => "+1");
        $countries[] = array("code" => "CF", "name" => "Central African Republic", "d_code" => "+236");
        $countries[] = array("code" => "TD", "name" => "Chad", "d_code" => "+235");
        $countries[] = array("code" => "CL", "name" => "Chile", "d_code" => "+56");
        $countries[] = array("code" => "CN", "name" => "China", "d_code" => "+86");
        $countries[] = array("code" => "CO", "name" => "Colombia", "d_code" => "+57");
        $countries[] = array("code" => "KM", "name" => "Comoros", "d_code" => "+269");
        $countries[] = array("code" => "CK", "name" => "Cook Islands", "d_code" => "+682");
        $countries[] = array("code" => "CR", "name" => "Costa Rica", "d_code" => "+506");
        $countries[] = array("code" => "CI", "name" => "Côte d'Ivoire", "d_code" => "+225");
        $countries[] = array("code" => "HR", "name" => "Croatia", "d_code" => "+385");
        $countries[] = array("code" => "CU", "name" => "Cuba", "d_code" => "+53");
        $countries[] = array("code" => "CY", "name" => "Cyprus", "d_code" => "+357");
        $countries[] = array("code" => "CZ", "name" => "Czech Republic", "d_code" => "+420");
        $countries[] = array("code" => "CD", "name" => "Democratic Republic of Congo", "d_code" => "+243");
        $countries[] = array("code" => "DK", "name" => "Denmark", "d_code" => "+45");
        $countries[] = array("code" => "DJ", "name" => "Djibouti", "d_code" => "+253");
        $countries[] = array("code" => "DM", "name" => "Dominica", "d_code" => "+1");
        $countries[] = array("code" => "DO", "name" => "Dominican Republic", "d_code" => "+1");
        $countries[] = array("code" => "EC", "name" => "Ecuador", "d_code" => "+593");
        $countries[] = array("code" => "EG", "name" => "Egypt", "d_code" => "+20");
        $countries[] = array("code" => "SV", "name" => "El Salvador", "d_code" => "+503");
        $countries[] = array("code" => "GQ", "name" => "Equatorial Guinea", "d_code" => "+240");
        $countries[] = array("code" => "ER", "name" => "Eritrea", "d_code" => "+291");
        $countries[] = array("code" => "EE", "name" => "Estonia", "d_code" => "+372");
        $countries[] = array("code" => "ET", "name" => "Ethiopia", "d_code" => "+251");
        $countries[] = array("code" => "FK", "name" => "Falkland Islands", "d_code" => "+500");
        $countries[] = array("code" => "FO", "name" => "Faroe Islands", "d_code" => "+298");
        $countries[] = array("code" => "FM", "name" => "Federated States of Micronesia", "d_code" => "+691");
        $countries[] = array("code" => "FJ", "name" => "Fiji", "d_code" => "+679");
        $countries[] = array("code" => "FI", "name" => "Finland", "d_code" => "+358");
        $countries[] = array("code" => "FR", "name" => "France", "d_code" => "+33");
        $countries[] = array("code" => "GF", "name" => "French Guiana", "d_code" => "+594");
        $countries[] = array("code" => "PF", "name" => "French Polynesia", "d_code" => "+689");
        $countries[] = array("code" => "GA", "name" => "Gabon", "d_code" => "+241");
        $countries[] = array("code" => "GE", "name" => "Georgia", "d_code" => "+995");
        $countries[] = array("code" => "DE", "name" => "Germany", "d_code" => "+49");
        $countries[] = array("code" => "GH", "name" => "Ghana", "d_code" => "+233");
        $countries[] = array("code" => "GI", "name" => "Gibraltar", "d_code" => "+350");
        $countries[] = array("code" => "GR", "name" => "Greece", "d_code" => "+30");
        $countries[] = array("code" => "GL", "name" => "Greenland", "d_code" => "+299");
        $countries[] = array("code" => "GD", "name" => "Grenada", "d_code" => "+1");
        $countries[] = array("code" => "GP", "name" => "Guadeloupe", "d_code" => "+590");
        $countries[] = array("code" => "GU", "name" => "Guam", "d_code" => "+1");
        $countries[] = array("code" => "GT", "name" => "Guatemala", "d_code" => "+502");
        $countries[] = array("code" => "GN", "name" => "Guinea", "d_code" => "+224");
        $countries[] = array("code" => "GW", "name" => "Guinea-Bissau", "d_code" => "+245");
        $countries[] = array("code" => "GY", "name" => "Guyana", "d_code" => "+592");
        $countries[] = array("code" => "HT", "name" => "Haiti", "d_code" => "+509");
        $countries[] = array("code" => "HN", "name" => "Honduras", "d_code" => "+504");
        $countries[] = array("code" => "HK", "name" => "Hong Kong", "d_code" => "+852");
        $countries[] = array("code" => "HU", "name" => "Hungary", "d_code" => "+36");
        $countries[] = array("code" => "IS", "name" => "Iceland", "d_code" => "+354");
        $countries[] = array("code" => "IN", "name" => "India", "d_code" => "+91");
        $countries[] = array("code" => "ID", "name" => "Indonesia", "d_code" => "+62");
        $countries[] = array("code" => "IR", "name" => "Iran", "d_code" => "+98");
        $countries[] = array("code" => "IQ", "name" => "Iraq", "d_code" => "+964");
        $countries[] = array("code" => "IE", "name" => "Ireland", "d_code" => "+353");
        $countries[] = array("code" => "IL", "name" => "Israel", "d_code" => "+972");
        $countries[] = array("code" => "IT", "name" => "Italy", "d_code" => "+39");
        $countries[] = array("code" => "JM", "name" => "Jamaica", "d_code" => "+1");
        $countries[] = array("code" => "JP", "name" => "Japan", "d_code" => "+81");
        $countries[] = array("code" => "JO", "name" => "Jordan", "d_code" => "+962");
        $countries[] = array("code" => "KZ", "name" => "Kazakhstan", "d_code" => "+7");
        $countries[] = array("code" => "KE", "name" => "Kenya", "d_code" => "+254");
        $countries[] = array("code" => "KI", "name" => "Kiribati", "d_code" => "+686");
        $countries[] = array("code" => "XK", "name" => "Kosovo", "d_code" => "+381");
        $countries[] = array("code" => "KW", "name" => "Kuwait", "d_code" => "+965");
        $countries[] = array("code" => "KG", "name" => "Kyrgyzstan", "d_code" => "+996");
        $countries[] = array("code" => "LA", "name" => "Laos", "d_code" => "+856");
        $countries[] = array("code" => "LV", "name" => "Latvia", "d_code" => "+371");
        $countries[] = array("code" => "LB", "name" => "Lebanon", "d_code" => "+961");
        $countries[] = array("code" => "LS", "name" => "Lesotho", "d_code" => "+266");
        $countries[] = array("code" => "LR", "name" => "Liberia", "d_code" => "+231");
        $countries[] = array("code" => "LY", "name" => "Libya", "d_code" => "+218");
        $countries[] = array("code" => "LI", "name" => "Liechtenstein", "d_code" => "+423");
        $countries[] = array("code" => "LT", "name" => "Lithuania", "d_code" => "+370");
        $countries[] = array("code" => "LU", "name" => "Luxembourg", "d_code" => "+352");
        $countries[] = array("code" => "MO", "name" => "Macau", "d_code" => "+853");
        $countries[] = array("code" => "MK", "name" => "Macedonia", "d_code" => "+389");
        $countries[] = array("code" => "MG", "name" => "Madagascar", "d_code" => "+261");
        $countries[] = array("code" => "MW", "name" => "Malawi", "d_code" => "+265");
        $countries[] = array("code" => "MY", "name" => "Malaysia", "d_code" => "+60");
        $countries[] = array("code" => "MV", "name" => "Maldives", "d_code" => "+960");
        $countries[] = array("code" => "ML", "name" => "Mali", "d_code" => "+223");
        $countries[] = array("code" => "MT", "name" => "Malta", "d_code" => "+356");
        $countries[] = array("code" => "MH", "name" => "Marshall Islands", "d_code" => "+692");
        $countries[] = array("code" => "MQ", "name" => "Martinique", "d_code" => "+596");
        $countries[] = array("code" => "MR", "name" => "Mauritania", "d_code" => "+222");
        $countries[] = array("code" => "MU", "name" => "Mauritius", "d_code" => "+230");
        $countries[] = array("code" => "YT", "name" => "Mayotte", "d_code" => "+262");
        $countries[] = array("code" => "MX", "name" => "Mexico", "d_code" => "+52");
        $countries[] = array("code" => "MD", "name" => "Moldova", "d_code" => "+373");
        $countries[] = array("code" => "MC", "name" => "Monaco", "d_code" => "+377");
        $countries[] = array("code" => "MN", "name" => "Mongolia", "d_code" => "+976");
        $countries[] = array("code" => "ME", "name" => "Montenegro", "d_code" => "+382");
        $countries[] = array("code" => "MS", "name" => "Montserrat", "d_code" => "+1");
        $countries[] = array("code" => "MA", "name" => "Morocco", "d_code" => "+212");
        $countries[] = array("code" => "MZ", "name" => "Mozambique", "d_code" => "+258");
        $countries[] = array("code" => "NA", "name" => "Namibia", "d_code" => "+264");
        $countries[] = array("code" => "NR", "name" => "Nauru", "d_code" => "+674");
        $countries[] = array("code" => "NP", "name" => "Nepal", "d_code" => "+977");
        $countries[] = array("code" => "NL", "name" => "Netherlands", "d_code" => "+31");
        $countries[] = array("code" => "AN", "name" => "Netherlands Antilles", "d_code" => "+599");
        $countries[] = array("code" => "NC", "name" => "New Caledonia", "d_code" => "+687");
        $countries[] = array("code" => "NZ", "name" => "New Zealand", "d_code" => "+64");
        $countries[] = array("code" => "NI", "name" => "Nicaragua", "d_code" => "+505");
        $countries[] = array("code" => "NE", "name" => "Niger", "d_code" => "+227");
        $countries[] = array("code" => "NG", "name" => "Nigeria", "d_code" => "+234");
        $countries[] = array("code" => "NU", "name" => "Niue", "d_code" => "+683");
        $countries[] = array("code" => "NF", "name" => "Norfolk Island", "d_code" => "+672");
        $countries[] = array("code" => "KP", "name" => "North Korea", "d_code" => "+850");
        $countries[] = array("code" => "MP", "name" => "Northern Mariana Islands", "d_code" => "+1");
        $countries[] = array("code" => "NO", "name" => "Norway", "d_code" => "+47");
        $countries[] = array("code" => "OM", "name" => "Oman", "d_code" => "+968");
        $countries[] = array("code" => "PK", "name" => "Pakistan", "d_code" => "+92");
        $countries[] = array("code" => "PW", "name" => "Palau", "d_code" => "+680");
        $countries[] = array("code" => "PS", "name" => "Palestine", "d_code" => "+970");
        $countries[] = array("code" => "PA", "name" => "Panama", "d_code" => "+507");
        $countries[] = array("code" => "PG", "name" => "Papua New Guinea", "d_code" => "+675");
        $countries[] = array("code" => "PY", "name" => "Paraguay", "d_code" => "+595");
        $countries[] = array("code" => "PE", "name" => "Peru", "d_code" => "+51");
        $countries[] = array("code" => "PH", "name" => "Philippines", "d_code" => "+63");
        $countries[] = array("code" => "PL", "name" => "Poland", "d_code" => "+48");
        $countries[] = array("code" => "PT", "name" => "Portugal", "d_code" => "+351");
        $countries[] = array("code" => "PR", "name" => "Puerto Rico", "d_code" => "+1");
        $countries[] = array("code" => "QA", "name" => "Qatar", "d_code" => "+974");
        $countries[] = array("code" => "CG", "name" => "Republic of the Congo", "d_code" => "+242");
        $countries[] = array("code" => "RE", "name" => "Réunion", "d_code" => "+262");
        $countries[] = array("code" => "RO", "name" => "Romania", "d_code" => "+40");
        $countries[] = array("code" => "RU", "name" => "Russia", "d_code" => "+7");
        $countries[] = array("code" => "RW", "name" => "Rwanda", "d_code" => "+250");
        $countries[] = array("code" => "BL", "name" => "Saint Barthélemy", "d_code" => "+590");
        $countries[] = array("code" => "SH", "name" => "Saint Helena", "d_code" => "+290");
        $countries[] = array("code" => "KN", "name" => "Saint Kitts and Nevis", "d_code" => "+1");
        $countries[] = array("code" => "MF", "name" => "Saint Martin", "d_code" => "+590");
        $countries[] = array("code" => "PM", "name" => "Saint Pierre and Miquelon", "d_code" => "+508");
        $countries[] = array("code" => "VC", "name" => "Saint Vincent and the Grenadines", "d_code" => "+1");
        $countries[] = array("code" => "WS", "name" => "Samoa", "d_code" => "+685");
        $countries[] = array("code" => "SM", "name" => "San Marino", "d_code" => "+378");
        $countries[] = array("code" => "ST", "name" => "São Tomé and Príncipe", "d_code" => "+239");
        $countries[] = array("code" => "SA", "name" => "Saudi Arabia", "d_code" => "+966");
        $countries[] = array("code" => "SN", "name" => "Senegal", "d_code" => "+221");
        $countries[] = array("code" => "RS", "name" => "Serbia", "d_code" => "+381");
        $countries[] = array("code" => "SC", "name" => "Seychelles", "d_code" => "+248");
        $countries[] = array("code" => "SL", "name" => "Sierra Leone", "d_code" => "+232");
        $countries[] = array("code" => "SG", "name" => "Singapore", "d_code" => "+65");
        $countries[] = array("code" => "SK", "name" => "Slovakia", "d_code" => "+421");
        $countries[] = array("code" => "SI", "name" => "Slovenia", "d_code" => "+386");
        $countries[] = array("code" => "SB", "name" => "Solomon Islands", "d_code" => "+677");
        $countries[] = array("code" => "SO", "name" => "Somalia", "d_code" => "+252");
        $countries[] = array("code" => "ZA", "name" => "South Africa", "d_code" => "+27");
        $countries[] = array("code" => "KR", "name" => "South Korea", "d_code" => "+82");
        $countries[] = array("code" => "ES", "name" => "Spain", "d_code" => "+34");
        $countries[] = array("code" => "LK", "name" => "Sri Lanka", "d_code" => "+94");
        $countries[] = array("code" => "LC", "name" => "St. Lucia", "d_code" => "+1");
        $countries[] = array("code" => "SD", "name" => "Sudan", "d_code" => "+249");
        $countries[] = array("code" => "SR", "name" => "Suriname", "d_code" => "+597");
        $countries[] = array("code" => "SZ", "name" => "Swaziland", "d_code" => "+268");
        $countries[] = array("code" => "SE", "name" => "Sweden", "d_code" => "+46");
        $countries[] = array("code" => "CH", "name" => "Switzerland", "d_code" => "+41");
        $countries[] = array("code" => "SY", "name" => "Syria", "d_code" => "+963");
        $countries[] = array("code" => "TW", "name" => "Taiwan", "d_code" => "+886");
        $countries[] = array("code" => "TJ", "name" => "Tajikistan", "d_code" => "+992");
        $countries[] = array("code" => "TZ", "name" => "Tanzania", "d_code" => "+255");
        $countries[] = array("code" => "TH", "name" => "Thailand", "d_code" => "+66");
        $countries[] = array("code" => "BS", "name" => "The Bahamas", "d_code" => "+1");
        $countries[] = array("code" => "GM", "name" => "The Gambia", "d_code" => "+220");
        $countries[] = array("code" => "TL", "name" => "Timor-Leste", "d_code" => "+670");
        $countries[] = array("code" => "TG", "name" => "Togo", "d_code" => "+228");
        $countries[] = array("code" => "TK", "name" => "Tokelau", "d_code" => "+690");
        $countries[] = array("code" => "TO", "name" => "Tonga", "d_code" => "+676");
        $countries[] = array("code" => "TT", "name" => "Trinidad and Tobago", "d_code" => "+1");
        $countries[] = array("code" => "TN", "name" => "Tunisia", "d_code" => "+216");
        $countries[] = array("code" => "TR", "name" => "Turkey", "d_code" => "+90");
        $countries[] = array("code" => "TM", "name" => "Turkmenistan", "d_code" => "+993");
        $countries[] = array("code" => "TC", "name" => "Turks and Caicos Islands", "d_code" => "+1");
        $countries[] = array("code" => "TV", "name" => "Tuvalu", "d_code" => "+688");
        $countries[] = array("code" => "UG", "name" => "Uganda", "d_code" => "+256");
        $countries[] = array("code" => "UA", "name" => "Ukraine", "d_code" => "+380");
        $countries[] = array("code" => "AE", "name" => "United Arab Emirates", "d_code" => "+971");
        $countries[] = array("code" => "GB", "name" => "United Kingdom", "d_code" => "+44");
        $countries[] = array("code" => "US", "name" => "United States", "d_code" => "+1");
        $countries[] = array("code" => "UY", "name" => "Uruguay", "d_code" => "+598");
        $countries[] = array("code" => "VI", "name" => "US Virgin Islands", "d_code" => "+1");
        $countries[] = array("code" => "UZ", "name" => "Uzbekistan", "d_code" => "+998");
        $countries[] = array("code" => "VU", "name" => "Vanuatu", "d_code" => "+678");
        $countries[] = array("code" => "VA", "name" => "Vatican City", "d_code" => "+39");
        $countries[] = array("code" => "VE", "name" => "Venezuela", "d_code" => "+58");
        $countries[] = array("code" => "VN", "name" => "Vietnam", "d_code" => "+84");
        $countries[] = array("code" => "WF", "name" => "Wallis and Futuna", "d_code" => "+681");
        $countries[] = array("code" => "YE", "name" => "Yemen", "d_code" => "+967");
        $countries[] = array("code" => "ZM", "name" => "Zambia", "d_code" => "+260");
        $countries[] = array("code" => "ZW", "name" => "Zimbabwe", "d_code" => "+263");

        foreach ($countries as $country) {
            if ($country['code'] == $countryISO) {
                return $country;
            }
        }
    }

    //created by a teammate
    private function validateMobileNumber($countryISO, $mobileNumber) {
        // verify mobile numbers with dialing code 1 = matched , 2 = not matched 3 = invalid country iso
        if ($countryInfo = $this->getCountryInfo(strtoupper($countryISO))) {
            $dialingCode = $countryInfo['d_code'];

            if (strpos($mobileNumber, $dialingCode) === 0) {
                return 1;
            } else {
                return 2;
            }
        }

        return 3;
    }

    //created by a teammate
    private function convertEnglishDateTimeToBanglaDateTime() {
        $currentDate = date("d F Y h:i a");
        $engDATE = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'am', 'pm');
        $bangDATE = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০', 'জানুয়ারী', 'ফেব্রুয়ারী', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর', 'শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', '
বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'পূর্বাহ্ণ', 'অপরাহ্ণ'
        );
        $convertedDATE = str_replace($engDATE, $bangDATE, $currentDate);
        return $convertedDATE;
    }

    //created by a teammate
    private function convertEnglishNumberIntoBanglaNumber($number) {
        $englishNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
        $banglaNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
        $convertNumber = str_replace($englishNumber, $banglaNumber, $number);
        return $convertNumber;
    }

    private function convertBanglaNumberIntoEnglishNumber($number) {
        $banglaNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
        $englishNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
        $convertNumber = str_replace($banglaNumber, $englishNumber, $number);
        return $convertNumber;
    }

    //created by a teammate
    protected function generateAccessTokenById($userId) {
        //created access token (by a teammate)
        $tokenInformation = array(
            'access_token' => uniqid(),
            'access_token_expire_time' => strtotime('+5 minutes')
        );
        $this->api_model->update('user', $tokenInformation, array('id' => $userId));

        return $tokenInformation['access_token'];
    }

    //created by a teammate
    private function getInternalUserIdByExternalUserId($userId) {

        $userInfo = $this->global_model->get_data('user', array('user_id' => $userId));

        if (!$userInfo) {
            return FALSE;
        }
        return $userInfo['id'];
    }

    private function getwayInfo() {
        $result = $this->global_model->get_data('subscription_gateway_settings', array('gateway_id' => 1));
        return $result;
    }

    private function getExternalUserIdByInternalUserId($userId) {
        $userInfo = $this->global_model->get_data('user', array('id' => $userId));
        if (!$userInfo) {
            return FALSE;
        }
        return $userInfo['user_id'];
    }

    private function deliverServiceToCustomer($productId, $userId, $paymentMethodName, $referenceId) {

        $productInfo = $this->subscription_model->get_data('subscription_product_list', array('id' => $productId));

        $productAttribueValue = $this->subscription_model->getProductAttributeAndValue($productId);

        if (isset($productAttribueValue['unit']) && isset($productAttribueValue['validity(days)'])) {
            if (!empty($productAttribueValue['unit']) && $productAttribueValue['unit'] == 'bond') {
                $validity = $productAttribueValue['validity(days)'];
                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
            }
        }

        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
            if ($productAttribueValue['unit'] == 'bond') {
                $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
            }
        }

        if (isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
            if ($productAttribueValue['unit'] == 'days') {
                $validity = $productAttribueValue['unit_value'];
                $validEndDateTime = date('Y-m-d H:i:s', strtotime("+ $validity days"));
            }
        }

        if (!isset($productAttribueValue['unit']) && !isset($productAttribueValue['validity(days)'])) {
            $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
        }

        $productPurchaseListData = array(
            'user_id' => $userId,
            'product_category_id' => $productInfo['product_category_id'],
            'product_type_id' => $productInfo['product_type_id'],
            'app_types_id' => $productInfo['app_types_id'],
            'product_id' => $productInfo['id'],
            'purchased_by' => $paymentMethodName,
            'valid_start_datetime' => date('Y-m-d H:i:s'),
            'valid_end_datetime' => $validEndDateTime,
            'status' => 1,
            'order_reference_id' => $referenceId,
            'created_datetime' => date('Y-m-d H:i:s')
        );



        if ($this->subscription_model->insert('subscription_product_purchase_list', $productPurchaseListData)) {
            $this->subscription_model->update('subscription_order_list', array('order_status' => 'completed'), array('reference_id' => $referenceId));
            $this->sendProductDeliveryConfirmationMessageToUser($userId, $referenceId);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function detectValidWebRequest() {

        $HTTP_HOSTS = array(
            'api.example.com',
            'prizebond-checker.com',
            'localhost',
            '192.168.50.54',
            '192.168.50.53'
        );

        if (isset($_SERVER['HTTP_HOST'])) {
            if (!in_array($_SERVER['HTTP_HOST'], $HTTP_HOSTS)) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' HTTP HOST : ' . $_SERVER['HTTP_HOST']);
                $this->response($response);
            }
        }

        if (isset($_SERVER['HTTP_REFERER'])) {
            if ($_SERVER['HTTP_REFERER'] != md5('prizebond-checker.com' . date('Y-m-d'))) {
                $response['response']['error'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['error'];
                $response['response']['code'] = $this->config->item('UNAUTHORIZED_ACCESS_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . ' Received HTTP Referer : ' . $_SERVER['HTTP_REFERER'] . ' Expected value : ' . md5('prizebond-checker.com' . date('Y-m-d')));
                $this->response($response);
            }

            return TRUE;
        }

        return FALSE;
    }

    private function verifyMobileNumberWithDialingCode($mobileNumber, $countryISOCode) {
        if ($validNumberStatus = $this->validateMobileNumber($countryISOCode, $mobileNumber)) {
            // 1 = matched , 2 = not matched, 3 = invalid country iso
            if ($validNumberStatus == 2) {
                $response['response']['error'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['error'];
                $response['response']['code'] = $this->config->item('MOBILE_VALIDATION_ERROR_02')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response);
            } else if ($validNumberStatus == 3) {
                $response['response']['error'] = $this->config->item('INVALID_COUNTRY_ISO_CODE_01')['error'];
                $response['response']['code'] = $this->config->item('INVALID_COUNTRY_ISO_CODE_01')['code'];
                $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url());
                $this->response($response, 200);
            }
        }
    }

    private function sendPushNotificationToWinner($bondList, $userId) {

        if (is_array($bondList)) {
            foreach ($bondList as $bondInfo) {

                if ($bondInfo['win_draw_number'] != NULL) {


                    $drawNumber = $this->convertEnglishNumberIntoBanglaNumber($bondInfo['win_draw_number']);
                    $bondNumber = $this->convertEnglishNumberIntoBanglaNumber($bondInfo['bond_number']);
                    $prizeAmount = $this->convertEnglishNumberIntoBanglaNumber($bondInfo['win_prize_money']);
                    $prizePosition = $this->convertPrizePositionEnglishToBangla($bondInfo['win_prize_position']);

                    $message = $drawNumber . "তম ড্রতে আপনার প্রাইজ বণ্ড # " . $bondInfo['bond_series'] . " " . $bondNumber . ", " . $prizePosition . " পুরষ্কার " . $prizeAmount . " টাকা জিতেছে।";
                    $this->sendGcmToWinnerDevice($userId, $bondNumber, $bondInfo['user_bond_id'], $message);
                }
            }
        }
    }

    private function convertPrizePositionEnglishToBangla($prizePosition) {
        switch ($prizePosition) {
            case "5th":
                return '৫ম';
                break;
            case "4th":
                return '৪র্থ';
                break;
            case "3rd":
                return '৩য়';
                break;
            case "2nd":
                return '২য়';
                break;
            case "1st":
                return '১ম';
                break;
        }
    }

    private function sendGcmToWinnerDevice($userId, $bondNumber, $userPrizebondId, $message) {
        $this->load->library('gcm');
        $this->gcm->clearRecepients();
        $registrationIds = array();

        $devicePushInfo = $this->global_model->get_data('device_info', array('user_id' => $userId, 'device_status' => 'active'));
        $registrationIds[] = (string) $devicePushInfo['device_push_id'];

        $this->gcm->setRecepients($registrationIds);
        $payloadData = array(
            'prizebond' => 'Custom content'
        );
        $this->gcm->setData($payloadData);
        $this->gcm->setMessage($message);
        $this->gcm->setGroup($message);

        if ($this->gcm->send()) {
            $this->global_model->update('user_prizebond_won_list', array('push_sent' => 'yes'), array('user_prizebond_id' => $userPrizebondId, 'user_id' => $userId));
        } else {
            //echo 'Failed to send push notification';
            $this->logActivity('error', 'Failed to send push notification </br> Data ->' . json_encode($devicePushInfo) . '</br> message ->' . json_encode($message) . '</br> bond number ->' . json_encode($bondNumber));
        }
    }

    private function sendInternationalSMS($mobileNumber, $message) {

        $this->load->library('experttexting');
        $this->experttexting->send($mobileNumber, $message);
//        $this->experttexting->from = '';
//        $this->experttexting->to = '';
//        $this->experttexting->msgtext = '';
    }

    private function sendProductDeliveryConfirmationMessageToUser($userId, $orderId) {

        $message = 'Your subscription for Order ID ' . $orderId . ' has been activated. Thank you - Prizebond-Checker Team';

        $devicePushInfo = $this->global_model->get_data('device_info', array('user_id' => $userId, 'device_status' => 'active'));
        $userInfo = $this->global_model->get_data('user', array('id' => $userId));

        // SEND GCM
        if (strlen($devicePushInfo['device_push_id']) > 25) {

            $this->load->library('gcm');
            $this->gcm->clearRecepients();
            $registrationIds = array();


            $registrationIds[] = (string) $devicePushInfo['device_push_id'];

            $this->gcm->setRecepients($registrationIds);
            $payloadData = array(
                'prizebond' => 'Custom content'
            );
            $this->gcm->setData($payloadData);
            $this->gcm->setMessage($message);
            $this->gcm->setGroup($message);

            if ($this->gcm->send()) {

                $this->logActivity('info', 'GCM sent : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'GCM sent : ' . $message);
            } else {
                $this->logActivity('error', 'GCM sent : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'GCM sent : ' . $message);
            }
        }



        // SEND SMS

        if ($userInfo['mobile_number'] != NULL) {
            //$senderText = $this->config->item('sms_sender_text');
            $smsText = $message;
            $gsm = $userInfo['mobile_number'];
            if ($this->sendSMSToCustomer($gsm, $smsText) == TRUE) {
                $this->logActivity('info', 'SMS verification Code sent : ' . $gsm . ' Message : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'SMS verification Code : ' . $gsm . ' Message : ' . $message);
            } else {
                $this->logActivity('error', 'SMS verification Code failed to sent : ' . $gsm . ' Message : ' . $message);
                $this->logUserActivity($userId, NULL, NULL, 'SMS verification Code failed to sent : ' . $gsm . ' Message : ' . $message);
            }
        }
    }

    private function determineSuspiciousActivity($deviceUUID, $userId) {

        //return array
        $deviceInfoArray = $this->global_model->get('device_info', array('device_uuid' => $deviceUUID, 'device_status' => 'active'));
        $totalRegistrations = count($deviceInfoArray);



        $startDate = date('Y-m-d', strtotime('-24 hours'));
        $endDate = date('Y-m-d');
        $query = $this->db->query("SELECT id FROM device_info WHERE device_uuid = '$deviceUUID' AND device_status = 'active' AND ( DATE(added_date) >= '$startDate' AND DATE(added_date) <= '$endDate' )");
        $totalRegistrationInLast24Hours = $query->num_rows();


        $query = $this->db->query("SELECT id FROM device_info WHERE device_uuid = '$deviceUUID' AND device_status = 'active' AND ( DATE(update_date) >= '$startDate' AND DATE(update_date) <= '$endDate' )");
        $totalLoginsInLast24Hours = $query->num_rows();


        /* if ($totalRegistrations >= 7) {
          // this is suspicious
          $message = "Device UUID: $deviceUUID has been used to register accounts $totalRegistrations times or more. So all user accounts related to this Device UUID has been ";
          $this->takeActionsOnAllUserAccountsRelatedToDeviceUUID($deviceUUID, 'USER_ACCOUNT_AUTO_SUSPEND', $message);
          } else if ($totalRegistrationInLast24Hours >= 3) {
          // this is suspicious
          $message = "Device UUID: $deviceUUID has been used to register $totalRegistrationInLast24Hours times or more in last 24 hours. So all user accounts related to this Device UUID has been ";
          $this->takeActionsOnAllUserAccountsRelatedToDeviceUUID($deviceUUID, 'USER_ACCOUNT_FLAG_ONLY', $message);
          } else if ($totalLoginsInLast24Hours >= 5) {
          $message = "Device UUID: $deviceUUID has been used to logged in $totalLoginsInLast24Hours times or more in last 24 hours. So all user accounts related to this Device UUID has been ";
          $this->takeActionsOnAllUserAccountsRelatedToDeviceUUID($deviceUUID, 'USER_ACCOUNT_FLAG_ONLY', $message);
          } */
    }

    private function takeActionsOnAllUserAccountsRelatedToDeviceUUID($deviceUUID, $actionType, $message) {

        $userIdList = array();
        $this->db->trans_start();

        if ($actionType == 'USER_ACCOUNT_FLAG_ONLY') {
            $message .= " flagged";
            $deviceInfoArray = $this->global_model->get('device_info', array('device_uuid' => $deviceUUID, 'device_status' => 'active'));

            if (is_array($deviceInfoArray) && (count($deviceInfoArray))) {
                foreach ($deviceInfoArray as $key => $deviceInfo) {

                    $userId = $deviceInfo['user_id'];
                    $date = date('Y-m-d H:i:s');

                    $SQL = "UPDATE user SET flag_status = 'suspicious', flag_message = '$message', flag_date_time = '$date' WHERE id='$userId'";
                    $this->db->query($SQL);

                    $data = array(
                        'user_id' => $userId,
                        'device_uuid' => $deviceUUID,
                        'message' => $message
                    );
                    $this->db->insert('user_flag_logs', $data);

                    $userIdList[] = $userId;
                }
            }
        } else if ($actionType == 'USER_ACCOUNT_AUTO_SUSPEND') {

            $message .= " automatically suspended";
            $deviceInfoArray = $this->global_model->get('device_info', array('device_uuid' => $deviceUUID, 'device_status' => 'active'));

            if (is_array($deviceInfoArray) && (count($deviceInfoArray))) {
                foreach ($deviceInfoArray as $key => $deviceInfo) {

                    $userId = $deviceInfo->user_id;
                    $date = date('Y-m-d H:i:s');

                    $SQL = "UPDATE user SET status='3', flag_status = 'suspicious', flag_message = '$message', flag_date_time = '$date' WHERE id='$userId'";
                    $this->db->query($SQL);

                    $data = array(
                        'user_id' => $userId,
                        'device_uuid' => $deviceUUID,
                        'message' => $message
                    );
                    $this->db->insert('user_flag_logs', $data);

                    $userIdList[] = $userId;
                }
            }
        }


        $this->db->trans_complete();

        if ($this->db->trans_status() == FALSE) {
            //echo 'DB Transaction Failed';
            $this->logUserActivity($userId, NULL, NULL, 'DB Transaction failed during user suspicious activity logging. Check func : determineSuspiciousActivity in Api Controller');
        } else {
            $message .= " suspended user ids : " . implode(",", $userIdList);
            //echo 'Suspicious Activity detected in PrizeBond System' . $message;
            mail('web-team@example.com,admin@example.com', 'Suspicious Activity detected in PrizeBond System', $message);
        }
    }

    private function sendMesageToSalesTeam($postData, $orderInfo) {

        $query = $this->db->get_where('user', array('id' => $orderInfo->user_id));

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $customerName = $row->name;
                $customerEmail = $row->email;
                $customerMobileNumber = $row->mobile_number;

                $senderEmail = 'no-reply@prizebond-checker.com';
                $senderName = 'PrizeBond';
                $receiverEmail = 'sales@example.com';
                $receiverName = 'Sales Team';
                $subject = 'PrizeBond COD Request';

                $message = "Dear Sales Team,<br> 1 new COD request has been submitted. Please confirm the order. Order details are as follow:<br><br>";
                $message .= "Order ID : " . $postData['merchant_reference_id'] . '<br>';
                $message .= "Order Created Date : " . date('l jS \of F Y h:i:s A', strtotime($orderInfo->order_created_date_time)) . '<br>';
                $message .= "Order Submitted Date : " . date('l jS \of F Y h:i:s A', strtotime($postData['payment_date'])) . '<br>';
                $message .= "Cutomer Information : " . $customerName . ' - ' . $customerMobileNumber . ' - ' . $customerEmail . '<br>';
                $message .= "Shipping Address : " . $postData['shipping_address'] . '<br>';
                $message .= "Shipping Cost : " . $postData['shipping_cost'] . '<br>';
                $message .= "Discount : " . $postData['discount'] . '<br>';
                $message .= "Quantity : " . $postData['quantity'] . '<br>';
                $message .= "Total Receivable Amount : " . $postData['total_received_amount'] . '<br>';

                if ($this->sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message)) {
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    private function sendPushNotificationToDevelopers($message) {

        $this->load->library('gcm');

        $query = $this->db->query("SELECT user.id,user.name,device_info.device_push_id AS push_id FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user_type_id = 2 AND device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' AND device_info.gcm_status = 'active'");
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $pushIds = array();
                $pushIds[] = $row->push_id;
                $this->gcm->clearRecepients();
                $this->gcm->setRecepients($pushIds);
                $payloadData = array(
                    'prizebond' => 'Custom content'
                );
                $this->gcm->setData($payloadData);
                $this->gcm->setMessage($message);
                $this->gcm->setGroup(md5($message));
                $this->gcm->send();
                $gcmStatus = $this->gcm->status;
                if ($gcmStatus['error'] != 0) {
                    $this->logActivity('error', 'Failed to send push notification to ' . $row->name . ' Error msg : ' . $gcmStatus['message']);

                    //log_message('error', "GCM : " . $gcmStatus['message']);
                    //echo $gcmStatus['message'];
                } else {
                    $this->logActivity('info', 'Push notification sent to developer ' . $row->name);
                    //echo "sent GCM successfully";
                }

                //echo "<br>";
            }
        }
    }

    private function sendPushNotificationToUsers($userType, $message) {

        if ($userType == API::USER_TYPE_DEVELOPER) {
            
        } else if ($userType == API::USER_TYPE_CUSTOMER) {
            
        } else if ($userType == API::USER_TYPE_CRM) {
            
        }
    }

    private function savePrizeBondNumbers($userId, $uuid, $prizebondNumbers, $bond_series) {
        return $this->api_model->savePrizeBondNumbers($userId, $uuid, $prizebondNumbers, $bond_series);
    }

    private function deletePrizeBondNumbers($data, $userId) {
        return $this->api_model->deletePrizeBondNumbers($data, $userId);
    }

    private function getSeriesList() {
        return $this->api_model->getSeriesList();
    }

    private function getListOfDraw() {
        return $this->api_model->getListOfDraw();
    }

    private function getResultOfADraw($draw_id) {
        return $this->api_model->getResultOfADraw($draw_id);
    }

    private function updateDeviceInfo($requestedData) {
        return $this->api_model->updateDeviceInfo($requestedData);
    }

    private function getLatestPrizebondDraw() {
        return $this->api_model->getLatestPrizebondDraw();
    }

    private function getDeviceInfoForSendPush() {
        return $this->api_model->getDeviceInfoForSendPush();
    }

    private function savePushStatus($id) {
        return $this->api_model->savePushStatus($id);
    }

    private function saveDrawInfoTablePushStatus($id) {
        return $this->api_model->saveDrawInfoTablePushStatus($id);
    }

    private function getPushId($deviceUuid) {
        return $this->api_model->getPushId($deviceUuid);
    }

    private function verifyAPIKey($apiKey, $secretKey = FALSE) {
        if (!$this->api_model->doesMatchApiKey($apiKey)) {
            $output['response']['error'] = 'api key not matched';
            $this->response($output);
            exit;
        } else {
            return TRUE;
        }
    }

    private function sendFormVelidationErrorResponse() {
        $error = !empty(str_replace("\n", ' ', strip_tags(validation_errors()))) ? str_replace("\n", ' ', strip_tags(validation_errors())) : $this->config->item('COMMON_ERROR_01')['error'];
        $response['response']['error'] = $error;
        $response['response']['code'] = $this->config->item('COMMON_ERROR_01')['code'];
        $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . 'User Data : ' . json_encode($this->post()) . '</br>Source -> ' . current_url());
        $this->response($response, 200);
    }

    private function sendGCMNotification($registrationIdsArray, $message) {
        $headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . (getenv('GCM_API_KEY') ?: 'YOUR_GCM_API_KEY'));
        $data = array(
            'data' => array('message' => $message),
            'registration_ids' => $registrationIdsArray
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
