<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2016-11-29
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . 'controllers/Api.php';

class Prizebond extends Api {

    public $PATH;

    function __construct() {
        parent::__construct();
        $this->PATH = './images';
        $this->load->model('api_model');
        $this->load->helper('string');
        $this->load->library('verify');
        $this->load->library('encryption');
    }

    public function checkEmail_post() {

        $data = array();
        $this->form_validation->set_rules('api_key', 'API key', 'required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        if ($this->form_validation->run() == TRUE) {
            if ($data = $this->global_model->get_data('user', array('email' => $this->post('email')))) {
                $response['response']['success'] = 'Success';
                $response['response']['data'] = $data;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = 'Email not found';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function userRegistration_post() {

        $response = array();
        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required');
        $this->form_validation->set_rules('name', 'Full Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[user.email]');
        $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|is_unique[user.mobile_number]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        $this->form_validation->set_rules('retype_password', 'Password Confirmation', 'trim|required|matches[password]');

        if ($this->form_validation->run()) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            $this->load->library('encryption');
            $userData['device_uuid'] = $this->input->post('device_uuid');
            $userData['name'] = $this->input->post('name');
            $userData['mobile_number'] = $this->input->post('mobile_number');
            $userData['email'] = $this->input->post('email');
            $encryptedPassword = $this->encryption->encrypt($this->input->post('password'));
            $userData['password'] = $encryptedPassword;
            $userData['created_date_time'] = date('Y-m-d H:i:s');
            // $userData['verify_code'] = random_string('numeric', 4);
            if (!empty($_FILES["image"]["name"])) {
                $allowed = array('jpeg', 'png', 'jpg');
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $output['response']['error'] = 'check your image';
                    $this->response($output, 200);
                    exit;
                }
            }

            if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != '') {
                $image_name = time();
                if (!file_exists($this->PATH)) {
                    mkdir($this->PATH, 0777, true);
                }
                $this->load->library('file_processing');
                $userData['image'] = $this->file_processing->image_upload('image', $this->PATH, 'size[400,400]', 'jpg|jpeg|png', $image_name);
            }

            if ($userInfo = $this->global_model->insert('user', $userData)) {
                $userInfo->user_id = $userInfo->id;
                $userInfo->password = $this->encryption->decrypt($userInfo->password);

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => 1,
                    //CURLOPT_URL => 'http://localhost/PartnerAddon/api/getAPIKey',
                    CURLOPT_URL => 'http://addon.example.com/api/getAPIKey',
                ));
                $resp = curl_exec($curl);
                curl_close($curl);

                $apiObj = json_decode($resp);
                $couponSystemApiKey = $apiObj->api_key;

                $postData = [
                    'api_key' => $couponSystemApiKey,
                    'app_id' => 1,
                    'user_id' => $userInfo->id,
                    'product_id' => 1
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://addon.example.com/api/saveSignUpPurchaseProduct');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_exec($ch);
                curl_close($ch);

                $postData1 = [
                    'api_key' => $couponSystemApiKey,
                    'app_id' => 1,
                    'user_id' => $userInfo->id
                ];

                $ch1 = curl_init();
                curl_setopt($ch1, CURLOPT_URL, 'http://addon.example.com/api/getAUserParchasedFromPrizebondStatus');
                curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch1, CURLOPT_POSTFIELDS, http_build_query($postData1));
                $productList1 = curl_exec($ch1);

                $hash = md5(serialize($userInfo));
                $response['response']['success'] = 'Success';
                $response['response']['data'] = $userInfo;
                $response['response']['purchase_info'] = json_decode($productList1);
                $response['response']['hash'] = $hash;
                $response['response']['env'] = ENVIRONMENT;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = 'Failed to save';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function changePassword_post() {

        $this->form_validation->set_rules('api_key', 'API Key', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('old_password', 'Old Password', 'trim|required');
        $this->form_validation->set_rules('new_password', 'New Password', 'trim|required');
        $this->form_validation->set_rules('retype_password', 'Retype Password', 'trim|required|matches[new_password]');

        if ($this->form_validation->run()) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
            $this->load->library('encryption');

            $email_address = $this->input->post('email');

            if ($result = $this->global_model->get_data('user', array('email' => $email_address))) {

                if ($this->input->post('old_password') == $this->encryption->decrypt($result['password'])) {
                    $updateData['password'] = $this->encryption->encrypt($this->input->post('new_password'));
                    if ($this->global_model->update('user', $updateData, array('email' => $this->input->post('email')))) {
                        $response['response']['success'] = 'Update Successfull';
                        $this->response($response, 200);
                    } else {
                        $response['response']['error'] = 'Failed to Update';
                        $this->response($response, 200);
                    }
                } else {
                    $response['response']['error'] = 'User Password Not Matched.';
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = 'User Email Not Matched.';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }

        $this->response($this->post(), 200);
    }

    public function updateProfile_post() {
        $response = array();
        $this->form_validation->set_rules('api_key', 'api key', 'trim|required');
        $this->form_validation->set_rules('name', 'Full Name', 'trim|required');
        $this->form_validation->set_rules('email', 'email', 'trim|required');
        $this->form_validation->set_rules('mobile_number', 'mobile number', 'trim|required');

        if ($this->form_validation->run()) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            //load the library
            $this->load->library('encryption');

            $userData['name'] = $this->input->post('name');
            $userData['mobile_number'] = $this->input->post('mobile_number');

            if (!empty($_FILES["image"]["name"])) {
                $allowed = array('jpeg', 'png', 'jpg');
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

                if (!in_array($ext, $allowed)) {
                    $output['response']['error'] = 'check your image';
                    $this->response($output, 200);
                    exit;
                }
            }

            if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != '') {
                $image_name = $userData['mobile_number'];
                if (!file_exists($this->PATH)) {
                    mkdir($this->PATH, 0777, true);
                }
                $this->load->library('file_processing');
                $userData['image'] = $this->file_processing->image_upload('image', $this->PATH, 'size[400,400]', 'jpg|jpeg|png', $image_name);
            }

            if ($this->global_model->get_data('user', array('email' => $this->input->post('email')))) {
                if ($this->global_model->update('user', $userData, array('email' => $this->input->post('email')))) {

                    $getAllInfo = $this->global_model->get_data('user', array('email' => $this->input->post('email')));

                    $getAllInfo['password'] = $this->encryption->decrypt($getAllInfo['password']);
                    if (!empty($getAllInfo['image'])) {
                        $getAllInfo['image'] = base_url('images') . '/' . $getAllInfo['image'];
                    }

                    $hash = md5(serialize($getAllInfo));
                    $response['response']['success'] = 'Update Successfull';
                    $response['response']['data'] = $getAllInfo;
                    $response['response']['hash'] = $hash;
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = 'Failed to update';
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = 'Invalid User Email';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function userLogin_post() {

        $this->form_validation->set_rules('device_uuid', 'device uuid', 'trim|required');
        $this->form_validation->set_rules('mobile_number', 'Email', 'trim');
        $this->form_validation->set_rules('email', 'Email', 'trim');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        $this->form_validation->set_rules('unix_timestamp', 'unix_timestamp', 'trim');

        if ($this->form_validation->run()) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
            $this->load->library('encryption');
            $email = $this->input->post('email');
            $mobile_number = $this->input->post('mobile_number');

            if ($email) {
                $credential = $email;
            } elseif ($mobile_number) {
                $credential = $mobile_number;
            } else {
                $response['response']['error'] = 'ইমেইল অথবা মোবাইল নাম্বার আবশ্যক';
                $this->response($response, 200);
            }

            // if ($userInfo = $this->global_model->get_data('user', array('email' => $email))) {
            if ($userInfo = $this->global_model->getUserInfoByCredential($credential)) {

                if ($this->input->post('password') == $this->encryption->decrypt($userInfo['password'])) {

                    $userInfo1 = $this->global_model->get_data('user', array('email' => $email));

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_RETURNTRANSFER => 1,
                        //CURLOPT_URL => 'http://localhost/PartnerAddon/api/getAPIKey',
                        CURLOPT_URL => 'http://addon.example.com/api/getAPIKey',
                    ));
                    $resp = curl_exec($curl);
                    curl_close($curl);

                    $apiObj = json_decode($resp);
                    $couponSystemApiKey = $apiObj->api_key;

                    $where = [
                        'api_key' => $couponSystemApiKey,
                        'app_id' => 1,
                        'user_id' => $userInfo['id']
                    ];

                    $ch2 = curl_init();
                    curl_setopt($ch2, CURLOPT_URL, 'http://addon.example.com/api/getAUserParchasedFromPrizebondStatus');
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query($where));
                    $purchaseInfo = curl_exec($ch2);

                    if ($this->global_model->get_data('user', array('id' => $userInfo['id'], 'verify_status' => 'YES'))) {

                        if ($this->input->post('force_login') == 'no') {

                            if ($userInfo['device_uuid'] != $this->input->post('device_uuid')) {

                                $userInfo['user_id'] = $userInfo['id'];
                                $userInfo['password'] = $this->encryption->decrypt($userInfo['password']);
                                if (!empty($userInfo['image'])) {
                                    $userInfo['image'] = base_url('images') . '/' . $userInfo['image'];
                                }
                                //  $response['response']['error'] = 'You are already registered from another device';
                                $hash = md5(serialize($userInfo));

                                // aikane kaj korte hobe
                                if ($this->global_model->get_data('user', array('id' => $userInfo['id'], 'previous_device_uuid' => $this->input->post('device_uuid')))) {
                                    $response['response']['success'] = 'Erase your bond numbers';
                                } else {
                                    $response['response']['success'] = 'You are already registered from another device';
                                }

                                $response['response']['data'] = $userInfo;
                                $response['response']['product'] = json_decode($purchaseInfo);
                                $response['response']['hash'] = $hash;
                                $response['response']['env'] = ENVIRONMENT;
                                $this->response($response, 200);
                            } else {

                                $userInfo['user_id'] = $userInfo['id'];
                                $userInfo['password'] = $this->encryption->decrypt($userInfo['password']);
                                if (!empty($userInfo['image'])) {
                                    $userInfo['image'] = base_url('images') . '/' . $userInfo['image'];
                                }
                                $userInfo['unix_timestamp'] = $this->input->post('unix_timestamp');
                                $hash = md5(serialize($userInfo));

                                $response['response']['success'] = 'Success';
                                $response['response']['data'] = $userInfo;
                                $response['response']['product'] = json_decode($purchaseInfo);
                                $response['response']['hash'] = $hash;
                                $response['response']['env'] = ENVIRONMENT;
                                $this->response($response, 200);
                            }
                        } elseif ($this->input->post('force_login') == 'yes') {

                            $this->global_model->update('user', array('verify_status' => 'NO'), array('id' => $userInfo['id']));

                            $userInfo['user_id'] = $userInfo['id'];
                            $userInfo['password'] = $this->encryption->decrypt($userInfo['password']);
                            if (!empty($userInfo['image'])) {
                                $userInfo['image'] = base_url('images') . '/' . $userInfo['image'];
                            }
                            $userInfo['unix_timestamp'] = $this->input->post('unix_timestamp');

                            $this->global_model->update('user', array('device_uuid' => $this->input->post('device_uuid'), 'previous_device_uuid' => $userInfo['device_uuid']), array('id' => $userInfo['id']));

                            $this->global_model->update('prizebond_numbers', array('device_uuid' => $this->input->post('device_uuid')), array('user_id' => $userInfo['id']));
                            $this->global_model->update('sync_info', array('device_uuid' => $this->input->post('device_uuid')), array('user_id' => $userInfo['id']));

                            $device_push_id = $this->getPushId($userInfo['device_uuid']);
                            $this->load->library('gcm');
                            $this->gcm->clearRecepients();
                            $registrationIds = array();
                            $registrationIds[] = $device_push_id;
                            $this->gcm->setRecepients($registrationIds);
                            $payloadData = array(
                                'prizebond' => 'Custon content'
                            );

                            $message = 'You have resently logged in from another device. So all prizebond data will be delete soon from this device.';
                            $this->gcm->setData($payloadData);
                            $this->gcm->setMessage($message);
                            $this->gcm->setGroup($message);
                            $this->gcm->send();

                            $hash = md5(serialize($userInfo));
                            $response['response']['success'] = 'Mobile Verification Required';
                            $response['response']['data'] = $userInfo;
                            $response['response']['product'] = json_decode($purchaseInfo);
                            $response['response']['hash'] = $hash;
                            $response['response']['env'] = ENVIRONMENT;
                            $this->response($response, 200);
                        }
                    } else {

                        $userInfo['user_id'] = $userInfo['id'];
                        $userInfo['password'] = $this->encryption->decrypt($userInfo['password']);
                        if (!empty($userInfo['image'])) {
                            $userInfo['image'] = base_url('images') . '/' . $userInfo['image'];
                        }

                        $hash = md5(serialize($userInfo1));
                        $response['response']['success'] = 'Mobile Verification Required';
                        $response['response']['data'] = $userInfo;
                        $response['response']['product'] = json_decode($purchaseInfo);
                        $response['response']['hash'] = $hash;
                        $response['response']['env'] = ENVIRONMENT;
                        $this->response($response, 200);
                    }
                } else {
                    $response['response']['error'] = 'সঠিক পাসওয়ার্ড লিখুন';
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = 'সঠিক ইমেইল ঠিকানা লিখুন';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function sendVerificationCode_post() {
        $data = array();
        $this->form_validation->set_rules('api_key', 'API key', 'required');
        $this->form_validation->set_rules('device_uuid', 'Device UUID', 'required');
        $this->form_validation->set_rules('user_id', 'user id', 'required');
        $this->form_validation->set_rules('mobile_number', 'mobile number', 'required');

        if ($this->form_validation->run() == TRUE) {

            $verifyCode = random_string('numeric', 4);

            $update = array(
                'verify_code' => $verifyCode,
                'mobile_number' => $this->input->post('mobile_number')
            );

            //$this->global_model->update('user', array('verify_code' => $verifyCode), array('id' => $this->input->post('user_id')));
            $this->global_model->update('user', $update, array('id' => $this->input->post('user_id')));

            $userInfo = $this->global_model->get_data('user', array('id' => $this->input->post('user_id')));

            $this->load->library('email');
            $this->email->from('noreply@example.com', 'Prizebond');
            $this->email->to($userInfo['email']);
            $this->email->subject('Prize Bond Verification Code');
            $message = 'Your registration verification code is : ' . $verifyCode;
            $this->email->message($message);
            $this->email->send();

            $senderText = 'PRIZEBOND';
            $smsText = 'This is your verification code : ' . $verifyCode;
            $gsm = $this->input->post('mobile_number');
            $SMSResponse = $this->verify->sendVarificationCodeToUser($senderText, $smsText, $gsm);

            if ($SMSResponse) {
                $this->response(array('response' => array('success' => 'Verification code sent')), 200);
            } else {
                $response['response']['error'] = 'Send failed';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function verifyMobileNumber_post() {

        $data = array();
        $this->form_validation->set_rules('api_key', 'API key', 'required');
        $this->form_validation->set_rules('device_uuid', 'Device UUID', 'required');
        $this->form_validation->set_rules('user_id', 'user id', 'required');
        $this->form_validation->set_rules('verification_code', 'Verification Code', 'required');
        if ($this->form_validation->run() == TRUE) {
            if ($this->api_model->verifyUser($this->input->post('device_uuid'), $this->input->post('user_id'), $this->input->post('verification_code'))) {
                $this->response(array('response' => array('success' => 'Verification successful')), 200);
            } else {
                $this->response(array('response' => array('error' => 'Invalide varification code')));
            }
        } else {
            $this->response(array('response' => array('error' => 'Field required')));
        }
    }

    public function getSeriesList_post() {
        $response = array();
        $isParamsValid = $this->validateCommonParam($this->post());
        if (is_string($isParamsValid)) {
            $this->response(array('error' => str_replace("\n", ' ', strip_tags($isParamsValid))));
        } elseif (is_bool($isParamsValid)) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            $seriesList = $this->getSeriesList();

            if (!empty($seriesList)) {
                $hash = md5(serialize($seriesList));
                $response['response']['success'] = 'Success';
                $response['response']['data'] = $seriesList;
                $response['response']['hash'] = $hash;
                $response['response']['env'] = ENVIRONMENT;
                $this->response($response, 200);
            } else {
                $output['response']['error'] = 'Data not found';
                $this->response($output, 200);
            }
        }
    }

    public function savePrizebondNumbers_post() {

        $response = array();
        $isParamsValid = $this->validateCommonParam($this->post());
        if (is_string($isParamsValid)) {
            $this->response(array('error' => str_replace("\n", ' ', strip_tags($isParamsValid))));
        } elseif (is_bool($isParamsValid)) {

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                //CURLOPT_URL => 'http://localhost/PartnerAddon/api/getAPIKey',
                CURLOPT_URL => 'http://addon.example.com/api/getAPIKey',
            ));
            $resp = curl_exec($curl);
            curl_close($curl);

            $apiObj = json_decode($resp);
            $couponSystemApiKey = $apiObj->api_key;

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);


            $postData1 = [
                'api_key' => $couponSystemApiKey,
                'app_id' => 1,
                'user_id' => $this->post('user_id')
            ];

            $ch1 = curl_init();
            curl_setopt($ch1, CURLOPT_URL, 'http://addon.example.com/api/getAUserParchasedFromPrizebondStatus');
            curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch1, CURLOPT_POSTFIELDS, http_build_query($postData1));
            $productList1 = curl_exec($ch1);
            $dataObject = json_decode($productList1);

            $totalBondCapacity = $dataObject->total_bond;

            $userExistingBondNumber = $this->api_model->countUserBondNumber($this->post('user_id'));

            $userExistingBondNumber += count($this->post('prizebond_numbers'));

            //logic ok
            if ($totalBondCapacity < $userExistingBondNumber) {
                $output['response']['error'] = 'আপনার প্রাইজবন্ড নম্বর সংরক্ষণ করার ধারন ক্ষমতা শেষ';
                $this->response($output, 200);
            }

            if ($this->savePrizeBondNumbers($this->post('user_id'), $this->post('device_uuid'), $this->post('prizebond_numbers'), $this->post('bond_series'))) {

                foreach ($this->post('prizebond_numbers') as $number) {
                    $drawNumber = '';
                    $message = '';
                    if ($bondNumberInfo = $this->global_model->get_data('first_prize_data', array('winner_bond_num' => $number))) {
                        $drawNumber = $number;
                        $message = "$number number is win. Please check Prizebond Draw Result";
                    } elseif ($bondNumberInfo = $this->global_model->get_data('second_prize_data', array('winner_bond_num' => $number))) {
                        $drawNumber = $number;
                        $message = "$number number is win. Please check Prizebond Draw Result";
                    } elseif ($bondNumberInfo = $this->global_model->get_data('third_prize_data', array('winner_bond_num' => $number))) {
                        $drawNumber = $number;
                        $message = "$number number is win. Please check Prizebond Draw Result";
                    } elseif ($bondNumberInfo = $this->global_model->get_data('fourth_prize_data', array('winner_bond_num' => $number))) {
                        $drawNumber = $number;
                        $message = "$number number is win. Please check Prizebond Draw Result";
                    } elseif ($bondNumberInfo = $this->global_model->get_data('fifth_prize_data', array('winner_bond_num' => $number))) {
                        $drawNumber = $number;
                        $message = "$number number is win. Please check Prizebond Draw Result";
                    }

                    if (!empty($drawNumber)) {

                        $url = 'http://api.example.com/cron/curlSendGCMPushToWinnerDevice';
                        $param = array(
                            'device_uuid' => $this->post('device_uuid'),
                            'message' => $message
                        );

                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_URL => $url,
                            CURLOPT_POSTFIELDS => http_build_query($param),
                            CURLOPT_POST => 1
                        ));
                        curl_exec($curl);
                    }
                }

                $response['response']['success'] = 'Save Successfull';
                $this->response($response, 200);
            } else {
                $output['response']['error'] = 'Failed To Save';
                $this->response($output, 200);
            }
        }
    }

    public function deletePrizebondNumber_post() {

        $response = array();
        $isParamsValid = $this->validateCommonParam($this->post());
        if (is_string($isParamsValid)) {
            $this->response(array('error' => str_replace("\n", ' ', strip_tags($isParamsValid))));
        } elseif (is_bool($isParamsValid)) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
            if ($this->deletePrizeBondNumbers($this->post('device_uuid'), $this->post('prizebond_number'))) {
                $response['response']['success'] = 'Delete Successfull';
                $this->response($response, 200);
            } else {
                $output['response']['error'] = 'Failed To Delete';
                $this->response($output, 200);
            }
        }
    }

