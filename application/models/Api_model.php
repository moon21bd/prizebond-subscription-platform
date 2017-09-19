<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-07-24
 */

class Api_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->db->query("SET time_zone='+6:00'"); // do not change this value
    }

    function __destruct() {
        $this->db->close();
    }

    public function getUserInfoByEmailAndPassword($email, $password) {
        $query = $this->db->get_where('user', array('email' => $email, 'password' => md5($password)));
        if ($query->result()) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function getUserInfoForForgotPassword($emailOrMobile) {
        $this->db->select('id, email, mobile_number');
        $this->db->where("(email = '$emailOrMobile' OR mobile_number LIKE  '%$emailOrMobile%')");
        $query = $this->db->get('user');
        ;
        if ($query->result()) {
            $data['id'] = $query->row()->id;
            $data['email'] = $query->row()->email;
            $data['mobile_number'] = $query->row()->mobile_number;
            return $data;
        } else {
            return false;
        }
    }

    public function getUserInfoByMobileNumberAndPassword($mobileNumber, $password) {
        $this->db->where("(mobile_number LIKE  '%$mobileNumber%')");
        $this->db->where('password', md5($password));
        $query = $this->db->get('user');
        if ($query->result()) {
            return $query->row();
        } else {
            return false;
        }
    }

    //created by a teammate
    public function getUserInfoByEmailOrMobileNumberAndPassword($emailOrMobile, $password) {
        $this->db->where("(email = '$emailOrMobile' OR mobile_number LIKE  '%$emailOrMobile%')");
        $this->db->where('password', md5($password));
        $query = $this->db->get('user');
        if ($query->result()) {
            return $query->row();
        } else {
            return false;
        }
    }

    //created by a teammate
    public function searchBondNumberByUserIdAndBondNumber($order_by, $bond_number, $userId) {
        $this->db->order_by($order_by, 'ASC');
        $this->db->where("(bond_number LIKE  '%$bond_number%')");
        $this->db->where('user_id', $userId);
        $query = $this->db->get('user_prizebond_list');
        if ($query->result()) {

            foreach ($query->result_array() as $data) {
                $result['bond_series'] = $data['bond_series'];
                $result['bond_number'] = $data['bond_number'];
                if (!empty($data['bond_image_name'])) {
                    $image = base_url() . '/images/' . $userId . '/' . $data['bond_image_name'];
                    $result['bond_image_path'] = $image;
                } else {
                    $result['bond_image_path'] = '';
                }

                $result['bond_added_date_time'] = $data['bond_added_by_user_date_time'];
                $result['bond_note'] = !empty($data['bond_note']) ? $data['bond_note'] : '';
                $result['win_prize_position'] = !empty($data['prize_position']) ? $data['prize_position'] : '';
                $result['win_draw_number'] = !empty($data['draw_number']) ? $data['draw_number'] : '';
                $result['win_prize_money'] = !empty($data['prize_amount']) ? $data['prize_amount'] : '';
                $bondResult[] = $result;
            }

            return $bondResult;
        } else {
            return false;
        }
    }

    //created by a teammate
    public function getBondsByUserId($limit, $offset, $order_by, $order_type, $userId) {
        $num = $offset ? $offset : 0;
        $this->db->limit($limit, $num);
        $this->db->where('user_id', $userId);
        $this->db->order_by('draw_number', 'DESC');
        $this->db->order_by('prize_amount', 'DESC');
        $this->db->order_by($order_by, $order_type);
        $query = $this->db->get('user_prizebond_list');
        if ($query->result()) {

            foreach ($query->result_array() as $data) {
                $result['bond_id'] = $data['id'];
                $result['bond_series'] = $data['bond_series'];
                $result['bond_number'] = $data['bond_number'];
                if (!empty($data['bond_image_name'])) {
                    $image = base_url() . '/images/' . $userId . '/' . $data['bond_image_name'];
                    $result['bond_image_path'] = $image;
                } else {
                    $result['bond_image_path'] = '';
                }

                $result['bond_added_date_time'] = $data['bond_added_by_user_date_time'];
                $result['bond_note'] = !empty($data['bond_note']) ? $data['bond_note'] : '';
                $result['win_prize_position'] = !empty($data['prize_position']) ? $data['prize_position'] : '';
                $result['win_draw_number'] = !empty($data['draw_number']) ? $data['draw_number'] : '';
                $result['win_prize_amount'] = !empty($data['prize_amount']) ? $data['prize_amount'] : '';
                $bondResult[] = $result;
            }

            return $bondResult;
        } else {
            return false;
        }
    }

    //for web api
    // moved to subscription folder
    public function getUserCouponFailedCount($userId) {

        $this->db->select('*')->from('subscription_error_coupon_tracking')->where('user_id', $userId);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {

            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function getAllSupportMessages($limit, $offset, $userId) {
        $num = $offset ? $offset : 0;
        $this->db->limit($limit, $num);
        $this->db->order_by('last_replied_date_time', 'DESC');
        //$this->db->select('message_id, subject, created_date_time, last_replied_by, last_replied_date_time');
        $query = $this->db->get_where('user_support_messages', array('sender_user_id' => $userId));
        if ($query->num_rows() > 0) {

            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getAllSupportConversations($limit, $offset, $messageId) {
        $num = $offset ? $offset : 0;
        $this->db->limit($limit, $num);
        $this->db->order_by('sent_date_time', 'DESC');
        $this->db->select('message_id, message, sent_by, sent_date_time');
        $query = $this->db->get_where('user_support_conversations', array('message_id' => $messageId));
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $query->row()->message_subject = $this->getMessageSubject($query->row()->message_id);
            }
            $result = array_reverse($query->result());
            return $result;
        } else {
            return FALSE;
        }
    }

    public function updateUserVerificationCode($verificationCode, $userId, $mobileNumber) {
        $this->db->update('user', array('verify_code' => $verificationCode, 'mobile_number' => $mobileNumber), array('id' => $userId));
        return TRUE;
    }

    public function updateMobileVerificationCode($verificationCode, $userId) {
        $this->db->update('user', array('verify_code' => $verificationCode), array('id' => $userId));
        return TRUE;
    }

    public function updateEmailVerificationCode($verificationCode, $userId) {
        $this->db->update('user', array('verify_code' => $verificationCode), array('id' => $userId));
        return TRUE;
    }

    public function getMatcheData($ids, $userId) {
        $ids = join(",", $ids);
        $query = $this->db->query("SELECT * FROM user_prizebond_list WHERE user_id ='$userId' AND id IN ($ids)");
        if ($query->num_rows()) {
            foreach ($query->result_array() as $data) {
                $result['bond_series'] = $data['bond_series'];
                $result['bond_number'] = $data['bond_number'];
                if (!empty($data['bond_image_name'])) {
                    $image = base_url() . '/images/' . $userId . '/' . $data['bond_image_name'];
                    $result['bond_image_path'] = $image;
                } else {
                    $result['bond_image_path'] = '';
                }

                $result['bond_added_date_time'] = $data['bond_added_by_user_date_time'];
                $result['bond_note'] = !empty($data['bond_note']) ? $data['bond_note'] : '';
                $result['prize_position'] = !empty($data['prize_position']) ? $data['prize_position'] : '';
                $result['draw_number'] = !empty($data['draw_number']) ? $data['draw_number'] : '';
                $result['prize_money'] = !empty($data['prize_amount']) ? $data['prize_amount'] : '';

                $matchedData[] = $result;
            }
            return json_encode($matchedData);
        }
    }

    public function getUploadedData($ids, $userId) {

        $ids = join(",", $ids);
        $query = $this->db->query("SELECT * FROM user_prizebond_list WHERE user_id ='$userId' AND id IN ($ids)");
        if ($query->num_rows()) {
            foreach ($query->result_array() as $data) {
                $result['bond_series'] = $data['bond_series'];
                $result['bond_number'] = $data['bond_number'];
                if (!empty($data['bond_image_name'])) {
                    $image = base_url() . '/images/' . $userId . '/' . $data['bond_image_name'];
                    $result['bond_image_path'] = $image;
                } else {
                    $result['bond_image_path'] = '';
                }

                $result['bond_added_date_time'] = $data['bond_added_by_user_date_time'];
                $result['bond_note'] = !empty($data['bond_note']) ? $data['bond_note'] : '';
                $result['prize_position'] = !empty($data['prize_position']) ? $data['prize_position'] : '';
                $result['draw_number'] = !empty($data['draw_number']) ? $data['draw_number'] : '';
                $result['prize_money'] = !empty($data['prize_amount']) ? $data['prize_amount'] : '';
                $uploadData[] = $result;
            }
            return json_encode($uploadData);
        }
    }

    public function updateUploadedBondStatus($ids, $hash) {
        $ids2 = join(",", $ids);
        $sql = "UPDATE user_prizebond_list SET sync_hash= '$hash' WHERE id IN ($ids2)";
        $this->db->query($sql);
        return TRUE;
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

    public function doesMatchApiKey($api_key) {
        $this->db->select('api_key');
        $query = $this->db->get_where('app_information', array('api_key' => $api_key));
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function countUserBondNumber($userId) {
        $this->db->select('id');
        $query = $this->db->get_where('user_prizebond_list', array('user_id' => $userId));
        if ($query->num_rows()) {
            return $query->num_rows();
        } else {
            return FALSE;
        }
    }

    public function getTotalBondNumersOfAUser($userId) {
        $query = $this->db->query('SELECT COUNT(id) AS total FROM user_prizebond_list WHERE user_id =' . $userId);
        return $query->row()->total;
    }

    public function getSeriesList() {

        $data = array();
        $query = $this->db->get('series_list');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $seriesIfo['series_id'] = $row->id;
                $seriesIfo['series_name'] = $row->name;
                $data[] = $seriesIfo;
            }
            $custom_series['series_id'] = "0";
            $custom_series['series_name'] = 'অন্যান্য';
            $data[] = $custom_series;
            return $data;
        } else {
            return FALSE;
        }
    }

    private function getDrawInfoByBondNumber($bondNumber) {
        $drawInfo = array();
        $query = $this->db->get_where('prizebond_result_bond_list', array('bond_number' => $bondNumber));
        if ($query->num_rows() > 0) {
            $drawInfo['prize_position'] = $query->row()->prize_position;
            $drawInfo['prizebond_result_info_id'] = $query->row()->prizebond_result_info_id;
            $drawInfo2 = $this->getPrizebondResultInfoById($query->row()->prizebond_result_info_id);
            $drawInfo['result_series'] = $drawInfo2->result_series;
            $drawInfo['draw_number'] = $drawInfo2->result_number;
            $drawInfo['result_date'] = $drawInfo2->result_date;

            if ($query->row()->prize_position == '1st') {
                $drawInfo['prize_amount'] = $drawInfo2->first_prize_value_tk;
            } else if ($query->row()->prize_position == '2nd') {
                $drawInfo['prize_amount'] = $drawInfo2->second_prize_value_tk;
            } else if ($query->row()->prize_position == '3rd') {
                $drawInfo['prize_amount'] = $drawInfo2->third_prize_value_tk;
            } else if ($query->row()->prize_position == '4th') {
                $drawInfo['prize_amount'] = $drawInfo2->fourth_prize_value_tk;
            } else if ($query->row()->prize_position == '5th') {
                $drawInfo['prize_amount'] = $drawInfo2->fifth_prize_value_tk;
            }
        }
        return $drawInfo;
    }

    public function getDrawInfoByBondNumberForUpdateBondNumber($bondNumber) {
        $drawInfo = array();
        $query = $this->db->get_where('prizebond_result_bond_list', array('bond_number' => $bondNumber));
        if ($query->num_rows() > 0) {
            $drawInfo['prize_position'] = $query->row()->prize_position;
            $drawInfo['prizebond_result_info_id'] = $query->row()->prizebond_result_info_id;
            $drawInfo2 = $this->getPrizebondResultInfoById($query->row()->prizebond_result_info_id);
            $drawInfo['result_series'] = $drawInfo2->result_series;
            $drawInfo['draw_number'] = $drawInfo2->result_number;
            $drawInfo['result_date'] = $drawInfo2->result_date;

            if ($query->row()->prize_position == '1st') {
                $drawInfo['prize_amount'] = $drawInfo2->first_prize_value_tk;
            } else if ($query->row()->prize_position == '2nd') {
                $drawInfo['prize_amount'] = $drawInfo2->second_prize_value_tk;
            } else if ($query->row()->prize_position == '3rd') {
                $drawInfo['prize_amount'] = $drawInfo2->third_prize_value_tk;
            } else if ($query->row()->prize_position == '4th') {
                $drawInfo['prize_amount'] = $drawInfo2->fourth_prize_value_tk;
            } else if ($query->row()->prize_position == '5th') {
                $drawInfo['prize_amount'] = $drawInfo2->fifth_prize_value_tk;
            }
        }

        return $drawInfo;
    }

    private function saveUserPrizeBond($userId, $prizePosition = NULL, $drawNumber = NULL, $drawResultDate = NULL, $deviceUUID = NULL, $bondNumber, $bondSeries, $bondResultInfoId = 0, $prizeAmount = NULL) {

        $query = $this->db->query("SELECT id FROM user_prizebond_list WHERE user_id='$userId' AND bond_series='$bondSeries' AND bond_number='$bondNumber'");

        if ($query->num_rows() > 0) { // bond already existed
            return FALSE;
        } else {
            $responseData = array();
            $date = date('Y-m-d H:i:s');
            $data = array(
                'user_id' => $userId,
                'prize_position' => $prizePosition,
                'draw_number' => $drawNumber,
                'prize_amount' => $prizeAmount,
                'user_devcie_uuid' => $deviceUUID,
                'bond_number' => $bondNumber,
                'bond_series' => $bondSeries,
                'bond_added_by_user_date_time' => $date
            );

            if ($this->db->insert('user_prizebond_list', $data)) {
                $insertedId = $this->db->insert_id();


                $responseData['bond_series'] = $bondSeries;
                $responseData['bond_number'] = $bondNumber;
                $responseData['bond_image_path'] = '';
                $responseData['bond_added_date'] = $date;
                $responseData['bond_note'] = '';
                $responseData['sync_status'] = 'ready';
                $responseData['user_bond_id'] = $insertedId;

                $responseData['win_draw_number'] = NULL;
                $responseData['win_draw_year'] = NULL;
                $responseData['win_prize_position'] = NULL;
                $responseData['win_prize_money'] = NULL;

                if ($insertedId) {
                    if ($prizePosition != NULL && $drawNumber != NULL) { // this is win bond
                        $query = $this->db->query("SELECT id FROM user_prizebond_won_list WHERE user_id='$userId' AND bond_series='$bondSeries' AND bond_number='$bondNumber'");

                        if ($query->num_rows() > 0) { // bond already existed
                            return FALSE;
                        } else {
                            $data = array(
                                'user_id' => $userId,
                                'user_prizebond_id' => $insertedId,
                                'bond_number' => $bondNumber,
                                'bond_series' => $bondSeries,
                                'prizebond_result_info_id' => $bondResultInfoId,
                                'prize_position' => $prizePosition,
                                'prize_amount' => $prizeAmount
                            );
                            if (!$this->db->insert('user_prizebond_won_list', $data)) {
                                // show db error with proper data
                                $this->logActivity('error', 'Code : ' . $this->config->item('DB_ERROR_01')['code'] . ' -> ' . $this->config->item('DB_ERROR_01')['error'] . 'Data ->' . json_encode($data));
                            } else {
                                $responseData['win_draw_number'] = $drawNumber;
                                $responseData['win_draw_year'] = $drawResultDate;
                                $responseData['win_prize_position'] = $prizePosition;
                                $responseData['win_prize_money'] = $prizeAmount;
                            }
                        }


                        /*
                          //$this->sendGCMPushToWinnerDevice($deviceUUID, $bondNumber);
                          $drawNumber = $this->convertEnglishNumberIntoBanglaNumber($drawNumber);
                          //$series = $this->convertEnglishleterToBanglaleter($bondSeries);
                          $bondNumber = $this->convertEnglishNumberIntoBanglaNumber($bondNumber);
                          $prizeAmount = $this->convertEnglishNumberIntoBanglaNumber($prizeAmount);
                          $prizePosition = $this->convertPrizePositionEnglishToBangla($prizePosition);

                          $message = $drawNumber. "তম ড্রতে আপনার প্রাইজ বণ্ড # ".$bondSeries." ".$bondNumber.", ".$prizePosition." পুরষ্কার ".$prizeAmount." টাকা জিতেছে।";
                          $this->sendGcmToWinnerDevice($userId, $bondNumber, $insertedId, $message); */
                    }
                } else {
                    // show db error with proper data
                }

                return $responseData;
            } else {
                // show db error with proper data
            }
        }

        return FALSE;
    }

    public function insertWinningBondInfo($data) {
        $this->db->insert('user_prizebond_won_list', $data);
        return $this->db->insert_id();
    }

    public function savePrizeBondNumbers($user_id, $device_uuid, $prizebondNumbersArray, $bondSeries) {

        if (strpos($bondSeries, ',')) {

            $bondSeries = explode(',', $bondSeries);

            $countSeriesArray = count($bondSeries);
            $countBondArray = count($prizebondNumbersArray);
            $prizebondNumber = $prizebondNumbersArray;

            if ($countSeriesArray == $countBondArray) {

                $resultData = array();
                $duplicateBonds = array();
                for ($i = 0; $i < $countBondArray; $i++) {

                    $this->db->select('id');
                    $where = array(
                        'user_id' => $user_id,
                        'bond_series' => $bondSeries[$i],
                        'bond_number' => $prizebondNumber[$i]
                    );

                    $query = $this->db->get_where('user_prizebond_list', $where);

                    if ($query->num_rows() == 0) {
                        // unique bond for the user
                        $drawInfoArray = $this->getDrawInfoByBondNumber($prizebondNumber[$i]);

                        if (count($drawInfoArray) > 0) {

                            $drawCheckBondSeries = trim(str_replace(' ', '', $bondSeries[$i]));

                            $drawSeriesArray = array_map("trim", explode(',', $drawInfoArray['result_series']));

                            if (in_array($drawCheckBondSeries, $drawSeriesArray)) { // matched bond with win result
                                $response = $this->saveUserPrizeBond($user_id, $drawInfoArray['prize_position'], $drawInfoArray['draw_number'], $drawInfoArray['result_date'], $device_uuid, $prizebondNumber[$i], $bondSeries[$i], $drawInfoArray['prizebond_result_info_id'], $drawInfoArray['prize_amount']);
                                if (is_array($response)) {
                                    $resultData[] = $response;
                                }
                            } else {
                                $response = $this->saveUserPrizeBond($user_id, NULL, NULL, NULL, $device_uuid, $prizebondNumber[$i], $bondSeries[$i], NULL, NULL);
                                if (is_array($response)) {
                                    $resultData[] = $response;
                                }
                            }
                        } else {
                            $response = $this->saveUserPrizeBond($user_id, NULL, NULL, NULL, $device_uuid, $prizebondNumber[$i], $bondSeries[$i], NULL, NULL);
                            if (is_array($response)) {
                                $resultData[] = $response;
                            }
                        }
                    } else { // duplicate bonds
                        $duplicateBonds[] = $bondSeries[$i] . ' সিরিজে ' . $prizebondNumber[$i];
                    }
                }
                if (count($resultData) > 0) {
                    $finalData['bond_info'] = $resultData;
                } else {
                    $finalData['bond_info'] = '';
                }
                if (count($duplicateBonds) > 0) {
                    $finalData['duplicate_numbers'] = join(',', $duplicateBonds);
                } else {
                    $finalData['duplicate_numbers'] = '';
                }
                return $finalData;
            } else {
                return FALSE;
            }
        } else {
            if (is_array($prizebondNumbersArray) && count($prizebondNumbersArray) > 0) {
                $resultData = array();
                $duplicateBonds = array();

                foreach ($prizebondNumbersArray as $prizebondNumber) {

                    $this->db->select('id');
                    $where = array(
                        'user_id' => $user_id,
                        'bond_series' => $bondSeries,
                        'bond_number' => $prizebondNumber
                    );

                    $query = $this->db->get_where('user_prizebond_list', $where);

                    if ($query->num_rows() == 0) {
                        // unique bond for the user
                        $drawInfoArray = $this->getDrawInfoByBondNumber($prizebondNumber);

                        if (count($drawInfoArray) > 0) {

                            $drawCheckBondSeries = trim(str_replace(' ', '', $bondSeries));

                            $drawSeriesArray = array_map("trim", explode(',', $drawInfoArray['result_series']));

                            if (in_array($drawCheckBondSeries, $drawSeriesArray)) { // matched bond with win result
                                $response = $this->saveUserPrizeBond($user_id, $drawInfoArray['prize_position'], $drawInfoArray['draw_number'], $drawInfoArray['result_date'], $device_uuid, $prizebondNumber, $bondSeries, $drawInfoArray['prizebond_result_info_id'], $drawInfoArray['prize_amount']);
                                if (is_array($response)) {
                                    $resultData[] = $response;
                                }
                                //$resultData[] = $this->getWinningBondAddedResponseArray($bondSeries, $prizebondNumber, $drawInfoArray['draw_number'], $drawInfoArray['prize_position'], $drawInfoArray['prize_amount'], $drawInfoArray['result_date']);
                            } else {
                                $response = $this->saveUserPrizeBond($user_id, NULL, NULL, NULL, $device_uuid, $prizebondNumber, $bondSeries, NULL, NULL);
                                //$resultData[] = $this->getNonWinningBondAddedResponseArray($bondSeries, $prizebondNumber);
                                if (is_array($response)) {
                                    $resultData[] = $response;
                                }
                            }
                        } else {
                            $response = $this->saveUserPrizeBond($user_id, NULL, NULL, NULL, $device_uuid, $prizebondNumber, $bondSeries, NULL, NULL);
                            //$resultData[] = $this->getNonWinningBondAddedResponseArray($bondSeries, $prizebondNumber);
                            if (is_array($response)) {
                                $resultData[] = $response;
                            }
                        }
                    } else { // duplicate bonds
                        $duplicateBonds[] = $prizebondNumber;
                    }
                }

                if (count($resultData) > 0) {
                    $finalData['bond_info'] = $resultData;
                } else {
                    $finalData['bond_info'] = '';
                }

                if (count($duplicateBonds) > 0) {
                    $finalData['duplicate_numbers'] = join(',', $duplicateBonds);
                } else {
                    $finalData['duplicate_numbers'] = '';
                }
                return $finalData;
            }
            return FALSE;
        }
    }

    public function getWinningBondUpdateResponseArray($userBondInfo = array(), $bondSeries, $prizebondNumber, $drawNumber, $prizePosition, $prizeAmount, $resultDate) {

        $responseData['bond_series'] = $bondSeries;
        $responseData['bond_number'] = $prizebondNumber;
        if (!empty($userBondInfo['bond_image_name'])) {
            $responseData['bond_image_path'] = base_url() . 'images/' . $userBondInfo['user_id'] . '/' . $userBondInfo['bond_image_name'];
        } else {
            $responseData['bond_image_path'] = '';
        }
        $responseData['bond_added_date'] = $userBondInfo['bond_added_by_user_date_time'];
        $responseData['bond_note'] = $userBondInfo['bond_note'];
        $responseData['sync_status'] = $userBondInfo['sync_status'];
        $responseData['win_draw_number'] = $drawNumber;
        $responseData['win_draw_year'] = $resultDate;
        $responseData['win_prize_position'] = $prizePosition;
        $responseData['win_prize_money'] = $prizeAmount;
        return $responseData;
    }

    private function getWinningBondAddedResponseArray($bondSeries, $prizebondNumber, $drawNumber, $prizePosition, $prizeAmount, $resultDate) {

        $responseData['bond_series'] = $bondSeries;
        $responseData['bond_number'] = $prizebondNumber;
        $responseData['bond_image_path'] = '';
        $responseData['bond_added_date'] = date('Y-m-d H:i:s');
        $responseData['bond_note'] = '';
        $responseData['sync_status'] = 'ready';
        $responseData['win_draw_number'] = $drawNumber;
        $responseData['win_draw_year'] = $resultDate;
        $responseData['win_prize_position'] = $prizePosition;
        $responseData['win_prize_money'] = $prizeAmount;
        return $responseData;
    }

    private function getNonWinningBondAddedResponseArray($bondSeries, $prizebondNumber) {
        $responseData['bond_series'] = $bondSeries;
        $responseData['bond_number'] = $prizebondNumber;
        $responseData['bond_image_path'] = '';
        $responseData['bond_added_date'] = date('Y-m-d H:i:s');
        $responseData['bond_note'] = '';
        $responseData['sync_status'] = 'ready';
        $responseData['win_draw_number'] = '';
        $responseData['win_draw_year'] = '';
        $responseData['win_prize_position'] = '';
        $responseData['win_prize_money'] = '';
        return $responseData;
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

    public function getId($table, $where) {
        $this->db->select('id');
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            return FALSE;
        }
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

    public function getUserIdById($id) {
        $this->db->select('user_id');
        $query = $this->db->get_where('user', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row()->user_id;
        } else {
            return FALSE;
        }
    }

    /*
      private function saveWinnerBond($device_uuid, $user_id, $prizebondNumber, $bondSeries) {
      $query1 = $this->db->get_where('prizebond_result_bond_list', array('bond_number' => $prizebondNumber));
      if ($query1->num_rows() > 0) {
      $row = $query1->row();
      $data = array(
      'user_id' => $user_id,
      'bond_number' => $prizebondNumber,
      'bond_series' => $bondSeries,
      'user_prizebond_id' => $row->id,
      'prizebond_result_info_id' => $row->prizebond_result_info_id,
      'prize_position' => $row->prize_position,
      'prize_amount' => $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position)
      );
      $this->db->insert('user_prizebond_won_list', $data);
      $message = "$prizebondNumber number is win. Please check prizebond draw result";
      $this->sendPushMessageToWinnerDevcie($device_uuid, $message);

      $responseData['bond_series'] = $bondSeries;
      $responseData['bond_number'] = $prizebondNumber;
      $responseData['bond_image_path'] = '';
      $responseData['bond_added_date'] = date('Y-m-d H:i:s');
      $responseData['bond_note'] = '';
      $responseData['sync_status'] = 'ready';
      $responseData['win_draw_number'] = $this->getResultNumberByResultId($row->prizebond_result_info_id);
      $responseData['win_draw_year'] = $this->getDrawYearByResultId($row->prizebond_result_info_id);
      $responseData['win_prize_position'] = $row->prize_position;
      $responseData['win_prize_money'] = $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position);
      return $responseData;
      }
      return TRUE;
      } */

    private function getDrawYearByResultId($resultId) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $resultId));
        if ($query->num_rows() > 0) {
            return $query->row()->result_date;
        }
    }

    private function getResultNumberByResultId($resultId) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $resultId));
        if ($query->num_rows() > 0) {
            return $query->row()->result_number;
        } else {
            return FALSE;
        }
    }

    private function getBondInfoByUserIdSeriesAndBondNumber($get_where) {
        $query = $this->db->get_where('user_prizebond_list', $get_where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    private function getMessageSubject($messageId) {
        $query = $this->db->get_where('user_support_messages', array('message_id' => $messageId));
        if ($query->result()) {
            return $query->row()->subject;
        } else {
            return false;
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

    /*
      public function sendGCMPushToWinnerDevice($deviceUUID, $prizebondNumber) {

      $devicePushId = $this->getPushIdByDeviceUuid($deviceUUID);

      if ($devicePushId) {
      $message = "$prizebondNumber number is win. Please check Prizebond draw result";
      $this->load->library('gcm');
      $this->gcm->clearRecepients();
      $registrationIds = array();
      $registrationIds[] = (string) $devicePushId;
      $this->gcm->setRecepients($registrationIds);
      $this->gcm->setMessage($message);
      $this->gcm->setGroup(md5($message));
      $this->gcm->send();
      }
      return TRUE;
      } */

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

    private function getPushIdByDeviceUuid($deviceUuid) {
        $this->db->select('device_push_id');
        $query = $this->db->get_where('device_info', array('device_uuid' => $deviceUuid));
        if ($query->num_rows() > 0) {
            return $query->row()->device_push_id;
        } else {
            return FALSE;
        }
    }

    private function getPrizebondResultInfoById($prizeBondResultInfoId) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $prizeBondResultInfoId));
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return FALSE;
    }

    private function getPrizeAmountByResultIdAndPrizePosition($resultId, $prizePosition) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $resultId));
        if ($query->num_rows() > 0) {
            switch ($prizePosition) {
                case '1st':
                    return $query->row()->first_prize_value_tk;
                    break;
                case '2nd':
                    return $query->row()->second_prize_value_tk;
                    break;
                case '3rd':
                    return $query->row()->third_prize_value_tk;
                    break;
                case '4th':
                    return $query->row()->fourth_prize_value_tk;
                    break;
                case '5th':
                    return $query->row()->fifth_prize_value_tk;
                    break;
            }
        } else {
            return FALSE;
        }
    }

    public function deletePrizeBondNumbers($data, $userId) {
        if (is_array($data['bond_series']) && count($data['bond_series']) > 0) {
            foreach ($data['bond_series'] as $key => $series) {
                $where = array(
                    'bond_series' => $series,
                    'bond_number' => $data['prizebond_number'][$key],
                    'user_id' => $userId
                );
                if ($this->db->delete('user_prizebond_list', $where)) {
                    $this->db->delete('user_prizebond_won_list', $where);
                }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getListOfDraw() {
        $this->db->order_by("id", "DESC");
        $this->db->limit(8);
        $query = $this->db->get('prizebond_result_info');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $result['draw_id'] = $row->id;
                $result['draw_num'] = $row->result_number;
                $result['draw_year'] = $row->result_date;
                $result['draw_prizes']['total_first_prizes'] = $row->first_prize_number;
                $result['draw_prizes']['total_second_prizes'] = $row->second_prize_number;
                $result['draw_prizes']['total_third_prizes'] = $row->third_prize_number;
                $result['draw_prizes']['total_fourth_prizes'] = $row->fourth_prize_number;
                $result['draw_prizes']['total_fifth_prizes'] = $row->fifth_prize_number;
                $resultInfoList[] = $result;
            }
            return $resultInfoList;
        } else {
            return FALSE;
        }
    }

    public function getListOfDrawNumbers() {
        $this->db->select('result_number');
        $this->db->order_by("id", "DESC");
        $this->db->limit(8);
        $query = $this->db->get('prizebond_result_info');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $result[] = $row->result_number;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function getResultOfADraw($resultId) {

        $query = $this->db->get_where('prizebond_result_info', array('id' => $resultId));
        $getPrizeBondDrawInfo = $query->row();
        if (!$getPrizeBondDrawInfo)
            return FALSE;

        $data['draw_id'] = $getPrizeBondDrawInfo->id;
        $data['draw_num'] = $getPrizeBondDrawInfo->result_number;
        $data['draw_year'] = $getPrizeBondDrawInfo->result_date;
        $data['total_first_prize'] = $getPrizeBondDrawInfo->first_prize_number;
        $data['total_second_prize'] = $getPrizeBondDrawInfo->second_prize_number;
        $data['total_third_prize'] = $getPrizeBondDrawInfo->third_prize_number;
        $data['total_fourth_prize'] = $getPrizeBondDrawInfo->fourth_prize_number;
        $data['total_fifth_prize'] = $getPrizeBondDrawInfo->fifth_prize_number;
        $data['list_of_series'] = $getPrizeBondDrawInfo->result_series;

        $query = $this->db->get_where('prizebond_result_bond_list', array('prizebond_result_info_id' => $resultId));
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {
                if ($row->prize_position == '1st') {
                    $data['first_prize_data']['prize_money'] = $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position);
                    $data['first_prize_data']['winner_bond_num'] = $row->bond_number;
                } elseif ($row->prize_position == '2nd') {
                    $data['second_prize_data']['prize_money'] = $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position);
                    $data['second_prize_data']['winner_bond_num'] = $row->bond_number;
                } elseif ($row->prize_position == '3rd') {

                    $bondInfo = array(
                        'prize_money' => $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position),
                        'winner_bond_num' => $row->bond_number
                    );

                    $data['third_prize_data'][] = $bondInfo;
                } elseif ($row->prize_position == '4th') {
                    $bondInfo = array(
                        'prize_money' => $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position),
                        'winner_bond_num' => $row->bond_number
                    );
                    $data['fourth_prize_data'][] = $bondInfo;
                } else {
                    $bondInfo = array(
                        'prize_money' => $this->getPrizeAmountByResultIdAndPrizePosition($row->prizebond_result_info_id, $row->prize_position),
                        'winner_bond_num' => $row->bond_number
                    );
                    $data['fifth_prize_data'][] = $bondInfo;
                }
            }
        }

        return $data;
    }

    public function saveDrawInfoTablePushStatus($id) {
        $this->db->where('id', $id);
        return $this->db->update('prize_bond_draw_info', array('push_status' => 1));
    }

    public function savePushStatus($id) {
        $this->db->where('id', $id);
        return $this->db->update('device_info', array('push_send' => 1));
    }

    public function getLatestPrizebondDraw() {
        $query = $this->db->select('*')
                ->from('prize_bond_draw_info')
                ->where('push_status', 0)
                ->limit(1)
                ->order_by('id', "desc");
        if ($result = $this->db->get()->row()) {
            return $result;
        } else {
            return false;
        }
    }

    public function getPushId($deviceUuid) {
        $query = $this->db->get_where('device_info', array('device_uuid' => $deviceUuid));
        if ($query->num_rows() > 0) {
            return $query->row()->device_push_id;
        } else {
            return FALSE;
        }
    }

    public function updateDeviceInfo($postData) {

        if (isset($postData['utm_campaign']) && (!empty($postData['utm_campaign']))) {
            $campaignId = $this->getCampaignIdByName($postData['utm_campaign']);
            $postData['campaign_id'] = $campaignId;
        } else {
            $postData['campaign_id'] = 0;
        }

        $date_time = date('Y-m-d H:i:s');
        $blank_date = '0000-00-00 00:00:00';
        $query = $this->db->get_where('device_info', array('device_uuid' => $postData['device_uuid'], 'user_id' => $postData['user_id']));
        if ($query->num_rows() > 0) { // existing device
            $row = $query->row();
            $data = array(
                'device_push_id' => $postData['device_push_id'],
                'app_version_name' => !empty($postData['app_version_name']) ? $postData['app_version_name'] : 'n/a',
                'app_version_code' => !empty($postData['app_version_code']) ? $postData['app_version_code'] : 'n/a',
                'update_date' => $date_time,
                'inactive_date' => $blank_date,
            );

            if ($row->utm_campaign == NULL && (!empty($postData['utm_campaign']))) {
                $data['utm_campaign'] = $postData['utm_campaign'];
            }

            if ($row->utm_source == NULL && (!empty($postData['utm_source']))) {
                $data['utm_source'] = $postData['utm_source'];
            }

            if ($row->utm_medium == NULL && (!empty($postData['utm_medium']))) {
                $data['utm_medium'] = $postData['utm_medium'];
            }

            if ($row->utm_term == NULL && (!empty($postData['utm_term']))) {
                $data['utm_term'] = $postData['utm_term'];
            }

            if ($row->utm_content == NULL && (!empty($postData['utm_content']))) {
                $data['utm_content'] = $postData['utm_content'];
            }

            $data['device_density'] = $postData['device_density'];
            $data['device_width'] = $postData['device_width'];
            $data['device_height'] = $postData['device_height'];
            $data['campaign_id'] = $postData['campaign_id'];


            $data['device_manufacturer'] = $postData['device_manufacturer'];
            $data['device_brand'] = $postData['device_brand'];
            $data['device_product'] = $postData['device_product'];
            $data['device_model'] = $postData['device_model'];
            $data['device_os_version'] = $postData['device_os_version'];
            $data['device_api_version'] = $postData['device_api_version'];
            $data['gcm_status'] = 'active';
            $data['inactive_date'] = '0000-00-00';


            $this->db->update('device_info', $data, array('device_uuid' => $postData['device_uuid'], 'user_id' => $postData['user_id']));
        } else { // new device 
            $data = array(
                'device_density' => $postData['device_density'],
                'device_width' => $postData['device_width'],
                'device_height' => $postData['device_height'],
                'device_manufacturer' => $postData['device_manufacturer'],
                'device_brand' => $postData['device_brand'],
                'device_product' => $postData['device_product'],
                'device_model' => $postData['device_model'],
                'device_os_version' => $postData['device_os_version'],
                'device_api_version' => $postData['device_api_version'],
                'utm_campaign' => $postData['utm_campaign'],
                'utm_source' => $postData['utm_source'],
                'utm_medium' => $postData['utm_medium'],
                'utm_term' => $postData['utm_term'],
                'utm_content' => $postData['utm_content'],
                'device_push_id' => $postData['device_push_id'],
                'user_id' => !empty($postData['user_id']) ? $postData['user_id'] : 0,
                'campaign_id' => $postData['campaign_id'],
                'device_uuid' => $postData['device_uuid'],
                'app_version_name' => !empty($postData['app_version_name']) ? $postData['app_version_name'] : 'n/a',
                'app_version_code' => !empty($postData['app_version_code']) ? $postData['app_version_code'] : 'n/a',
                'device_type' => $postData['device_type'],
                'device_status' => 'inactive',
                'inactive_date' => $blank_date,
                'added_date' => $date_time
            );

            $this->db->insert('device_info', $data);
        }
        return TRUE;
    }

    private function getCampaignIdByName($name) {
        $this->db->select('id');
        $query = $this->db->get_where('campaign_list', array('name' => $name));
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            $this->db->insert('campaign_list', array('name' => $name));
            return $this->db->insert_id();
        }
    }

