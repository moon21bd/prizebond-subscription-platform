<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-08-09
 */

set_time_limit(0);
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('gcm');
        $this->load->model('cron_model');
        $this->load->model('api_model');
        $this->load->model('global_model');
    }

    public function index() {
//log_message('info', 'Inactive Android Devices found', true);
    }

    public function curlSendGCMPushToWinnerDevice() {

        $devicePushId = $this->global_model->getPushIdByDeviceUuid($this->post('device_uuid'));

        if ($devicePushId) {
            $this->load->library('gcm');
            $this->gcm->clearRecepients();
            $registrationIds = array();
            $registrationIds[] = (string) $devicePushId['device_push_id'];
            $this->gcm->setRecepients($registrationIds);
//$payloadData = array('prizebond' => '');
//$this->gcm->setData($payloadData);
            $this->gcm->setMessage($this->post('message'));
            $this->gcm->setGroup(md5($this->post('message')));
            $this->gcm->send();
//$this->gcm->status;
        }
        return TRUE;
    }

    //run this function in every minitue
    public function determinePrizebondWinners() {
        $drawBondInfoArrayList = $this->cron_model->getDrawInfo();


        $message = '';
        if ($drawBondInfoArrayList) {

            foreach ($drawBondInfoArrayList as $drawInfoArray) {
                $drawSeries = explode(',', $drawInfoArray['series']);

                foreach ($drawInfoArray['bonds'] as $prizePostion => $bondArray) {

                    $bonds = join(',', $bondArray);
                    $userWinBondInfoArray = $this->cron_model->getUserWinBondsInfo($bonds);
                    if ($userWinBondInfoArray) {
                        foreach ($userWinBondInfoArray as $bondInfo) {

                            if (in_array($bondInfo->bond_series, $drawSeries)) {
                                if (!$this->api_model->doesExist('user_prizebond_won_list', array('bond_number' => $bondInfo->bond_number))) {
                                    $winBondInfo = array(
                                        'user_id' => $bondInfo->user_id,
                                        'bond_number' => $bondInfo->bond_number,
                                        'bond_series' => $bondInfo->bond_series,
                                        'user_prizebond_id' => $bondInfo->id,
                                        'prizebond_result_info_id' => $drawInfoArray['prizebond_result_info_id'],
                                        'prize_position' => $prizePostion,
                                        'prize_amount' => $this->cron_model->getPrizeAmount($prizePostion, $drawInfoArray['draw']),
                                    );
                                    $updateData = array('prize_position' => $prizePostion, 'prize_amount' => $winBondInfo['prize_amount'], 'draw_number' => $drawInfoArray['draw']);
                                    $this->cron_model->updateUserBondInfo($updateData, $bondInfo->id);
                                    $this->cron_model->winBondInfo($winBondInfo);
                                    //send gcm to winners device
                                    //$message = "Prizebond number " . $winBondInfo['bond_number'] . " is win. Please check Prizebond Draw Result.";
                                    //$message = "Your added Prizebond number" . $winBondInfo['bond_number'] . " won " . $prizePostion . " prize in " . $drawInfoArray['draw'] . "th draw result. Congratulations.";


                                    $drawNumber = $this->convertEnglishNumberIntoBanglaNumber($drawInfoArray['draw']);
                                    //$series = $this->convertEnglishleterToBanglaleter($winBondInfo['bond_series']);
                                    $bondNumber = $this->convertEnglishNumberIntoBanglaNumber($winBondInfo['bond_number']);
                                    $prizeAmount = $this->convertEnglishNumberIntoBanglaNumber($winBondInfo['prize_amount']);
                                    $prizePostion = $this->convertPrizePositionEnglishToBangla($prizePostion);

                                    $message = $drawNumber . "তম ড্রতে আপনার প্রাইজ বণ্ড # " . $winBondInfo['bond_series'] . " " . $bondNumber . ", " . $prizePostion . " পুরষ্কার " . $prizeAmount . " টাকা জিতেছে।
";
                                    $this->sendGcmToWinnerDevice($bondInfo->user_id, $message);
                                }
                            }
                        }
                    }
                }
            }
        }

        exit;
    }

    private function sendGcmToWinnerDevice($userId, $message) {
        $this->load->library('gcm');
        $this->gcm->clearRecepients();
        $registrationIds = array();

        $devicePushInfo = $this->global_model->get_data('device_info', array('user_id' => $userId));
        $registrationIds[] = (string) $devicePushInfo['device_push_id'];

        $this->gcm->setRecepients($registrationIds);
        $payloadData = array(
            'prizebond' => 'Custom content'
        );
        $this->gcm->setData($payloadData);
        $this->gcm->setMessage($message);
        $this->gcm->setGroup($message);

        if ($this->gcm->send()) {
            $this->global_model->update('user_prizebond_won_list', array('push_send' => 'yes'));
        } else {
            echo 'Failed to send push notification';
        }
    }

    public function sendPushNotificationToAllUsers() {

        $pushIdArray = $this->cron_model->getAllDevicePushId();

        //$message = 'There is new version of your Prizebond App released. Please install it.';
        $message = 'প্রাইজ বন্ড চেকার অ্যাপের নতুন ভার্সন এসেছে। সঠিক তথ্য পেতে আপনার অ্যাপটি আপডেট করুন।';
        if ($pushIdArray) {
            $this->load->library('gcm');
            $this->gcm->clearRecepients();

            $this->gcm->setRecepients($pushIdArray);
            $this->gcm->setMessage($message);
            $this->gcm->setGroup(md5($message));
            $this->gcm->send();
            $this->handleListOfInactiveAndroidDevices($this->gcm->messagesStatuses);

            exit;
        }
    }

    public function sendGCMPushToAllUsersWhenNewDrawResultAdded() {

        echo "<pre>";
        ob_start();
        $offset = 0;
        $limit = 500;
        $query = $this->db->query("SELECT COUNT(id) AS total FROM device_info WHERE device_status='active' AND gcm_status = 'active'");
        $total = $query->result()[0]->total;
        
        echo "Total devices : ".$total."<br>";
        echo "Process started on ".date('l jS \of F Y h:i:s A')."<br><br><br>";
        
        $message = 'নতুন প্রাইজ বন্ড রেজাল্ট পাবলিশ হয়েছে। আপনার বন্ডের ফলাফল চেক করুন।';

        while ($total >= $offset) {
            $registrationIds = array();
            $query2 = $this->db->query("SELECT device_push_id FROM device_info WHERE device_status='active' AND gcm_status = 'active' LIMIT $limit OFFSET $offset");
            if ($query2->num_rows()) {
                foreach ($query2->result() as $row) {
                    if(strlen($row->device_push_id) > 100){
                        $registrationIds[] = (string) $row->device_push_id;
                    }
                }
            }
            
            if(count($registrationIds) > 0){
                $response = $this->sendNotification($registrationIds, $message);
                $this->processGcmResponse($registrationIds, $response);
                $offset = $offset + $limit;
                echo "message sent to ". $offset." devices of ".$total." on ".date('l jS \of F Y h:i:s A')."<br>";
                flush();
                ob_flush();
                sleep(2);
            }
            else {
                echo "No data found <br>";
                flush();
                ob_flush();
                break;
            }
        }
    }
    public function sendPusshToAllUsersToUpgradeLatestApp() {

        echo "<pre>";
        ob_start();
        $offset = 0;
        $limit = 500;
        $query = $this->db->query("SELECT COUNT(id) AS total FROM device_info WHERE app_version_code < 70 AND device_status='active' AND gcm_status = 'active'");
        $total = $query->result()[0]->total;
        
        echo "Total devices : ".$total."<br>";
        echo "Process started on ".date('l jS \of F Y h:i:s A')."<br><br><br>";
        
        $message = 'Dear valued user, Please download latest PrizeBond app from Google Play Store.';

        while ($total >= $offset) {
            $registrationIds = array();
            $query2 = $this->db->query("SELECT device_push_id FROM device_info WHERE app_version_code < 70 AND device_status='active' AND gcm_status = 'active' LIMIT $limit OFFSET $offset");
            if ($query2->num_rows()) {
                foreach ($query2->result() as $row) {
                    if(strlen($row->device_push_id) > 100){
                        $registrationIds[] = (string) $row->device_push_id;
                    }
                }
            }
            
            if(count($registrationIds) > 0){
                $response = $this->sendNotification($registrationIds, $message);
                $this->processGcmResponse($registrationIds, $response);
                $offset = $offset + $limit;
                echo "message sent to ". $offset." devices of ".$total." on ".date('l jS \of F Y h:i:s A')."<br>";
                flush();
                ob_flush();
                sleep(2);
            }
            else {
                echo "No data found <br>";
                flush();
                ob_flush();
                break;
            }
        }
    }

    public function sendGCMPushToWinnnerDevice() {

        $userPrizebondNumbers = $this->global_model->getPrizeBondInfo();

        if (is_array($userPrizebondNumbers) && count($userPrizebondNumbers > 0)) {

            foreach ($userPrizebondNumbers as $number) {

                $drawNumber = '';
                $message = '';
                if ($bondNumberInfo = $this->global_model->get_data('first_prize_data', array('winner_bond_num' => $number->bond_number))) {
                    $drawNumber = $number->bond_number;
                    $message = "$drawNumber number is win. Please check Prizebond Draw Result.";
                } elseif ($bondNumberInfo = $this->global_model->get_data('second_prize_data', array('winner_bond_num' => $number->bond_number))) {
                    $drawNumber = $number->bond_number;
                    $message = "$drawNumber number is win. Please check Prizebond Draw Result.";
                } elseif ($bondNumberInfo = $this->global_model->get_data('third_prize_data', array('winner_bond_num' => $number->bond_number))) {
                    $drawNumber = $number->bond_number;
                    $message = "$drawNumber number is win. Please check Prizebond Draw Result.";
                } elseif ($bondNumberInfo = $this->global_model->get_data('fourth_prize_data', array('winner_bond_num' => $number->bond_number))) {
                    $drawNumber = $number->bond_number;
                    $message = "$drawNumber number is win. Please check Prizebond Draw Result.";
                } elseif ($bondNumberInfo = $this->global_model->get_data('fifth_prize_data', array('winner_bond_num' => $number->bond_number))) {
                    $drawNumber = $number->bond_number;
                    $message = "$drawNumber number is win. Please check Prizebond Draw Result.";
                }

                if (!empty($drawNumber)) {
                    $this->load->library('gcm');
                    $this->gcm->clearRecepients();
                    $registrationIds = array();

                    $bondInfo = $this->global_model->get('sync_info', array('bond_number' => $drawNumber));
                    foreach ($bondInfo as $row) {
                        $devicePushInfo = $this->global_model->get_data('device_info', array('device_uuid' => $row->device_uuid));
                        $registrationIds[] = (string) $devicePushInfo['device_push_id'];
                    }

                    $this->gcm->setRecepients($registrationIds);
                    $payloadData = array(
                        'prizebond' => 'Custon content'
                    );
                    $this->gcm->setData($payloadData);
                    $this->gcm->setMessage($message);
                    $this->gcm->setGroup($message);

                    if ($this->gcm->send()) {
                        $this->global_model->update('sync_info', array('push_send' => 1), array('bond_number' => $drawNumber));
                        echo 'push notification sent';
                    } else {
                        echo 'Failed to send push notification';
                    }
                }
            }
        }
    }

    //created by a teammate
    public function validateUserAccessToken() {
        $expiredId = $this->cron_model->getInvalidAccessTokensId();


        if (!empty($expiredId) && is_array($expiredId)) {
            foreach ($expiredId as $id) {
                $data = array(
                    'access_token' => NULL,
                    'access_token_expire_time' => NULL
                );
                if ($this->db->update('user', $data, array('id' => $id))) {
                    
                }
            }
        }
    }

    //created by a teammate
    public function resetVerifyCounter() {

        $this->cron_model->resetVerifyCounter('verify_counter');
    }

    //created by a teammate
    public function resetForgotVerifyCounter() {

        $this->cron_model->resetVerifyCounter('forgot_verify_counter');
    }

    public function sendPushMessageToDevelopersWhenErrorOccourd() {
        //log-2016-12-01.php
        // error types : Warning , error, Parsing Error, Query error:
        $errorTypes = array(
            'Warning',
            'error',
            'Parsing Error'
        );

        $fileName = "log-" . date("Y-m-d") . ".php";
        $finalArray = array();
        $file = FCPATH . "application/v4.0/logs/" . $fileName;

        if (file_exists($file)) {
            //$data['file_name']=$file_name;
            $errorArray = file($file);
            foreach ($errorArray as $error) {

                $errorArr = explode('-->', $error);
                if (count($errorArr) == 2) {
                    //  $data['error_date'] = substr($errorArr[0], -19);
                    $data['error_date'] = trim(str_replace('ERROR -', '', $errorArr[0]));
                    $data['severity'] = '404';
                    $data['message'] = $errorArr[1];
                    $finalArray[] = $data;
                } elseif (count($errorArr) == 3) {
                    //    $data['error_date'] = substr($errorArr[0], -21);
                    $data['error_date'] = trim(str_replace('ERROR -', '', $errorArr[0]));
                    $data['severity'] = trim(str_replace('Severity:', '', $errorArr[1]));
                    $data['message'] = $errorArr[2];
                    $finalArray[] = $data;
                }
            }

            $errorFound = 0;
            $oneHourAgo = strtotime("-1 hours");
            if (is_array($finalArray) && count($finalArray)) {
                foreach ($finalArray as $error) {

                    $errorDateTime = strtotime($error['error_date']);

                    if ($errorDateTime <= $oneHourAgo) {
                        if (in_array($error['severity'], $errorTypes)) {
                            // send push to developer devices
                            $errorFound++;
                        } else if (stripos(strtolower($error['message']), 'query error') !== false) {
                            // send push to developer devices
                            $errorFound++;
                        }
                    }
                }
            }

            if ($errorFound > 0) {

                echo $message = $errorFound . " error(s) found file : " . $fileName;
                echo "<br>";
                //$this->load->library('gcm');



                $query = $this->db->query("SELECT user.id,user.name,device_info.device_push_id AS push_id FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user_type_id = 2 AND device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' AND device_info.gcm_status = 'active'");

                $registrationIds = array();

                foreach ($query->result() as $row) {

                    $registrationIds[] = (string) $row->push_id;
                }

                $response = $this->sendNotification($registrationIds, $message);
                $this->processGcmResponse($registrationIds, $response);
            }
        }
    }

    public function sendPushToDeveloperIfLastFiveUsersUnVerified() {

        $message = 'Last Five Registered Users are not verified by mobile';
        $unVerifiedUsers = $this->cron_model->getUnVerifiedUsers();

        if (count($unVerifiedUsers) >= 5) {
            $query = $this->db->query("SELECT user.id,device_info.device_push_id AS push_id FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user_type_id = 2 AND device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' AND device_info.gcm_status = 'active'");
            $registrationIds = array();

            foreach ($query->result() as $row) {

                $registrationIds[] = (string) $row->push_id;
            }

            $response = $this->sendNotification($registrationIds, $message);
            $this->processGcmResponse($registrationIds, $response);
        }
    }

    public function sendPushMessageToAllUnVerifiedUsers() {

        $limit = 1000;
        $offset = 0;

        $query = $this->db->query("SELECT COUNT(device_info.device_push_id) AS total FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user.verify_status = 'NO' AND device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' AND device_info.gcm_status = 'active'");
        $total = $query->row("total");


        while ($total > $offset) {

            $query = $this->db->query("SELECT user.id,user.name,device_info.device_push_id AS push_id FROM user LEFT JOIN device_info ON device_info.user_id = user.id WHERE user.verify_status = 'NO' AND device_info.device_push_id IS NOT NULL AND device_info.device_status = 'active' AND device_info.gcm_status = 'active' LIMIT $limit OFFSET $offset");

            $registrationIds = array();

            foreach ($query->result() as $row) {
                $registrationIds[] = (string) $row->push_id;
            }
            $message = 'Dear valued user, Please verify your account to check prizebonds';
            $response = $this->sendNotification($registrationIds, $message);
            $this->processGcmResponse($registrationIds, $response);
            $offset = $offset + $limit;
        }
    }

    public function sendEmailToAllUnVerifiedUsers() {
        $message = '';
        $query = $this->db->query("SELECT id,name,email FROM user WHERE verify_status = 'NO'");
        $total = count($query->result());

        print"<pre>";
        print_r($this->db->last_query());
        print "</pre>";
        die();
    }

    // per hour
    public function notifyCustomerSupportTeamIfPaymentFailedMoreThanOnceInLastOneHour() {
        $sql = "SELECT user.name,user.mobile_number,subscription_payment_method_types.name AS payment_method, orders.total FROM (SELECT `user_id`, reference_id,payment_method_type_id, COUNT(id) AS total FROM `subscription_order_list` WHERE `payment_status` = 'failure' AND `order_created_date_time` > (NOW() - INTERVAL 1 HOUR) GROUP BY user_id,payment_method_type_id ORDER BY user_id DESC) orders LEFT JOIN user ON orders.user_id = user.id LEFT JOIN subscription_payment_method_types ON orders.payment_method_type_id = subscription_payment_method_types.id WHERE orders.total > 1 GROUP BY orders.user_id,orders.payment_method_type_id";

        $query = $this->db->query($sql);
        if ($query->num_rows()) {

            $senderEmail = "noreply@prizebond-checker.com";
            $senderName = "PRIZEBOND-CHECKER.COM";
            $receiverEmail = "support@prizebond-checker.com";
            $receiverName = "Prizebond-Checker Team";
            $subject = "URGENT : Customer failed to pay";

            foreach ($query->result() as $row) {
                $message = "";
                $message = "The Following customer failed to subscribe our service $row->total time(s) using $row->payment_method . Please help him/her to get job done<br>";
                $message .= "Customer Name : $row->name <br>";
                $message .= "Customer Mobile Number : $row->mobile_number <br>";
                $message .= "Thank you<br><br>";
                $message .= "[This is system generated email]";
                $this->sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message);
            }
        }
    }

    public function changeOrderStatusFromPendingToCancelAfterThreeDays() {

        $orderIds = array();
        log_message('info', "Cron Job : changeOrderStatusFromPendingToCancelAfterThreeDays started");

        $sql = "SELECT id,reference_id,order_created_date_time FROM `subscription_order_list` WHERE `order_status` = 'pending' AND `order_created_date_time` <  (DATE_SUB(CURDATE(), INTERVAL 3 DAY))";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {

            foreach ($query->result() as $row) {
                $sql2 = "UPDATE subscription_order_list SET order_status = 'cancel' WHERE id = '$row->id'";
                $query2 = $this->db->query($sql2);
                $orderIds[] = $row->reference_id;
            }
        }

        log_message('info', "Cron Job : order ids : " . implode(",", $orderIds) . " status have been changed to cancel");

        log_message('info', "Cron Job : changeOrderStatusFromPendingToCancelAfterThreeDays completed");
    }

    public function deleteCanceledOrderAfterOneWeek() {

        $orderIds = array();
        log_message('info', "Cron Job : deleteCanceledOrderAfterOneWeek started");

        $sql = "SELECT id,reference_id,order_created_date_time FROM `subscription_order_list` WHERE `order_status` = 'cancel' AND payment_status='pending' AND `order_created_date_time` <  (DATE_SUB(CURDATE(), INTERVAL 7 DAY))";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {

            foreach ($query->result() as $row) {
                $sql2 = "DELETE FROM subscription_order_list WHERE id = '$row->id'";
                $query2 = $this->db->query($sql2);
                $orderIds[] = $row->reference_id;
            }
        }

        log_message('info', "Cron Job : canceled order ids : " . implode(",", $orderIds) . " have been deleted");

        log_message('info', "Cron Job : deleteCanceledOrderAfterOneWeek completed");
    }

    public function optimizeDBTablesOncePerDay() {
        log_message('info', "Cron Job : optimizeDBTablesOncePerDay started");

        $this->deleteActivityLogsOncePerDay();

        $sql = "SHOW TABLES";
        $query = $this->db->query($sql);
        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                echo $row->Tables_in_prizebon_new_db;
                echo "<br>";
                $sql2 = "OPTIMIZE TABLE " . $row->Tables_in_prizebon_new_db;
                $query2 = $this->db->query($sql2);
            }
        }
        log_message('info', "Cron Job : optimizeDBTablesOncePerDay completed");
    }

    public function sendAdPushAllUsers() {
//        $userType = 1; //subscriberOnly
//        $userType = 2; //nonSubscriberOnly
//        $userType = 3; //allUsers
        $userType = 4; //allUsers
        $message = 'প্রাইজ বন্ড চেকার অ্যাপের নতুন ভার্সন এসেছে। সঠিক তথ্য পেতে আপনার অ্যাপটি আপডেট করুন।';

        $offset = 0;
        $limit = 1000;

        $totalDevices = $this->cron_model->countDevicesForSendPush($userType);

        while ($totalDevices >= $offset) {
            $deviceList = $this->cron_model->getDevicesForSendPush($userType, $limit, $offset);

            if (!empty($deviceList) && (count($deviceList))) {
                $pushIds = array();
                foreach ($deviceList as $value) {
                    $pushIds[] = $value->push_id;
                }
            }
            print "<pre>";
            print_r($pushIds);
            print "</pre>";
            die();

            if (count($pushIds) > 0) {
                $this->gcm->clearRecepients();
                $this->gcm->setRecepients($pushIds);
                $payloadData = array(
                    'prizebond' => 'Custom content',
                    'offer' => 'YES'
                );
                $this->gcm->setData($payloadData);
                $this->gcm->setMessage($message);
                $this->gcm->setGroup(md5($message));
                $this->gcm->send();

                $this->handleListOfInactiveAndroidDevices($this->gcm->messagesStatuses);

                $gcmStatus = $this->gcm->status;
                if ($gcmStatus['error'] != 0) {
                    //log_message('error', "GCM : " . $gcmStatus['message']);
                    echo $gcmStatus['message'];
                } else {
                    echo "sent GCM successfully";
                }
            }

            $offset = $limit + $offset;
        }
    }

    public function sendPushForOfferTest() {
        $message = 'Test push';

        $query = $this->db->query("SELECT user.id,user.name,device_info.device_push_id AS push_id FROM user
                LEFT JOIN device_info ON device_info.user_id = user.id 
                WHERE user.id = 129 AND device_info.device_status = 'active'");

        // atique = 3941
        // boss = 129

        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                $pushIds = array();
                $pushIds[] = $row->push_id;
            }

            if (count($pushIds) > 0) {
                $this->gcm->clearRecepients();
                $this->gcm->setRecepients($pushIds);
                $payloadData = array(
                    'prizebond' => 'Custom content',
                    'offer' => 'NO' // YES
                );
                $this->gcm->setData($payloadData);
                $this->gcm->setMessage($message);
                $this->gcm->setGroup(md5($message));
                $this->gcm->send();
                $gcmStatus = $this->gcm->status;
                if ($gcmStatus['error'] != 0) {
                    //log_message('error', "GCM : " . $gcmStatus['message']);
                    echo $gcmStatus['message'];
                } else {
                    echo "sent GCM successfully";
                }
            }
        } else {
            echo "No user found";
        }
    }

    private function deleteActivityLogsOncePerDay() {
        log_message('info', "Cron Job : deleteActivityLogsOncePerDay started");

        $oneMonthBack = date("Y-m-d", strtotime("-30 days"));
        $sql = "DELETE FROM device_activity_log WHERE DATE(`update_date`) < '$oneMonthBack'";
        $query = $this->db->query($sql);

        $sql = "DELETE FROM user_activity_logs WHERE DATE(`created_date_time`) < '$oneMonthBack'";
        $query = $this->db->query($sql);

        log_message('info', "Cron Job : deleteActivityLogsOncePerDay completed");
    }

    private function sendEmailToAUser($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message) {
        $data = array($senderEmail, $senderName, $receiverEmail, $receiverName, $subject, $message);
        $this->load->library('email');
        $this->email->from($senderEmail, $senderName);
        $this->email->to($receiverEmail);
        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_mailtype("html");
        if ($this->email->send()) {
            return TRUE;
        } else {
            $response['response']['error'] = "unable to send email from cron";
            $response['response']['code'] = "not defined";
            $this->logActivity('error', 'Code : ' . $response['response']['code'] . ' -> ' . $response['response']['error'] . '</br>Source -> ' . current_url() . '</br> Data ->' . json_encode($data));
        }
    }

    private function handleListOfInactiveAndroidDevices($response = array()) {
        if (count($response)) {
            log_message('info', 'Inactive Android Devices found : ' . print_r($response, true));

            foreach ($response as $status) {
                if ($status['error'] == 1) {
                    $date_time = date('Y-m-d H:i:s');
                    $this->cron_model->updateTable('device_info', array('gcm_status' => 'inactive', 'inactive_date' => $date_time, 'gcm_response' => $status['message']), array('device_push_id' => $status['regid']));
                }
            }
        }
    }

    private function processGcmResponse($registrationIds, $response) {
        $responseData = json_decode($response);
        if (count($responseData->results)) {
            $i = 0;
            foreach ($responseData->results as $status) {

                if (!empty($status->error)) {
                    $pushId = $registrationIds[$i];

                    $data = array(
                        'gcm_status' => 'inactive',
                        'gcm_response' => $status->error
                    );
                    $this->db->where('device_push_id', $pushId);
                    $this->db->update('device_info', $data);
                }
                $i++;
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

    private function convertEnglishNumberIntoBanglaNumber($number) {
        $englishNumber = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0);
        $banglaNumber = array('১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '০');
        $convertNumber = str_replace($englishNumber, $banglaNumber, $number);
        return $convertNumber;
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

    private function sendNotification($registrationIdsArray, $message) {
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
