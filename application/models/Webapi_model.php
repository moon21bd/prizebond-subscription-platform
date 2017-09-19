<?php

/**
 * Created by NetBeans
 * User: Raqibul Hasan Moon
 * Email: rhmoon21@gmail.com
 * Date: 2017-03-27
 */
class Webapi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUserInfoByEmailAndPassword($email, $password) {
        $this->db->select('id');
        $query = $this->db->get_where('user', array('email' => $email, 'password' => md5($password)));
        if ($query->result()) {
            return $query->row()->id;
        } else {
            return false;
        }
    }

    public function getUserInfoByMobileNumberAndPassword($mobileNumber, $password) {
        $this->db->select('id');
        $this->db->where("(mobile_number LIKE  '%$mobileNumber%')");
        $this->db->where('password', md5($password));
        $query = $this->db->get('user');
        if ($query->result()) {
            return $query->row()->id;
        } else {
            return false;
        }
    }

    public function updateUserVerificationCode($verificationCode, $userId, $mobileNumber) {
        $this->db->update('user', array('verify_code' => $verificationCode, 'mobile_number' => $mobileNumber), array('id' => $userId));
        return TRUE;
    }

    public function updateMobileVerificationCode($verificationCode, $userId) {
        $this->db->update('user', array('update_mobile_verification_code' => $verificationCode), array('id' => $userId));
        return TRUE;
    }

    public function updateEmailVerificationCode($verificationCode, $userId) {
        $this->db->update('user', array('update_email_verification_code' => $verificationCode), array('id' => $userId));
        return TRUE;
    }

    public function updateSyncInfo($userId) {
        $this->db->update('user_prizebond_list', array('sync_status' => 'ready', 'user_devcie_uuid' => NULL), array('user_id' => $userId));
        $this->db->select('id');
        $query = $this->db->get_where('user_prizebond_list', array('user_id' => $userId));
        if ($query->num_rows()) {
            return $query->num_rows();
        } else {
            return FALSE;
        }
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

    public function getBondsInfoWhereStatusReady($userId, $last_sync_hash, $device_uuid) {

        if (!empty($last_sync_hash)) {
            $this->db->update('user_prizebond_list', array('user_devcie_uuid' => $device_uuid, 'sync_status' => 'uploaded'), array('sync_hash' => $last_sync_hash));
        }

        $this->db->limit(100);
        $query = $this->db->get_where('user_prizebond_list', array('user_id' => $userId, 'sync_status' => 'ready'));
        if ($query->num_rows()) {
            foreach ($query->result_array() as $data) {
                $ids[] = $data['id'];
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
                $final[] = $result;
            }

            $final1['data'] = json_encode($final);
            $final1['ids'] = $ids;
            return $final1;
        } else {
            return FALSE;
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

    private function saveUserPrizeBond($userId, $prizePosition, $drawNumber, $deviceUUID, $bondNumber, $bondSeries, $bondResultInfoId = 0, $prizeAmount = 0) {
        $data = array(
            'user_id' => $userId,
            'prize_position' => $prizePosition,
            'draw_number' => $drawNumber,
            'prize_amount' => !empty($prizeAmount) ? $prizeAmount : NULL,
            'user_devcie_uuid' => $deviceUUID,
            'bond_number' => $bondNumber,
            'bond_series' => $bondSeries,
            'bond_added_by_user_date_time' => date('Y-m-d H:i:s')
        );

        $this->db->insert('user_prizebond_list', $data);
        $insertedId = $this->db->insert_id();

        if ($insertedId) {
            if ($prizePosition != NULL && $drawNumber != NULL) { // this is win bond
                $data = array(
                    'user_id' => $userId,
                    'user_prizebond_id' => $insertedId,
                    'bond_number' => $bondNumber,
                    'bond_series' => $bondSeries,
                    'prizebond_result_info_id' => $bondResultInfoId,
                    'prize_position' => $prizePosition,
                    'prize_amount' => $prizeAmount
                );
                $this->db->insert('user_prizebond_won_list', $data);
                $this->sendGCMPushToWinnerDevice($deviceUUID, $bondNumber);
            }
        }

        return TRUE;
    }

    public function insertWinningBondInfo($data) {
        $this->db->insert('user_prizebond_won_list', $data);
        return $this->db->insert_id();
    }

    public function savePrizeBondNumbers($user_id, $device_uuid, $prizebondNumbersArray, $bondSeries) {

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

                if ($query->num_rows() == 0) { // unique bond for the user
                    $drawInfoArray = $this->getDrawInfoByBondNumber($prizebondNumber);

                    if (count($drawInfoArray) > 0) {

                        $bondSeries = str_replace(' ', '', $bondSeries);

                        $drawSeriesArray = explode(',', $drawInfoArray['result_series']);

                        if (in_array($bondSeries, $drawSeriesArray)) { // match win bond
                            $this->saveUserPrizeBond($user_id, $drawInfoArray['prize_position'], $drawInfoArray['draw_number'], $device_uuid, $prizebondNumber, $bondSeries, $drawInfoArray['prizebond_result_info_id'], $drawInfoArray['prize_amount']);
                            $resultData[] = $this->getWinningBondAddedResponseArray($bondSeries, $prizebondNumber, $drawInfoArray['draw_number'], $drawInfoArray['prize_position'], $drawInfoArray['prize_amount'], $drawInfoArray['result_date']);
                        } else {
                            $this->saveUserPrizeBond($user_id, NULL, NULL, $device_uuid, $prizebondNumber, $bondSeries);
                            $resultData[] = $this->getNonWinningBondAddedResponseArray($bondSeries, $prizebondNumber);
                        }
                    } else {
                        $this->saveUserPrizeBond($user_id, NULL, NULL, $device_uuid, $prizebondNumber, $bondSeries);
                        $resultData[] = $this->getNonWinningBondAddedResponseArray($bondSeries, $prizebondNumber);
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
        }return FALSE;
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
    }

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
            //$this->gcm->status;
        }
        return TRUE;
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
                $this->db->delete('user_prizebond_list', $where);
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

    public function updateDeviceInfo($requestedData) {

        $date_time = date('Y-m-d H:i:s');
        $blank_date = '0000-00-00 00:00:00';
        $query = $this->db->get_where('device_info', array('device_uuid' => $requestedData['device_uuid']));
        if ($query->num_rows() > 0) {
            $result = $query->row();
            $data = array(
                'device_push_id' => $requestedData['device_push_id'],
                'app_version_name' => !empty($requestedData['app_version_name']) ? $requestedData['app_version_name'] : 'n/a',
                'app_version_code' => !empty($requestedData['app_version_code']) ? $requestedData['app_version_code'] : 'n/a',
                'device_status' => 'active',
                'inactive_date' => $blank_date,
            );

            if ($requestedData['user_id'] != 0) {
                $data['user_id'] = $requestedData['user_id'];
            } else {
                $data['device_status'] = 'inactive';
            }

            $this->db->where('device_uuid', $requestedData['device_uuid']);
            $this->db->update('device_info', $data);
        } else {
            $data = array(
                //'user_id' => $requestedData['user_id'],
                'device_push_id' => $requestedData['device_push_id'],
                'user_id' => !empty($requestedData['user_id']) ? $requestedData['user_id'] : 0,
                'device_uuid' => $requestedData['device_uuid'],
                'app_version_name' => !empty($requestedData['app_version_name']) ? $requestedData['app_version_name'] : 'n/a',
                'app_version_code' => !empty($requestedData['app_version_code']) ? $requestedData['app_version_code'] : 'n/a',
                'device_type' => $requestedData['device_type'],
                'device_status' => 'active',
                'inactive_date' => $blank_date,
                'added_date' => $date_time
            );
            $this->db->insert('device_info', $data);
        }

        return TRUE;
    }

    public function updateDeviceActivityLog($requestedData) {

        $today = date('Y-m-d');
        $query = $this->db->get_where('device_info', array('device_uuid' => $requestedData['device_uuid']));
        $result = $query->result();
        $queryLog = $this->db->get_where('device_activity_log', array('device_uuid' => $requestedData['device_uuid'], 'DATE(update_date)' => $today));
        if ($queryLog->num_rows() == 0) {
            if (is_array($result)) {
                $added_date1 = $result[0]->added_date;
                $added_date = explode(' ', $added_date1);
            }

            $dataLog = array(
                //'user_id' => $requestedData['user_id'],
                'user_id' => !empty($requestedData['user_id']) ? $requestedData['user_id'] : 0,
                'device_uuid' => $requestedData['device_uuid'],
                'device_type' => $requestedData['device_type'],
                'update_date' => $today,
                'added_date' => $added_date[0]
            );
            $this->db->insert('device_activity_log', $dataLog);
        }
        return TRUE;
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
                $data[$i]['device_uuid'] = $row->user_devcie_uuid;
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

    /**
     * Get user prizebond by user id - for web
     *
     * @param $userId
     * @param int $offset
     * @param $type
     * @return array|bool
     */
    public function getUserPrizeBonds($userId, $offset = 0, $view = 'date') {

        $this->db->select('*')->from('user_prizebond_list')->where('user_id', $userId);
        $this->db->limit(21, $offset);

        if($view == 'date'){
            $this->db->order_by('bond_added_by_user_date_time', 'DESC');
        }elseif($view == 'number'){
            $this->db->order_by('bond_number', 'ASC');
        }else{
            $this->db->order_by('bond_added_by_user_date_time', 'DESC');
        }

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $data[$i]['id'] = $row->id;
                $data[$i]['device_uuid'] = $row->user_devcie_uuid;
                $data[$i]['user_id'] = $row->user_id;
                $data[$i]['bond_series'] = $row->bond_series;
                $data[$i]['bond_number'] = $row->bond_number;
                $data[$i]['bond_note'] = !empty($row->bond_note) ? $row->bond_note : '';
                $data[$i]['bond_added_date'] = !empty($row->bond_added_by_user_date_time) ? $row->bond_added_by_user_date_time : '';
                if (!empty($row->image)) {
                    $data[$i]['image'] = base_url('images/'.$userId.'/'.$row->bond_image_name);
                }
                $data[$i]['created_date_time'] = !empty($row->created_date_time) ? $row->created_date_time : '';
                $data[$i]['prize_position'] = !empty($row->prize_position) ? $row->prize_position : '';
                $data[$i]['draw_number'] = !empty($row->draw_number) ? $row->draw_number : '';
                $data[$i]['prize_money'] = !empty($row->prize_amount) ? $row->prize_amount : '';
                $i++;
            }
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getUserPrizeBondsBySarch($userId, $bond_series, $number,  $view = 'date') {

        $this->db->select('*')->from('user_prizebond_list')->where('user_id', $userId)->where('bond_series', $bond_series);
        if(!empty($number)){
            $this->db->like('bond_number', $number);
        }

        if($view == 'date'){
            $this->db->order_by('bond_added_by_user_date_time', 'DESC');
        }elseif($view == 'number'){
            $this->db->order_by('bond_number', 'ASC');
        }else{
            $this->db->order_by('bond_added_by_user_date_time', 'DESC');
        }

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $data[$i]['id'] = $row->id;
                $data[$i]['device_uuid'] = $row->user_devcie_uuid;
                $data[$i]['user_id'] = $row->user_id;
                $data[$i]['bond_series'] = $row->bond_series;
                $data[$i]['bond_number'] = $row->bond_number;
                $data[$i]['bond_note'] = !empty($row->bond_note) ? $row->bond_note : '';
                $data[$i]['bond_added_date'] = !empty($row->bond_added_by_user_date_time) ? $row->bond_added_by_user_date_time : '';
                if (!empty($row->image)) {
                    $data[$i]['image'] = base_url('images/'.$userId.'/'.$row->bond_image_name);
                }
                $data[$i]['created_date_time'] = !empty($row->created_date_time) ? $row->created_date_time : '';
                $data[$i]['prize_position'] = !empty($row->prize_position) ? $row->prize_position : '';
                $data[$i]['draw_number'] = !empty($row->draw_number) ? $row->draw_number : '';
                $data[$i]['prize_money'] = !empty($row->prize_amount) ? $row->prize_amount : '';
                $i++;
            }
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getUserWiningPrizeBonds($userId, $offset = 0) {

        $this->db->select('bond_series,bond_number')->from('user_prizebond_list')->where('user_id', $userId);
       // $this->db->limit(20, $offset);
        $this->db->order_by('draw_number', 'DESC');
        $this->db->group_by('bond_number');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $resultData = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $drawInfoArray = $this->getDrawInfoByBondNumber($row->bond_number);
                if (count($drawInfoArray) > 0) {
                    $bondSeries = str_replace(' ', '', $row->bond_series);
                    $drawSeriesArray = explode(',', $drawInfoArray['result_series']);

                    if (in_array($bondSeries, $drawSeriesArray)) { // match win bond
                        $resultData[] = $this->getWinningBondAddedResponseArray($bondSeries, $row->bond_number, $drawInfoArray['draw_number'], $drawInfoArray['prize_position'], $drawInfoArray['prize_amount'], $drawInfoArray['result_date']);
                    }
                }
            }
            return $resultData;
        } else {
            return FALSE;
        }
    }

    public function getUserWiningPrizeBondsOLD($userId, $offset = 0) {

        $this->db->select('*')->from('user_prizebond_list')->where('user_id', $userId);
        $this->db->where('prize_position !=', '');
        $this->db->limit(20, $offset);
        $this->db->order_by('draw_number', 'DESC');

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $data = array();
            $i = 0;
            foreach ($query->result() as $row) {
                $data[$i]['id'] = $row->id;
                $data[$i]['device_uuid'] = $row->user_devcie_uuid;
                $data[$i]['user_id'] = $row->user_id;
                $data[$i]['bond_series'] = $row->bond_series;
                $data[$i]['bond_number'] = $row->bond_number;
                $data[$i]['bond_note'] = !empty($row->bond_note) ? $row->bond_note : '';
                $data[$i]['bond_added_date'] = !empty($row->bond_added_by_user_date_time) ? $row->bond_added_by_user_date_time : '';
                if (!empty($row->image)) {
                    $data[$i]['image'] = base_url('images/'.$userId.'/'.$row->bond_image_name);
                }
                $data[$i]['created_date_time'] = !empty($row->created_date_time) ? $row->created_date_time : '';
                $data[$i]['prize_position'] = !empty($row->prize_position) ? $row->prize_position : '';
                $data[$i]['draw_number'] = !empty($row->draw_number) ? $row->draw_number : '';
                $data[$i]['prize_money'] = !empty($row->prize_amount) ? $row->prize_amount : '';
                $i++;
            }
            return $data;
        } else {
            return FALSE;
        }
    }

    public function getUserCouponFailedCount($userId) {

        $this->db->select('*')->from('subscription_error_coupon_tracking')->where('user_id', $userId);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {

            return $query->row_array();

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
            // return $query->row_array();
            $row = $query->row();
            $startDate = $row->create_date_time;
            //$endDate = $row->update_date_time;
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

    public function saveSignUpPurchaseProduct($userId) {
        $query = $this->db->get_where('subscription_product_list', array('id' => 1));
        if ($query->num_rows()) {
            $productInfo = $query->row_array();
            $validEndDateTime = date('Y-m-d H:i:s', strtotime('+21 years'));
            $productPurchaseListData = array(
                'user_id' => $userId,
                'product_category_id' => $productInfo['product_category_id'],
                'product_type_id' => $productInfo['product_type_id'],
                'app_types_id' => $productInfo['app_types_id'],
                'product_list_id' => 1,
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

                    //$query = $this->db->get_where('subscription_product_list', array('id' => $row->product_list_id));
                    if(!empty($row->product_list_id)){
                        $attrValue = $this->getProductAttributeAndValue($row->product_list_id);
                    }else{
                        $attrValue = 0;
                    }


                    if (isset($attrValue['unit']) && !empty($attrValue['unit'])) {
                        if ($attrValue['unit'] == 'bond') {

                            $bonds = $this->getProductUnitValueByProductIdAndAppId($row->product_list_id);
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

    public function getUserPurchaseStatusByRedeemCoupon($where /* $productId, $parchaseId */) {

        $query = $this->db->get_where('subscription_product_purchase_list', $where);
        if ($query->num_rows() > 0) {

            $advertisement = 'ON';
            $i = 0;
            foreach ($query->result() as $row) {
                $bondInfo = array();
                $vadidEndDate = date('Y-m-d', strtotime($row->valid_end_datetime));
                if ($vadidEndDate > date('Y-m-d')) {
                    $query = $this->db->get_where('subscription_product_list', array('id' => $row->product_list_id));

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
        //$query = $this->db->get_where('payment_bkash_queue', array('trxId' => $txtId));
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

}