//    private function saveCampaignData($campaignData) {
//        $this->db->insert('campaign_list', array('name' => $campaignData));
//        return $this->db->insert_id();
//    }

    public function saveDeviceInfo($data) {
        $this->db->insert('device_info', $data);
    }

    public function updateDeviceActivityLog($postData) {

        $today = date('Y-m-d');
        $query = $this->db->get_where('device_info', array('device_uuid' => $postData['device_uuid'], 'user_id' => $postData['user_id']));
        $row = $query->row();

        if ($row) {
            $queryLog = $this->db->get_where('device_activity_log', array('device_uuid' => $postData['device_uuid'], 'user_id' => $postData['user_id'], 'DATE(update_date)' => $today));
            if ($queryLog->num_rows() == 0) {
                $dataLog = array(
                    'user_id' => !empty($postData['user_id']) ? $postData['user_id'] : 0,
                    'device_uuid' => $postData['device_uuid'],
                    'device_type' => $postData['device_type'],
                    'update_date' => $today,
                    'added_date' => $row->added_date
                );
                $this->db->insert('device_activity_log', $dataLog);
            }
            return TRUE;
        }

        return FALSE;
    }

    public function getProductUnitValueByProductIdAndAppId($productId) {
        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            return $query->row()->value;
        } else {
            return TRUE;
        }
    }

    public function getProductAttributeAndValue($productId) {

        $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
        if ($query->num_rows() > 0) {
            $data = array();
            foreach ($query->result() as $row) {
                $query1 = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                if ($query1->num_rows() > 0) {
                    $atrr = strtolower($query1->row()->name);
                    if (strpos($atrr, ' ') != FALSE) {
                        $atrr = str_replace(' ', '_', $atrr);
                    }
                    $data["$atrr"] = $row->value;
                }
            }
            return $data;
        }return FALSE;
    }

    //created by a teammate
    public function getProductInfoById($productId) {

        $query = $this->db->get_where('subscription_product_list', array('id' => $productId));

        if ($query->num_rows() > 0) {

            $data = new stdClass();
            $data->summary = $query->row();


            $query = $this->db->get_where('subscription_product_attribute_values', array('product_id' => $productId));
            if ($query->num_rows() > 0) {
                $attributes = array();
                foreach ($query->result() as $row) {
                    $query1 = $this->db->get_where('subscription_products_attributes', array('id' => $row->attribute_id));
                    if ($query1->num_rows() > 0) {
                        $atrr = strtolower($query1->row()->name);
                        if (strpos($atrr, ' ') != FALSE) {
                            $atrr = str_replace(' ', '_', $atrr);
                        }
                        $attributes["$atrr"] = $row->value;
                    }
                }
                $data->attributes = $attributes;
                return $data;
            }return FALSE;
        } else {
            return FALSE;
        }
    }

    public function saveSyncInfo($data) {
        $this->db->insert('user_prizebond_list', $data);
        return $this->db->insert_id();
    }

    public function verifyUser($userId, $varificationCode) {
        $query = $this->db->get_where('user', array('verify_code' => trim($varificationCode), 'id' => $userId));
        if ($query->num_rows() > 0) {
            $data = array();
            $data['verify_status'] = "YES";
            return $this->db->update('user', $data, array('id' => $userId));
        } else {
            return FALSE;
        }
    }

    public function getUserAllBondInfo($userId) {

        $query = $this->db->get_where('user_prizebond_list', array('user_id' => trim($userId)));
        if ($query->num_rows()) {
            $data = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $data[$i]['id'] = $row->id;
                $data[$i]['user_id'] = $row->user_id;
                $data[$i]['bond_series'] = $row->bond_series;
                $data[$i]['bond_number'] = $row->bond_number;
                $data[$i]['bond_note'] = !empty($row->bond_note) ? $row->bond_note : '';
                $data[$i]['bond_added_date'] = !empty($row->bond_added_by_user_date_time) ? $row->bond_added_by_user_date_time : '';
                if (!empty($row->image)) {
                    $data[$i]['image'] = base_url() . '/images/' . $userId . '/' . $row->bond_image_name;
                }
                $data[$i]['created_date_time'] = !empty($row->created_date_time) ? $row->created_date_time : '';
                $i++;
            }
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getAppDetails($device_density) {
        $this->db->select('app_name,app_icon,app_screen_shot,app_description,app_store_link')->from('app_details');
        $query = $this->db->get();
        $result = array();
        $appInfo = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $result['app_name'] = $row->app_name;
                $result['app_icon'] = base_url('images/app_icon') . '/' . $device_density . '/' . $row->app_icon;
                $result['app_screen_shot'] = base_url('images/app_screen_shots') . '/' . $device_density . '/' . $row->app_screen_shot;
                $result['app_description'] = str_replace("\\n", "\n", $row->app_description);
                $result['app_store_link'] = $row->app_store_link;
                $appInfo[] = $result;
            }
            return $appInfo;
        } else {
            return false;
        }
    }

    public function getWinnerInfoByUserId($userId) {

        $this->db->select('bond_number, bond_series, prizebond_result_info_id, prize_position, prize_amount')
                ->from('user_prizebond_won_list')->where('user_id', $userId);
        $this->db->order_by('prize_position', 'ASC');
        $query = $this->db->get();
        $data = array();
        $i = 0;
        if ($query->num_rows() > 0) {

            foreach ($query->result() as $row) {
                $data[$i]['bond_number'] = $row->bond_number;
                $data[$i]['bond_series'] = str_replace(' ', '', $row->bond_series);
                $data[$i]['prizebond_result_number'] = $this->getDrawById($row->prizebond_result_info_id);
                $data[$i]['prizebond_draw_result_date'] = $this->getDrawDateById($row->prizebond_result_info_id);
                $data[$i]['prize_position'] = $row->prize_position;
                $data[$i]['prize_amount'] = $row->prize_amount;
                $i++;
            }
            return $data;
        } else {
            return false;
        }
    }

    public function updateUserVerifyCounter($userId, $updateData = array()) {
        $this->db->update('verify_counter', $updateData, array('user_id' => $userId));
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function insert($table, $data = array()) {
        $this->db->insert($table, $data);
        return TRUE;
    }

    public function update($table, $data, $where) {
        $this->db->where($where);
        $this->db->update($table, $data);
        return TRUE;
    }

    public function getDeviceInfo($deviceUUID, $userId) {
        $query = $this->db->get_where('device_info', array('device_uuid' => $deviceUUID, 'user_id' => $userId));
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function doesExit($table, $where) {
        $this->db->select('id');
        $query = $this->db->get_where($table, $where);
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getVerifyCodeSentCounterForAUser($userId) {

        $query = $this->db->get_where('verify_counter', array('user_id' => trim($userId)));
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            $endDate = date('Y-m-d H:i:s');
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            if ($hourdiff > 24) {
                $this->db->delete('verify_counter', array('id' => $row->id));
                $this->getErrorCouponInfo($userId);
            } else {
                return $query->row_array();
            }
        } else {
            $this->getErrorCouponInfo($userId);
        }
    }

    public function getVerifyCodeSentCounterForAUser11($userId) {

        $query = $this->db->get_where('verify_counter', array('user_id' => trim($userId)));
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            $endDate = $row->update_date_time;
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            if ($hourdiff > 24) {
                $this->db->delete('subscription_error_coupon_tracking', array('id' => $row->id));
                $this->getErrorCouponInfo($userId);
            } else {
                return $query->row_array();
            }
        } else {
            $this->getErrorCouponInfo($userId);
        }
    }

    private function getErrorCouponInfo($userId) {
        $verifyCounterData = array(
            'user_id' => $userId,
            'counter' => 0,
            'create_date_time' => date('Y-m-d H:i:s')
        );

        $this->db->insert('verify_counter', $verifyCounterData);
        $query = $this->db->get_where('verify_counter', array('user_id' => trim($userId)));
        return $query->row_array();
    }

    public function checkVerifyCodeSendCounter($table, $where = array()) {

        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            $row = $query->row();
            $startDate = $row->create_date_time;
            $endDate = $row->update_date_time;
            $hourdiff = round((strtotime($endDate) - strtotime($startDate)) / 3600, 1);
            if ($hourdiff > 24) {
                $this->db->delete($table, array('id' => $row->id));
                return TRUE;
            } else {
                if ($row->counter >= 3 && $hourdiff < 24) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            }
        } else {
            return TRUE;
        }
    }

    public function allocateFreeProductToUserAfterSuccessfulRegistration($userId) {
        $query = $this->db->get_where('subscription_product_list', array('id' => 1));
        if ($query->num_rows()) {
            $productInfo = $query->row_array();
            $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
            $productPurchaseListData = array(
                'user_id' => $userId,
                'product_category_id' => $productInfo['product_category_id'],
                'product_type_id' => $productInfo['product_type_id'],
                'app_types_id' => $productInfo['app_types_id'],
                'product_id' => 1,
                'purchased_by' => 'sign up',
                'valid_start_datetime' => date('Y-m-d H:i:s'),
                'valid_end_datetime' => $validEndDateTime,
                'status' => 1,
                'created_datetime' => date('Y-m-d H:i:s')
            );
            $this->db->insert('subscription_product_purchase_list', $productPurchaseListData);
            return TRUE;
        }
        return TRUE;
    }

    public function getAUserParchasedSummaryInfo($userId) {

        $query = $this->db->get_where('subscription_product_purchase_list', array('user_id' => $userId));

        if ($query->num_rows() > 0) {
            $advertisement = 'ON';
            $total_bond = 0;
            foreach ($query->result() as $row) {

                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));

                if ($vadidEndDate > date('Y-m-d')) {

                    $attrValue = $this->getProductAttributeAndValue($row->product_id);

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'bond') {

                            $bonds = $this->getProductUnitValueByProductIdAndAppId($row->product_id);
                            $total_bond += (int) $bonds;
                        }
                    }

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'days') {
                            $advertisement = 'OFF';
                        }
                    }

                    if (!isset($attrValue['unit']) && !empty($attrValue['price'])) {
                        $advertisement = 'OFF';
                    }
                }
            }

            $final['advertisement'] = $advertisement;
            $final['total_user_purchased_prizebond'] = $total_bond;
            return $final;
        } else {
            return array();
        }
    }

    public function getAUserSubscriptionInfo($userId) {
        $subscription = new stdClass();
        $query = $this->db->get_where('subscription_product_purchase_list', array('user_id' => $userId));

        if ($query->num_rows() > 0) {
            $advertisement = 'ON';
            $total_bond = 0;

            foreach ($query->result() as $row) {

                if ($row->purchased_by != 'sign up') {
                    $advertisement = 'OFF';
                }

                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));

                if ($vadidEndDate > date('Y-m-d')) {

                    $productInfo = $this->getProductInfoById($row->product_id);


                    if ($productInfo->attributes) {

                        if ($productInfo->attributes['unit'] == 'bond') {
                            $total_bond += (int) $productInfo->attributes['unit_value'];
                        } elseif ($productInfo->attributes['unit'] == 'days') {

                            $advertisement = 'OFF';
                        }
                    }
                }
            }

            $advertisement = 'OFF'; // insturcted by Boss on 27 Apr 2017 5.01pm
            $subscription->advertisement = $advertisement;
            $subscription->bond_max_capacity = $total_bond;
            $subscription->bond_added = $this->getTotalBondNumersOfAUser($userId);
            return $subscription;
        } else {
            return FALSE;
        }
    }

    public function getAUsersWinInfo($userId) {
        $winInfo = new stdClass();
        $query = $this->db->get_where('user_prizebond_won_list', array('user_id' => $userId));

        if ($query->num_rows() > 0) {

            return $query->result();
        } else {
            return FALSE;
        }
    }

    public function getUserPurchaseStatusByRedeemCoupon($where /* $productId, $parchaseId */) {

        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {

            $advertisement = 'ON';
            $i = 0;
            foreach ($query->result() as $row) {
                $bondInfo = array();
                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));
                if ($vadidEndDate > date('Y-m-d')) {
                    $query = $this->db->get_where('subscription_product_list', array('id' => $row->product_id));

                    $attrValue = $this->getProductAttributeAndValue($row->product_list_id);

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'bond') {
                            $bondInfo['product_name'] = $query->row()->name;
                            $bondInfo['total_prizebond'] = $attrValue['unit_value'];
                            $bondInfo['valid_end_date'] = $row->valid_end_datetime;
                            $i++;
                        }
                    }

                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'days') {
                            $advertisement = 'OFF';
                        }
                    }

                    if (!isset($attrValue['unit']) && !empty($attrValue['price'])) {
                        $advertisement = 'OFF';
                    }
                }
            }

            $final['advertisement'] = $advertisement;
            $final['bond_info'] = $bondInfo;
            return $final;
        } else {
            return FALSE;
        }
    }

    public function get_data($table, $where) {
        $query = $this->db->get_where($table, $where);
        if ($query->result()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function checkExistingTrxID($txtId) {
        $query = $this->db->get_where('payment_bkash_queue', array('trxId' => $txtId, 'payment_status' => 'success'));
        if ($query->num_rows() == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getUserInfo($userId) {
        $query = $this->db->get_where('user', array('id' => $userId));
        if ($query->result()) {
            $row = $query->row_array();
            $data['user_id'] = $row['id'];
            $data['name'] = $row['name'];
            $data['email'] = $row['email'];
            $data['mobile_number'] = $row['mobile_number'];
            if (!empty($row['image'])) {
                $data['image'] = base_url('images') . '/' . $row['image'];
            }
            return $data;
        } else {
            return false;
        }
    }

    public function updateAPIKey($data) {
        $this->db->update('app_information', array('api_key' => $data['update_api_key']));
    }

    public function getAllAssociatedDevicesOfAUser($userId) {
        $query = $this->db->get_where('device_info', array('user_id' => $userId));
        if ($query->result()) {
            return $query->result();
        } else {
            return false;
        }
    }

    public function getAllPaymentMethod() {
        $this->db->select('name');
        $query = $this->db->get('subscription_payment_method_types');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $method[] = $row->name;
            }
            return $method;
        } else {
            return FALSE;
        }
    }

    private function getDrawById($id) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row()->result_number;
        } else {
            return FALSE;
        }
    }

    private function getDrawDateById($id) {
        $query = $this->db->get_where('prizebond_result_info', array('id' => $id));
        if ($query->num_rows() > 0) {
            return $query->row()->result_date;
        } else {
            return FALSE;
        }
    }

    //created by a teammate
    public function logActivity($type, $message) {

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

}