    public function updatePrizebondNumber_post() {

        $data = array();
        $this->form_validation->set_rules('api_key', 'API key', 'required');
        $this->form_validation->set_rules('device_uuid', 'Device UUID', 'trim');
        $this->form_validation->set_rules('user_id', 'user id', 'trim|required');
        $this->form_validation->set_rules('new_prizebond_number', 'new prizebond number', 'trim|required');
        $this->form_validation->set_rules('old_prizebond_number', 'old prizebond number', 'trim|required');
        $this->form_validation->set_rules('new_series', 'New Series', 'trim|required');
        $this->form_validation->set_rules('old_series', 'Old Series', 'trim|required');

        if ($this->form_validation->run() == TRUE) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            if (!empty($_FILES["image"]["name"])) {
                $allowed = array('jpeg', 'png', 'jpg');
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);

                if (!in_array($ext, $allowed)) {
                    $output['response']['error'] = 'check your image';
                    $this->response($output, 200);
                    exit;
                }
            }

            if (isset($_FILES["image"]["name"]) && $_FILES["image"]["name"] != '') {
                $image_name = time();
                if (!file_exists($this->PATH)) {
                    mkdir($this->PATH, 0777, true);
                }
                $this->load->library('file_processing');
                $bond_image = $this->file_processing->image_upload('image', $this->PATH, 'size[400,400]', 'jpg|jpeg|png', $image_name);
            }


