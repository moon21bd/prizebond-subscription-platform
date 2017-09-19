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

class Admin extends Main {

    public function __construct() {
        parent::__construct();

        if ($this->checkHost()) {
            $this->checkAuth();
        }
        
        if($this->input->get('debug')==1){
            $this->output->enable_profiler(TRUE);
        }

        $this->load->model('admin_model');
        $this->load->model('global_model');
        $this->load->library('form_validation');
        $this->load->helper('global_helper');
        $this->load->helper('dashboard_helper');
        $this->load->helper('user_profile_info_helper');

        $this->initDateRange();
    }

    public function __destruct() {
        $this->db->close();
    }

    //showing dashboard
    public function index() {
        $data = array();

        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');

        $data['title'] = 'Dashboard';
        $this->load->view('admin/dashboard', $data);
    }

    public function live() {
        $data = array();
        $data['title'] = 'Live Statistics';
        //$data["orders"] = $this->generateOrderHistoryLive();
        //$data["users_data"] = $this->userActivityLive();
        //$data["top"] = $this->topDevicesLive();

        $this->load->view('admin/dashboard_live', $data);
    }

    public function dashboard_ajax() {
        header('Content-Type: application/json');

        $startDate = $this->session->userdata('start_date');
        $endDate = $this->session->userdata('end_date');
        $data["orders"] = $this->orderHistory($startDate, $endDate);
        $data["apps"] = $this->totalAppInstalledByVersionCode($startDate, $endDate);
        $data["sales"] = $this->totalSalesByPaymentMethods($startDate, $endDate);
        $data["usersData"] = $this->userActivity($startDate, $endDate);
        $data["devices"] = $this->totalDevices($startDate, $endDate);

        echo json_encode($data);
    }

    public function existingCheckDrawInfo() {
        $data = array();
        $data['title'] = 'Check Draw Info';
        $inputData = array();
        $existingData = '';
        if ($this->input->post()) {

            $this->form_validation->set_rules('first', 'first', 'trim');
            $this->form_validation->set_rules('third', 'third', 'trim');
            $this->form_validation->set_rules('fourth', 'fourth', 'trim');
            $this->form_validation->set_rules('fifth', 'fifth', 'trim');
            $this->form_validation->set_rules('draw_number', 'draw_number', 'trim');

            $uncommon_bond = '';
            if ($this->form_validation->run()) {

                $inputData['first_second_bonds'] = $this->input->post('first');
                $inputData['third_bonds'] = $this->input->post('third');
                $inputData['fourth_bonds'] = $this->input->post('fourth');
                $inputData['fifth_bonds'] = $this->input->post('fifth');
                $tatal = explode(' ', $this->input->post('fifth'));

                $trimmed_array = array_map('trim', $tatal);

                $inputData['total_fifth_number'] = count($tatal);
                $inputData['hash'] = md5(serialize($inputData));
                $existingData = $this->admin_model->getExistingData($this->input->post('draw_number'));
                $total2 = explode(' ', $existingData['fifth_bonds']);
                $inputData['existing_fifth_number'] = count($total2);

                //$uncommon_bond = array_intersect($trimmed_array, $total2);
                $uncommon_bond = array_diff($trimmed_array, $total2);
                if (is_array($uncommon_bond)) {
                    $uncommon_bond = implode(',', $uncommon_bond);
                }
            }
        }

        $data['input'] = $inputData;
        $data['output'] = $existingData;

        if (!empty($uncommon_bond)) {
            $data['uncommon'] = $uncommon_bond;
        }

        $data['prize_bond_info'] = $this->admin_model->getDrawNumber();
        $this->load->view('admin/check_draw_info', $data);
    }

    public function refactorDraw($id) {
        $data = array();
        $data['title'] = 'Draw Information Manage';

        $this->load->view('admin/draw_info/refactor_process', $data);
    }

    public function userStatus($id, $status) {

        if ($this->global_model->update('user', array('status' => $status), array('id' => $id))) {

            redirect('admin/userProfile/' . $id);
        }
    }