            $updateData = array(
                'device_uuid' => $this->input->post('device_uuid'),
                'prizebond_number' => $this->input->post('new_prizebond_number')
            );

            $syncData = array(
                'device_uuid' => $this->input->post('device_uuid'),
                'bond_series' => $this->input->post('new_series'),
                'bond_added_date' => date('Y-m-d'),
                'image' => $bond_image,
                'bond_number' => $this->input->post('new_prizebond_number')
            );

            if ($this->global_model->update('prizebond_numbers', $updateData, array('user_id' => $this->input->post('user_id'), 'prizebond_number' => $this->input->post('old_prizebond_number')))) {
                if ($this->global_model->update('sync_info', $syncData, array('user_id' => $this->input->post('user_id'), 'bond_series' => $this->input->post('old_series'), 'bond_number' => $this->input->post('old_prizebond_number')))) {
                    $response['response']['success'] = 'Updated  Successfully';
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = 'Failed to Update Sync info';
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = 'Failed to Update';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function getListOfDraw_post() {
        $response = array();
        $isParamsValid = $this->validateCommonParam($this->post());
        if (is_string($isParamsValid)) {
            $this->response(array('error' => str_replace("\n", ' ', strip_tags($isParamsValid))));
        } elseif (is_bool($isParamsValid)) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            $drawList = $this->getListOfDraw();
            if (!empty($drawList)) {
                $hash = md5(serialize($drawList));
                $response['response']['success'] = 'Success';
                $response['response']['data'] = $drawList;
                $response['response']['hash'] = $hash;
                $response['response']['env'] = ENVIRONMENT;
                $this->response($response, 200);
            } else {
                $output['response']['error'] = 'তথ্য পাওয়া যায়নি';
                $this->response($output, 200);
            }
        }
    }

    public function getResultOfADraw_post() {
        $response = array();
        $isParamsValid = $this->validateCommonParam($this->post());
        if (is_string($isParamsValid)) {
            $this->response(array('error' => str_replace("\n", ' ', strip_tags($isParamsValid))));
        } elseif (is_bool($isParamsValid)) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            if (!$this->post('draw_id')) {
                $output['response']['error'] = 'Draw id requered';
                $this->response($output, 200);
            }

            $drawList = $this->getResultOfADraw($this->post('draw_id'));
            if (!empty($drawList)) {
                $hash = md5(serialize($drawList));
                $response['response']['success'] = 'Success';
                $response['response']['data'] = $drawList;
                $response['response']['hash'] = $hash;
                $response['response']['env'] = ENVIRONMENT;
                $this->response($response, 200);
            } else {
                $output['response']['error'] = 'তথ্য পাওয়া যায়নি';
                $this->response($output, 200);
            }
        }
    }

    public function sendPushNotification_post() {

        $isParamsValid = $this->validateCommonParamForSendPush($this->post());
        if (is_string($isParamsValid)) {
            $this->response(array('error' => str_replace("\n", ' ', strip_tags($isParamsValid))));
        } elseif (is_bool($isParamsValid)) {

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            $device_push_id = '';

            if ($this->post('device_uuid')) {
                $device_push_id = $this->getPushId($this->post('device_uuid'));
            }
            if ($this->post('device_push_id')) {
                $device_push_id = $this->post('device_push_id');
            }

            $message = $this->input->post('message');
            if ($message) {
                $this->load->library('gcm');
                $this->gcm->clearRecepients();
                $registrationIds = array();
                $registrationIds[] = $device_push_id;

                $this->gcm->setRecepients($registrationIds);
                $payloadData = array(
                    'prizebond' => 'Custon content'
                );

                $this->gcm->setData($payloadData);
                $this->gcm->setMessage($message);
                $this->gcm->setGroup($message);
                $this->gcm->send();
                $gcmStatus = $this->gcm->status;
                $output['response']['gcm_status'] = $gcmStatus;
                $output['response']['message'] = $message;
                $this->response($output, 200);
            } else {
                $output['response']['error'] = 'Message Not Send';
                $this->response($output, 200);
            }
        }
    }