    //for deleteing testing users
    /*    public function userDelete($id) {
      $this->db->trans_start();
      if ($this->global_model->delete('user', array('id' => $id))) {

      $this->global_model->delete('device_info', array('user_id' => $id));
      $this->global_model->delete('forgot_verify_counter', array('user_id' => $id));
      $this->global_model->delete('subscription_coupon_redeemed_history', array('user_id' => $id));
      $this->global_model->delete('subscription_error_coupon_tracking', array('user_id' => $id));
      $this->global_model->delete('subscription_order_list', array('user_id' => $id));
      $this->global_model->delete('subscription_product_purchase_list', array('user_id' => $id));
      $this->global_model->delete('user_device_log', array('user_id' => $id));
      $this->global_model->delete('user_prizebond_list', array('user_id' => $id));
      $this->global_model->delete('user_prizebond_won_list', array('user_id' => $id));
      $this->global_model->delete('user_support_messages', array('sender_user_id' => $id));
      $this->global_model->delete('verify_counter', array('user_id' => $id));

      $this->db->trans_complete();
      if ($this->db->trans_status() === FALSE) {
      echo 'delete failed.';
      } else {
      echo 'Users data deleted.';
      }
      }
      } */

    public function saveWinnerPrizebondToUserPrizebondWonListTable() {
        $this->load->model('cron_model');
        $drawBondInfoArrayList = $this->cron_model->getDrawInfo();
        $message = '';
        if ($drawBondInfoArrayList) {
            foreach ($drawBondInfoArrayList as $drawInfoArray) {
                $drawSeries = explode(',', $drawInfoArray['series']);

                foreach ($drawInfoArray['bonds'] as $prizePostion => $bondArray) {
                    $bonds = join(',', $bondArray);
                    $bonds = str_replace("'", "", $bonds);

                    $userWinBondInfo = $this->cron_model->getUserWinBondsInfo($bonds);


                    if ($userWinBondInfo) {
                        foreach ($userWinBondInfo as $bondInfo) {

                            if (in_array($bondInfo->bond_series, $drawSeries)) {

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
                                $message = "Prizebond number " . $winBondInfo['bond_number'] . " is win. Please check Prizebond Draw Result.";
                                $this->sendGcmToWinnerDevice($bondInfo->user_id, $message);
                            }
                        }
                    }
                }
            }
        }

        exit;
    }

    public function sendTestPush() {
        $deviceUuid = '65e639c0e474c599';
        $message = 'Hello this is test notification';
        $this->load->library('gcm');
        $this->gcm->clearRecepients();
        $registrationIds = array();

        $devicePushInfo = $this->global_model->get_data('device_info', array('device_uuid' => $deviceUuid));
        $registrationIds[] = (string) $devicePushInfo['device_push_id'];

        $this->gcm->setRecepients($registrationIds);
        $payloadData = array(
            'prizebond' => 'Custom content'
        );
        $this->gcm->setData($payloadData);
        $this->gcm->setMessage($message);
        $this->gcm->setGroup($message);
        if ($this->gcm->send()) {
            echo 'Push notification Sent';
        } else {
            echo 'Failed to send push notification';
        }
    }

    public function updateDateRange_ajax() {
        $startDate = $this->input->post('start_date');
        $endDate = $this->input->post('end_date');

        if (!empty($startDate) && (!empty($endDate))) {
            $this->session->set_userdata('start_date', $startDate);
            $this->session->set_userdata('end_date', $endDate);
        }
        echo json_encode(array('type' => 'success'));
    }

//    private function processSeires($serieses = '') {
//        $explode = explode(',', $serieses);
//        $error = '';
//
//        foreach ($explode as $value) {
//            if (!($this->global_model->doesExist('series_list', array('name' => trim($value))))) {
//                $this->global_model->insert('series_list', array('name' => trim($value)));
//            } else {
//
//                $error .= $value;
//            }
//        }
//        $this->session->set_flashdata('success_msg', 'Your series has been successfully Inserted.</br>' . $error . ' Already exists in database so ' . $error . ' did not inserted.');
//        redirect('admin/checkSeries');
//    }

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

    private function initDateRange() {

        if (empty($this->session->userdata('start_date')) && (empty($this->session->userdata('end_date')))) {
            $this->session->set_userdata('start_date', date('Y-m-d', strtotime('-30 Days')));
            $this->session->set_userdata('end_date', date('Y-m-d'));
        }
    }