    public function forgotPassword_post() {

        $this->form_validation->set_rules('api_key', 'Api key', 'trim|required');
        $this->form_validation->set_rules('email_address', 'Email', 'trim|required');

        if ($this->form_validation->run()) {
            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
            $this->load->library('encryption');
            $email = $this->input->post('email_address');

            if ($this->global_model->doesExit('user', array('email' => $email))) {

                $existingPassword = $this->global_model->get_data('user', array('email' => $email));

                $decryptedPassword = $this->encryption->decrypt($existingPassword['password']);

                $this->load->library('email');
                $this->email->from('no-reply@email.com', 'Prize Bond');
                $this->email->to($email);
                $this->email->subject('Forgot Password');
                $message = 'Your password is ' . $decryptedPassword;
                $this->email->message($message);
                //$this->email->send();

                if ($this->email->send()) {
                    $response['response']['success'] = 'Success';
                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = 'সঠিক পাসওয়ার্ড লিখুন';
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = 'সঠিক ইমেইল ঠিকানা লিখুন';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function getaRegisteredUser_post() {


        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run()) {
            $data = array();

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
            $this->load->library('encryption');
            $email = $this->input->post('email');

            if ($existingPassword = $this->global_model->get_data('user', array('email' => $email))) {

                if ($this->input->post('password') == $this->encryption->decrypt($existingPassword['password'])) {

                    $decryptedPassword = $this->encryption->decrypt($existingPassword['password']);

                    $data['id'] = $existingPassword['id'];
                    $data['name'] = $existingPassword['name'];
                    $data['email'] = $existingPassword['email'];
                    $data['mobile_number'] = $existingPassword['mobile_number'];
                    $data['password'] = $decryptedPassword;

                    if (!empty($existingPassword['image'])) {
                        $data['image'] = base_url('images') . '/' . $existingPassword['image'];
                    }

                    $data['verify_code'] = $existingPassword['verify_code'];
                    $data['status'] = $existingPassword['status'];
                    $data['verify_status'] = $existingPassword['verify_status'];

                    $hash = md5(serialize($data));
                    $response['response']['success'] = 'Success';
                    $response['response']['data'] = $data;
                    $response['response']['hash'] = $hash;

                    $this->response($response, 200);
                } else {
                    $response['response']['error'] = 'সঠিক পাসওয়ার্ড লিখুন';
                    $this->response($response, 200);
                }
            } else {
                $response['response']['error'] = 'সঠিক ইমেইল ঠিকানা লিখুন';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function saveDeviceInfo_post() {

        $this->form_validation
                ->set_rules('api_key', 'API Key', 'trim|required')
                ->set_rules('device_uuid', 'Device uuid', 'trim|required')
                ->set_rules('device_density', 'Device Density', 'trim|required')
                ->set_rules('device_width', 'Device Width', 'trim|required')
                ->set_rules('device_height', 'Device Height', 'trim|required')
                ->set_rules('device_token', 'Device Token', 'trim|required')
                ->set_rules('device_type', 'Device Type', 'trim')
                ->set_rules('device_imei', 'Device imei', 'trim')
                ->set_rules('app_version_name', 'App Version Name', 'trim')
                ->set_rules('app_version_code', 'App Version Code', 'trim');

        if ($this->form_validation->run()) {

            $data = array();
            $requestedData = array();

            $secretKey = "*&^%$";
            $this->verifyAPIKey($this->post('api_key'), $secretKey);

            $requestedData['device_uuid'] = $this->post('device_uuid');
            $requestedData['device_density'] = $this->post('device_density');
            $requestedData['device_width'] = $this->post('device_width');
            $requestedData['device_height'] = $this->post('device_height');
            $requestedData['device_token'] = $this->post('device_token');
            $requestedData['device_type'] = $this->post('device_type');
            $requestedData['device_imei'] = $this->post('device_imei');
            $requestedData['app_version_name'] = $this->post('app_version_name');
            $requestedData['app_version_code'] = $this->post('app_version_code');


            if (($this->api_model->updateDeviceInfo($requestedData) == TRUE) && ($this->api_model->updateDeviceActivityLog($requestedData) == TRUE)) {
                $output['response']['success'] = 'Device Info updated';
                $this->response($output, 200);
            } else {
                $output['response']['error'] = 'Device Info not updated';
                $this->response($output, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function getIpAddress_post() {
        $ip = $this->input->ip_address();
        if ($ip) {
            $data['ip_address'] = $ip;
            $response['response']['success'] = 'success';
            $response['response']['data'] = $data;
            $this->response($response, 200);
        } else {
            $response['response']['error'] = 'Invalid requested';
            $this->response($response, 200);
        }
    }

    public function sync_post() {

        $this->load->library('file_processing');
        //$this->form_validation->set_rules('user_id', 'user id', 'trim|required');
        $syncInfo = $this->input->post('sync_info');
//        if (!empty($syncInfo)) {
//            // Loop through hotels and add the validation
//            foreach ($syncInfo as $id => $data) {
//                $this->form_validation->set_rules('sync_info[' . $id . '][bond_series]', 'bond series', 'trim|required');
//                $this->form_validation->set_rules('sync_info[' . $id . '][bond_number]', 'bond number', 'trim|required');
//                //$this->form_validation->set_rules('sync_info[' . $id . '][bond_image]', 'bond number', 'trim|required');
//              //  $this->form_validation->set_rules('sync_info[' . $id . '][bond_added_date]', 'bond added date', 'trim');
//                $this->form_validation->set_rules('sync_info[' . $id . '][bond_note]', 'bond added date', 'trim|required');
//            }
//        }
        //if ($this->form_validation->run()) {

        $secretKey = "*&^%$";
        if ($this->post('api_key')) {
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
        }


        $device_uuid = $this->input->post('device_uuid');
        $userId = $this->input->post('user_id');
        $syncInfo = $this->input->post('sync_info');

        if (is_array($syncInfo) && count($syncInfo) > 0) {

            $ids = array();
            foreach ($syncInfo as $key => $syncData) {

                $userData['device_uuid'] = $device_uuid;
                $userData['user_id'] = $userId;
                $userData['bond_series'] = $syncData['bond_series'];
                $userData['bond_number'] = $syncData['bond_number'];
                $userData['bond_note'] = $syncData['bond_note'];

                if (!empty($_FILES['image']['name'][$key])) {

                    $_FILES['photo']['name'] = $_FILES['image']['name'][$key];
                    $_FILES['photo']['type'] = $_FILES['image']['type'][$key];
                    $_FILES['photo']['tmp_name'] = $_FILES['image']['tmp_name'][$key];
                    $_FILES['photo']['size'] = $_FILES['image']['size'][$key];
                    $_FILES['photo']['error'] = $_FILES['image']['error'][$key];
                    $imageName = $syncData['bond_number'];
                    // upload photo
                    if (!file_exists($this->PATH)) {
                        mkdir($this->PATH, 0777, true);
                    }
                    $userData['image'] = $this->file_processing->image_upload('photo', $this->PATH, 'size[600,400]', 'jpg|jpeg|png', $imageName);
                } else {
                    $userData['image'] = '';
                }

                $userData['bond_added_date'] = $syncData['bond_added_date'];

                if ($signleSyncInfo = $this->global_model->get_data('sync_info', array('user_id' => $userId, 'bond_number' => $syncData['bond_number']))) {
                    //$this->global_model->update('sync_info', $userData, array('id' => $signleSyncInfo['id']));
                    $this->global_model->update('sync_info', $userData, array('user_id' => $userId, 'bond_number' => $syncData['bond_number']));
                    $ids[] = $signleSyncInfo['id'];
                } else {
                    $insertId = $this->api_model->saveSyncInfo($userData);
                    $ids[] = $insertId;
                }
            }

            if ($result = $this->api_model->getResponse($ids, $userId)) {
                $hash = md5(serialize($result));
                $response['response']['success'] = 'Update Successfull';
                $response['response']['data'] = $result;
                $response['response']['hash'] = $hash;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = 'সিঙ্ক সফল হয়েছে কিন্তু ডিভাইস এর ডাটাবেজে সংরক্ষণ করার মত কোনো তথ্য পাওয়া যায়নি।';
                $this->response($response, 200);
            }
        } else {

            $result = $this->api_model->getUserAllBondInfo($userId);
            if ($result) {
                $hash = md5(serialize($result));
                $response['response']['success'] = 'Update Successfull';
                $response['response']['data'] = $result;
                $response['response']['hash'] = $hash;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = 'Data not found';
                $this->response($response, 200);
            }
        }
//        } else {
//            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
//            $this->response($response, 200);
//        }
    }

    public function syncPrizbond_post() {

        $da = $this->input->post('data');

//        $this->load->library('email');
//        $this->email->from('admin@example.com', 'Priz Bond');
//        $this->email->to('ops-team@example.com');
//        $this->email->subject('Priz Bond');
//        $this->email->message($da);
//        $this->email->send();

        $this->load->library('file_processing');
        $postData = $this->input->post();

        $device_uuid = $this->input->post('device_uuid');
        $userId = $this->input->post('user_id');
        $action = $this->input->post('action');
        $last_sync_hash = $this->input->post('last_sync_hash');
        $data = $this->input->post('data');

        $secretKey = "*&^%$";
        if ($this->post('api_key')) {
            $this->verifyAPIKey($this->post('api_key'), $secretKey);
        }

        // case 1
        if ($action == 'start' && !empty($data)) {
            $response['response']['error'] = 'Data must be empty when action is start';
            $this->response($response, 200);
        }
        if ($action == 'start' && empty($data)) {
            if ($bondsNumber = $this->api_model->updateSyncInfo($userId)) {
                $response['response']['success'] = 'Success';
                $response['response']['data'] = "You have $bondsNumber bonds stored in our server.";
                $response['response']['next_action'] = "upload";
                $this->response($response, 200);
            } else {
                $response['response']['success'] = "You have 0 bond store in our server.";
                $this->response($response, 200);
            }
        }

        //case 2
        if ($action == 'upload' && empty($data)) {
            $response['response']['error'] = 'No uploaded data found';
            $this->response($response, 200);
        }
        $final = array();
        if ($action == 'upload' && !empty($data)) {
            $data = base64_decode($data);
            $data = explode(',', $data);
            $final = array();
            foreach ($data as $info) {
                $ex = explode(':', $info);
                $final[] = $ex;
            }
        }

        if (is_array($final) && count($final) > 0) {
            $ids = array();
            foreach ($final as $key => $data) {
                $userData['device_uuid'] = $device_uuid;
                $userData['user_id'] = $userId;

                if (!empty($data[0])) {
                    $userData['bond_series'] = $data[0];
                } else {
                    $response['response']['error'] = 'Bond series required';
                    $this->response($response, 200);
                }

                if (!empty($data[1])) {
                    $userData['bond_number'] = $data[1];
                } else {
                    $response['response']['error'] = 'Bond number required';
                    $this->response($response, 200);
                }

                $userData['bond_added_date'] = !empty($data[2]) ? $data[2] : date('Y-m-d H:i:s');
                if (!empty($data[3])) {
                    $userData['bond_note'] = $data[3];
                }

                $userData['sync_status'] = 'uploaded';

                if (!empty($_FILES['image']['name'][$key])) {
                    $_FILES['photo']['name'] = $_FILES['image']['name'][$key];
                    $_FILES['photo']['type'] = $_FILES['image']['type'][$key];
                    $_FILES['photo']['tmp_name'] = $_FILES['image']['tmp_name'][$key];
                    $_FILES['photo']['size'] = $_FILES['image']['size'][$key];
                    $_FILES['photo']['error'] = $_FILES['image']['error'][$key];
                    $imageName = $data[1];
                    // upload photo
                    if (!file_exists($this->PATH)) {
                        mkdir($this->PATH, 0777, true);
                    }
                    $userData['image'] = $this->file_processing->image_upload('photo', $this->PATH, 'size[600,400]', 'jpg|jpeg|png', $imageName);
                } else {
                    $userData['image'] = '';
                }

                //$uploadedIDs = array();
                //$machesIDs = array();
                //$userData['bond_added_date'] = $syncData['bond_added_date'];
                if ($signleSyncInfo = $this->global_model->get_data('sync_info', array('user_id' => $userId, 'bond_series' => $data[0], 'bond_number' => $data[1]))) {
                    if (!empty($userData['image'])) {
                        $updateData = array(
                            'device_uuid' => $device_uuid,
                            'user_id' => $userId,
                            'bond_note' => !empty($userData['bond_note']) ? $userData['bond_note'] : '',
                            'bond_added_date' => !empty($userData['bond_added_date']) ? $userData['bond_added_date'] : '',
                            'image' => $userData['image'],
                            'sync_status' => 'matched',
                        );
                    } else {
                        $updateData = array(
                            'device_uuid' => $device_uuid,
                            'user_id' => $userId,
                            'bond_note' => !empty($userData['bond_note']) ? $userData['bond_note'] : '',
                            'bond_added_date' => !empty($userData['bond_added_date']) ? $userData['bond_added_date'] : '',
                            'sync_status' => 'matched',
                        );
                    }

                    $this->global_model->update('sync_info', $updateData, array('user_id' => $userId, 'bond_series' => $data[0], 'bond_number' => $data[1]));
                    $machesIDs[] = $signleSyncInfo['id'];
                } else {
                    $insertId = $this->api_model->saveSyncInfo($userData);
                    $uploadedIDs[] = $insertId;
                }
            }

            $matchedResult = '';
            $uploadedResult = '';
            if (!empty($machesIDs)) {
                $matchedResult = $this->api_model->getMatcheData($machesIDs, $userId);
            }

            if (!empty($uploadedIDs)) {
                $uploadedResult = $this->api_model->getUploadedData($uploadedIDs, $userId);
            }

            $response['response']['success'] = 'Success';
            $response['response']['matched_bonds'] = !empty($matchedResult) ? base64_encode($matchedResult) : '';
            $response['response']['uploaded_bonds'] = !empty($uploadedResult) ? base64_encode($uploadedResult) : '';
            $this->response($response, 200);
        }

        //case 3
        if ($action == 'download') {
            if ($data = $this->api_model->getBondsInfoWhereStatusReady($userId, $last_sync_hash, $device_uuid)) {
                $hash = md5($data['data']);
                $this->api_model->updateUploadedBondStatus($data['ids'], $hash);
                // $this->global_model->update('sync_info', array('sync_hash' => $hash), array('user_id' => $userId));
                $response['response']['success'] = 'Success';
                $response['response']['data'] = base64_encode($data['data']);
                $response['response']['synch_hash'] = $hash;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = 'Data Not Found';
                $this->response($response, 200);
            }
        }

        //case 4
        if ($action == 'completed') {
            if ($this->global_model->update('sync_info', array('device_uuid' => $device_uuid, 'sync_status' => 'completed'), array('user_id' => $userId))) {
                // if ($this->global_model->update('sync_info', array('sync_status' => 'completed'), array('user_id' => $userId))) {
                $response['response']['success'] = 'Success';
                $this->response($response, 200);
            }
        }
    }

    public function testRegistration_post() {
        $this->response($this->input->post(), 200);
    }

    public function getAppDetails_post() {


        // $this->form_validation->set_rules('app_name', 'App Name', 'trim|required');
        $this->form_validation->set_rules('device_density', 'Device density', 'trim');

        if ($this->form_validation->run()) {
            $data = array();

            $secretKey = "*&^%$";
           // $this->verifyAPIKey($this->post('api_key'), $secretKey);
            $device_density = $this->input->post('device_density');

            if ($existingAppInformation = $this->api_model->getAppDetails($device_density)) {

                $response['response']['success'] = 'Success';
                $response['response']['data'] = $existingAppInformation;
                $this->response($response, 200);
            } else {
                $response['response']['error'] = 'কোন তথ্য পাওয়া যায় নি';
                $this->response($response, 200);
            }
        } else {
            $response['response']['error'] = str_replace("\n", ' ', strip_tags(validation_errors()));
            $this->response($response, 200);
        }
    }

    public function generateCsv($data, $delimiter = ',', $enclosure = '"') {
        $contents = '';
        $handle = fopen('php://temp', 'r+');
        foreach ($data as $line) {
            fputcsv($handle, $line, $delimiter, $enclosure);
        }
        rewind($handle);
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        return $contents;
    }

    function base64_to_jpeg($base64_string, $output_file) {
        $ifp = fopen($output_file, "wb");
        fwrite($ifp, base64_decode($base64_string));
        fclose($ifp);
        return $output_file;
    }

}