    private function userActivity($startDate, $endDate) {

        $data = array();

        $first = $startDate;
        $last = $endDate;

        $step = '+1 day';
        $output_format = 'F j';

        $dateArray = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dateArray[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        foreach ($dateArray as $key => $day) {

            $labels[] = $day;

            $queryStartDate = date("Y-m-d H:i:s", strtotime("$day"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+1 days", strtotime($queryStartDate)));

            //$endDate = date("Y-m-d H:i:s");
            //SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE `created_date_time` >= '2017-05-07 20:00' AND `created_date_time` <= '2017-05-07 20:05'
            //$sql = "SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE `created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate'";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["activity"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate'";

            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["registration"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `verify_status` = 'YES' AND ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `verify_status` = 'YES' AND (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["verification"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["bonds_added"][] = $row->TOTAL;
            }
        }

        $data["labels"] = $labels;
        return $data;
    }

    private function orderHistory($startDate, $endDate) {

        $data = array();
        $orderStatus = array(
            'pending',
            'completed',
            'failed'
        );

        $first = $startDate;
        $last = $endDate;

        $step = '+1 day';
        $output_format = 'F j';

        $dateArray = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dateArray[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        foreach ($dateArray as $key => $day) {

            $labels[] = $day;

            $queryStartDate = date("Y-m-d H:i:s", strtotime("$day"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+1 days", strtotime($queryStartDate)));


            foreach ($orderStatus as $status) {
                $sql = "SELECT COUNT(id) AS TOTAL FROM subscription_order_list WHERE `order_status`='$status' AND (`order_created_date_time` >= '$queryStartDate' AND `order_created_date_time` <= '$queryEndDate')";

                $query = $this->db->query($sql);
                foreach ($query->result() as $row) {
                    $data["data"][$status][] = $row->TOTAL;
                }
            }
        }
        $data["labels"] = $labels;
        return $data;
    }

    private function totalRegistrations($startDate, $endDate) {

        $data = array();

        $first = $startDate;
        $last = $endDate;

        $step = '+1 day';
        $output_format = 'F j';

        $dateArray = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dateArray[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        foreach ($dateArray as $key => $day) {

            $labels[] = $day;

            $queryStartDate = date("Y-m-d H:i:s", strtotime("$day"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+1 days", strtotime($queryStartDate)));

            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
//echo $this->db->last_query();
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"][] = $row->TOTAL;
            }
        }
        $data["labels"] = $labels;


        return $data;
    }

    private function totalBondAdded($startDate, $endDate) {

        $data = array();

        $first = $startDate;
        $last = $endDate;

        $step = '+1 day';
        $output_format = 'F j';

        $dateArray = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {

            $dateArray[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        foreach ($dateArray as $key => $day) {

            $labels[] = $day;

            $queryStartDate = date("Y-m-d H:i:s", strtotime("$day"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+1 days", strtotime($queryStartDate)));

            $sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
//echo $this->db->last_query();
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"][] = $row->TOTAL;
            }
        }
        $data["labels"] = $labels;


        return $data;
    }

    private function totalAppInstalledByVersionCode($startDate, $endDate) {
        $data = array();
        $sql = "SELECT DISTINCT `app_version_code`, COUNT(id) AS TOTAL FROM `device_info` WHERE `app_version_code` != 'n/a' AND (device_brand !='' OR device_brand != NULL) AND date(added_date) >= '$startDate' AND date(added_date) <= '$endDate' GROUP BY app_version_code ORDER BY LENGTH(`app_version_code`) ASC, `app_version_code` ASC";

        $query = $this->db->query($sql);

        foreach ($query->result() as $row) {
            $data["data"][] = $row->TOTAL;
            $data["labels"][] = $row->app_version_code;
        }

        return $data;
    }

//    private function totalAppInstalledByVersionCode($startDate, $endDate) {
//        $data = array();
//        $sql1 = "SELECT `app_version_code` FROM `device_info` WHERE `app_version_code` != 'n/a' GROUP BY `app_version_code` ORDER BY `app_version_code` ASC ";
//        $query1 = $this->db->query($sql1);
//
//        foreach ($query1->result() as $row) {
//            $appVersioCodeArray[] = $row->app_version_code;
//        }
//        asort($appVersioCodeArray);
//
//        foreach ($appVersioCodeArray as $versionCode) {
//
//            $labels[] = $versionCode;
//
//            $sql = "SELECT COUNT(id) AS TOTAL FROM device_info WHERE `app_version_code`='$versionCode' AND date(added_date) >= '$startDate' AND date(added_date) <= '$endDate'";
//            $query = $this->db->query($sql);
//            foreach ($query->result() as $row) {
//                $data["data"][] = $row->TOTAL;
//            }
//            $data["labels"] = $labels;
//        }
//
//
//        return $data;
//    }

    private function totalSalesByPaymentMethods($startDate, $endDate) {
        $data = array();

        $paymentMethodArray = $this->admin_model->getPaymentMethods();
        $total = 0;
        foreach ($paymentMethodArray as $paymentMethod) {
            $payment = $paymentMethod->name;
            $labels[] = $payment;
            $sql = "SELECT COUNT(id) AS TOTAL FROM subscription_product_purchase_list WHERE purchased_by = '$payment' AND date(created_datetime) >= '$startDate' AND date(created_datetime) <= '$endDate'";
            $query = $this->db->query($sql);

            foreach ($query->result() as $row) {
                $data["data"][] = $row->TOTAL;
                $total += $row->TOTAL;
            }

            $data["labels"] = $labels;
        }
        $data["total"] = $total;
        return $data;
    }

    private function totalDevices($startDate, $endDate) {
        $data = array();
        $sql = "SELECT COUNT(id) AS TOTAL,device_brand,device_model FROM device_info WHERE (device_brand !='' OR device_brand != NULL) AND date(added_date) >= '$startDate' AND date(added_date) <= '$endDate' GROUP BY device_brand,device_model ORDER BY TOTAL DESC LIMIT 10";
        $query = $this->db->query($sql);
        $total = 0;
        foreach ($query->result() as $row) {
            $data["data"][] = $row->TOTAL;
            $total += $row->TOTAL;
            if ($row->device_model != NULL) {
                $data["labels"][] = $row->device_brand . ' (' . $row->device_model . ')';
            } else {
                $data["labels"][] = $row->device_brand;
            }
        }
        $data["total"] = $total;
        return $data;
    }

    public function serverSentEvent() {

        if( ! $this->input->get("test")){
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
        }
        
        $data["orders"] = $this->generateOrderHistoryLive();
        $data["users_data"] = $this->userActivityLive();
        $data["top_devices"] = $this->topDevicesLive();
        $data["db_stats"] = $this->mysqlDatabaseStatisticsLive();
        $data["app_installed_by_version_code"] = $this->appInstalledByVersionCodeLive();
        $data["app_installed_by_campaign_names"] = $this->appInstalledByCampaignNamesLive();
        $data["app_installed_by_campaign_sources"] = $this->appInstalledByCampaignSourceLive();
        $data["app_installed_by_campaign_medium"] = $this->appInstalledByCampaignMediumLive();
        
        if( ! $this->input->get("test")){
            echo 'data: ' . json_encode($data) . "\n\n";
            flush();
        }
        else{
            echo "<pre>";
            print_r($data);
        }

        
    }

    private function generateOrderHistoryLive() {

        $data = array();
        $orderStatus = array(
            'pending',
            'completed',
            'failed'
        );

        $minArray = array(
            60,
            50,
            40,
            30,
            20,
            10,
            0
        );

        foreach ($minArray as $min) {

            $labels[] = date("g:i a", strtotime("-$min minute"));

            $queryStartDate = date("Y-m-d H:i:s", strtotime("-$min minutes"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+10 minutes", strtotime($queryStartDate)));

            foreach ($orderStatus as $status) {
                //$sql = "SELECT COUNT(id) AS TOTAL FROM subscription_order_list WHERE `order_status`='$status' AND ((`order_created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`order_created_date_time` < NOW()))";
                $sql = "SELECT COUNT(id) AS TOTAL FROM subscription_order_list WHERE `order_status`='$status' AND (`order_created_date_time` >= '$queryStartDate' AND `order_created_date_time` <= '$queryEndDate')";

                $query = $this->db->query($sql);
                foreach ($query->result() as $row) {
                    $data["data"][$status][] = $row->TOTAL;
                }
            }
        }

        $data["labels"] = $labels;
        return $data;
    }

    private function userActivityLive() {

        $data = array();

        $minArray = array(
            60,
            50,
            40,
            30,
            20,
            10,
            0
        );

        foreach ($minArray as $min) {

            $labels[] = date("g:i a", strtotime("-$min minute"));

            $queryStartDate = date("Y-m-d H:i:s", strtotime("-$min minutes"));
            $queryEndDate = date("Y-m-d H:i:s", strtotime("+10 minutes", strtotime($queryStartDate)));

            //$endDate = date("Y-m-d H:i:s");
            //SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE `created_date_time` >= '2017-05-07 20:00' AND `created_date_time` <= '2017-05-07 20:05'
            //$sql = "SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user_activity_logs WHERE `created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate'";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["activity"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate'";

            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["registration"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `verify_status` = 'YES' AND ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user WHERE `verify_status` = 'YES' AND (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["verification"][] = $row->TOTAL;
            }

            //$sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE ((`created_date_time` > DATE_SUB(NOW(), INTERVAL $min MINUTE)) AND (`created_date_time` < NOW()))";
            $sql = "SELECT COUNT(id) AS TOTAL FROM user_prizebond_list WHERE (`created_date_time` >= '$queryStartDate' AND `created_date_time` <= '$queryEndDate')";
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $data["data"]["bonds_added"][] = $row->TOTAL;
            }
        }

        $data["labels"] = $labels;
        return $data;
    }

    private function topDevicesLive() {

        $sql = "SELECT COUNT(id) AS TOTAL,device_brand,device_model FROM device_info WHERE (device_brand !='' OR device_brand != NULL) GROUP BY device_brand,device_model ORDER BY TOTAL DESC LIMIT 10";
        $query = $this->db->query($sql);
        $total = 0;
        foreach ($query->result() as $row) {
            
            if ($row->device_model != NULL) {
                $data["labels"][] = $row->device_brand . ' (' . $row->device_model . ')';
            } else {
                $data["labels"][] = $row->device_brand;
            }
            
            $data["data"][] = $row->TOTAL;
        }
        return $data;
    }
    
    
    private function appInstalledByVersionCodeLive(){
        
        $queryStartDate = date("Y-m-d H:i:s", strtotime("-720 minutes"));
        $queryEndDate = date("Y-m-d H:i:s", strtotime("+720 minutes", strtotime($queryStartDate)));
        
        $data = array();
        $sql = "SELECT DISTINCT `app_version_code`, COUNT(id) AS TOTAL FROM `device_info` WHERE `app_version_code` != 'n/a' AND (device_brand !='' OR device_brand != NULL) AND added_date >= '$queryStartDate' AND added_date <= '$queryEndDate' GROUP BY app_version_code ORDER BY LENGTH(`app_version_code`) ASC, `app_version_code` ASC";

        $query = $this->db->query($sql);

        foreach ($query->result() as $row) {
            $data["data"][] = $row->TOTAL;
            $data["labels"][] = "version : ".$row->app_version_code;
        }
        
        return $data;
    }
    
    
    private function appInstalledByCampaignNamesLive(){
        
        $queryStartDate = date("Y-m-d H:i:s", strtotime("-720 minutes"));
        $queryEndDate = date("Y-m-d H:i:s", strtotime("+720 minutes", strtotime($queryStartDate)));
        $data = array();
        
        $sql = "SELECT `utm_campaign`, COUNT(id) AS TOTAL FROM `device_info` WHERE `utm_campaign` != 'NULL' AND `added_date` >= '$queryStartDate' AND `added_date` <= '$queryEndDate' GROUP BY `utm_campaign`";
        $query = $this->db->query($sql);

        foreach ($query->result() as $row) {
            $data["data"][] = $row->TOTAL;
            
            if($row->utm_campaign == ''){
                $data["labels"][] = "Unknown";
            }
            else {
                $data["labels"][] = $row->utm_campaign;
            }
        }
        
        return $data;
    }
    
    private function appInstalledByCampaignSourceLive(){
        
        $queryStartDate = date("Y-m-d H:i:s", strtotime("-720 minutes"));
        $queryEndDate = date("Y-m-d H:i:s", strtotime("+720 minutes", strtotime($queryStartDate)));
        $data = array();
        
        $sql = "SELECT `utm_source`, COUNT(id) AS TOTAL FROM `device_info` WHERE `utm_source` != 'NULL' AND `added_date` >= '$queryStartDate' AND `added_date` <= '$queryEndDate' GROUP BY `utm_source`";
        $query = $this->db->query($sql);

        foreach ($query->result() as $row) {
            $data["data"][] = $row->TOTAL;
            
            if($row->utm_source == ''){
                $data["labels"][] = "Unknown";
            }
            else {
                $data["labels"][] = $row->utm_source;
            }
        }
        
        return $data;
    }
    
    private function appInstalledByCampaignMediumLive(){
        
        $queryStartDate = date("Y-m-d H:i:s", strtotime("-720 minutes"));
        $queryEndDate = date("Y-m-d H:i:s", strtotime("+720 minutes", strtotime($queryStartDate)));
        $data = array();
        
        $sql = "SELECT `utm_medium`, COUNT(id) AS TOTAL FROM `device_info` WHERE `utm_medium` != 'NULL' AND `added_date` >= '$queryStartDate' AND `added_date` <= '$queryEndDate' GROUP BY `utm_medium`";
        $query = $this->db->query($sql);

        foreach ($query->result() as $row) {
            $data["data"][] = $row->TOTAL;
            
            if($row->utm_medium == ''){
                $data["labels"][] = "Unknown";
            }
            else {
                $data["labels"][] = $row->utm_medium;
            }
        }
        
        return $data;
    }
    
    private function mysqlDatabaseStatisticsLive(){
        $sql = "SHOW STATUS WHERE `variable_name` LIKE '%threads%'";
        $query = $this->db->query($sql);
        foreach ($query->result() as $row) {
            
            if($row->Variable_name != 'Threads_created'){
                $data["data"][] = $row->Value;
                $data["labels"][] = $row->Variable_name;
            }
        }
        
        return $data;
    }

}
